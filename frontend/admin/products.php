<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.html');
    exit;
}
require_once '../../backend/db.php';

$message = '';
$messageType = '';

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = trim($_POST['name']);
    $category = $_POST['category'];
    $price = floatval($_POST['price']);
    $threshold = intval($_POST['threshold']);

    if (empty($name)) {
        $message = "Product name is required.";
        $messageType = "error";
    } elseif ($price < 0) {
        $message = "Price cannot be negative.";
        $messageType = "error";
    } elseif ($threshold < 0) {
        $message = "Stock threshold cannot be negative.";
        $messageType = "error";
    } else {
        $check = $pdo->prepare("SELECT id FROM products WHERE name = ?");
        $check->execute([$name]);
        if ($check->fetch()) {
            $message = "Product with this name already exists.";
            $messageType = "error";
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (name, category, price, low_stock_threshold) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $category, $price, $threshold]);
            $message = "Product added successfully.";
            $messageType = "success";
        }
    }
    header("Location: products.php?msg=" . urlencode($message) . "&type=" . $messageType);
    exit;
}

// Handle Delete Product via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    $check = $pdo->prepare("SELECT id FROM janeth_records WHERE product_id = ? LIMIT 1");
    $check->execute([$id]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete: product has sales records.']);
    } else {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
    }
    exit;
}

// Handle Batch Update (Save All Changes)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_update'])) {
    $names = $_POST['name'] ?? [];
    $categories = $_POST['category'] ?? [];
    $prices = $_POST['price'] ?? [];
    $thresholds = $_POST['threshold'] ?? [];

    $errors = [];
    $pdo->beginTransaction();
    try {
        foreach ($names as $id => $name) {
            $category = $categories[$id];
            $price = floatval($prices[$id]);
            $threshold = intval($thresholds[$id]);
            if (empty(trim($name)) || $price < 0 || $threshold < 0) {
                $errors[] = "Invalid data for product ID $id";
                continue;
            }
            $stmt = $pdo->prepare("UPDATE products SET name = ?, category = ?, price = ?, low_stock_threshold = ? WHERE id = ?");
            $stmt->execute([trim($name), $category, $price, $threshold, $id]);
        }
        if (empty($errors)) {
            $pdo->commit();
            $message = "All products updated successfully.";
            $messageType = "success";
        } else {
            $pdo->rollBack();
            $message = "Errors: " . implode(", ", $errors);
            $messageType = "error";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error updating products: " . $e->getMessage();
        $messageType = "error";
    }
    header("Location: products.php?msg=" . urlencode($message) . "&type=" . $messageType);
    exit;
}

// Get message from URL
if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
    $messageType = $_GET['type'] ?? 'info';
}

// Pagination, search, and category filter
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : 'all';
$limit = 15;
$offset = ($page - 1) * $limit;

// Build query with optional category filter
$sql = "SELECT * FROM products WHERE name LIKE :search";
$params = [':search' => "%$search%"];
if ($categoryFilter !== 'all') {
    $sql .= " AND category = :category";
    $params[':category'] = $categoryFilter;
}
$sql .= " ORDER BY id LIMIT :limit OFFSET :offset";
$countSql = "SELECT COUNT(*) as total FROM products WHERE name LIKE :search";
if ($categoryFilter !== 'all') {
    $countSql .= " AND category = :category";
}

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
if ($categoryFilter !== 'all') {
    $stmt->bindValue(':category', $categoryFilter, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

$countStmt = $pdo->prepare($countSql);
$countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
if ($categoryFilter !== 'all') {
    $countStmt->bindValue(':category', $categoryFilter, PDO::PARAM_STR);
}
$countStmt->execute();
$total = $countStmt->fetch()['total'];
$totalPages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Admin</title>
    <style>
        * { margin:0; padding:0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #f1f5f9; padding: 2rem; }
        .container { max-width: 1400px; margin: 0 auto; background: white; border-radius: 1.5rem; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        h1 { font-size: 1.5rem; margin-bottom: 0.2rem; }
        .sub { color: #5b6e8c; margin-bottom: 1.5rem; font-size: 0.85rem; }
        .message { padding: 0.8rem; border-radius: 0.8rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
        .message.success { background: #dcfce7; color: #15803d; border-left: 4px solid #15803d; }
        .message.error { background: #fee2e2; color: #b91c1c; border-left: 4px solid #b91c1c; }
        .toolbar { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; align-items: center; }
        .search-box { display: flex; gap: 0.5rem; }
        .search-box input { padding: 0.5rem 1rem; border-radius: 2rem; border: 1px solid #cbd5e1; width: 250px; }
        .search-box button { background: #0f7b8a; color: white; border: none; padding: 0.5rem 1rem; border-radius: 2rem; cursor: pointer; }
        .filter-select { padding: 0.5rem 1rem; border-radius: 2rem; border: 1px solid #cbd5e1; background: white; cursor: pointer; }
        .add-form { background: #f8fafc; padding: 1rem; border-radius: 1rem; margin-bottom: 2rem; display: flex; flex-wrap: wrap; gap: 0.8rem; align-items: flex-end; }
        .add-form input, .add-form select { padding: 0.5rem; border-radius: 0.8rem; border: 1px solid #cbd5e1; }
        .add-form button { background: #0f7b8a; color: white; border: none; padding: 0.5rem 1.2rem; border-radius: 2rem; cursor: pointer; }
        .save-all-btn { background: #0f7b8a; color: white; border: none; padding: 0.6rem 1.5rem; border-radius: 2rem; cursor: pointer; font-weight: bold; margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.8rem 0.5rem; text-align: left; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        th { background: #f8fafc; font-weight: 600; }
        input, select { padding: 0.3rem 0.5rem; border-radius: 0.5rem; border: 1px solid #cbd5e1; width: 100%; min-width: 80px; }
        .delete-btn { background: #ef4444; color: white; border: none; padding: 0.3rem 0.8rem; border-radius: 2rem; cursor: pointer; }
        .pagination { display: flex; justify-content: center; gap: 0.5rem; margin-top: 1.5rem; flex-wrap: wrap; }
        .page-link { padding: 0.3rem 0.7rem; border-radius: 30px; background: white; border: 1px solid #cbd5e1; text-decoration: none; color: #1e293b; }
        .page-link.active { background: #0f7b8a; color: white; border: none; }
        .back-link { display: inline-block; margin-bottom: 1rem; color: #0f7b8a; text-decoration: none; }
        .action-cell { white-space: nowrap; }
        @media (max-width: 768px) {
            .add-form { flex-direction: column; align-items: stretch; }
            table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>
<div class="container">
    <a href="../janeth-input.php" class="back-link">← Back to Entry</a>
    <h1>📦 Product Management</h1>
    <div class="sub">Manage chicken & frozen products – edit multiple and save all at once</div>

    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php if ($messageType === 'success') echo '✅'; else echo '⚠️'; ?> <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="toolbar">
        <div class="search-box">
            <form method="GET" style="display: flex; gap: 0.5rem;" id="filterForm">
                <input type="text" name="search" placeholder="🔍 Search by name..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="category" class="filter-select" onchange="this.form.submit()">
                    <option value="all" <?php echo $categoryFilter == 'all' ? 'selected' : ''; ?>>All Categories</option>
                    <option value="Chicken" <?php echo $categoryFilter == 'Chicken' ? 'selected' : ''; ?>>🐔 Chicken</option>
                    <option value="Frozen" <?php echo $categoryFilter == 'Frozen' ? 'selected' : ''; ?>>❄️ Frozen</option>
                </select>
                <button type="submit">Filter</button>
                <?php if ($search || $categoryFilter !== 'all'): ?>
                    <a href="products.php"><button type="button">Clear</button></a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Add product form -->
    <div class="add-form">
        <input type="text" id="newName" placeholder="Product name" style="flex:2;">
        <select id="newCategory">
            <option value="Chicken">🐔 Chicken</option>
            <option value="Frozen">❄️ Frozen</option>
        </select>
        <input type="number" id="newPrice" placeholder="Price (₱)" step="0.01" style="width:120px;">
        <input type="number" id="newThreshold" placeholder="Low stock threshold" value="10" style="width:150px;">
        <button id="addBtn">+ Add Product</button>
    </div>

    <!-- Batch update form -->
    <form method="POST" id="batchForm">
        <input type="hidden" name="batch_update" value="1">
        <div style="text-align: right; margin-bottom: 1rem;">
            <button type="submit" class="save-all-btn">💾 Save All Changes</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price (₱)</th>
                    <th>Low Stock Threshold</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $counter = ($page - 1) * $limit + 1;
                foreach ($products as $p): 
                ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><input type="text" name="name[<?php echo $p['id']; ?>]" value="<?php echo htmlspecialchars($p['name']); ?>" required></td>
                    <td>
                        <select name="category[<?php echo $p['id']; ?>]">
                            <option value="Chicken" <?php echo $p['category'] == 'Chicken' ? 'selected' : ''; ?>>🐔 Chicken</option>
                            <option value="Frozen" <?php echo $p['category'] == 'Frozen' ? 'selected' : ''; ?>>❄️ Frozen</option>
                        </select>
                    </td>
                    <td><input type="number" step="0.01" name="price[<?php echo $p['id']; ?>]" value="<?php echo $p['price']; ?>" required></td>
                    <td><input type="number" name="threshold[<?php echo $p['id']; ?>]" value="<?php echo $p['low_stock_threshold'] ?? 10; ?>" required></td>
                    <td class="action-cell">
                        <button type="button" onclick="deleteProduct(<?php echo $p['id']; ?>)" class="delete-btn">Delete</button>
                     </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <tr><td colspan="6" style="text-align:center;">No products found. Add some using the form above.<?php endif; ?>
                </tbody>
            </table>
        </form>

    <!-- Pagination (preserves search and category filter) -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($categoryFilter); ?>" class="page-link">‹ Prev</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($categoryFilter); ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($categoryFilter); ?>" class="page-link">Next ›</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<script>
    // Add product via AJAX
    document.getElementById('addBtn').addEventListener('click', async function() {
        const name = document.getElementById('newName').value.trim();
        const category = document.getElementById('newCategory').value;
        const price = parseFloat(document.getElementById('newPrice').value);
        const threshold = parseInt(document.getElementById('newThreshold').value);

        if (!name) { alert('Product name required'); return; }
        if (isNaN(price) || price < 0) { alert('Valid price required'); return; }
        if (isNaN(threshold) || threshold < 0) { alert('Valid threshold required'); return; }

        const formData = new FormData();
        formData.append('add', '1');
        formData.append('name', name);
        formData.append('category', category);
        formData.append('price', price);
        formData.append('threshold', threshold);

        const res = await fetch(window.location.href, { method: 'POST', body: formData });
        if (res.ok) {
            window.location.reload();
        } else {
            alert('Error adding product');
        }
    });

    // Delete product using AJAX
    async function deleteProduct(id) {
        if (!confirm('⚠️ Delete this product permanently? It will also remove all related sales records if any.')) return;
        const formData = new FormData();
        formData.append('delete_id', id);
        const res = await fetch(window.location.href, { method: 'POST', body: formData });
        const result = await res.json();
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message);
        }
    }
</script>
</body>
</html>