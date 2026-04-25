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

// Handle Batch Update
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

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
    $messageType = $_GET['type'] ?? 'info';
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : 'all';
$limit = 15;
$offset = ($page - 1) * $limit;

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
if ($categoryFilter !== 'all') $stmt->bindValue(':category', $categoryFilter, PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

$countStmt = $pdo->prepare($countSql);
$countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
if ($categoryFilter !== 'all') $countStmt->bindValue(':category', $categoryFilter, PDO::PARAM_STR);
$countStmt->execute();
$total = $countStmt->fetch()['total'];
$totalPages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management – Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0d1117;
            --surface: #161b24;
            --surface-2: #1e2633;
            --surface-3: #252d3a;
            --border: rgba(255,255,255,0.07);
            --accent: #f5a623;
            --accent-dim: rgba(245,166,35,0.12);
            --accent-glow: rgba(245,166,35,0.25);
            --teal: #29b6c8;
            --teal-dim: rgba(41,182,200,0.1);
            --text: #e8edf5;
            --text-muted: #6b7a93;
            --text-faint: #3d4d63;
            --danger: #f87171;
            --danger-dim: rgba(248,113,113,0.1);
            --success: #34d399;
            --success-dim: rgba(52,211,153,0.1);
            --chicken: #fbbf24;
            --frozen: #60a5fa;
            --radius: 14px;
            --radius-sm: 8px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Sora', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 1.5rem;
            background-image:
                radial-gradient(ellipse 80% 50% at 10% -10%, rgba(41,182,200,0.07) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 90% 110%, rgba(245,166,35,0.05) 0%, transparent 60%);
        }

        .container { max-width: 1360px; margin: 0 auto; }

        /* ── Header ── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.75rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--border);
        }

        .logo { display: flex; align-items: center; gap: 0.75rem; }

        .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--accent), #e8920f);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            box-shadow: 0 4px 16px var(--accent-glow);
        }

        .logo-text { display: flex; flex-direction: column; }
        .logo-title { font-size: 1.1rem; font-weight: 700; color: var(--text); letter-spacing: -0.02em; }
        .logo-sub {
            font-size: 0.68rem; color: var(--text-muted); font-weight: 400;
            letter-spacing: 0.06em; text-transform: uppercase;
        }

        .admin-badge {
            background: var(--accent-dim);
            border: 1px solid rgba(245,166,35,0.2);
            color: var(--accent);
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
        }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.45rem 1rem; border-radius: 50px;
            font-size: 0.78rem; font-weight: 600;
            font-family: 'Sora', sans-serif;
            cursor: pointer; border: none;
            transition: all 0.18s ease;
            text-decoration: none; white-space: nowrap;
            letter-spacing: 0.01em;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #e8920f);
            color: #0d1117;
            box-shadow: 0 3px 12px var(--accent-glow);
        }
        .btn-primary:hover { box-shadow: 0 6px 20px rgba(245,166,35,0.4); transform: translateY(-1px); }

        .btn-ghost {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text-muted);
        }
        .btn-ghost:hover { border-color: var(--teal); color: var(--teal); background: var(--teal-dim); }

        .btn-teal {
            background: var(--teal-dim);
            border: 1px solid rgba(41,182,200,0.2);
            color: var(--teal);
        }
        .btn-teal:hover { background: rgba(41,182,200,0.18); }

        .btn-danger-sm {
            background: var(--danger-dim);
            border: 1px solid rgba(248,113,113,0.2);
            color: var(--danger);
            padding: 0.3rem 0.75rem;
            font-size: 0.72rem;
        }
        .btn-danger-sm:hover { background: rgba(248,113,113,0.18); }

        .btn-save {
            background: linear-gradient(135deg, var(--teal), #1a9aab);
            color: #0d1117;
            box-shadow: 0 3px 12px rgba(41,182,200,0.3);
            font-weight: 700;
        }
        .btn-save:hover { box-shadow: 0 6px 20px rgba(41,182,200,0.4); transform: translateY(-1px); }

        /* ── Alert message ── */
        .alert {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.85rem 1.1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
            font-size: 0.82rem; font-weight: 500;
        }
        .alert-success {
            background: var(--success-dim);
            border: 1px solid rgba(52,211,153,0.2);
            color: var(--success);
        }
        .alert-error {
            background: var(--danger-dim);
            border: 1px solid rgba(248,113,113,0.2);
            color: var(--danger);
        }

        /* ── Controls / Add form ── */
        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.1rem 1.25rem;
            margin-bottom: 1.25rem;
        }

        .panel-title {
            font-size: 0.68rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.09em;
            color: var(--text-faint);
            margin-bottom: 0.9rem;
        }

        .toolbar {
            display: flex; justify-content: space-between;
            align-items: center; flex-wrap: wrap; gap: 1rem;
        }

        .toolbar-left, .toolbar-right { display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap; }

        input[type="text"], input[type="number"], select {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text);
            font-family: 'Sora', sans-serif;
            font-size: 0.8rem;
            padding: 0.45rem 0.85rem;
            outline: none;
            transition: 0.18s;
        }
        input[type="text"]:focus, input[type="number"]:focus, select:focus {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px var(--teal-dim);
        }
        select option { background: #1e2633; }

        .add-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 0.75rem;
            align-items: end;
        }

        .field-group { display: flex; flex-direction: column; gap: 0.35rem; }
        .field-label {
            font-size: 0.67rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.07em;
            color: var(--text-muted);
        }

        /* ── Table ── */
        .table-wrap {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            margin-bottom: 1.25rem;
        }

        .table-header-bar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid var(--border);
            background: var(--surface-2);
        }

        .table-title { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.07em; }
        .record-count { font-family: 'DM Mono', monospace; font-size: 0.72rem; color: var(--text-faint); }

        .table-scroll { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; min-width: 700px; }

        thead tr { border-bottom: 1px solid var(--border); }

        th {
            padding: 0.7rem 1rem;
            text-align: left;
            font-size: 0.67rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.09em;
            color: var(--text-faint);
            background: var(--surface-2);
            white-space: nowrap;
        }

        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.14s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--surface-2); }

        td {
            padding: 0.6rem 1rem;
            font-size: 0.81rem; color: var(--text);
            vertical-align: middle;
        }

        .row-num {
            font-family: 'DM Mono', monospace;
            font-size: 0.72rem; color: var(--text-faint);
            width: 40px;
        }

        /* Inline table inputs */
        .tbl-input {
            background: var(--surface-3) !important;
            border: 1px solid var(--border) !important;
            border-radius: var(--radius-sm) !important;
            color: var(--text) !important;
            font-family: 'Sora', sans-serif !important;
            font-size: 0.78rem !important;
            padding: 0.36rem 0.65rem !important;
            width: 100%; min-width: 80px;
            outline: none; transition: 0.15s;
        }
        .tbl-input:focus {
            border-color: var(--teal) !important;
            background: rgba(41,182,200,0.05) !important;
            box-shadow: 0 0 0 2px var(--teal-dim);
        }

        .tbl-select {
            background: var(--surface-3) !important;
            border: 1px solid var(--border) !important;
            border-radius: var(--radius-sm) !important;
            color: var(--text) !important;
            font-family: 'Sora', sans-serif !important;
            font-size: 0.78rem !important;
            padding: 0.36rem 0.65rem !important;
            width: 100%;
            outline: none; transition: 0.15s;
        }
        .tbl-select:focus {
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 2px var(--accent-dim);
        }

        .badge {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.22rem 0.65rem; border-radius: 50px;
            font-size: 0.67rem; font-weight: 700;
            letter-spacing: 0.04em; text-transform: uppercase;
        }
        .badge-chicken { background: rgba(251,191,36,0.12); color: var(--chicken); border: 1px solid rgba(251,191,36,0.2); }
        .badge-frozen  { background: rgba(96,165,250,0.1);  color: var(--frozen);  border: 1px solid rgba(96,165,250,0.2); }

        .price-cell {
            font-family: 'DM Mono', monospace;
            font-size: 0.8rem; color: var(--success);
        }

        /* ── Pagination ── */
        .pagination {
            display: flex; justify-content: center; gap: 0.4rem;
            padding: 0.85rem; border-top: 1px solid var(--border);
            flex-wrap: wrap;
        }

        .page-btn {
            min-width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            padding: 0 0.65rem;
            border-radius: var(--radius-sm);
            background: var(--surface-2);
            border: 1px solid var(--border);
            color: var(--text-muted);
            font-size: 0.78rem; font-weight: 600;
            cursor: pointer; transition: 0.15s;
            text-decoration: none;
            font-family: 'Sora', sans-serif;
        }
        .page-btn:hover { border-color: var(--teal); color: var(--teal); }
        .page-btn.active { background: var(--accent); color: #0d1117; border-color: var(--accent); }

        /* ── Bottom bar ── */
        .bottom-bar {
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 1rem;
        }
        .hint { font-size: 0.7rem; color: var(--text-faint); font-family: 'DM Mono', monospace; }

        /* ── Modal ── */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(8px);
            display: none; justify-content: center; align-items: center;
            z-index: 1000;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2rem 2.25rem;
            max-width: 380px; width: 90%;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
            animation: popIn 0.2s cubic-bezier(0.34,1.56,0.64,1);
        }
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.9) translateY(12px); }
            to   { opacity: 1; transform: scale(1)   translateY(0); }
        }
        .modal-icon { font-size: 2rem; margin-bottom: 0.75rem; }
        .modal-message { font-size: 0.92rem; color: var(--text); margin-bottom: 1.5rem; line-height: 1.5; font-weight: 500; }
        .modal-buttons { display: flex; gap: 0.75rem; justify-content: center; }

        .empty-row td { padding: 3rem; color: var(--text-faint); font-size: 0.85rem; text-align: center; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--surface-3); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-faint); }

        @media (max-width: 768px) {
            body { padding: 1rem; }
            .add-grid { grid-template-columns: 1fr 1fr; }
            .add-grid .btn { grid-column: 1 / -1; }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Header -->
    <div class="header">
        <div class="logo">
            <div class="logo-icon">⚙️</div>
            <div class="logo-text">
                <span class="logo-title">Product Management</span>
                <span class="logo-sub">Janeth Business · Admin Panel</span>
            </div>
            <span class="admin-badge">Admin</span>
        </div>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:center;">
            <a href="../janeth-input.php" class="btn btn-ghost">← Back to Entry</a>
        </div>
    </div>

    <!-- Flash message -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
            <?php echo $messageType === 'success' ? '✅' : '⚠️'; ?>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="panel">
        <div class="panel-title">Filter & Search</div>
        <form method="GET" class="toolbar" id="filterForm">
            <div class="toolbar-left">
                <input type="text" name="search" placeholder="🔍 Search by name…" value="<?php echo htmlspecialchars($search); ?>" style="width:220px;">
                <select name="category" onchange="this.form.submit()">
                    <option value="all"    <?php echo $categoryFilter == 'all'    ? 'selected' : ''; ?>>All Categories</option>
                    <option value="Chicken"<?php echo $categoryFilter == 'Chicken'? 'selected' : ''; ?>>🐔 Chicken</option>
                    <option value="Frozen" <?php echo $categoryFilter == 'Frozen' ? 'selected' : ''; ?>>❄️ Frozen</option>
                </select>
                <button type="submit" class="btn btn-teal">Filter</button>
                <?php if ($search || $categoryFilter !== 'all'): ?>
                    <a href="products.php" class="btn btn-ghost">✕ Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Add product -->
    <div class="panel">
        <div class="panel-title">Add New Product</div>
        <div class="add-grid">
            <div class="field-group">
                <span class="field-label">Product Name</span>
                <input type="text" id="newName" placeholder="e.g. Whole Chicken">
            </div>
            <div class="field-group">
                <span class="field-label">Category</span>
                <select id="newCategory">
                    <option value="Chicken">🐔 Chicken</option>
                    <option value="Frozen">❄️ Frozen</option>
                </select>
            </div>
            <div class="field-group">
                <span class="field-label">Price (₱)</span>
                <input type="number" id="newPrice" placeholder="0.00" step="0.01" min="0">
            </div>
            <div class="field-group">
                <span class="field-label">Low Stock Threshold</span>
                <input type="number" id="newThreshold" value="10" min="0">
            </div>
            <button class="btn btn-primary" id="addBtn" style="align-self:end;">+ Add</button>
        </div>
    </div>

    <!-- Modal -->
    <div id="modalOverlay" class="modal-overlay">
        <div class="modal">
            <div class="modal-icon" id="modalIcon">💬</div>
            <p class="modal-message" id="modalMessage">Are you sure?</p>
            <div class="modal-buttons">
                <button id="modalConfirmBtn" class="btn btn-primary">Confirm</button>
                <button id="modalCancelBtn" class="btn btn-ghost">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Products table -->
    <div class="table-wrap">
        <div class="table-header-bar">
            <span class="table-title">Products</span>
            <span class="record-count"><?php echo $total; ?> total · page <?php echo $page; ?> of <?php echo max(1,$totalPages); ?></span>
        </div>

        <form method="POST" id="batchForm">
            <input type="hidden" name="batch_update" value="1">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th style="width:40px">#</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price (₱)</th>
                            <th>Low Stock</th>
                            <th style="width:80px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr class="empty-row"><td colspan="6">No products found. Add one using the form above.</td></tr>
                        <?php else: ?>
                        <?php $counter = ($page - 1) * $limit + 1; foreach ($products as $p): ?>
                        <tr>
                            <td class="row-num"><?php echo $counter++; ?></td>
                            <td><input class="tbl-input" type="text" name="name[<?php echo $p['id']; ?>]" value="<?php echo htmlspecialchars($p['name']); ?>" required></td>
                            <td>
                                <select class="tbl-select" name="category[<?php echo $p['id']; ?>]">
                                    <option value="Chicken" <?php echo $p['category']=='Chicken'?'selected':''; ?>>🐔 Chicken</option>
                                    <option value="Frozen"  <?php echo $p['category']=='Frozen' ?'selected':''; ?>>❄️ Frozen</option>
                                </select>
                            </td>
                            <td><input class="tbl-input" type="number" step="0.01" min="0" name="price[<?php echo $p['id']; ?>]" value="<?php echo $p['price']; ?>" required style="width:100px;font-family:'DM Mono',monospace;"></td>
                            <td><input class="tbl-input" type="number" min="0" name="threshold[<?php echo $p['id']; ?>]" value="<?php echo $p['low_stock_threshold'] ?? 10; ?>" required style="width:80px;"></td>
                            <td>
                                <button type="button" onclick="deleteProduct(<?php echo $p['id']; ?>)" class="btn btn-danger-sm">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($categoryFilter); ?>" class="page-btn">‹</a>
                <?php endif; ?>
                <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($categoryFilter); ?>" class="page-btn <?php echo $i==$page?'active':''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($categoryFilter); ?>" class="page-btn">›</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Bottom bar -->
    <div class="bottom-bar">
        <span class="hint">✦ Edit inline then save all · Deletes are immediate</span>
        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
            <a href="../janeth-input.php" class="btn btn-ghost">← Back to Entry</a>
            <button type="submit" form="batchForm" class="btn btn-save">💾 Save All Changes</button>
        </div>
    </div>

</div>

<script>
    function showModal(message, onConfirm, onCancel, icon = '💬') {
        document.getElementById('modalMessage').innerText = message;
        document.getElementById('modalIcon').innerText = icon;
        const overlay = document.getElementById('modalOverlay');
        overlay.classList.add('active');

        const confirmBtn = document.getElementById('modalConfirmBtn');
        const cancelBtn  = document.getElementById('modalCancelBtn');

        const newConfirm = confirmBtn.cloneNode(true);
        const newCancel  = cancelBtn.cloneNode(true);
        confirmBtn.replaceWith(newConfirm);
        cancelBtn.replaceWith(newCancel);

        const close = () => overlay.classList.remove('active');
        newConfirm.addEventListener('click', () => { close(); if (onConfirm) onConfirm(); });
        newCancel.addEventListener('click',  () => { close(); if (onCancel)  onCancel();  });
    }

    function showAlert(message, isError = false) {
        showModal(message, null, null, isError ? '⚠️' : '✅');
        setTimeout(() => document.getElementById('modalOverlay').classList.remove('active'), 2200);
    }

    document.getElementById('addBtn').addEventListener('click', async function () {
        const name      = document.getElementById('newName').value.trim();
        const category  = document.getElementById('newCategory').value;
        const price     = parseFloat(document.getElementById('newPrice').value);
        const threshold = parseInt(document.getElementById('newThreshold').value);

        if (!name)                         return showAlert('Product name is required.', true);
        if (isNaN(price) || price < 0)     return showAlert('A valid price is required.', true);
        if (isNaN(threshold) || threshold < 0) return showAlert('A valid threshold is required.', true);

        const formData = new FormData();
        formData.append('add', '1');
        formData.append('name', name);
        formData.append('category', category);
        formData.append('price', price);
        formData.append('threshold', threshold);

        const res = await fetch(window.location.href, { method: 'POST', body: formData });
        if (res.ok) window.location.reload();
        else showAlert('Error adding product.', true);
    });

    async function deleteProduct(id) {
        showModal('Delete this product permanently?', async () => {
            const formData = new FormData();
            formData.append('delete_id', id);
            const res = await fetch(window.location.href, { method: 'POST', body: formData });
            const result = await res.json();
            if (result.success) window.location.reload();
            else showAlert(result.message, true);
        }, null, '🗑️');
    }
</script>
</body>
</html>