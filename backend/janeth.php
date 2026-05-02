<?php
// janeth.php – Main API (inventory + expenses + suppliers + liquidation)
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

// Require login for all API calls
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

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

    // ── Products list (only non-deleted) ──
    if (isset($_GET['products'])) {
        $page = $_GET['page'] ?? 'all'; // 'input' or 'dashboard' for visibility filter
        if ($page === 'input') {
            $stmt = $pdo->query("SELECT id, name, category, selling_price AS price, low_stock_threshold, visible_input, visible_dashboard FROM products WHERE is_deleted=0 AND visible_input=1 ORDER BY category, name");
        } elseif ($page === 'dashboard') {
            $stmt = $pdo->query("SELECT id, name, category, selling_price AS price, low_stock_threshold, visible_input, visible_dashboard FROM products WHERE is_deleted=0 AND visible_dashboard=1 ORDER BY category, name");
        } else {
            $stmt = $pdo->query("SELECT id, name, category, selling_price AS price, low_stock_threshold, visible_input, visible_dashboard FROM products WHERE is_deleted=0 ORDER BY category, name");
        }
        send(['products' => $stmt->fetchAll()]);
    }

    // ── Deleted products ──
    if (isset($_GET['deleted_products'])) {
        $stmt = $pdo->query("SELECT id, name, category, selling_price AS price, deleted_at FROM products WHERE is_deleted=1 ORDER BY deleted_at DESC");
        send(['products' => $stmt->fetchAll()]);
    }

    // ── Suppliers ──
    if (isset($_GET['suppliers'])) {
        $stmt = $pdo->query("SELECT * FROM suppliers ORDER BY id");
        send(['suppliers' => $stmt->fetchAll()]);
    }

    // ── Distinct dates with records ──
    if (isset($_GET['list_dates'])) {
        $stmt = $pdo->query("SELECT DISTINCT record_date FROM janeth_records ORDER BY record_date DESC");
        send(['dates' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
    }

    // ── Records for a specific date (dashboard-visible products only unless all=1) ──
    if (isset($_GET['date'])) {
        $date    = $_GET['date'];
        $forPage = $_GET['for'] ?? 'input'; // 'input' or 'dashboard'
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) send(['error' => 'Invalid date format'], 400);

        $visCol = $forPage === 'dashboard' ? 'visible_dashboard' : 'visible_input';

        $stmt = $pdo->prepare("
            SELECT
                p.id              AS product_id,
                p.name            AS product_name,
                p.category        AS product_category,
                p.selling_price   AS price,
                p.low_stock_threshold,
                p.visible_input,
                p.visible_dashboard,
                COALESCE(jr.yesterday_qty, 0) AS yesterday_qty,
                COALESCE(jr.stock_in,      0) AS stock_in,
                COALESCE(jr.remaining_qty, 0) AS remaining_qty,
                COALESCE(jr.sold,          0) AS sold
            FROM products p
            LEFT JOIN janeth_records jr ON jr.product_id = p.id AND jr.record_date = ?
            WHERE p.is_deleted = 0 AND p.$visCol = 1
            ORDER BY p.category, p.name
        ");
        $stmt->execute([$date]);

        // Also get stock entries for this date
        $seStmt = $pdo->prepare("
            SELECT se.*, s.name AS supplier_name, p.name AS product_name,
                   (se.qty * se.cost_price) AS total_cost
            FROM stock_entries se
            JOIN suppliers s ON s.id = se.supplier_id
            JOIN products p ON p.id = se.product_id
            WHERE se.record_date = ?
            ORDER BY se.product_id, se.id
        ");
        $seStmt->execute([$date]);

        send(['records' => $stmt->fetchAll(), 'stock_entries' => $seStmt->fetchAll()]);
    }

    // ── Stock entries for a date / product ──
    if (isset($_GET['stock_entries'])) {
        $date = $_GET['stock_entries'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) send(['error' => 'Invalid date format'], 400);
        $stmt = $pdo->prepare("
            SELECT se.*, p.name AS product_name, s.name AS supplier_name,
                   (se.qty * se.cost_price) AS total_cost
            FROM stock_entries se
            JOIN products p ON p.id = se.product_id
            JOIN suppliers s ON s.id = se.supplier_id
            WHERE se.record_date = ?
            ORDER BY se.product_id, se.id
        ");
        $stmt->execute([$date]);
        send(['stock_entries' => $stmt->fetchAll()]);
    }

    // ── Expenses for a date ──
    if (isset($_GET['expenses'])) {
        $date = $_GET['expenses'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) send(['error' => 'Invalid date format'], 400);
        $stmt = $pdo->prepare("SELECT * FROM daily_expenses WHERE expense_date = ? ORDER BY created_at DESC");
        $stmt->execute([$date]);
        send(['expenses' => $stmt->fetchAll()]);
    }

    // ── Liquidation for a date ──
    if (isset($_GET['liquidation'])) {
        $date = $_GET['liquidation'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) send(['error' => 'Invalid date format'], 400);
        $stmt = $pdo->prepare("SELECT * FROM liquidations WHERE liquidation_date = ?");
        $stmt->execute([$date]);
        $liq = $stmt->fetch();
        send(['liquidation' => $liq ?: null]);
    }

    // ── Analytics: top/bottom sellers over date range (weekly now only last 7 days) ──
    if (isset($_GET['analytics'])) {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            send(['error' => 'Invalid date range'], 400);
        }
        $stmt = $pdo->prepare("
            SELECT
                p.id, p.name, p.category, p.selling_price AS price,
                SUM(jr.sold)                    AS total_sold_qty,
                SUM(jr.sold * p.selling_price)  AS total_sold_peso,
                SUM(jr.stock_in)                AS total_stock_in,
                COUNT(DISTINCT jr.record_date)  AS days_recorded
            FROM products p
            LEFT JOIN janeth_records jr ON jr.product_id = p.id
                AND jr.record_date BETWEEN ? AND ?
            WHERE p.is_deleted = 0 AND p.visible_dashboard = 1
            GROUP BY p.id, p.name, p.category, p.selling_price
            ORDER BY total_sold_peso DESC
        ");
        $stmt->execute([$from, $to]);
        $rows = $stmt->fetchAll();

        $expStmt = $pdo->prepare("SELECT SUM(amount) AS total FROM daily_expenses WHERE expense_date BETWEEN ? AND ?");
        $expStmt->execute([$from, $to]);
        $totalExpenses = (float)($expStmt->fetch()['total'] ?? 0);

        // Total stock cost in range
        $costStmt = $pdo->prepare("SELECT SUM(qty * cost_price) AS total FROM stock_entries WHERE record_date BETWEEN ? AND ?");
        $costStmt->execute([$from, $to]);
        $totalCost = (float)($costStmt->fetch()['total'] ?? 0);

        // Weekly sales – LAST 7 DAYS within range (or less)
        $weeklyStmt = $pdo->prepare("
            SELECT record_date, SUM(jr.sold * p.selling_price) AS day_sales
            FROM janeth_records jr
            JOIN products p ON p.id = jr.product_id
            WHERE jr.record_date BETWEEN ? AND ?
            GROUP BY record_date
            ORDER BY record_date DESC
            LIMIT 7
        ");
        $weeklyStmt->execute([$from, $to]);
        $weekly = array_reverse($weeklyStmt->fetchAll()); // ascending order for chart

        // Monthly sales (aggregate by month)
        $monthlyStmt = $pdo->prepare("
            SELECT DATE_FORMAT(jr.record_date,'%Y-%m') AS month,
                   SUM(jr.sold * p.selling_price) AS month_sales
            FROM janeth_records jr
            JOIN products p ON p.id = jr.product_id
            WHERE jr.record_date BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(jr.record_date,'%Y-%m')
            ORDER BY month ASC
        ");
        $monthlyStmt->execute([$from, $to]);
        $monthly = $monthlyStmt->fetchAll();

        send([
            'analytics'       => $rows,
            'total_expenses'  => $totalExpenses,
            'total_stock_cost'=> $totalCost,
            'weekly'          => $weekly,
            'monthly'         => $monthly,
            'from'            => $from,
            'to'              => $to
        ]);
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
                    INSERT INTO janeth_records (record_date, product_id, yesterday_qty, stock_in, remaining_qty, sold)
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

    // ── Save stock entry (supplier purchase) ──
    if (isset($input['save_stock_entry'])) {
        $date       = $input['entry_date']   ?? '';
        $productId  = (int)($input['product_id'] ?? 0);
        $supplierId = (int)($input['supplier_id'] ?? 1);
        $qty        = (int)($input['qty']        ?? 0);
        $costPrice  = (float)($input['cost_price'] ?? 0);
        $notes      = trim($input['notes'] ?? '');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) send(['error' => 'Invalid date'], 400);
        if ($productId <= 0)   send(['error' => 'Product required'], 400);
        if ($supplierId <= 0)  send(['error' => 'Supplier required'], 400);
        if ($qty <= 0)         send(['error' => 'Qty must be > 0'], 400);
        if ($costPrice < 0)    send(['error' => 'Cost price cannot be negative'], 400);

        $stmt = $pdo->prepare("INSERT INTO stock_entries (record_date, product_id, supplier_id, qty, cost_price, notes) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$date, $productId, $supplierId, $qty, $costPrice, $notes ?: null]);
        send(['success' => true, 'id' => $pdo->lastInsertId()]);
    }

    // ── Delete stock entry ──
    if (isset($input['delete_stock_entry'])) {
        $id = (int)$input['delete_stock_entry'];
        $pdo->prepare("DELETE FROM stock_entries WHERE id = ?")->execute([$id]);
        send(['success' => true]);
    }

    // ── Save expense ──
    if (isset($input['save_expense'])) {
        $date   = $input['expense_date']  ?? '';
        $cat    = trim($input['category'] ?? 'General');
        $desc   = trim($input['description'] ?? '');
        $amount = (float)($input['amount'] ?? 0);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) send(['error' => 'Invalid date'], 400);
        if (empty($desc))  send(['error' => 'Description required'], 400);
        if ($amount <= 0)  send(['error' => 'Amount must be > 0'], 400);
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

    // ── Save / update liquidation ──
    if (isset($input['save_liquidation'])) {
        $date        = $input['liquidation_date'] ?? '';
        $opening     = (float)($input['opening_cash']   ?? 0);
        $cashSales   = (float)($input['cash_sales']     ?? 0);
        $expenses    = (float)($input['total_expenses'] ?? 0);
        $stockCost   = (float)($input['stock_cost']     ?? 0);
        $actualCash  = (float)($input['actual_cash']    ?? 0);
        $notes       = trim($input['notes'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) send(['error' => 'Invalid date'], 400);
        $stmt = $pdo->prepare("
            INSERT INTO liquidations (liquidation_date, opening_cash, cash_sales, total_expenses, stock_cost, actual_cash, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                opening_cash   = VALUES(opening_cash),
                cash_sales     = VALUES(cash_sales),
                total_expenses = VALUES(total_expenses),
                stock_cost     = VALUES(stock_cost),
                actual_cash    = VALUES(actual_cash),
                notes          = VALUES(notes)
        ");
        $stmt->execute([$date, $opening, $cashSales, $expenses, $stockCost, $actualCash, $notes ?: null]);
        send(['success' => true]);
    }

    // ── Update selling price ──
    if (isset($input['update_price'])) {
        $id    = (int)$input['product_id'];
        $price = (float)$input['price'];
        if ($price < 0) send(['error' => 'Price cannot be negative'], 400);
        $pdo->prepare("UPDATE products SET selling_price = ? WHERE id = ?")->execute([$price, $id]);
        send(['success' => true]);
    }

    // ── Update product visibility ──
    if (isset($input['update_visibility'])) {
        $id      = (int)$input['product_id'];
        $visIn   = isset($input['visible_input'])     ? (int)(bool)$input['visible_input']     : null;
        $visDash = isset($input['visible_dashboard']) ? (int)(bool)$input['visible_dashboard'] : null;
        if ($visIn !== null)   $pdo->prepare("UPDATE products SET visible_input=? WHERE id=?")->execute([$visIn, $id]);
        if ($visDash !== null) $pdo->prepare("UPDATE products SET visible_dashboard=? WHERE id=?")->execute([$visDash, $id]);
        send(['success' => true]);
    }

    // ── Soft-delete product ──
    if (isset($input['delete_product'])) {
        $id = (int)$input['delete_product'];
        $pdo->prepare("UPDATE products SET is_deleted=1, deleted_at=NOW() WHERE id=?")->execute([$id]);
        send(['success' => true]);
    }

    // ── Restore deleted product ──
    if (isset($input['restore_product'])) {
        $id = (int)$input['restore_product'];
        $pdo->prepare("UPDATE products SET is_deleted=0, deleted_at=NULL WHERE id=?")->execute([$id]);
        send(['success' => true]);
    }

    // ── Add new product ──
    if (isset($input['add_product'])) {
        $name      = trim($input['name']      ?? '');
        $category  = $input['category']       ?? 'Chicken';
        $price     = (float)($input['price']  ?? 0);
        $threshold = (int)($input['threshold']?? 10);
        if (empty($name))  send(['error' => 'Name required'], 400);
        if ($price < 0)    send(['error' => 'Price cannot be negative'], 400);
        // Check duplicate name
        $chk = $pdo->prepare("SELECT id FROM products WHERE name=? AND is_deleted=0"); $chk->execute([$name]);
        if ($chk->fetch()) send(['error' => 'Product with this name already exists'], 409);
        $stmt = $pdo->prepare("INSERT INTO products (name, category, selling_price, low_stock_threshold) VALUES (?,?,?,?)");
        $stmt->execute([$name, $category, $price, $threshold]);
        send(['success' => true, 'id' => $pdo->lastInsertId()]);
    }

    // ── Update product (admin) ──
    if (isset($input['update_product'])) {
        $id        = (int)$input['product_id'];
        $name      = trim($input['name']      ?? '');
        $category  = $input['category']       ?? 'Chicken';
        $price     = (float)($input['price']  ?? 0);
        $threshold = (int)($input['threshold']?? 10);
        if (empty($name)) send(['error' => 'Name required'], 400);
        $pdo->prepare("UPDATE products SET name=?, category=?, selling_price=?, low_stock_threshold=? WHERE id=?")
            ->execute([$name, $category, $price, $threshold, $id]);
        send(['success' => true]);
    }

    // ── Add / update supplier ──
    if (isset($input['save_supplier'])) {
        $id      = (int)($input['id'] ?? 0);
        $name    = trim($input['name'] ?? '');
        $contact = trim($input['contact'] ?? '');
        $notes   = trim($input['notes'] ?? '');
        if (empty($name)) send(['error' => 'Supplier name required'], 400);
        if ($id > 0) {
            $pdo->prepare("UPDATE suppliers SET name=?, contact=?, notes=? WHERE id=?")->execute([$name, $contact ?: null, $notes ?: null, $id]);
        } else {
            $pdo->prepare("INSERT INTO suppliers (name, contact, notes) VALUES (?,?,?)")->execute([$name, $contact ?: null, $notes ?: null]);
            $id = $pdo->lastInsertId();
        }
        send(['success' => true, 'id' => $id]);
    }

    send(['error' => 'Invalid payload'], 400);
}

send(['error' => 'Method not allowed'], 405);
?>