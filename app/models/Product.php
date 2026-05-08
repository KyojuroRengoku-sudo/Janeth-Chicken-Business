<?php
namespace App\Models;

use PDO;

/**
 * Product model – wraps all product / inventory / analytics queries.
 */
class Product
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // ── Products (controller-friendly names) ─────────────────────────────

    public function getProducts(string $page = 'all'): array
    {
        $vis = match ($page) {
            'input'     => 'AND visible_input=1',
            'dashboard' => 'AND visible_dashboard=1',
            default     => '',
        };
        return $this->pdo
            ->query("SELECT id, name, category, selling_price AS price,
                            low_stock_threshold, visible_input, visible_dashboard
                     FROM products WHERE is_deleted=0 $vis ORDER BY category, name")
            ->fetchAll();
    }

    public function getSuppliers(): array
    {
        return $this->pdo->query('SELECT * FROM suppliers ORDER BY id')->fetchAll();
    }

    // ── Stock entries ────────────────────────────────────────────────────

    public function getStockEntries(string $date): array
    {
        $stmt = $this->pdo->prepare("
            SELECT se.*, s.name AS supplier_name, p.name AS product_name,
                   (se.qty * se.cost_price) AS total_cost
            FROM stock_entries se
            JOIN suppliers s ON s.id = se.supplier_id
            JOIN products  p ON p.id = se.product_id
            WHERE se.record_date = ?
            ORDER BY se.product_id, se.id
        ");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }

    public function saveStockEntry(
        string $date, int $productId, int $supplierId,
        int $qty, float $costPrice, string $notes
    ): int {
        $this->pdo->prepare(
            'INSERT INTO stock_entries (record_date, product_id, supplier_id, qty, cost_price, notes)
             VALUES (?,?,?,?,?,?)'
        )->execute([$date, $productId, $supplierId, $qty, $costPrice, $notes ?: null]);
        return (int)$this->pdo->lastInsertId();
    }

    public function deleteStockEntry(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM stock_entries WHERE id=?');
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    // ── Inventory records (janeth_records) ───────────────────────────────

    public function getRecords(string $date, string $forPage = 'input'): array
    {
        $visCol = $forPage === 'dashboard' ? 'visible_dashboard' : 'visible_input';
        $stmt   = $this->pdo->prepare("
            SELECT p.id AS product_id, p.name AS product_name, p.category AS product_category,
                   p.selling_price AS price, p.low_stock_threshold,
                   p.visible_input, p.visible_dashboard,
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
        return $stmt->fetchAll();
    }

    /**
     * Saves daily inventory records.
     * @throws \PDOException on database error
     */
    public function saveRecords(string $date, array $records): bool
    {
        $this->pdo->beginTransaction();
        $sql = "INSERT INTO janeth_records (record_date, product_id, yesterday_qty, stock_in, remaining_qty, sold)
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    yesterday_qty = VALUES(yesterday_qty),
                    stock_in      = VALUES(stock_in),
                    remaining_qty = VALUES(remaining_qty),
                    sold          = VALUES(sold)";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($records as $rec) {
            $yesterday = (int)($rec['yesterday_qty'] ?? 0);
            $stockIn   = (int)($rec['stock_in']      ?? 0);
            $remaining = (int)($rec['remaining_qty'] ?? 0);
            $sold      = max(0, ($yesterday + $stockIn) - $remaining);
            $stmt->execute([
                $date,
                (int)$rec['product_id'],
                $yesterday,
                $stockIn,
                $remaining,
                $sold
            ]);
        }
        $this->pdo->commit();
        return true;
    }

    // ── Expenses ─────────────────────────────────────────────────────────

    public function getExpenses(string $date): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM daily_expenses WHERE expense_date=? ORDER BY created_at DESC'
        );
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }

    public function saveExpense(string $date, string $cat, string $desc, float $amount): int
    {
        $this->pdo->prepare(
            'INSERT INTO daily_expenses (expense_date, category, description, amount) VALUES (?,?,?,?)'
        )->execute([$date, $cat, $desc, $amount]);
        return (int)$this->pdo->lastInsertId();
    }

    public function deleteExpense(int $id): bool
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM daily_expenses WHERE id=?');
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    // ── Liquidation (extended with JSON extra_data) ──────────────────────

    public function getLiquidation(string $date): array
    {
        $stmt = $this->pdo->prepare("
            SELECT liquidation_date, opening_cash, cash_sales, total_expenses,
                   stock_cost, actual_cash, notes, extra_data
            FROM liquidations WHERE liquidation_date = ?
        ");
        $stmt->execute([$date]);
        $row = $stmt->fetch();
        if (!$row) {
            return ['liquidation' => null];
        }
        $extra = json_decode($row['extra_data'], true) ?? [];
        return [
            'liquidation' => array_merge([
                'opening_cash'  => (float)$row['opening_cash'],
                'cash_sales'    => (float)$row['cash_sales'],
                'total_expenses'=> (float)$row['total_expenses'],
                'stock_cost'    => (float)$row['stock_cost'],
                'actual_cash'   => (float)$row['actual_cash'],
                'notes'         => $row['notes'],
            ], $extra)
        ];
    }

    public function saveLiquidationExtended(
        string $date, float $openingCash, float $cashSales, float $totalExpenses,
        float $stockCost, float $actualCash, string $notes, array $extra
    ): bool {
        try {
            $json = json_encode($extra, JSON_UNESCAPED_UNICODE);
            $stmt = $this->pdo->prepare("
                INSERT INTO liquidations 
                    (liquidation_date, opening_cash, cash_sales, total_expenses, stock_cost, actual_cash, notes, extra_data)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    opening_cash = VALUES(opening_cash),
                    cash_sales   = VALUES(cash_sales),
                    total_expenses = VALUES(total_expenses),
                    stock_cost   = VALUES(stock_cost),
                    actual_cash  = VALUES(actual_cash),
                    notes        = VALUES(notes),
                    extra_data   = VALUES(extra_data)
            ");
            $stmt->execute([$date, $openingCash, $cashSales, $totalExpenses, $stockCost, $actualCash, $notes, $json]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // ── Analytics ────────────────────────────────────────────────────────

    public function getAnalytics(string $from, string $to): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.name, p.category, p.selling_price AS price,
                   SUM(jr.sold)                   AS total_sold_qty,
                   SUM(jr.sold * p.selling_price) AS total_sold_peso,
                   SUM(jr.stock_in)               AS total_stock_in,
                   COUNT(DISTINCT jr.record_date) AS days_recorded
            FROM products p
            LEFT JOIN janeth_records jr ON jr.product_id = p.id
                AND jr.record_date BETWEEN ? AND ?
            WHERE p.is_deleted=0 AND p.visible_dashboard=1
            GROUP BY p.id, p.name, p.category, p.selling_price
            ORDER BY total_sold_peso DESC
        ");
        $stmt->execute([$from, $to]);
        $rows = $stmt->fetchAll();

        $exp = $this->pdo->prepare('SELECT SUM(amount) FROM daily_expenses WHERE expense_date BETWEEN ? AND ?');
        $exp->execute([$from, $to]);
        $totalExpenses = (float)($exp->fetchColumn() ?? 0);

        $cost = $this->pdo->prepare('SELECT SUM(qty*cost_price) FROM stock_entries WHERE record_date BETWEEN ? AND ?');
        $cost->execute([$from, $to]);
        $totalCost = (float)($cost->fetchColumn() ?? 0);

        $weekly = $this->pdo->prepare("
            SELECT record_date, SUM(jr.sold * p.selling_price) AS day_sales
            FROM janeth_records jr JOIN products p ON p.id=jr.product_id
            WHERE jr.record_date BETWEEN ? AND ?
            GROUP BY record_date ORDER BY record_date DESC LIMIT 7
        ");
        $weekly->execute([$from, $to]);
        $weeklyData = array_reverse($weekly->fetchAll());

        $monthly = $this->pdo->prepare("
            SELECT DATE_FORMAT(jr.record_date,'%Y-%m') AS month,
                   SUM(jr.sold * p.selling_price) AS month_sales
            FROM janeth_records jr JOIN products p ON p.id=jr.product_id
            WHERE jr.record_date BETWEEN ? AND ?
            GROUP BY DATE_FORMAT(jr.record_date,'%Y-%m') ORDER BY month ASC
        ");
        $monthly->execute([$from, $to]);

        return [
            'analytics'        => $rows,
            'total_expenses'   => $totalExpenses,
            'total_stock_cost' => $totalCost,
            'weekly'           => $weeklyData,
            'monthly'          => $monthly->fetchAll(),
            'from'             => $from,
            'to'               => $to,
        ];
    }

    // ── Product management (admin) ───────────────────────────────────────

    public function addProduct(array $data): int
    {
        $name      = trim($data['name'] ?? '');
        $category  = $data['category'] ?? 'Chicken';
        $price     = (float)($data['price'] ?? 0);
        $threshold = (int)($data['low_stock_threshold'] ?? 10);

        $this->pdo->prepare(
            'INSERT INTO products (name, category, selling_price, low_stock_threshold)
             VALUES (?, ?, ?, ?)'
        )->execute([$name, $category, $price, $threshold]);
        return (int)$this->pdo->lastInsertId();
    }

    public function updateVisibility(int $id, ?int $visInput, ?int $visDash): bool
    {
        try {
            if ($visInput !== null) {
                $this->pdo->prepare('UPDATE products SET visible_input=? WHERE id=?')->execute([$visInput, $id]);
            }
            if ($visDash !== null) {
                $this->pdo->prepare('UPDATE products SET visible_dashboard=? WHERE id=?')->execute([$visDash, $id]);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}