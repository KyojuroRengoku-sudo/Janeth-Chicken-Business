<?php
namespace App\Controllers;

use App\Models\Product;

/**
 * ProductController – handles all GET/POST requests to the inventory API.
 * Replaces the old janeth.php monolith.
 */
class ProductController
{
    private Product $product;

    public function __construct()
    {
        $this->product = new Product();
    }

    // ── Router ────────────────────────────────────────────────────────────

    public function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'OPTIONS') exit(0);
        if ($method === 'GET')    $this->handleGet();
        if ($method === 'POST')   $this->handlePost();

        send(['error' => 'Method not allowed'], 405);
    }

    // ── GET ───────────────────────────────────────────────────────────────

    private function handleGet(): void
    {
        if (isset($_GET['products'])) {
            send(['products' => $this->product->all($_GET['page'] ?? 'all')]);
        }

        if (isset($_GET['deleted_products'])) {
            send(['products' => $this->product->deleted()]);
        }

        if (isset($_GET['suppliers'])) {
            send(['suppliers' => $this->product->allSuppliers()]);
        }

        if (isset($_GET['list_dates'])) {
            send(['dates' => $this->product->distinctDates()]);
        }

        if (isset($_GET['date'])) {
            $date = $_GET['date'];
            if (!validDate($date)) send(['error' => 'Invalid date format'], 400);
            $forPage = $_GET['for'] ?? 'input';
            send([
                'records'       => $this->product->recordsForDate($date, $forPage),
                'stock_entries' => $this->product->stockEntriesForDate($date),
            ]);
        }

        if (isset($_GET['stock_entries'])) {
            $date = $_GET['stock_entries'];
            if (!validDate($date)) send(['error' => 'Invalid date format'], 400);
            send(['stock_entries' => $this->product->stockEntriesForDate($date)]);
        }

        if (isset($_GET['expenses'])) {
            $date = $_GET['expenses'];
            if (!validDate($date)) send(['error' => 'Invalid date format'], 400);
            send(['expenses' => $this->product->expensesForDate($date)]);
        }

        if (isset($_GET['liquidation'])) {
            $date = $_GET['liquidation'];
            if (!validDate($date)) send(['error' => 'Invalid date format'], 400);
            $liq = $this->product->liquidationForDate($date);
            send(['liquidation' => $liq ?: null]);
        }

        if (isset($_GET['analytics'])) {
            $from = $_GET['from'] ?? date('Y-m-01');
            $to   = $_GET['to']   ?? date('Y-m-d');
            if (!validDate($from) || !validDate($to)) send(['error' => 'Invalid date range'], 400);
            send($this->product->analytics($from, $to));
        }

        send(['error' => 'Missing parameters'], 400);
    }

    // ── POST ──────────────────────────────────────────────────────────────

    private function handlePost(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        // Save inventory records
        if (isset($input['date'], $input['records'])) {
            $date = $input['date'];
            if (!validDate($date)) send(['error' => 'Invalid date format'], 400);
            try {
                $this->product->saveRecords($date, $input['records']);
                send(['success' => true, 'message' => "Saved for $date"]);
            } catch (\Exception $e) {
                send(['error' => $e->getMessage()], 500);
            }
        }

        // Save stock entry
        if (isset($input['save_stock_entry'])) {
            $date       = $input['entry_date']  ?? '';
            $productId  = (int)($input['product_id']  ?? 0);
            $supplierId = (int)($input['supplier_id'] ?? 1);
            $qty        = (int)($input['qty']         ?? 0);
            $costPrice  = (float)($input['cost_price']?? 0);
            $notes      = trim($input['notes'] ?? '');

            if (!validDate($date))    send(['error' => 'Invalid date'], 400);
            if ($productId  <= 0)     send(['error' => 'Product required'], 400);
            if ($supplierId <= 0)     send(['error' => 'Supplier required'], 400);
            if ($qty <= 0)            send(['error' => 'Qty must be > 0'], 400);
            if ($costPrice < 0)       send(['error' => 'Cost price cannot be negative'], 400);

            $id = $this->product->saveStockEntry($date, $productId, $supplierId, $qty, $costPrice, $notes);
            send(['success' => true, 'id' => $id]);
        }

        // Delete stock entry
        if (isset($input['delete_stock_entry'])) {
            $this->product->deleteStockEntry((int)$input['delete_stock_entry']);
            send(['success' => true]);
        }

        // Save expense
        if (isset($input['save_expense'])) {
            $date   = $input['expense_date']  ?? '';
            $cat    = trim($input['category']    ?? 'General');
            $desc   = trim($input['description'] ?? '');
            $amount = (float)($input['amount'] ?? 0);

            if (!validDate($date)) send(['error' => 'Invalid date'], 400);
            if (empty($desc))      send(['error' => 'Description required'], 400);
            if ($amount <= 0)      send(['error' => 'Amount must be > 0'], 400);

            $id = $this->product->saveExpense($date, $cat, $desc, $amount);
            send(['success' => true, 'id' => $id]);
        }

        // Delete expense
        if (isset($input['delete_expense'])) {
            $this->product->deleteExpense((int)$input['delete_expense']);
            send(['success' => true]);
        }

        // Save liquidation
        if (isset($input['save_liquidation'])) {
            $date = $input['liquidation_date'] ?? '';
            if (!validDate($date)) send(['error' => 'Invalid date'], 400);
            $this->product->saveLiquidation(
                $date,
                (float)($input['opening_cash']   ?? 0),
                (float)($input['cash_sales']     ?? 0),
                (float)($input['total_expenses'] ?? 0),
                (float)($input['stock_cost']     ?? 0),
                (float)($input['actual_cash']    ?? 0),
                trim($input['notes'] ?? '')
            );
            send(['success' => true]);
        }

        // Update price
        if (isset($input['update_price'])) {
            $price = (float)$input['price'];
            if ($price < 0) send(['error' => 'Price cannot be negative'], 400);
            $this->product->updatePrice((int)$input['product_id'], $price);
            send(['success' => true]);
        }

        // Update visibility
        if (isset($input['update_visibility'])) {
            $id      = (int)$input['product_id'];
            $visIn   = isset($input['visible_input'])     ? (int)(bool)$input['visible_input']     : null;
            $visDash = isset($input['visible_dashboard']) ? (int)(bool)$input['visible_dashboard'] : null;
            $this->product->updateVisibility($id, $visIn, $visDash);
            send(['success' => true]);
        }

        // Soft-delete product
        if (isset($input['delete_product'])) {
            $this->product->softDelete((int)$input['delete_product']);
            send(['success' => true]);
        }

        // Restore product
        if (isset($input['restore_product'])) {
            $this->product->restore((int)$input['restore_product']);
            send(['success' => true]);
        }

        // Add product
        if (isset($input['add_product'])) {
            $name      = trim($input['name']      ?? '');
            $category  = $input['category']       ?? 'Chicken';
            $price     = (float)($input['price']  ?? 0);
            $threshold = (int)($input['threshold']?? 10);
            if (empty($name)) send(['error' => 'Name required'], 400);
            if ($price < 0)   send(['error' => 'Price cannot be negative'], 400);
            if ($this->product->nameExists($name)) send(['error' => 'Product with this name already exists'], 409);
            $id = $this->product->create($name, $category, $price, $threshold);
            send(['success' => true, 'id' => $id]);
        }

        // Update product
        if (isset($input['update_product'])) {
            $id = (int)$input['product_id'];
            $name = trim($input['name'] ?? '');
            if (empty($name)) send(['error' => 'Name required'], 400);
            $this->product->update($id, $name, $input['category'] ?? 'Chicken',
                (float)($input['price'] ?? 0), (int)($input['threshold'] ?? 10));
            send(['success' => true]);
        }

        // Save supplier
        if (isset($input['save_supplier'])) {
            $id   = (int)($input['id'] ?? 0);
            $name = trim($input['name'] ?? '');
            if (empty($name)) send(['error' => 'Supplier name required'], 400);
            $newId = $this->product->saveSupplier($id, $name,
                trim($input['contact'] ?? ''), trim($input['notes'] ?? ''));
            send(['success' => true, 'id' => $newId]);
        }

        send(['error' => 'Invalid payload'], 400);
    }
}
