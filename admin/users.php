<?php
// users.php – Admin: manage users and approve registration requests
require_once __DIR__ . '/bootstrap.php';

// ── AJAX: Approve registration request ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    header('Content-Type: application/json');
    $id = (int)$_POST['approve_id'];
    $req = $pdo->prepare("SELECT * FROM registration_requests WHERE id = ? AND status = 'pending'");
    $req->execute([$id]);
    $row = $req->fetch();
    if (!$row) { echo json_encode(['success'=>false,'message'=>'Request not found or already handled.']); exit; }

    // Check username still not taken
    $chk = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $chk->execute([$row['username']]);
    if ($chk->fetch()) {
        $pdo->prepare("UPDATE registration_requests SET status='rejected', reviewed_at=NOW() WHERE id=?")->execute([$id]);
        echo json_encode(['success'=>false,'message'=>'Username already exists. Request rejected.']); exit;
    }

    $pdo->beginTransaction();
    try {
        $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?,?,?)")
            ->execute([$row['username'], $row['password_hash'], $row['requested_role']]);
        $pdo->prepare("UPDATE registration_requests SET status='approved', reviewed_at=NOW() WHERE id=?")
            ->execute([$id]);
        $pdo->commit();
        echo json_encode(['success'=>true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    }
    exit;
}

// ── AJAX: Reject registration request ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_id'])) {
    header('Content-Type: application/json');
    $id = (int)$_POST['reject_id'];
    $pdo->prepare("UPDATE registration_requests SET status='rejected', reviewed_at=NOW() WHERE id=?")->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
}

// ── AJAX: Add user directly ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    header('Content-Type: application/json');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = in_array($_POST['role'] ?? '', ['admin','staff']) ? $_POST['role'] : 'staff';
    if (empty($username) || strlen($username) < 3) { echo json_encode(['success'=>false,'message'=>'Username too short (min 3 chars).']); exit; }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) { echo json_encode(['success'=>false,'message'=>'Username: letters, numbers, underscores only.']); exit; }
    if (strlen($password) < 6) { echo json_encode(['success'=>false,'message'=>'Password must be at least 6 characters.']); exit; }
    $chk = $pdo->prepare("SELECT id FROM users WHERE username=?"); $chk->execute([$username]);
    if ($chk->fetch()) { echo json_encode(['success'=>false,'message'=>'Username already exists.']); exit; }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?,?,?)")->execute([$username, $hash, $role]);
    echo json_encode(['success'=>true]);
    exit;
}

// ── AJAX: Change user password ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    header('Content-Type: application/json');
    $id       = (int)$_POST['user_id'];
    $password = $_POST['new_password'] ?? '';
    if (strlen($password) < 6) { echo json_encode(['success'=>false,'message'=>'Password must be at least 6 characters.']); exit; }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash, $id]);
    echo json_encode(['success'=>true]);
    exit;
}

// ── AJAX: Change user role ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_role'])) {
    header('Content-Type: application/json');
    $id   = (int)$_POST['user_id'];
    $role = in_array($_POST['role']??'', ['admin','staff']) ? $_POST['role'] : 'staff';
    // Prevent demoting yourself
    if ($id === (int)$_SESSION['user_id'] && $role !== 'admin') {
        echo json_encode(['success'=>false,'message'=>'You cannot demote yourself.']); exit;
    }
    $pdo->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role, $id]);
    echo json_encode(['success'=>true]);
    exit;
}

// ── AJAX: Delete user ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    header('Content-Type: application/json');
    $id = (int)$_POST['delete_user'];
    if ($id === (int)$_SESSION['user_id']) { echo json_encode(['success'=>false,'message'=>'You cannot delete your own account.']); exit; }
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    echo json_encode(['success'=>true]);
    exit;
}

// ── Load data ──
$users    = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY role DESC, username")->fetchAll();
$pending  = $pdo->query("SELECT * FROM registration_requests WHERE status='pending' ORDER BY created_at DESC")->fetchAll();
$history  = $pdo->query("SELECT * FROM registration_requests WHERE status != 'pending' ORDER BY reviewed_at DESC LIMIT 30")->fetchAll();
$myId     = (int)$_SESSION['user_id'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management · Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="theme.js"></script>
    <style>
        :root{
            --bg:#0a0e17;--surface:#111827;--surface-2:#1a2234;--surface-3:#222d42;
            --border:rgba(255,255,255,0.07);
            --accent:#f5a623;--accent-dim:rgba(245,166,35,.12);--accent-glow:rgba(245,166,35,.25);
            --teal:#29b6c8;--teal-dim:rgba(41,182,200,.1);
            --text:#e8edf5;--text-muted:#6b7a93;--text-faint:#3d4d63;
            --danger:#f87171;--danger-dim:rgba(248,113,113,.1);
            --success:#34d399;--success-dim:rgba(52,211,153,.1);
            --purple:#a78bfa;
            --radius:14px;--radius-sm:9px;
        }
        [data-theme="light"] { --bg:#f0f4f9;--surface:#ffffff;--surface-2:#e8eef5;--surface-3:#d8e3ef;--border:rgba(0,0,0,0.08);--text:#0d1b2a;--text-muted:#4a6080;--text-faint:#7090b0;--danger-dim:rgba(248,113,113,0.1);--success-dim:rgba(52,211,153,0.1); }
        [data-theme="light"] input,[data-theme="light"] select { background:var(--surface-2);color:var(--text);border-color:var(--border); }
        [data-theme="light"] select option { background:#e8eef5;color:#0d1b2a; }
        [data-theme="light"] tbody tr:hover { background:var(--surface-2); }
        [data-theme="light"] .panel { background:var(--surface); }
        [data-theme="light"] thead tr { background:var(--surface-2); }
        body { font-size:17px; }
        th   { font-size:.88rem!important; }
        td   { font-size:.95rem!important; }
        .logo-title { font-size:1.3rem!important; }
        .btn        { font-size:.95rem!important;padding:.6rem 1.25rem!important; }
        .panel-title { font-size:.82rem!important; }
        .fg label    { font-size:.74rem!important; }
        input[type="text"],input[type="password"],select { font-size:.95rem!important; }
        .role-badge { font-size:.72rem!important; }
        #themeToggle { background:var(--surface-2);border:1px solid var(--border);color:var(--text-muted);border-radius:50px;padding:.42rem .9rem;font-size:.8rem;font-weight:600;cursor:pointer;font-family:'Sora',sans-serif;transition:.18s; }
        #themeToggle:hover { border-color:var(--teal);color:var(--teal); }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Sora',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding:1.5rem;
             background-image:radial-gradient(ellipse 70% 50% at 10% -10%,rgba(41,182,200,.06) 0%,transparent 60%),
                              radial-gradient(ellipse 60% 40% at 90% 110%,rgba(245,166,35,.04) 0%,transparent 60%);}
        .container{max-width:1200px;margin:0 auto;}
        .header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;
                margin-bottom:1.75rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border);}
        .logo{display:flex;align-items:center;gap:.75rem;}
        .logo-icon{width:40px;height:40px;background:linear-gradient(135deg,var(--purple),#7c3aed);border-radius:10px;
                   display:flex;align-items:center;justify-content:center;font-size:1.1rem;box-shadow:0 4px 16px rgba(167,139,250,.3);}
        .logo-text{display:flex;flex-direction:column;}
        .logo-title{font-size:1.1rem;font-weight:700;letter-spacing:-.02em;}
        .logo-sub{font-size:.67rem;color:var(--text-muted);font-weight:400;letter-spacing:.07em;text-transform:uppercase;}
        .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.42rem 1rem;border-radius:50px;
             font-size:.76rem;font-weight:600;font-family:'Sora',sans-serif;cursor:pointer;border:none;
             transition:.18s;text-decoration:none;white-space:nowrap;}
        .btn-ghost{background:var(--surface);border:1px solid var(--border);color:var(--text-muted);}
        .btn-ghost:hover{border-color:var(--teal);color:var(--teal);background:var(--teal-dim);}
        .btn-teal{background:var(--teal-dim);border:1px solid rgba(41,182,200,.2);color:var(--teal);}
        .btn-teal:hover{background:rgba(41,182,200,.18);}
        .btn-primary{background:linear-gradient(135deg,var(--accent),#e8920f);color:#0a0e17;box-shadow:0 3px 12px var(--accent-glow);}
        .btn-primary:hover{box-shadow:0 6px 20px rgba(245,166,35,.4);transform:translateY(-1px);}
        .btn-success{background:var(--success-dim);border:1px solid rgba(52,211,153,.2);color:var(--success);padding:.32rem .75rem;font-size:.72rem;}
        .btn-success:hover{background:rgba(52,211,153,.18);}
        .btn-danger{background:var(--danger-dim);border:1px solid rgba(248,113,113,.2);color:var(--danger);padding:.32rem .75rem;font-size:.72rem;}
        .btn-danger:hover{background:rgba(248,113,113,.2);}
        .btn-purple{background:rgba(167,139,250,.12);border:1px solid rgba(167,139,250,.2);color:var(--purple);padding:.32rem .75rem;font-size:.72rem;}
        .btn-purple:hover{background:rgba(167,139,250,.2);}
        .panel{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:1.25rem;}
        .panel-hd{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.9rem 1.25rem;
                  border-bottom:1px solid var(--border);background:var(--surface-2);flex-wrap:wrap;}
        .panel-title{font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);}
        .badge-count{background:var(--accent-dim);color:var(--accent);border:1px solid rgba(245,166,35,.2);
                     font-size:.62rem;font-weight:700;padding:.15rem .55rem;border-radius:50px;}
        .badge-pending{background:rgba(251,191,36,.12);color:#fbbf24;border:1px solid rgba(251,191,36,.2);}
        input[type="text"],input[type="password"],select{
            background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-sm);
            color:var(--text);font-family:'Sora',sans-serif;font-size:.78rem;
            padding:.42rem .85rem;outline:none;transition:.18s;}
        input:focus,select:focus{border-color:var(--teal);box-shadow:0 0 0 3px var(--teal-dim);}
        select option{background:#1a2234;}
        .add-grid{display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;padding:1.1rem 1.25rem;border-bottom:1px solid var(--border);}
        .fg{display:flex;flex-direction:column;gap:.3rem;}
        .fg label{font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);}
        table{width:100%;border-collapse:collapse;}
        thead tr{border-bottom:1px solid var(--border);}
        th{padding:.62rem 1rem;text-align:left;font-size:.63rem;font-weight:700;text-transform:uppercase;
           letter-spacing:.09em;color:var(--text-faint);background:var(--surface-2);white-space:nowrap;}
        tbody tr{border-bottom:1px solid var(--border);transition:background .12s;}
        tbody tr:last-child{border-bottom:none;}
        tbody tr:hover{background:var(--surface-2);}
        td{padding:.62rem 1rem;font-size:.8rem;color:var(--text);vertical-align:middle;}
        .role-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.2rem .6rem;border-radius:50px;
                    font-size:.64rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;}
        .r-admin{background:var(--accent-dim);color:var(--accent);border:1px solid rgba(245,166,35,.2);}
        .r-staff{background:var(--teal-dim);color:var(--teal);border:1px solid rgba(41,182,200,.2);}
        .r-pending{background:rgba(251,191,36,.1);color:#fbbf24;border:1px solid rgba(251,191,36,.2);}
        .r-approved{background:var(--success-dim);color:var(--success);border:1px solid rgba(52,211,153,.2);}
        .r-rejected{background:var(--danger-dim);color:var(--danger);border:1px solid rgba(248,113,113,.2);}
        .you-chip{font-size:.6rem;background:rgba(167,139,250,.12);color:var(--purple);
                  border:1px solid rgba(167,139,250,.2);padding:.1rem .4rem;border-radius:50px;margin-left:.4rem;}
        .mono{font-family:'DM Mono',monospace;font-size:.76rem;}
        .actions{display:flex;gap:.4rem;align-items:center;flex-wrap:wrap;}
        .pw-form{display:none;align-items:center;gap:.4rem;flex-wrap:wrap;margin-top:.4rem;}
        .pw-form.open{display:flex;}
        .empty-state{padding:2rem;text-align:center;color:var(--text-faint);font-size:.82rem;}
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(8px);
                       display:none;justify-content:center;align-items:center;z-index:1000;padding:1rem;}
        .modal-overlay.active{display:flex;}
        .modal{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
               padding:2rem 2.25rem;max-width:400px;width:100%;text-align:center;
               box-shadow:0 25px 60px rgba(0,0,0,.5);animation:popIn .2s cubic-bezier(.34,1.56,.64,1);}
        @keyframes popIn{from{opacity:0;transform:scale(.9)}to{opacity:1;transform:none}}
        .modal-icon{font-size:2rem;margin-bottom:.75rem;}
        .modal-msg{font-size:.9rem;color:var(--text);margin-bottom:1.5rem;line-height:1.5;font-weight:500;}
        .modal-btns{display:flex;gap:.75rem;justify-content:center;}
        .toast{position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;display:flex;align-items:center;gap:.55rem;
               padding:.75rem 1.2rem;border-radius:var(--radius-sm);font-size:.8rem;font-weight:600;
               box-shadow:0 8px 32px rgba(0,0,0,.4);opacity:0;transform:translateY(10px);
               transition:.25s;pointer-events:none;}
        .toast.show{opacity:1;transform:none;}
        .toast-ok{background:var(--success-dim);border:1px solid rgba(52,211,153,.3);color:var(--success);}
        .toast-err{background:var(--danger-dim);border:1px solid rgba(248,113,113,.3);color:var(--danger);}
        ::-webkit-scrollbar{width:6px;height:6px;}::-webkit-scrollbar-track{background:transparent;}
        ::-webkit-scrollbar-thumb{background:var(--surface-3);border-radius:3px;}
        @media(max-width:640px){body{padding:1rem;}.add-grid{flex-direction:column;}.actions{flex-wrap:wrap;}}
    </style>
</head>
<body>
<div class="container">

<div class="header">
    <div class="logo">
        <div class="logo-icon">👥</div>
        <div class="logo-text">
            <span class="logo-title">User Management</span>
            <span class="logo-sub">Janeth Business · Admin</span>
        </div>
    </div>
    <div style="display:flex;gap:.65rem;flex-wrap:wrap;align-items:center">
        <a href="janeth-dashboard.php" class="btn btn-ghost">📊 Dashboard</a>
        <a href="products.php" class="btn btn-ghost">⚙️ Products</a>
        <button id="themeToggle" onclick="toggleTheme()">☀️ Light</button>
        <a href="janeth-input.php" class="btn btn-ghost">← Entry</a>
    </div>
</div>

<!-- Pending registration requests -->
<div class="panel">
    <div class="panel-hd">
        <span class="panel-title">📬 Pending Registration Requests</span>
        <?php if (count($pending)): ?>
        <span class="badge-count badge-pending"><?= count($pending) ?> pending</span>
        <?php endif; ?>
    </div>
    <?php if (empty($pending)): ?>
    <div class="empty-state">No pending requests. ✅</div>
    <?php else: ?>
    <div style="overflow-x:auto">
    <table>
        <thead><tr>
            <th>Username</th>
            <th>Requested Role</th>
            <th>Requested At</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($pending as $r): ?>
        <tr id="req_<?= $r['id'] ?>">
            <td><strong><?= htmlspecialchars($r['username']) ?></strong></td>
            <td><span class="role-badge r-pending"><?= $r['requested_role'] ?></span></td>
            <td class="mono"><?= $r['created_at'] ?></td>
            <td>
                <div class="actions">
                    <button class="btn btn-success" onclick="approveReq(<?= $r['id'] ?>)">✅ Approve</button>
                    <button class="btn btn-danger"  onclick="rejectReq(<?= $r['id'] ?>)">✕ Reject</button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- Add user directly -->
<div class="panel">
    <div class="panel-hd">
        <span class="panel-title">➕ Add User Directly</span>
    </div>
    <div class="add-grid">
        <div class="fg"><label>Username</label><input type="text" id="newUser" placeholder="username" style="width:160px"></div>
        <div class="fg"><label>Password</label><input type="password" id="newPass" placeholder="min 6 chars" style="width:160px"></div>
        <div class="fg"><label>Role</label>
            <select id="newRole">
                <option value="staff">👤 Staff</option>
                <option value="admin">👑 Admin</option>
            </select>
        </div>
        <button class="btn btn-primary" id="addUserBtn">+ Add User</button>
    </div>
</div>

<!-- Current users -->
<div class="panel">
    <div class="panel-hd">
        <span class="panel-title">👥 All Users</span>
        <span class="badge-count"><?= count($users) ?> user<?= count($users)!==1?'s':'' ?></span>
    </div>
    <div style="overflow-x:auto">
    <table>
        <thead><tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Created</th>
            <th>Actions</th>
        </tr></thead>
        <tbody id="usersBody">
        <?php foreach ($users as $u): ?>
        <tr id="user_<?= $u['id'] ?>">
            <td class="mono"><?= $u['id'] ?></td>
            <td>
                <strong><?= htmlspecialchars($u['username']) ?></strong>
                <?php if ($u['id'] === $myId): ?><span class="you-chip">You</span><?php endif; ?>
            </td>
            <td>
                <span class="role-badge <?= $u['role']==='admin'?'r-admin':'r-staff' ?>" id="rolebadge_<?= $u['id'] ?>">
                    <?= $u['role']==='admin'?'👑':'👤' ?> <?= $u['role'] ?>
                </span>
            </td>
            <td class="mono"><?= substr($u['created_at'],0,10) ?></td>
            <td>
                <div class="actions">
                    <button class="btn btn-purple" onclick="togglePwForm(<?= $u['id'] ?>)">🔑 Change PW</button>
                    <?php if ($u['id'] !== $myId): ?>
                    <button class="btn btn-teal" onclick="toggleRole(<?= $u['id'] ?>, '<?= $u['role'] ?>')"
                            id="rolebtn_<?= $u['id'] ?>">
                        <?= $u['role']==='admin'?'Demote to Staff':'Promote to Admin' ?>
                    </button>
                    <button class="btn btn-danger" onclick="deleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>')">🗑️</button>
                    <?php endif; ?>
                </div>
                <div class="pw-form" id="pwform_<?= $u['id'] ?>">
                    <input type="password" id="pwval_<?= $u['id'] ?>" placeholder="New password" style="width:180px">
                    <button class="btn btn-teal" onclick="changePw(<?= $u['id'] ?>)">Save</button>
                    <button class="btn btn-ghost" onclick="togglePwForm(<?= $u['id'] ?>)">✕</button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Request history -->
<?php if (!empty($history)): ?>
<div class="panel">
    <div class="panel-hd"><span class="panel-title">📋 Recent Request History</span></div>
    <div style="overflow-x:auto">
    <table>
        <thead><tr><th>Username</th><th>Role</th><th>Status</th><th>Requested</th><th>Reviewed</th></tr></thead>
        <tbody>
        <?php foreach ($history as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['username']) ?></td>
            <td><span class="role-badge <?= $r['requested_role']==='admin'?'r-admin':'r-staff' ?>"><?= $r['requested_role'] ?></span></td>
            <td><span class="role-badge <?= $r['status']==='approved'?'r-approved':'r-rejected' ?>"><?= $r['status'] ?></span></td>
            <td class="mono"><?= substr($r['created_at'],0,16) ?></td>
            <td class="mono"><?= $r['reviewed_at'] ? substr($r['reviewed_at'],0,16) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

</div><!-- /container -->

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
<div id="toast" class="toast">✅ Done</div>

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
function toast(msg, isErr=false) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className   = 'toast ' + (isErr ? 'toast-err' : 'toast-ok');
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2800);
}
async function post(data) {
    const fd = new FormData();
    Object.entries(data).forEach(([k,v]) => fd.append(k, v));
    const res  = await fetch(window.location.href, {method:'POST', body:fd});
    return await res.json();
}

// ── Approve request ──
function approveReq(id) {
    modal('Approve this registration and create the user account?', async () => {
        const r = await post({approve_id: id});
        if (r.success) { document.getElementById('req_'+id)?.remove(); toast('✅ User approved and created!'); setTimeout(()=>location.reload(),1500); }
        else toast('⚠️ ' + (r.message || 'Error'), true);
    }, null, '✅');
}

// ── Reject request ──
function rejectReq(id) {
    modal('Reject this registration request?', async () => {
        const r = await post({reject_id: id});
        if (r.success) { document.getElementById('req_'+id)?.remove(); toast('Request rejected.'); }
        else toast('Error', true);
    }, null, '✕');
}

// ── Add user directly ──
document.getElementById('addUserBtn').addEventListener('click', async () => {
    const username = document.getElementById('newUser').value.trim();
    const password = document.getElementById('newPass').value;
    const role     = document.getElementById('newRole').value;
    if (!username || !password) return toast('⚠️ Username and password are required.', true);
    const r = await post({add_user:1, username, password, role});
    if (r.success) { toast('✅ User added!'); setTimeout(()=>location.reload(),1200); }
    else toast('⚠️ ' + (r.message||'Error'), true);
});

// ── Toggle password form ──
function togglePwForm(id) {
    document.getElementById('pwform_'+id).classList.toggle('open');
}

// ── Change password ──
async function changePw(id) {
    const pw = document.getElementById('pwval_'+id).value;
    if (!pw) return toast('⚠️ Enter a password.', true);
    const r = await post({change_password:1, user_id:id, new_password:pw});
    if (r.success) { togglePwForm(id); toast('✅ Password updated!'); }
    else toast('⚠️ ' + (r.message||'Error'), true);
}

// ── Toggle role ──
async function toggleRole(id, currentRole) {
    const newRole = currentRole === 'admin' ? 'staff' : 'admin';
    const label   = newRole === 'admin' ? 'Promote to Admin' : 'Demote to Staff';
    modal(`Change this user to ${newRole}?`, async () => {
        const r = await post({change_role:1, user_id:id, role:newRole});
        if (r.success) {
            const badge = document.getElementById('rolebadge_'+id);
            const btn   = document.getElementById('rolebtn_'+id);
            if (badge) { badge.className = 'role-badge ' + (newRole==='admin'?'r-admin':'r-staff'); badge.textContent = (newRole==='admin'?'👑 ':'👤 ') + newRole; }
            if (btn)   { btn.textContent = newRole==='admin' ? 'Demote to Staff' : 'Promote to Admin'; btn.onclick = () => toggleRole(id, newRole); }
            toast('✅ Role updated!');
        } else toast('⚠️ ' + (r.message||'Error'), true);
    }, null, '🔄');
}

// ── Delete user ──
function deleteUser(id, name) {
    modal(`Delete user "${name}"? This cannot be undone.`, async () => {
        const r = await post({delete_user: id});
        if (r.success) { document.getElementById('user_'+id)?.remove(); toast('User deleted.'); }
        else toast('⚠️ ' + (r.message||'Error'), true);
    }, null, '🗑️');
}
</script>
</body>
</html>