<?php
// janeth.php – Main API (inventory + expenses)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once 'db.php';

function send($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// ─────────────────────────────────────────────
//  GET
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Products list
    if (isset($_GET['products'])) {
        $stmt = $pdo->query("SELECT id, name, category, price, low_stock_threshold FROM products ORDER BY category, name");
        send(['products' => $stmt->fetchAll()]);
    }

    // Distinct dates that have records
    if (isset($_GET['list_dates'])) {
        $stmt = $pdo->query("SELECT DISTINCT record_date FROM janeth_records ORDER BY record_date DESC");
        send(['dates' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
    }

    // Records for a specific date
    if (isset($_GET['date'])) {
        $date = $_GET['date'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) send(['error' => 'Invalid date format'], 400);

        $stmt = $pdo->prepare("
            SELECT
                p.id            AS product_id,
                p.name          AS product_name,
                p.category      AS product_category,
                p.price         AS price,
                p.low_stock_threshold,
                COALESCE(jr.yesterday_qty, 0) AS yesterday_qty,
                COALESCE(jr.stock_in,      0) AS stock_in,
                COALESCE(jr.remaining_qty, 0) AS remaining_qty,
                COALESCE(jr.sold,          0) AS sold
            FROM products p
            LEFT JOIN janeth_records jr ON jr.product_id = p.id AND jr.record_date = ?
            ORDER BY p.category, p.name
        ");
        $stmt->execute([$date]);
        send(['records' => $stmt->fetchAll()]);
    }

    // ── Expenses for a date ──
    if (isset($_GET['expenses'])) {
        $date = $_GET['expenses'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) send(['error' => 'Invalid date format'], 400);
        $stmt = $pdo->prepare("SELECT * FROM daily_expenses WHERE expense_date = ? ORDER BY created_at DESC");
        $stmt->execute([$date]);
        send(['expenses' => $stmt->fetchAll()]);
    }

    // ── Analytics: top/bottom sellers over a date range ──
    if (isset($_GET['analytics'])) {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            send(['error' => 'Invalid date range'], 400);
        }
        $stmt = $pdo->prepare("
            SELECT
                p.id, p.name, p.category, p.price,
                SUM(jr.sold)          AS total_sold_qty,
                SUM(jr.sold * p.price) AS total_sold_peso,
                SUM(jr.stock_in)      AS total_stock_in,
                COUNT(DISTINCT jr.record_date) AS days_recorded
            FROM products p
            LEFT JOIN janeth_records jr ON jr.product_id = p.id
                AND jr.record_date BETWEEN ? AND ?
            GROUP BY p.id, p.name, p.category, p.price
            ORDER BY total_sold_peso DESC
        ");
        $stmt->execute([$from, $to]);
        $rows = $stmt->fetchAll();

        // Expenses in range
        $expStmt = $pdo->prepare("SELECT SUM(amount) AS total FROM daily_expenses WHERE expense_date BETWEEN ? AND ?");
        $expStmt->execute([$from, $to]);
        $totalExpenses = (float)($expStmt->fetch()['total'] ?? 0);

        send(['analytics' => $rows, 'total_expenses' => $totalExpenses, 'from' => $from, 'to' => $to]);
    }

    send(['error' => 'Missing parameters'], 400);
}

// ─────────────────────────────────────────────
//  POST
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // ── Save inventory records ──
    if (isset($input['date']) && isset($input['records'])) {
        $date    = $input['date'];
        $records = $input['records'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) send(['error' => 'Invalid date format'], 400);

        try {
            $pdo->beginTransaction();
            foreach ($records as $rec) {
                $yesterday = (int)($rec['yesterday_qty'] ?? 0);
                $stockIn   = (int)($rec['stock_in']      ?? 0);
                $remaining = (int)($rec['remaining_qty'] ?? 0);
                $sold      = max(0, ($yesterday + $stockIn) - $remaining);

                $stmt = $pdo->prepare("
                    INSERT INTO janeth_records
                        (record_date, product_id, yesterday_qty, stock_in, remaining_qty, sold)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        yesterday_qty = VALUES(yesterday_qty),
                        stock_in      = VALUES(stock_in),
                        remaining_qty = VALUES(remaining_qty),
                        sold          = VALUES(sold)
                ");
                $stmt->execute([$date, (int)$rec['product_id'], $yesterday, $stockIn, $remaining, $sold]);
            }
            $pdo->commit();
            send(['success' => true, 'message' => "Saved for $date"]);
        } catch (Exception $e) {
            $pdo->rollBack();
            send(['error' => $e->getMessage()], 500);
        }
    }

    // ── Save expense ──
    if (isset($input['save_expense'])) {
        $date   = $input['expense_date']  ?? '';
        $cat    = trim($input['category'] ?? 'General');
        $desc   = trim($input['description'] ?? '');
        $amount = (float)($input['amount'] ?? 0);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) send(['error' => 'Invalid date'], 400);
        if (empty($desc))    send(['error' => 'Description required'], 400);
        if ($amount <= 0)    send(['error' => 'Amount must be > 0'], 400);

        $stmt = $pdo->prepare("INSERT INTO daily_expenses (expense_date, category, description, amount) VALUES (?,?,?,?)");
        $stmt->execute([$date, $cat, $desc, $amount]);
        send(['success' => true, 'id' => $pdo->lastInsertId()]);
    }

    // ── Delete expense ──
    if (isset($input['delete_expense'])) {
        $id = (int)$input['delete_expense'];
        $pdo->prepare("DELETE FROM daily_expenses WHERE id = ?")->execute([$id]);
        send(['success' => true]);
    }

    // ── Update price (staff allowed) ──
    if (isset($input['update_price'])) {
        $id    = (int)$input['product_id'];
        $price = (float)$input['price'];
        if ($price < 0) send(['error' => 'Price cannot be negative'], 400);
        $pdo->prepare("UPDATE products SET price = ? WHERE id = ?")->execute([$price, $id]);
        send(['success' => true]);
    }

    send(['error' => 'Invalid payload'], 400);
}

send(['error' => 'Method not allowed'], 405);