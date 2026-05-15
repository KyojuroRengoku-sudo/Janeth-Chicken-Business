<?php
/**
 * app/controllers/ProductController.php
 * Handles all inventory API requests (GET + POST).
 */

namespace App\Controllers;

use App\Models\Product;

class ProductController
{
    private Product $product;

    public function __construct()
    {
        $this->product = new Product();
    }

    public function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $this->handleGet();
        } elseif ($method === 'POST') {
            $body = json_decode(file_get_contents('php://input'), true) ?? [];
            $this->handlePost($body);
        } else {
            send(['error' => 'Method not allowed'], 405);
        }
    }

    // ── GET ───────────────────────────────────────────────────────────────────

    private function handleGet(): void
    {
        // ?products=1&page=input|dashboard|all
        if (isset($_GET['products'])) {
            $page     = $_GET['page'] ?? 'all';
            $products = $this->product->getProducts($page);
            send(['products' => $products]);
            return;
        }

        // ?suppliers=1
        if (isset($_GET['suppliers'])) {
            $suppliers = $this->product->getSuppliers();
            send(['suppliers' => $suppliers]);
            return;
        }

        // ?list_dates=1  ← dashboard date dropdown
        // BUG FIX: this entire branch was missing — the dashboard called
        // ?list_dates=1 but there was no handler, so it got a 400 error,
        // the date selector never populated, and the dashboard showed nothing.
        if (isset($_GET['list_dates'])) {
            $dates = $this->product->getListDates();
            send(['dates' => $dates]);
            return;
        }

        // ?stock_entries=YYYY-MM-DD
        if (isset($_GET['stock_entries'])) {
            $date = validDate($_GET['stock_entries']);
            if (!$date) { send(['error' => 'Invalid date'], 400); return; }
            $entries = $this->product->getStockEntries($date);
            send(['stock_entries' => $entries]);
            return;
        }

        // ?date=YYYY-MM-DD&for=input|dashboard
        if (isset($_GET['date'])) {
            $date = validDate($_GET['date']);
            if (!$date) { send(['error' => 'Invalid date'], 400); return; }
            $for     = $_GET['for'] ?? 'input';
            $records = $this->product->getRecords($date, $for);
            send(['records' => $records, 'date' => $date]);
            return;
        }

        // ?expenses=YYYY-MM-DD
        if (isset($_GET['expenses'])) {
            $date = validDate($_GET['expenses']);
            if (!$date) { send(['error' => 'Invalid date'], 400); return; }
            $expenses = $this->product->getExpenses($date);
            send(['expenses' => $expenses]);
            return;
        }

        // ?liquidation=YYYY-MM-DD
        if (isset($_GET['liquidation'])) {
            $date = validDate($_GET['liquidation']);
            if (!$date) { send(['error' => 'Invalid date'], 400); return; }
            $data = $this->product->getLiquidation($date);
            send($data);
            return;
        }

        // ?analytics=1&from=YYYY-MM-DD&to=YYYY-MM-DD
        if (isset($_GET['analytics'])) {
            $from = validDate($_GET['from'] ?? '');
            $to   = validDate($_GET['to']   ?? '');
            if (!$from || !$to) { send(['error' => 'Invalid date range'], 400); return; }
            $data = $this->product->getAnalytics($from, $to);
            send($data);
            return;
        }

        send(['error' => 'Unknown GET request'], 400);
    }

    // ── POST ──────────────────────────────────────────────────────────────────

    private function handlePost(array $body): void
    {
        // Save daily inventory records
        if (isset($body['records'])) {
            $date    = validDate($body['date'] ?? '');
            $records = $body['records'] ?? [];
            if (!$date || !is_array($records)) {
                send(['success' => false, 'error' => 'Invalid payload: date or records missing']);
                return;
            }
            try {
                $ok = $this->product->saveRecords($date, $records);
                send(['success' => $ok]);
            } catch (\PDOException $e) {
                error_log('saveRecords PDO error: ' . $e->getMessage());
                send(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
            } catch (\Exception $e) {
                error_log('saveRecords general error: ' . $e->getMessage());
                send(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
            }
            return;
        }

        // Save stock entry
        if (isset($body['save_stock_entry'])) {
            $date        = validDate($body['entry_date']  ?? '');
            $product_id  = (int)($body['product_id']     ?? 0);
            $supplier_id = (int)($body['supplier_id']    ?? 0);
            $qty         = (int)($body['qty']             ?? 0);
            $cost_price  = (float)($body['cost_price']   ?? 0);
            $notes       = trim($body['notes']            ?? '');

            if (!$date || !$product_id || !$supplier_id || $qty <= 0) {
                send(['success' => false, 'error' => 'Missing or invalid fields']);
                return;
            }
            $id = $this->product->saveStockEntry($date, $product_id, $supplier_id, $qty, $cost_price, $notes);
            send(['success' => (bool)$id, 'id' => $id]);
            return;
        }

        // Delete stock entry
        if (isset($body['delete_stock_entry'])) {
            $id = (int)$body['delete_stock_entry'];
            $ok = $this->product->deleteStockEntry($id);
            send(['success' => $ok]);
            return;
        }

        // Save expense
        if (isset($body['save_expense'])) {
            $date        = validDate($body['expense_date'] ?? '');
            $category    = trim($body['category']         ?? 'Other');
            $description = trim($body['description']      ?? '');
            $amount      = (float)($body['amount']        ?? 0);

            if (!$date || !$description || $amount <= 0) {
                send(['success' => false, 'error' => 'Missing fields']);
                return;
            }
            $id = $this->product->saveExpense($date, $category, $description, $amount);
            send(['success' => (bool)$id, 'id' => $id]);
            return;
        }

        // Delete expense
        if (isset($body['delete_expense'])) {
            $id = (int)$body['delete_expense'];
            $ok = $this->product->deleteExpense($id);
            send(['success' => $ok]);
            return;
        }

        // Save liquidation
        if (isset($body['save_liquidation'])) {
            $date = validDate($body['date'] ?? '');
            if (!$date) {
                send(['success' => false, 'error' => 'Invalid or missing date']);
                return;
            }
            $openingCash   = (float)($body['opening_cash']    ?? 0);
            $cashSales     = (float)($body['cash_sales']      ?? 0);
            $totalExpenses = (float)($body['total_expenses']  ?? 0);
            $stockCost     = (float)($body['stock_cost']      ?? 0);
            $actualCash    = (float)($body['actual_cash']     ?? 0);
            $notes         = trim($body['notes']              ?? '');
            $extra = [
                'cash_bills'      => (float)($body['cash_bills']      ?? 0),
                'cash_coins'      => (float)($body['cash_coins']      ?? 0),
                'cash_ice'        => (float)($body['cash_ice']        ?? 0),
                'cash_ticket'     => (float)($body['cash_ticket']     ?? 0),
                'cash_suga'       => (float)($body['cash_suga']       ?? 0),
                'cash_plastic'    => (float)($body['cash_plastic']    ?? 0),
                'cash_meal'       => (float)($body['cash_meal']       ?? 0),
                'cash_plete'      => (float)($body['cash_plete']      ?? 0),
                'cash_pu'         => (float)($body['cash_pu']         ?? 0),
                'debts'           => $body['debts']           ?? [],
                'payables'        => $body['payables']        ?? [],
                'discounts'       => $body['discounts']       ?? [],
                'supplier_return' => (float)($body['supplier_return'] ?? 0),
            ];
            $ok = $this->product->saveLiquidationExtended(
                $date, $openingCash, $cashSales, $totalExpenses,
                $stockCost, $actualCash, $notes, $extra
            );
            send(['success' => $ok]);
            return;
        }

        // Add product (admin)
        if (isset($body['add_product'])) {
            requireAuth('admin', true);
            $id = $this->product->addProduct($body);
            send(['success' => (bool)$id, 'id' => $id]);
            return;
        }

        // Update product visibility
        if (isset($body['update_visibility'])) {
            $product_id        = (int)$body['product_id'];
            $visible_input     = (int)($body['visible_input']     ?? 0);
            $visible_dashboard = (int)($body['visible_dashboard'] ?? 0);
            $ok = $this->product->updateVisibility($product_id, $visible_input, $visible_dashboard);
            send(['success' => $ok]);
            return;
        }

        send(['error' => 'Unknown POST action'], 400);
    }
}