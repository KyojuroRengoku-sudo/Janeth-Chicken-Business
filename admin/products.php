<?php
require_once __DIR__ . '/bootstrap.php';

// ── Add product (AJAX) ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add'])) {
    header('Content-Type: application/json');
    $name      = trim($_POST['name']      ?? '');
    $category  = $_POST['category']       ?? 'Chicken';
    $price     = floatval($_POST['price'] ?? 0);
    $threshold = intval($_POST['threshold'] ?? 10);

    if (empty($name))   { echo json_encode(['success'=>false,'message'=>'Product name is required.']);  exit; }
    if ($price < 0)     { echo json_encode(['success'=>false,'message'=>'Price cannot be negative.']);  exit; }
    if ($threshold < 0) { echo json_encode(['success'=>false,'message'=>'Threshold cannot be negative.']); exit; }

    $chk = $pdo->prepare("SELECT id FROM products WHERE name=? AND is_deleted=0");
    $chk->execute([$name]);
    if ($chk->fetch()) { echo json_encode(['success'=>false,'message'=>'A product with this name already exists.']); exit; }

    $pdo->prepare(
        "INSERT INTO products (name,category,selling_price,low_stock_threshold,visible_input,visible_dashboard)
         VALUES (?,?,?,?,1,1)"
    )->execute([$name,$category,$price,$threshold]);
    echo json_encode(['success'=>true,'message'=>'Product added successfully.']);
    exit;
}

// ── Soft-delete (AJAX) ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete_id'])) {
    header('Content-Type: application/json');
    $id = intval($_POST['delete_id']);
    // Soft-delete: set is_deleted=1 and record the time
    $pdo->prepare("UPDATE products SET is_deleted=1, deleted_at=NOW() WHERE id=?")->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'Product moved to Recently Deleted.']);
    exit;
}

// ── Restore (AJAX) ───────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['restore_id'])) {
    header('Content-Type: application/json');
    $id = intval($_POST['restore_id']);
    $pdo->prepare("UPDATE products SET is_deleted=0, deleted_at=NULL WHERE id=?")->execute([$id]);
    echo json_encode(['success'=>true,'message'=>'Product restored.']);
    exit;
}

// ── Permanently delete (AJAX) ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['purge_id'])) {
    header('Content-Type: application/json');
    $id  = intval($_POST['purge_id']);
    $chk = $pdo->prepare("SELECT id FROM janeth_records WHERE product_id=? LIMIT 1");
    $chk->execute([$id]);
    if ($chk->fetch()) {
        echo json_encode(['success'=>false,'message'=>'Cannot permanently delete: product has sales history.']);
    } else {
        $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
        echo json_encode(['success'=>true]);
    }
    exit;
}

// ── Batch update (name, category, price, threshold, visibility) ──────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['batch_update'])) {
    $names      = $_POST['name']             ?? [];
    $categories = $_POST['category']         ?? [];
    $prices     = $_POST['price']            ?? [];
    $thresholds = $_POST['threshold']        ?? [];
    $vis_input  = $_POST['visible_input']    ?? [];   // checkboxes — only present when checked
    $vis_dash   = $_POST['visible_dashboard']?? [];

    $pdo->beginTransaction();
    try {
        foreach ($names as $id => $name) {
            $cat  = $categories[$id]  ?? 'Chicken';
            $pri  = floatval($prices[$id]      ?? 0);
            $thr  = intval($thresholds[$id]    ?? 10);
            $vi   = isset($vis_input[$id])  ? 1 : 0;
            $vd   = isset($vis_dash[$id])   ? 1 : 0;
            if (empty(trim($name)) || $pri < 0 || $thr < 0) continue;
            $pdo->prepare(
                "UPDATE products
                    SET name=?, category=?, selling_price=?, low_stock_threshold=?,
                        visible_input=?, visible_dashboard=?
                  WHERE id=?"
            )->execute([trim($name), $cat, $pri, $thr, $vi, $vd, $id]);
        }
        $pdo->commit();
        header("Location: products.php?msg=".urlencode('All products updated.')."&type=success");
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: products.php?msg=".urlencode('Error: '.$e->getMessage())."&type=error");
    }
    exit;
}

// ── Ensure deleted_at column exists (runs silently if already present) ───
try {
    $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL DEFAULT NULL");
    $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS visible_input TINYINT(1) NOT NULL DEFAULT 1");
    $pdo->exec("ALTER TABLE products ADD COLUMN IF NOT EXISTS visible_dashboard TINYINT(1) NOT NULL DEFAULT 1");
} catch (Exception $e) { /* columns already exist */ }

$message = $msgType = '';
if (isset($_GET['msg'])) { $message = htmlspecialchars($_GET['msg']); $msgType = $_GET['type'] ?? 'info'; }

// ── Active products ──────────────────────────────────────────────────────
$page   = max(1, intval($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$catF   = $_GET['category'] ?? 'all';
$limit  = 20; $offset = ($page-1)*$limit;

$where = "is_deleted=0 AND name LIKE :s" . ($catF!=='all' ? " AND category=:c" : "");
$stmt  = $pdo->prepare("SELECT * FROM products WHERE $where ORDER BY category,name LIMIT :l OFFSET :o");
$stmt->bindValue(':s', "%$search%");
if ($catF!=='all') $stmt->bindValue(':c', $catF);
$stmt->bindValue(':l', $limit, PDO::PARAM_INT);
$stmt->bindValue(':o', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

$cntStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE $where");
$cntStmt->bindValue(':s', "%$search%");
if ($catF!=='all') $cntStmt->bindValue(':c', $catF);
$cntStmt->execute();
$total = $cntStmt->fetchColumn();
$totPages = max(1, ceil($total/$limit));

// ── Soft-deleted products ────────────────────────────────────────────────
$deleted = $pdo->query(
    "SELECT * FROM products WHERE is_deleted=1 ORDER BY deleted_at DESC LIMIT 50"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management · Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* ── CSS Variables (dark default) ── */
        :root {
            --bg:#0a0e17; --surface:#111827; --surface-2:#1a2234; --surface-3:#222d42;
            --border:rgba(255,255,255,0.07); --border-hi:rgba(255,255,255,0.12);
            --accent:#f5a623; --accent-dim:rgba(245,166,35,.12); --accent-glow:rgba(245,166,35,.25);
            --teal:#29b6c8; --teal-dim:rgba(41,182,200,.1); --teal-glow:rgba(41,182,200,.28);
            --text:#e8edf5; --text-muted:#6b7a93; --text-faint:#3d4d63;
            --danger:#f87171; --danger-dim:rgba(248,113,113,.1);
            --success:#34d399; --success-dim:rgba(52,211,153,.1);
            --chicken:#fbbf24; --frozen:#60a5fa;
            --radius:14px; --radius-sm:9px;
            --input-bg:#1a2234;
        }

        /* ── Light mode overrides ── */
        [data-theme="light"] {
            --bg:#f0f4f9; --surface:#ffffff; --surface-2:#e8eef5; --surface-3:#d8e3ef;
            --border:rgba(0,0,0,0.09); --border-hi:rgba(0,0,0,0.16);
            --text:#0d1b2a; --text-muted:#4a6080; --text-faint:#7090b0;
            --danger-dim:rgba(248,113,113,.12); --success-dim:rgba(52,211,153,.12);
            --input-bg:#f0f4f9;
        }
        /* Light-mode specific overrides for elements that need explicit bg */
        [data-theme="light"] input,
        [data-theme="light"] select,
        [data-theme="light"] .tbl-in,
        [data-theme="light"] .tbl-sel {
            background: var(--surface-2) !important;
            color: var(--text) !important;
            border-color: var(--border) !important;
        }
        [data-theme="light"] select option,
        [data-theme="light"] .tbl-sel option { background: #e8eef5; color: #0d1b2a; }
        [data-theme="light"] tbody tr:hover  { background: var(--surface-2); }
        [data-theme="light"] .panel          { background: var(--surface); border-color: var(--border); }
        [data-theme="light"] thead tr        { background: var(--surface-2); }
        [data-theme="light"] th              { background: var(--surface-2); color: var(--text-faint); }
        [data-theme="light"] .table-wrap     { background: var(--surface); border-color: var(--border); }
        [data-theme="light"] .table-hd-bar  { background: var(--surface-2); }
        [data-theme="light"] .modal          { background: var(--surface); }
        [data-theme="light"] .deleted-section { background: var(--surface); border-color: var(--border); }

        /* ── Reset & Base ── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Sora', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            font-size: 16px;
            padding: 1.5rem;
            background-image:
                radial-gradient(ellipse 70% 50% at 10% -10%, rgba(41,182,200,.06) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 90% 110%, rgba(245,166,35,.04) 0%, transparent 60%);
        }
        .container { max-width: 1400px; margin: 0 auto; }

        /* ── Header ── */
        .header {
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 1rem;
            margin-bottom: 1.75rem; padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--border);
        }
        .logo { display: flex; align-items: center; gap: .75rem; }
        .logo-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--accent), #e8920f);
            border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem; box-shadow: 0 4px 16px var(--accent-glow);
        }
        .logo-title { font-size: 1.15rem; font-weight: 700; letter-spacing: -.02em; }
        .logo-sub   { font-size: .67rem; color: var(--text-muted); letter-spacing: .07em; text-transform: uppercase; }
        .admin-badge {
            background: var(--accent-dim); border: 1px solid rgba(245,166,35,.25);
            color: var(--accent); font-size: .62rem; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase;
            padding: .18rem .6rem; border-radius: 50px;
        }
        .hd-actions { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .5rem 1.1rem; border-radius: 50px;
            font-size: .82rem; font-weight: 600; font-family: 'Sora', sans-serif;
            cursor: pointer; border: none; transition: .18s;
            text-decoration: none; white-space: nowrap; letter-spacing: .01em;
        }
        .btn-primary { background: linear-gradient(135deg, var(--accent), #e8920f); color: #0a0e17; box-shadow: 0 3px 12px var(--accent-glow); }
        .btn-primary:hover { box-shadow: 0 6px 20px rgba(245,166,35,.45); transform: translateY(-1px); }
        .btn-ghost { background: var(--surface-2); border: 1px solid var(--border); color: var(--text-muted); }
        .btn-ghost:hover { border-color: var(--teal); color: var(--teal); background: var(--teal-dim); }
        .btn-teal { background: var(--teal-dim); border: 1px solid rgba(41,182,200,.25); color: var(--teal); font-weight: 700; }
        .btn-teal:hover { background: rgba(41,182,200,.2); }
        .btn-save { background: linear-gradient(135deg, var(--teal), #1a9aab); color: #05111e; font-weight: 700; box-shadow: 0 3px 12px var(--teal-glow); }
        .btn-save:hover { box-shadow: 0 6px 20px rgba(41,182,200,.45); transform: translateY(-1px); }
        .btn-danger { background: var(--danger-dim); border: 1px solid rgba(248,113,113,.25); color: var(--danger); }
        .btn-danger:hover { background: rgba(248,113,113,.2); }
        .btn-sm { padding: .28rem .7rem; font-size: .72rem; }
        .btn-icon { padding: .3rem .5rem; font-size: .85rem; border-radius: var(--radius-sm); }

        /* ── Theme toggle ── */
        #themeToggle {
            background: var(--surface-2); border: 1px solid var(--border-hi);
            color: var(--text-muted); border-radius: 50px;
            padding: .42rem .9rem; font-size: .8rem; font-weight: 600;
            cursor: pointer; font-family: 'Sora', sans-serif; transition: .18s;
        }
        #themeToggle:hover { border-color: var(--teal); color: var(--teal); }

        /* ── Alert ── */
        .alert {
            display: flex; align-items: center; gap: .75rem;
            padding: .9rem 1.1rem; border-radius: var(--radius-sm);
            margin-bottom: 1.25rem; font-size: .85rem; font-weight: 500;
            animation: alertIn .2s ease-out;
        }
        @keyframes alertIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
        .alert-success { background: var(--success-dim); border: 1px solid rgba(52,211,153,.25);  color: var(--success); }
        .alert-error   { background: var(--danger-dim);  border: 1px solid rgba(248,113,113,.25); color: var(--danger); }

        /* ── Panel (filter + add form) ── */
        .panel {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 1.15rem 1.4rem; margin-bottom: 1.1rem;
        }
        .panel-title {
            font-size: .65rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .09em; color: var(--text-faint); margin-bottom: .9rem;
        }

        /* ── Toolbar (filter bar) ── */
        .toolbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .75rem; }
        .toolbar-l { display: flex; align-items: center; gap: .65rem; flex-wrap: wrap; }

        /* ── Inputs (global in this page) ── */
        input[type="text"], input[type="number"], select {
            background: var(--surface-2); border: 1px solid var(--border);
            border-radius: var(--radius-sm); color: var(--text);
            font-family: 'Sora', sans-serif; font-size: .82rem;
            padding: .45rem .9rem; outline: none; transition: .18s;
        }
        input:focus, select:focus { border-color: var(--teal); box-shadow: 0 0 0 3px var(--teal-dim); }
        select option { background: #1a2234; }

        /* ── Add-product grid ── */
        .add-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: .75rem; align-items: end;
        }
        .fg { display: flex; flex-direction: column; gap: .3rem; }
        .fg label {
            font-size: .67rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .08em; color: var(--text-muted);
        }

        /* ── Table wrapper ── */
        .table-wrap {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); overflow: hidden; margin-bottom: 1.25rem;
        }
        .table-hd-bar {
            display: flex; justify-content: space-between; align-items: center;
            padding: .9rem 1.2rem; border-bottom: 1px solid var(--border);
            background: var(--surface-2); flex-wrap: wrap; gap: .65rem;
        }
        .table-title { font-size: .78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .07em; }
        .rec-count   { font-family: 'DM Mono', monospace; font-size: .72rem; color: var(--text-faint); }
        .tbl-scroll  { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; min-width: 820px; }
        thead tr { border-bottom: 1px solid var(--border); }
        th {
            padding: .7rem 1rem; text-align: left;
            font-size: .65rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .09em; color: var(--text-faint);
            background: var(--surface-2); white-space: nowrap;
        }
        th.center { text-align: center; }
        tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--surface-2); }
        td { padding: .6rem 1rem; font-size: .82rem; color: var(--text); vertical-align: middle; }
        td.center { text-align: center; }
        .row-num { font-family: 'DM Mono', monospace; font-size: .7rem; color: var(--text-faint); }

        /* ── Inline table inputs ── */
        .tbl-in {
            width: 100%; background: var(--surface-3) !important;
            border: 1px solid var(--border) !important;
            border-radius: var(--radius-sm) !important;
            color: var(--text) !important; font-family: 'Sora', sans-serif !important;
            font-size: .8rem !important; padding: .35rem .7rem !important;
            outline: none; transition: .15s;
        }
        .tbl-in:focus { border-color: var(--teal) !important; box-shadow: 0 0 0 2px var(--teal-dim); }

        .tbl-sel {
            width: 100%; background: var(--surface-3) !important;
            border: 1px solid var(--border) !important;
            border-radius: var(--radius-sm) !important;
            color: var(--text) !important; font-family: 'Sora', sans-serif !important;
            font-size: .8rem !important; padding: .35rem .7rem !important; outline: none;
        }
        .tbl-sel:focus { border-color: var(--accent) !important; }
        .tbl-sel option { background: #1a2234; }

        /* ── Visibility toggle (pill switch) ── */
        .vis-toggle {
            display: inline-flex; align-items: center; gap: .4rem;
            font-size: .72rem; font-weight: 600; cursor: pointer; user-select: none;
        }
        .vis-toggle input[type="checkbox"] { display: none; }
        .vis-pill {
            width: 36px; height: 20px; border-radius: 30px;
            background: var(--surface-3); border: 1px solid var(--border);
            position: relative; transition: background .2s, border-color .2s; flex-shrink: 0;
        }
        .vis-pill::after {
            content: ''; position: absolute;
            width: 14px; height: 14px; border-radius: 50%;
            background: var(--text-faint); top: 2px; left: 2px;
            transition: transform .2s, background .2s;
        }
        .vis-toggle input:checked + .vis-pill { background: var(--teal); border-color: rgba(41,182,200,.5); }
        .vis-toggle input:checked + .vis-pill::after { transform: translateX(16px); background: #fff; }
        .vis-label { color: var(--text-faint); transition: color .15s; font-size: .7rem; }
        .vis-toggle input:checked ~ .vis-label { color: var(--teal); }

        /* ── Category badges ── */
        .badge {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .2rem .65rem; border-radius: 50px;
            font-size: .65rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
        }
        .b-ch { background: rgba(251,191,36,.12); color: var(--chicken); border: 1px solid rgba(251,191,36,.22); }
        .b-fr { background: rgba(96,165,250,.1);  color: var(--frozen);  border: 1px solid rgba(96,165,250,.22); }

        /* ── Pagination ── */
        .pagination {
            display: flex; justify-content: center; gap: .4rem;
            padding: .85rem; border-top: 1px solid var(--border); flex-wrap: wrap;
        }
        .pg-btn {
            min-width: 32px; height: 32px; display: flex; align-items: center;
            justify-content: center; padding: 0 .65rem;
            border-radius: var(--radius-sm); background: var(--surface-2);
            border: 1px solid var(--border); color: var(--text-muted);
            font-size: .78rem; font-weight: 600; cursor: pointer;
            transition: .15s; text-decoration: none; font-family: 'Sora', sans-serif;
        }
        .pg-btn:hover { border-color: var(--teal); color: var(--teal); }
        .pg-btn.active { background: var(--accent); color: #0a0e17; border-color: var(--accent); font-weight: 700; }

        /* ── Bottom action bar ── */
        .bot-bar {
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;
        }
        .hint { font-size: .7rem; color: var(--text-faint); font-family: 'DM Mono', monospace; }

        /* ── Deleted products section ── */
        .deleted-section {
            background: var(--surface); border: 1px solid rgba(248,113,113,.2);
            border-radius: var(--radius); overflow: hidden; margin-bottom: 1.5rem;
        }
        .deleted-hd {
            display: flex; align-items: center; gap: .85rem;
            padding: .9rem 1.2rem; border-bottom: 1px solid rgba(248,113,113,.15);
            background: rgba(248,113,113,.05); cursor: pointer; user-select: none;
        }
        .deleted-hd-title {
            font-size: .82rem; font-weight: 700; color: var(--danger);
            display: flex; align-items: center; gap: .5rem; flex: 1;
        }
        .deleted-count {
            font-family: 'DM Mono', monospace; font-size: .7rem;
            background: rgba(248,113,113,.15); color: var(--danger);
            border: 1px solid rgba(248,113,113,.2); border-radius: 50px;
            padding: .1rem .55rem;
        }
        .deleted-chevron { color: var(--danger); font-size: .85rem; transition: transform .2s; }
        .deleted-section.open .deleted-chevron { transform: rotate(180deg); }
        .deleted-body { display: none; }
        .deleted-section.open .deleted-body { display: block; }
        .deleted-item {
            display: flex; align-items: center; gap: 1rem;
            padding: .75rem 1.2rem; border-bottom: 1px solid var(--border);
            flex-wrap: wrap; transition: background .12s;
        }
        .deleted-item:last-child { border-bottom: none; }
        .deleted-item:hover { background: var(--surface-2); }
        .del-name  { font-weight: 600; font-size: .85rem; flex: 1; min-width: 120px; }
        .del-meta  { font-size: .72rem; color: var(--text-muted); font-family: 'DM Mono', monospace; }
        .del-time  { font-size: .7rem; color: var(--text-faint); font-family: 'DM Mono', monospace; margin-left: auto; white-space: nowrap; }
        .del-actions { display: flex; gap: .5rem; }

        /* ── Modal ── */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.65);
            backdrop-filter: blur(8px);
            display: none; justify-content: center; align-items: center;
            z-index: 1000; padding: 1rem;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 2rem 2.25rem;
            max-width: 420px; width: 100%; text-align: center;
            box-shadow: 0 25px 60px rgba(0,0,0,.5);
            animation: popIn .2s cubic-bezier(.34,1.56,.64,1);
        }
        @keyframes popIn { from { opacity:0; transform:scale(.9) translateY(12px); } to { opacity:1; transform:none; } }
        .modal-icon { font-size: 2.25rem; margin-bottom: .75rem; }
        .modal-msg  { font-size: .92rem; color: var(--text); margin-bottom: 1.5rem; line-height: 1.5; font-weight: 500; }
        .modal-btns { display: flex; gap: .75rem; justify-content: center; }

        /* ── Misc ── */
        .empty-row td { padding: 3rem; color: var(--text-faint); font-size: .85rem; text-align: center; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--surface-3); border-radius: 3px; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            body { padding: 1rem; }
            .add-grid { grid-template-columns: 1fr 1fr; }
            .add-grid .btn { grid-column: 1 / -1; }
            table { min-width: 600px; }
        }
    </style>
</head>
<body>
<div class="container">

<!-- Header -->
<div class="header">
    <div class="logo">
        <div class="logo-icon">⚙️</div>
        <div>
            <div class="logo-title">Product Management</div>
            <div class="logo-sub">Janeth's Business · Admin</div>
        </div>
        <span class="admin-badge">Admin</span>
    </div>
    <div class="hd-actions">
        <a href="../public/janeth-input.php"    class="btn btn-ghost">✏️ Entry</a>
        <a href="../public/janeth-dashboard.php" class="btn btn-ghost">📊 Dashboard</a>
        <a href="users.php"                      class="btn btn-ghost">👥 Users</a>
        <!-- BUG FIX: single #themeToggle button; theme.js moved to end of body -->
        <button id="themeToggle" onclick="toggleTheme()">☀️ Light</button>
    </div>
</div>

<!-- Flash message -->
<?php if ($message): ?>
<div class="alert alert-<?= $msgType==='success'?'success':'error' ?>">
    <?= $msgType==='success'?'✅':'⚠️' ?> <?= $message ?>
</div>
<?php endif; ?>

<!-- Filter panel -->
<div class="panel">
    <div class="panel-title">🔍 Filter &amp; Search</div>
    <form method="GET" class="toolbar">
        <div class="toolbar-l">
            <input type="text" name="search" placeholder="Search by name…"
                   value="<?= htmlspecialchars($search) ?>" style="width:220px">
            <select name="category" onchange="this.form.submit()">
                <option value="all"     <?= $catF==='all'    ?'selected':''?>>All Categories</option>
                <option value="Chicken" <?= $catF==='Chicken'?'selected':''?>>🐔 Chicken</option>
                <option value="Frozen"  <?= $catF==='Frozen' ?'selected':''?>>❄️ Frozen</option>
            </select>
            <button type="submit" class="btn btn-teal">Filter</button>
            <?php if ($search || $catF!=='all'): ?>
                <a href="products.php" class="btn btn-ghost">✕ Clear</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Add product panel -->
<div class="panel">
    <div class="panel-title">➕ Add New Product</div>
    <div class="add-grid">
        <div class="fg">
            <label>Product Name</label>
            <input type="text" id="nName" placeholder="e.g. Whole Chicken">
        </div>
        <div class="fg">
            <label>Category</label>
            <select id="nCat">
                <option value="Chicken">🐔 Chicken</option>
                <option value="Frozen">❄️ Frozen</option>
            </select>
        </div>
        <div class="fg">
            <label>Price (₱)</label>
            <input type="number" id="nPrice" placeholder="0.00" step="0.01" min="0">
        </div>
        <div class="fg">
            <label>Low Stock Threshold</label>
            <input type="number" id="nThreshold" value="10" min="0">
        </div>
        <button class="btn btn-primary" id="addBtn" style="align-self:end">+ Add Product</button>
    </div>
</div>

<!-- Confirmation modal -->
<div id="modalOverlay" class="modal-overlay">
    <div class="modal">
        <div class="modal-icon" id="mIcon">💬</div>
        <p class="modal-msg" id="mMsg">Are you sure?</p>
        <div class="modal-btns">
            <button id="mOk"  class="btn btn-primary">OK</button>
            <button id="mCan" class="btn btn-ghost">Cancel</button>
        </div>
    </div>
</div>

<!-- Products table -->
<div class="table-wrap">
    <div class="table-hd-bar">
        <span class="table-title">📦 Active Products</span>
        <span class="rec-count">
            <?= $total ?> product<?= $total!=1?'s':''?> &nbsp;·&nbsp;
            page <?= $page ?> of <?= $totPages ?>
        </span>
    </div>
    <form method="POST" id="batchForm">
        <input type="hidden" name="batch_update" value="1">
        <div class="tbl-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="width:36px">#</th>
                        <th>Product Name</th>
                        <th style="width:130px">Category</th>
                        <th style="width:110px">Price (₱)</th>
                        <th style="width:100px">Threshold</th>
                        <th class="center" style="width:90px"
                            title="Show on Daily Entry page">Entry</th>
                        <th class="center" style="width:90px"
                            title="Show on Dashboard">Dashboard</th>
                        <th class="center" style="width:80px">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($products)): ?>
                    <tr class="empty-row"><td colspan="8">No products found. Add one above.</td></tr>
                <?php else:
                    $rowNum = ($page-1)*$limit + 1;
                    foreach ($products as $p): ?>
                <tr>
                    <td class="row-num"><?= $rowNum++ ?></td>
                    <td>
                        <input class="tbl-in" type="text"
                               name="name[<?= $p['id'] ?>]"
                               value="<?= htmlspecialchars($p['name']) ?>" required>
                    </td>
                    <td>
                        <select class="tbl-sel" name="category[<?= $p['id'] ?>]">
                            <option value="Chicken" <?= $p['category']==='Chicken'?'selected':''?>>🐔 Chicken</option>
                            <option value="Frozen"  <?= $p['category']==='Frozen' ?'selected':''?>>❄️ Frozen</option>
                        </select>
                    </td>
                    <td>
                        <input class="tbl-in" type="number" step="0.01" min="0"
                               name="price[<?= $p['id'] ?>]"
                               value="<?= number_format((float)$p['selling_price'],2,'.','') ?>"
                               required style="font-family:'DM Mono',monospace;width:95px">
                    </td>
                    <td>
                        <input class="tbl-in" type="number" min="0"
                               name="threshold[<?= $p['id'] ?>]"
                               value="<?= intval($p['low_stock_threshold'] ?? 10) ?>"
                               required style="width:75px">
                    </td>

                    <!-- Visibility: Input page -->
                    <td class="center">
                        <label class="vis-toggle" title="Show on Entry page">
                            <input type="checkbox"
                                   name="visible_input[<?= $p['id'] ?>]"
                                   value="1"
                                   <?= ($p['visible_input'] ?? 1) ? 'checked' : '' ?>>
                            <span class="vis-pill"></span>
                        </label>
                    </td>

                    <!-- Visibility: Dashboard -->
                    <td class="center">
                        <label class="vis-toggle" title="Show on Dashboard">
                            <input type="checkbox"
                                   name="visible_dashboard[<?= $p['id'] ?>]"
                                   value="1"
                                   <?= ($p['visible_dashboard'] ?? 1) ? 'checked' : '' ?>>
                            <span class="vis-pill"></span>
                        </label>
                    </td>

                    <td class="center">
                        <button type="button" class="btn btn-danger btn-sm"
                                onclick="softDelete(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>')">
                            🗑 Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totPages > 1): ?>
        <div class="pagination">
            <?php if ($page>1): ?>
                <a href="?page=1&search=<?=urlencode($search)?>&category=<?=urlencode($catF)?>"    class="pg-btn">«</a>
                <a href="?page=<?=$page-1?>&search=<?=urlencode($search)?>&category=<?=urlencode($catF)?>" class="pg-btn">‹</a>
            <?php endif; ?>
            <?php for ($i=max(1,$page-2); $i<=min($totPages,$page+2); $i++): ?>
                <a href="?page=<?=$i?>&search=<?=urlencode($search)?>&category=<?=urlencode($catF)?>"
                   class="pg-btn <?=$i===$page?'active':''?>"><?=$i?></a>
            <?php endfor; ?>
            <?php if ($page<$totPages): ?>
                <a href="?page=<?=$page+1?>&search=<?=urlencode($search)?>&category=<?=urlencode($catF)?>" class="pg-btn">›</a>
                <a href="?page=<?=$totPages?>&search=<?=urlencode($search)?>&category=<?=urlencode($catF)?>"  class="pg-btn">»</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </form>
</div>

<!-- Bottom action bar -->
<div class="bot-bar">
    <span class="hint">
        ✦ Edit inline then click "Save All Changes" &nbsp;·&nbsp;
        Toggle switches control which products appear on Entry / Dashboard pages
    </span>
    <div style="display:flex;gap:.65rem;flex-wrap:wrap">
        <button type="submit" form="batchForm" class="btn btn-save">💾 Save All Changes</button>
    </div>
</div>

<!-- Recently Deleted section -->
<div class="deleted-section <?= count($deleted)?'':'open' ?>"
     id="deletedSection"
     style="<?= count($deleted)?'':'display:none' ?>">
    <div class="deleted-hd" onclick="toggleDeleted()">
        <div class="deleted-hd-title">
            🗂️ Recently Deleted
            <span class="deleted-count" id="deletedCount"><?= count($deleted) ?></span>
        </div>
        <span class="deleted-chevron" id="delChevron">▼</span>
    </div>
    <div class="deleted-body" id="deletedBody">
        <?php if (empty($deleted)): ?>
            <div style="padding:2rem;text-align:center;color:var(--text-faint);font-size:.85rem">
                No recently deleted products.
            </div>
        <?php else: foreach ($deleted as $d): ?>
            <div class="deleted-item" id="del-row-<?= $d['id'] ?>">
                <span class="del-name"><?= htmlspecialchars($d['name']) ?></span>
                <span class="badge <?= $d['category']==='Chicken'?'b-ch':'b-fr' ?>">
                    <?= $d['category']==='Chicken'?'🐔':'❄️' ?> <?= $d['category'] ?>
                </span>
                <span class="del-meta">₱<?= number_format((float)$d['selling_price'],2) ?></span>
                <?php if ($d['deleted_at']): ?>
                    <span class="del-time">Deleted <?= date('M j, Y g:ia', strtotime($d['deleted_at'])) ?></span>
                <?php endif; ?>
                <div class="del-actions">
                    <button class="btn btn-teal btn-sm"
                            onclick="restoreProduct(<?= $d['id'] ?>, '<?= htmlspecialchars(addslashes($d['name'])) ?>')">
                        ↩ Restore
                    </button>
                    <button class="btn btn-danger btn-sm"
                            onclick="purgeProduct(<?= $d['id'] ?>, '<?= htmlspecialchars(addslashes($d['name'])) ?>')">
                        ✕ Purge
                    </button>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<!-- BUG FIX: theme.js and themeToggle moved to END of body.
     In the original, theme.js was in <head> and ran before the DOM was parsed —
     applyTheme() couldn't find #themeToggle, so the button label never updated.
     Loading here guarantees the button exists when the script executes. -->
<script src="../public/theme.js"></script>
<script>
/* ── Modal helper ──────────────────────────────────────────────────── */
function modal(msg, onOk, onCan, icon='💬', okLabel='OK', canLabel='Cancel') {
    document.getElementById('mMsg').textContent  = msg;
    document.getElementById('mIcon').textContent = icon;
    const ov  = document.getElementById('modalOverlay');
    const ok  = document.getElementById('mOk').cloneNode(true);
    const can = document.getElementById('mCan').cloneNode(true);
    ok.textContent  = okLabel;
    can.textContent = canLabel;
    can.style.display = canLabel ? '' : 'none';
    document.getElementById('mOk').replaceWith(ok);
    document.getElementById('mCan').replaceWith(can);
    ov.classList.add('active');
    const close = () => ov.classList.remove('active');
    ok.addEventListener('click',  () => { close(); onOk  && onOk();  });
    can.addEventListener('click', () => { close(); onCan && onCan(); });
}
function alert2(msg, isErr=false) {
    modal(msg, null, null, isErr?'⚠️':'✅', 'OK', '');
    setTimeout(() => document.getElementById('modalOverlay').classList.remove('active'), 2400);
}

/* ── Add product ───────────────────────────────────────────────────── */
document.getElementById('addBtn').addEventListener('click', async () => {
    const name  = document.getElementById('nName').value.trim();
    const cat   = document.getElementById('nCat').value;
    const price = parseFloat(document.getElementById('nPrice').value);
    const thr   = parseInt(document.getElementById('nThreshold').value);
    if (!name)              return alert2('Product name is required.', true);
    if (isNaN(price)||price<0) return alert2('Enter a valid price.', true);
    if (isNaN(thr)||thr<0)    return alert2('Enter a valid threshold.', true);
    const fd = new FormData();
    fd.append('add','1'); fd.append('name',name); fd.append('category',cat);
    fd.append('price',price); fd.append('threshold',thr);
    try {
        const res    = await fetch(window.location.href, { method:'POST', body:fd });
        const result = await res.json();
        if (result.success) {
            alert2(result.message || 'Product added!');
            setTimeout(() => window.location.reload(), 1300);
        } else {
            alert2(result.message || 'Error adding product.', true);
        }
    } catch { alert2('Server error.', true); }
});

/* ── Soft-delete ───────────────────────────────────────────────────── */
function softDelete(id, name) {
    modal(
        `Move "${name}" to Recently Deleted? You can restore it later.`,
        async () => {
            const fd = new FormData(); fd.append('delete_id', id);
            const res    = await fetch(window.location.href, { method:'POST', body:fd });
            const result = await res.json();
            if (result.success) window.location.reload();
            else alert2(result.message || 'Error.', true);
        },
        null, '🗑️', 'Move to Deleted', 'Cancel'
    );
}

/* ── Restore ────────────────────────────────────────────────────────── */
function restoreProduct(id, name) {
    modal(
        `Restore "${name}" as an active product?`,
        async () => {
            const fd = new FormData(); fd.append('restore_id', id);
            const res    = await fetch(window.location.href, { method:'POST', body:fd });
            const result = await res.json();
            if (result.success) window.location.reload();
            else alert2(result.message || 'Error.', true);
        },
        null, '↩️', 'Restore', 'Cancel'
    );
}

/* ── Permanent purge ────────────────────────────────────────────────── */
function purgeProduct(id, name) {
    modal(
        `Permanently delete "${name}"? This CANNOT be undone. Products with sales history cannot be purged.`,
        async () => {
            const fd = new FormData(); fd.append('purge_id', id);
            const res    = await fetch(window.location.href, { method:'POST', body:fd });
            const result = await res.json();
            if (result.success) {
                document.getElementById('del-row-'+id)?.remove();
                // Update badge count
                const remaining = document.querySelectorAll('[id^="del-row-"]').length;
                document.getElementById('deletedCount').textContent = remaining;
                if (!remaining) document.getElementById('deletedSection').style.display='none';
            } else {
                alert2(result.message || 'Cannot delete.', true);
            }
        },
        null, '⛔', 'Permanently Delete', 'Cancel'
    );
}

/* ── Toggle deleted section ─────────────────────────────────────────── */
function toggleDeleted() {
    const sec = document.getElementById('deletedSection');
    const chv = document.getElementById('delChevron');
    sec.classList.toggle('open');
    chv.textContent = sec.classList.contains('open') ? '▲' : '▼';
    document.getElementById('deletedBody').style.display =
        sec.classList.contains('open') ? 'block' : 'none';
}

/* Auto-show deleted section if it has items */
(function() {
    const sec = document.getElementById('deletedSection');
    if (!sec) return;
    const count = parseInt(document.getElementById('deletedCount')?.textContent || '0');
    if (count > 0) {
        sec.style.display = '';
        // Start collapsed — user clicks to expand
        document.getElementById('deletedBody').style.display = 'none';
    }
})();

/* ── Enter key on add form ───────────────────────────────────────────── */
['nName','nPrice','nThreshold'].forEach(id => {
    document.getElementById(id)?.addEventListener('keydown', e => {
        if (e.key === 'Enter') document.getElementById('addBtn').click();
    });
});
</script>
</body>
</html>