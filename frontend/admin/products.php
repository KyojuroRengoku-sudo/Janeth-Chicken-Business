<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html'); exit;
}
require_once '../backend/db.php';

$message = ''; $msgType = '';

// Add product
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add'])) {
    $name      = trim($_POST['name']      ?? '');
    $category  = $_POST['category']       ?? 'Chicken';
    $price     = floatval($_POST['price'] ?? 0);
    $threshold = intval($_POST['threshold'] ?? 10);

    if (empty($name))         { $message='Product name is required.'; $msgType='error'; }
    elseif ($price < 0)       { $message='Price cannot be negative.'; $msgType='error'; }
    elseif ($threshold < 0)   { $message='Threshold cannot be negative.'; $msgType='error'; }
    else {
        $chk = $pdo->prepare("SELECT id FROM products WHERE name = ?"); $chk->execute([$name]);
        if ($chk->fetch()) { $message='A product with this name already exists.'; $msgType='error'; }
        else {
            $pdo->prepare("INSERT INTO products (name,category,selling_price,low_stock_threshold) VALUES (?,?,?,?)")->execute([$name,$category,$price,$threshold]);
            $message='Product added successfully.'; $msgType='success';
        }
    }
    header("Location: products.php?msg=".urlencode($message)."&type=$msgType"); exit;
}

// Delete (AJAX)
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete_id'])) {
    $id  = intval($_POST['delete_id']);
    $chk = $pdo->prepare("SELECT id FROM janeth_records WHERE product_id=? LIMIT 1"); $chk->execute([$id]);
    if ($chk->fetch()) echo json_encode(['success'=>false,'message'=>'Cannot delete: product has sales history.']);
    else { $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]); echo json_encode(['success'=>true]); }
    exit;
}

// Batch update
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['batch_update'])) {
    $names      = $_POST['name']      ?? [];
    $categories = $_POST['category']  ?? [];
    $prices     = $_POST['price']     ?? [];
    $thresholds = $_POST['threshold'] ?? [];
    $pdo->beginTransaction();
    try {
        foreach ($names as $id => $name) {
            $cat   = $categories[$id]; $price=floatval($prices[$id]); $thr=intval($thresholds[$id]);
            if (empty(trim($name))||$price<0||$thr<0) continue;
            $pdo->prepare("UPDATE products SET name=?,category=?,selling_price=?,low_stock_threshold=? WHERE id=?")->execute([trim($name),$cat,$price,$thr,$id]);
        }
        $pdo->commit(); $message='All products updated.'; $msgType='success';
    } catch (Exception $e) { $pdo->rollBack(); $message='Error: '.$e->getMessage(); $msgType='error'; }
    header("Location: products.php?msg=".urlencode($message)."&type=$msgType"); exit;
}

if (isset($_GET['msg'])) { $message=htmlspecialchars($_GET['msg']); $msgType=$_GET['type']??'info'; }

$page   = max(1, intval($_GET['page'] ?? 1));
$search = $_GET['search'] ?? '';
$catF   = $_GET['category'] ?? 'all';
$limit  = 20; $offset = ($page-1)*$limit;

$where  = "name LIKE :s" . ($catF!=='all'?" AND category=:c":"");
$stmt   = $pdo->prepare("SELECT * FROM products WHERE $where ORDER BY category,name LIMIT :l OFFSET :o");
$stmt->bindValue(':s', "%$search%"); if ($catF!=='all') $stmt->bindValue(':c',$catF);
$stmt->bindValue(':l',$limit,PDO::PARAM_INT); $stmt->bindValue(':o',$offset,PDO::PARAM_INT);
$stmt->execute(); $products = $stmt->fetchAll();

$cntStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE $where");
$cntStmt->bindValue(':s',"%$search%"); if ($catF!=='all') $cntStmt->bindValue(':c',$catF);
$cntStmt->execute(); $total=$cntStmt->fetchColumn(); $totPages=ceil($total/$limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management · Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg:#0a0e17;--surface:#111827;--surface-2:#1a2234;--surface-3:#222d42;
            --border:rgba(255,255,255,0.07);
            --accent:#f5a623;--accent-dim:rgba(245,166,35,.12);--accent-glow:rgba(245,166,35,.25);
            --teal:#29b6c8;--teal-dim:rgba(41,182,200,.1);
            --text:#e8edf5;--text-muted:#6b7a93;--text-faint:#3d4d63;
            --danger:#f87171;--danger-dim:rgba(248,113,113,.1);
            --success:#34d399;--success-dim:rgba(52,211,153,.1);
            --chicken:#fbbf24;--frozen:#60a5fa;
            --radius:14px;--radius-sm:9px;
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Sora',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding:1.5rem;
             background-image:radial-gradient(ellipse 70% 50% at 10% -10%,rgba(41,182,200,.06) 0%,transparent 60%),
                              radial-gradient(ellipse 60% 40% at 90% 110%,rgba(245,166,35,.04) 0%,transparent 60%);}
        .container{max-width:1400px;margin:0 auto;}
        .header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;
                margin-bottom:1.75rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border);}
        .logo{display:flex;align-items:center;gap:.75rem;}
        .logo-icon{width:40px;height:40px;background:linear-gradient(135deg,var(--accent),#e8920f);border-radius:10px;
                   display:flex;align-items:center;justify-content:center;font-size:1.1rem;box-shadow:0 4px 16px var(--accent-glow);}
        .logo-text{display:flex;flex-direction:column;}
        .logo-title{font-size:1.1rem;font-weight:700;letter-spacing:-.02em;}
        .logo-sub{font-size:.67rem;color:var(--text-muted);font-weight:400;letter-spacing:.07em;text-transform:uppercase;}
        .admin-badge{background:var(--accent-dim);border:1px solid rgba(245,166,35,.2);color:var(--accent);
                     font-size:.62rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
                     padding:.18rem .55rem;border-radius:50px;}
        .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.42rem 1rem;border-radius:50px;
             font-size:.76rem;font-weight:600;font-family:'Sora',sans-serif;cursor:pointer;border:none;
             transition:.18s;text-decoration:none;white-space:nowrap;letter-spacing:.01em;}
        .btn-primary{background:linear-gradient(135deg,var(--accent),#e8920f);color:#0a0e17;box-shadow:0 3px 12px var(--accent-glow);}
        .btn-primary:hover{box-shadow:0 6px 20px rgba(245,166,35,.4);transform:translateY(-1px);}
        .btn-ghost{background:var(--surface);border:1px solid var(--border);color:var(--text-muted);}
        .btn-ghost:hover{border-color:var(--teal);color:var(--teal);background:var(--teal-dim);}
        .btn-teal{background:var(--teal-dim);border:1px solid rgba(41,182,200,.2);color:var(--teal);}
        .btn-teal:hover{background:rgba(41,182,200,.18);}
        .btn-save{background:linear-gradient(135deg,var(--teal),#1a9aab);color:#0a0e17;font-weight:700;box-shadow:0 3px 12px rgba(41,182,200,.3);}
        .btn-save:hover{box-shadow:0 6px 20px rgba(41,182,200,.4);transform:translateY(-1px);}
        .btn-del{background:var(--danger-dim);border:1px solid rgba(248,113,113,.2);color:var(--danger);
                 padding:.3rem .7rem;font-size:.7rem;}
        .btn-del:hover{background:rgba(248,113,113,.2);}
        .alert{display:flex;align-items:center;gap:.75rem;padding:.85rem 1.1rem;border-radius:var(--radius-sm);
               margin-bottom:1.25rem;font-size:.82rem;font-weight:500;}
        .alert-success{background:var(--success-dim);border:1px solid rgba(52,211,153,.2);color:var(--success);}
        .alert-error{background:var(--danger-dim);border:1px solid rgba(248,113,113,.2);color:var(--danger);}
        .panel{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
               padding:1.1rem 1.25rem;margin-bottom:1.1rem;}
        .panel-title{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;
                     color:var(--text-faint);margin-bottom:.9rem;}
        .toolbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;}
        .toolbar-l,.toolbar-r{display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;}
        input[type="text"],input[type="number"],select{
            background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-sm);
            color:var(--text);font-family:'Sora',sans-serif;font-size:.78rem;
            padding:.42rem .85rem;outline:none;transition:.18s;}
        input:focus,select:focus{border-color:var(--teal);box-shadow:0 0 0 3px var(--teal-dim);}
        select option{background:#1a2234;}
        .add-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:.75rem;align-items:end;}
        .fg{display:flex;flex-direction:column;gap:.3rem;}
        .fg label{font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);}
        .table-wrap{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:1.1rem;}
        .table-hd-bar{display:flex;justify-content:space-between;align-items:center;padding:.85rem 1.2rem;
                      border-bottom:1px solid var(--border);background:var(--surface-2);}
        .table-title{font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;}
        .rec-count{font-family:'DM Mono',monospace;font-size:.7rem;color:var(--text-faint);}
        .tbl-scroll{overflow-x:auto;}
        table{width:100%;border-collapse:collapse;min-width:700px;}
        thead tr{border-bottom:1px solid var(--border);}
        th{padding:.65rem 1rem;text-align:left;font-size:.63rem;font-weight:700;text-transform:uppercase;
           letter-spacing:.09em;color:var(--text-faint);background:var(--surface-2);white-space:nowrap;}
        tbody tr{border-bottom:1px solid var(--border);transition:background .12s;}
        tbody tr:last-child{border-bottom:none;}
        tbody tr:hover{background:var(--surface-2);}
        td{padding:.58rem 1rem;font-size:.8rem;color:var(--text);vertical-align:middle;}
        .row-num{font-family:'DM Mono',monospace;font-size:.7rem;color:var(--text-faint);}
        .tbl-in{width:100%;min-width:80px;background:var(--surface-3)!important;border:1px solid var(--border)!important;
                border-radius:var(--radius-sm)!important;color:var(--text)!important;font-family:'Sora',sans-serif!important;
                font-size:.76rem!important;padding:.34rem .65rem!important;outline:none;transition:.15s;}
        .tbl-in:focus{border-color:var(--teal)!important;box-shadow:0 0 0 2px var(--teal-dim);}
        .tbl-sel{width:100%;background:var(--surface-3)!important;border:1px solid var(--border)!important;
                 border-radius:var(--radius-sm)!important;color:var(--text)!important;font-family:'Sora',sans-serif!important;
                 font-size:.76rem!important;padding:.34rem .65rem!important;outline:none;}
        .tbl-sel:focus{border-color:var(--accent)!important;}
        .badge{display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .6rem;border-radius:50px;
               font-size:.64rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;}
        .b-ch{background:rgba(251,191,36,.12);color:var(--chicken);border:1px solid rgba(251,191,36,.2);}
        .b-fr{background:rgba(96,165,250,.1);color:var(--frozen);border:1px solid rgba(96,165,250,.2);}
        .price-td{font-family:'DM Mono',monospace;font-size:.79rem;color:var(--success);}
        .pagination{display:flex;justify-content:center;gap:.4rem;padding:.8rem;border-top:1px solid var(--border);flex-wrap:wrap;}
        .pg-btn{min-width:30px;height:30px;display:flex;align-items:center;justify-content:center;padding:0 .6rem;
                border-radius:var(--radius-sm);background:var(--surface-2);border:1px solid var(--border);
                color:var(--text-muted);font-size:.76rem;font-weight:600;cursor:pointer;transition:.15s;
                text-decoration:none;font-family:'Sora',sans-serif;}
        .pg-btn:hover{border-color:var(--teal);color:var(--teal);}
        .pg-btn.active{background:var(--accent);color:#0a0e17;border-color:var(--accent);}
        .bot-bar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:2rem;}
        .hint{font-size:.68rem;color:var(--text-faint);font-family:'DM Mono',monospace;}
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(8px);
                       display:none;justify-content:center;align-items:center;z-index:1000;}
        .modal-overlay.active{display:flex;}
        .modal{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
               padding:2rem 2.25rem;max-width:380px;width:90%;text-align:center;
               box-shadow:0 25px 60px rgba(0,0,0,.5);animation:popIn .2s cubic-bezier(.34,1.56,.64,1);}
        @keyframes popIn{from{opacity:0;transform:scale(.9) translateY(12px)}to{opacity:1;transform:none}}
        .modal-icon{font-size:2rem;margin-bottom:.75rem;}
        .modal-msg{font-size:.9rem;color:var(--text);margin-bottom:1.5rem;line-height:1.5;font-weight:500;}
        .modal-btns{display:flex;gap:.75rem;justify-content:center;}
        .empty-row td{padding:3rem;color:var(--text-faint);font-size:.83rem;text-align:center;}
        ::-webkit-scrollbar{width:6px;height:6px;}::-webkit-scrollbar-track{background:transparent;}
        ::-webkit-scrollbar-thumb{background:var(--surface-3);border-radius:3px;}
        @media(max-width:768px){body{padding:1rem;}.add-grid{grid-template-columns:1fr 1fr;}.add-grid .btn{grid-column:1/-1;}}
    </style>
</head>
<body>
<div class="container">

<div class="header">
    <div class="logo">
        <div class="logo-icon">⚙️</div>
        <div class="logo-text">
            <span class="logo-title">Product Management</span>
            <span class="logo-sub">Janeth Business · Admin</span>
        </div>
        <span class="admin-badge">Admin</span>
    </div>
    <div style="display:flex;gap:.65rem;flex-wrap:wrap;align-items:center">
        <a href="janeth-dashboard.php" class="btn btn-ghost">📊 Dashboard</a>
        <a href="janeth-input.php" class="btn btn-ghost">← Entry</a>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $msgType==='success'?'success':'error' ?>">
    <?= $msgType==='success'?'✅':'⚠️' ?> <?= $message ?>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="panel">
    <div class="panel-title">Filter & Search</div>
    <form method="GET" class="toolbar">
        <div class="toolbar-l">
            <input type="text" name="search" placeholder="🔍 Search by name…" value="<?= htmlspecialchars($search) ?>" style="width:220px">
            <select name="category" onchange="this.form.submit()">
                <option value="all"     <?= $catF==='all'?'selected':'' ?>>All Categories</option>
                <option value="Chicken" <?= $catF==='Chicken'?'selected':'' ?>>🐔 Chicken</option>
                <option value="Frozen"  <?= $catF==='Frozen'?'selected':'' ?>>❄️ Frozen</option>
            </select>
            <button type="submit" class="btn btn-teal">Filter</button>
            <?php if ($search||$catF!=='all'): ?>
            <a href="products.php" class="btn btn-ghost">✕ Clear</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Add product -->
<div class="panel">
    <div class="panel-title">Add New Product</div>
    <div class="add-grid">
        <div class="fg"><label>Product Name</label><input type="text" id="nName" placeholder="e.g. Whole Chicken"></div>
        <div class="fg"><label>Category</label>
            <select id="nCat">
                <option value="Chicken">🐔 Chicken</option>
                <option value="Frozen">❄️ Frozen</option>
            </select>
        </div>
        <div class="fg"><label>Price (₱)</label><input type="number" id="nPrice" placeholder="0.00" step="0.01" min="0"></div>
        <div class="fg"><label>Low Stock Threshold</label><input type="number" id="nThreshold" value="10" min="0"></div>
        <button class="btn btn-primary" id="addBtn" style="align-self:end">+ Add</button>
    </div>
</div>

<!-- Modal -->
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
        <span class="table-title">Products</span>
        <span class="rec-count"><?= $total ?> total · page <?= $page ?> of <?= max(1,$totPages) ?></span>
    </div>
    <form method="POST" id="batchForm">
        <input type="hidden" name="batch_update" value="1">
        <div class="tbl-scroll">
            <table>
                <thead><tr>
                    <th style="width:36px">#</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price (₱)</th>
                    <th>Low Stock Threshold</th>
                    <th style="width:80px">Action</th>
                </tr></thead>
                <tbody>
                <?php if (empty($products)): ?>
                    <tr class="empty-row"><td colspan="6">No products found. Add one above.</td></tr>
                <?php else: $c=($page-1)*$limit+1; foreach ($products as $p): ?>
                <tr>
                    <td class="row-num"><?= $c++ ?></td>
                    <td><input class="tbl-in" type="text" name="name[<?= $p['id'] ?>]" value="<?= htmlspecialchars($p['name']) ?>" required></td>
                    <td>
                        <select class="tbl-sel" name="category[<?= $p['id'] ?>]">
                            <option value="Chicken" <?= $p['category']==='Chicken'?'selected':'' ?>>🐔 Chicken</option>
                            <option value="Frozen"  <?= $p['category']==='Frozen' ?'selected':'' ?>>❄️ Frozen</option>
                        </select>
                    </td>
                    <td><input class="tbl-in" type="number" step="0.01" min="0" name="price[<?= $p['id'] ?>]" value="<?= $p['selling_price'] ?>" required style="width:100px;font-family:'DM Mono',monospace"></td>
                    <td><input class="tbl-in" type="number" min="0" name="threshold[<?= $p['id'] ?>]" value="<?= $p['low_stock_threshold']??10 ?>" required style="width:80px"></td>
                    <td><button type="button" class="btn btn-del" onclick="delProduct(<?= $p['id'] ?>)">Delete</button></td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totPages>1): ?>
        <div class="pagination">
            <?php if ($page>1): ?><a href="?page=<?=$page-1?>&search=<?=urlencode($search)?>&category=<?=urlencode($catF)?>" class="pg-btn">‹</a><?php endif; ?>
            <?php for ($i=max(1,$page-2);$i<=min($totPages,$page+2);$i++): ?>
            <a href="?page=<?=$i?>&search=<?=urlencode($search)?>&category=<?=urlencode($catF)?>" class="pg-btn <?=$i===$page?'active':''?>"><?=$i?></a>
            <?php endfor; ?>
            <?php if ($page<$totPages): ?><a href="?page=<?=$page+1?>&search=<?=urlencode($search)?>&category=<?=urlencode($catF)?>" class="pg-btn">›</a><?php endif; ?>
        </div>
        <?php endif; ?>
    </form>
</div>

<div class="bot-bar">
    <span class="hint">✦ Edit inline and click "Save All Changes" · Prices also editable from the Entry page</span>
    <div style="display:flex;gap:.65rem;flex-wrap:wrap">
        <a href="janeth-input.php" class="btn btn-ghost">← Entry</a>
        <button type="submit" form="batchForm" class="btn btn-save">💾 Save All Changes</button>
    </div>
</div>
</div>

<script>
function modal(msg, onOk, onCan, icon='💬') {
    document.getElementById('mMsg').textContent  = msg;
    document.getElementById('mIcon').textContent = icon;
    document.getElementById('modalOverlay').classList.add('active');
    const ok  = document.getElementById('mOk').cloneNode(true);
    const can = document.getElementById('mCan').cloneNode(true);
    document.getElementById('mOk').replaceWith(ok);
    document.getElementById('mCan').replaceWith(can);
    const close = () => document.getElementById('modalOverlay').classList.remove('active');
    ok.addEventListener('click',  () => { close(); onOk  && onOk(); });
    can.addEventListener('click', () => { close(); onCan && onCan(); });
}
function alert2(msg, isErr=false) {
    modal(msg, null, null, isErr?'⚠️':'✅');
    document.getElementById('mCan').style.display='none';
    setTimeout(()=>document.getElementById('modalOverlay').classList.remove('active'),2200);
}

document.getElementById('addBtn').addEventListener('click', async () => {
    const name  = document.getElementById('nName').value.trim();
    const cat   = document.getElementById('nCat').value;
    const price = parseFloat(document.getElementById('nPrice').value);
    const thr   = parseInt(document.getElementById('nThreshold').value);
    if (!name)                      return alert2('Product name is required.', true);
    if (isNaN(price)||price<0)      return alert2('A valid price is required.', true);
    if (isNaN(thr)||thr<0)          return alert2('A valid threshold is required.', true);
    const fd = new FormData();
    fd.append('add','1'); fd.append('name',name); fd.append('category',cat);
    fd.append('price',price); fd.append('threshold',thr);
    const res = await fetch(window.location.href,{method:'POST',body:fd});
    if (res.ok) window.location.reload();
    else alert2('Error adding product.', true);
});

async function delProduct(id) {
    modal('Delete this product permanently?', async () => {
        const fd = new FormData(); fd.append('delete_id',id);
        const res    = await fetch(window.location.href,{method:'POST',body:fd});
        const result = await res.json();
        if (result.success) window.location.reload();
        else alert2(result.message, true);
    }, null, '🗑️');
}
</script>
</body>
</html>