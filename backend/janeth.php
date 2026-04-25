<?php
// janeth.php - API for Janeth's inventory system
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once 'db.php';

function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get all products (with category)
    if (isset($_GET['products'])) {
    $stmt = $pdo->query("SELECT id, name, category, price FROM products ORDER BY category, name");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    sendResponse(['products' => $products]);
}
    
    // Get list of dates with records
    if (isset($_GET['list_dates'])) {
        $stmt = $pdo->query("SELECT DISTINCT record_date FROM janeth_records ORDER BY record_date DESC");
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        sendResponse(['dates' => $dates]);
    }
    
    // Get records for a specific date
    if (isset($_GET['date'])) {
        $date = $_GET['date'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            sendResponse(['error' => 'Invalid date format'], 400);
        }
        $stmt = $pdo->prepare("
    SELECT 
        p.id AS product_id,
        p.name AS product_name,
        p.category AS product_category,
        p.price AS price,
        COALESCE(jr.yesterday_qty, 0) AS yesterday_qty,
        COALESCE(jr.stock_in, 0) AS stock_in,
        COALESCE(jr.remaining_qty, 0) AS remaining_qty,
        COALESCE(jr.sold, 0) AS sold
    FROM products p
    LEFT JOIN janeth_records jr ON jr.product_id = p.id AND jr.record_date = ?
    ORDER BY p.category, p.name
");

        $stmt->execute([$date]);
        $records = $stmt->fetchAll();
        sendResponse(['records' => $records]);
    }
    
    sendResponse(['error' => 'Missing parameters. Use ?products, ?list_dates, or ?date=YYYY-MM-DD'], 400);
}

// POST request (save)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['date']) || !isset($input['records'])) {
        sendResponse(['error' => 'Invalid payload'], 400);
    }
    
    $date = $input['date'];
    $records = $input['records'];
    
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        sendResponse(['error' => 'Invalid date format'], 400);
    }
    
    try {
        $pdo->beginTransaction();
        foreach ($records as $rec) {
            $yesterday = $rec['yesterday_qty'];
            $stockIn = $rec['stock_in'];
            $remaining = $rec['remaining_qty'];
            $sold = ($yesterday + $stockIn) - $remaining;
            
            $stmt = $pdo->prepare("
                INSERT INTO janeth_records 
                    (record_date, product_id, yesterday_qty, stock_in, remaining_qty, sold)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    yesterday_qty = VALUES(yesterday_qty),
                    stock_in = VALUES(stock_in),
                    remaining_qty = VALUES(remaining_qty),
                    sold = VALUES(sold)
            ");
            $stmt->execute([
                $date,
                $rec['product_id'],
                $yesterday,
                $stockIn,
                $remaining,
                $sold
            ]);
        }
        $pdo->commit();
        sendResponse(['success' => true, 'message' => "Data saved for $date"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

sendResponse(['error' => 'Method not allowed'], 405);
?>