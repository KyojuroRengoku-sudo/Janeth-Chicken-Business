<?php
// janeth.php - API for Janeth's inventory system

// Allow cross-origin requests (since frontend and backend are on same domain, but good practice)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db.php';

// Helper function to send JSON response
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// ---------- GET REQUESTS ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // 1. Get all products (for the input form)
    if (isset($_GET['products'])) {
        $stmt = $pdo->query("SELECT id, name FROM products ORDER BY id");
        $products = $stmt->fetchAll();
        sendResponse(['products' => $products]);
    }
    
    // 2. Get list of all dates that have records
    if (isset($_GET['list_dates'])) {
        $stmt = $pdo->query("SELECT DISTINCT record_date FROM janeth_records ORDER BY record_date DESC");
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        sendResponse(['dates' => $dates]);
    }
    
    // 3. Get records for a specific date
    if (isset($_GET['date'])) {
        $date = $_GET['date'];
        
        // Validate date format (basic)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            sendResponse(['error' => 'Invalid date format. Use YYYY-MM-DD'], 400);
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                p.id AS product_id,
                p.name AS product_name,
                COALESCE(jr.yesterday_qty, 0) AS yesterday_qty,
                COALESCE(jr.stock_in, 0) AS stock_in,
                COALESCE(jr.distributed, 0) AS distributed,
                COALESCE(jr.sold, 0) AS sold
            FROM products p
            LEFT JOIN janeth_records jr ON jr.product_id = p.id AND jr.record_date = ?
            ORDER BY p.id
        ");
        $stmt->execute([$date]);
        $records = $stmt->fetchAll();
        sendResponse(['records' => $records]);
    }
    
    // If no valid parameter
    sendResponse(['error' => 'Missing or invalid parameters. Use ?products, ?list_dates, or ?date=YYYY-MM-DD'], 400);
}

// ---------- POST REQUESTS (Save data) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['date']) || !isset($input['records'])) {
        sendResponse(['error' => 'Invalid payload. Required: { "date": "YYYY-MM-DD", "records": [...] }'], 400);
    }
    
    $date = $input['date'];
    $records = $input['records'];
    
    // Validate date
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        sendResponse(['error' => 'Invalid date format. Use YYYY-MM-DD'], 400);
    }
    
    try {
        $pdo->beginTransaction();
        
        foreach ($records as $rec) {
            // Validate each record
            if (!isset($rec['product_id'], $rec['yesterday_qty'], $rec['stock_in'], $rec['distributed'], $rec['sold'])) {
                throw new Exception('Missing fields in one of the records');
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO janeth_records 
                    (record_date, product_id, yesterday_qty, stock_in, distributed, sold)
                VALUES 
                    (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    yesterday_qty = VALUES(yesterday_qty),
                    stock_in = VALUES(stock_in),
                    distributed = VALUES(distributed),
                    sold = VALUES(sold)
            ");
            $stmt->execute([
                $date,
                $rec['product_id'],
                $rec['yesterday_qty'],
                $rec['stock_in'],
                $rec['distributed'],
                $rec['sold']
            ]);
        }
        
        $pdo->commit();
        sendResponse(['success' => true, 'message' => "Data saved for $date"]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        sendResponse(['error' => $e->getMessage()], 500);
    }
}

// If method not allowed
sendResponse(['error' => 'Method not allowed'], 405);
?>