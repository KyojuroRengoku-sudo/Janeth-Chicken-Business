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

    // ── Products ──────────────────────────────────────────────────────────

    public function all(string $page = 'all'): array
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

    public function deleted(): array
    {
        return $this->pdo
            ->query('SELECT id, name, category, selling_price AS price, deleted_at
                     FROM products WHERE is_deleted=1 ORDER BY deleted_at DESC')
            ->fetchAll();
    }

    public function paginated(string $search, string $category, int $limit, int $offset): array
    {
        $where = "is_deleted=0 AND name LIKE :s" . ($category !== 'all' ? ' AND category=:c' : '');
        $stmt  = $this->pdo->prepare(
            "SELECT * FROM products WHERE $where ORDER BY category,name LIMIT :l OFFSET :o"
        );
        $stmt->bindValue(':s', "%$search%");
        if ($category !== 'all') $stmt->bindValue(':c', $category);
        $stmt->bindValue(':l', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':o', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(string $search, string $category): int
    {
        $where = "is_deleted=0 AND name LIKE :s" . ($category !== 'all' ? ' AND category=:c' : '');
        $stmt  = $this->pdo->prepare("SELECT COUNT(*) FROM products WHERE $where");
        $stmt->bindValue(':s', "%$search%");
        if ($category !== 'all') $stmt->bindValue(':c', $category);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function nameExists(string $name): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM products WHERE name=? AND is_deleted=0');
        $stmt->execute([$name]);
        return (bool)$stmt->fetch();
    }

    public function hasHistory(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM janeth_records WHERE product_id=? LIMIT 1');
        $stmt->execute([$id]);
        return (bool)$stmt->fetch();
    }

    public function create(string $name, string $category, float $price, int $threshold): int
    {
        $this->pdo->prepare(
            'INSERT INTO products (name, category, selling_price, low_stock_threshold) VALUES (?,?,?,?)'
        )->execute([$name, $category, $price, $threshold]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, string $name, string $category, float $price, int $threshold): void
    {
        $this->pdo->prepare(
            'UPDATE products SET name=?, category=?, selling_price=?, low_stock_threshold=? WHERE id=?'
        )->execute([$name, $category, $price, $threshold, $id]);
    }

    public function updatePrice(int $id, float $price): void
    {
        $this->pdo->prepare('UPDATE products SET selling_price=? WHERE id=?')->execute([$price, $id]);
    }

    public function updateVisibility(int $id, ?int $visInput, ?int $visDash): void
    {
        if ($visInput !== null) {
            $this->pdo->prepare('UPDATE products SET visible_input=? WHERE id=?')->execute([$visInput, $id]);
        }
        if ($visDash !== null) {
            $this->pdo->prepare('UPDATE products SET visible_dashboard=? WHERE id=?')->execute([$visDash, $id]);
        }
    }

    public function softDelete(int $id): void
    {
        $this->pdo->prepare('UPDATE products SET is_deleted=1, deleted_at=NOW() WHERE id=?')->execute([$id]);
    }

    public function restore(int $id): void
    {
        $this->pdo->prepare('UPDATE products SET is_deleted=0, deleted_at=NULL WHERE id=?')->execute([$id]);
    }

    public function hardDelete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
    }

    // ── Suppliers ─────────────────────────────────────────────────────────

    public function allSuppliers(): array
    {
        return $this->pdo->query('SELECT * FROM suppliers ORDER BY id')->fetchAll();
    }

    public function saveSupplier(int $id, string $name, string $contact, string $notes): int
    {
        if ($id > 0) {
            $this->pdo->prepare('UPDATE suppliers SET name=?, contact=?, notes=? WHERE id=?')
                ->execute([$name, $contact ?: null, $notes ?: null, $id]);
            return $id;
        }
        $this->pdo->prepare('INSERT INTO suppliers (name, contact, notes) VALUES (?,?,?)')
            ->execute([$name, $contact ?: null, $notes ?: null]);
        return (int)$this->pdo->lastInsertId();
    }

    // ── Inventory records ─────────────────────────────────────────────────

    public function distinctDates(): array
    {
        return $this->pdo
            ->query('SELECT DISTINCT record_date FROM janeth_records ORDER BY record_date DESC')
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    public function recordsForDate(string $date, string $forPage = 'input'): array
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

    public function saveRecords(string $date, array $records): void
    {
        $this->pdo->beginTransaction();
        try {
            foreach ($records as $rec) {
                $yesterday = (int)($rec['yesterday_qty'] ?? 0);
                $stockIn   = (int)($rec['stock_in']      ?? 0);
                $remaining = (int)($rec['remaining_qty'] ?? 0);
                $sold      = max(0, ($yesterday + $stockIn) - $remaining);
                $this->pdo->prepare("
                    INSERT INTO janeth_records (record_date, product_id, yesterday_qty, stock_in, remaining_qty, sold)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        yesterday_qty=VALUES(yesterday_qty), stock_in=VALUES(stock_in),
                        remaining_qty=VALUES(remaining_qty), sold=VALUES(sold)
                ")->execute([$date, (int)$rec['product_id'], $yesterday, $stockIn, $remaining, $sold]);
            }
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // ── Stock entries ─────────────────────────────────────────────────────

    public function stockEntriesForDate(string $date): array
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

    public function deleteStockEntry(int $id): void
    {
        $this->pdo->prepare('DELETE FROM stock_entries WHERE id=?')->execute([$id]);
    }

    // ── Expenses ──────────────────────────────────────────────────────────

    public function expensesForDate(string $date): array
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

    public function deleteExpense(int $id): void
    {
        $this->pdo->prepare('DELETE FROM daily_expenses WHERE id=?')->execute([$id]);
    }

    // ── Liquidation ───────────────────────────────────────────────────────

    public function liquidationForDate(string $date): array|false
    {
        $stmt = $this->pdo->prepare('SELECT * FROM liquidations WHERE liquidation_date=?');
        $stmt->execute([$date]);
        return $stmt->fetch();
    }

    public function saveLiquidation(
        string $date, float $opening, float $cashSales,
        float $expenses, float $stockCost, float $actualCash, string $notes
    ): void {
        $this->pdo->prepare("
            INSERT INTO liquidations (liquidation_date, opening_cash, cash_sales, total_expenses, stock_cost, actual_cash, notes)
            VALUES (?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE
                opening_cash=VALUES(opening_cash), cash_sales=VALUES(cash_sales),
                total_expenses=VALUES(total_expenses), stock_cost=VALUES(stock_cost),
                actual_cash=VALUES(actual_cash), notes=VALUES(notes)
        ")->execute([$date, $opening, $cashSales, $expenses, $stockCost, $actualCash, $notes ?: null]);
    }

    // ── Analytics ─────────────────────────────────────────────────────────

    public function analytics(string $from, string $to): array
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
}
