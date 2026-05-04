<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_role = $_SESSION['role'];
$username  = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Liquidation · Janeth's Business</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="theme.js"></script>
    <style>
        :root {
            --bg:#0a0e17;--surface:#111827;--surface-2:#1a2234;--surface-3:#222d42;
            --border:rgba(255,255,255,0.07);--border-hi:rgba(255,255,255,0.12);
            --accent:#f5a623;--accent-dim:rgba(245,166,35,.12);--accent-glow:rgba(245,166,35,.25);
            --teal:#29b6c8;--teal-dim:rgba(41,182,200,.1);--teal-glow:rgba(41,182,200,.3);
            --text:#e8edf5;--text-muted:#6b7a93;--text-faint:#3d4d63;
            --danger:#f87171;--success:#34d399;--purple:#a78bfa;--warning:#fbbf24;
            --radius:16px;--radius-sm:10px;
            --sidebar-w:220px;
        }
        [data-theme="light"] {
            --bg:#f0f4f9;--surface:#ffffff;--surface-2:#e8eef5;--surface-3:#d8e3ef;
            --border:rgba(0,0,0,0.08);--border-hi:rgba(0,0,0,0.14);
            --text:#0d1b2a;--text-muted:#4a6080;--text-faint:#7090b0;
        }
        [data-theme="light"] body{background-image:radial-gradient(ellipse 70% 50% at 10% -10%,rgba(41,182,200,.04) 0%,transparent 60%),radial-gradient(ellipse 60% 40% at 90% 110%,rgba(245,166,35,.03) 0%,transparent 60%);}
        [data-theme="light"] input,[data-theme="light"] select{background:var(--surface-2)!important;color:var(--text)!important;border-color:var(--border)!important;}
        [data-theme="light"] select option{background:#e8eef5;color:#0d1b2a;}
        [data-theme="light"] .sidebar{background:var(--surface);border-right-color:var(--border);}
        [data-theme="light"] .nav-item:hover{background:var(--surface-2);}
        [data-theme="light"] .nav-item.active{background:rgba(41,182,200,.1);}
        [data-theme="light"] .card{background:var(--surface);}
        [data-theme="light"] .liq-input{background:var(--surface-2)!important;color:var(--text)!important;}
        [data-theme="light"] .result-card{background:var(--surface);}
        [data-theme="light"] .section-hd{background:var(--surface-2);}
        [data-theme="light"] .cash-item{background:var(--surface-2);border-color:var(--border);}

        *{margin:0;padding:0;box-sizing:border-box;}
        html{font-size:17px;}
        body{font-family:'Sora',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;
             background-image:radial-gradient(ellipse 70% 50% at 10% -10%,rgba(41,182,200,.06) 0%,transparent 60%),
                              radial-gradient(ellipse 60% 40% at 90% 110%,rgba(245,166,35,.04) 0%,transparent 60%);}
        .app{display:flex;min-height:100vh;}

        /* Sidebar */
        .sidebar{width:var(--sidebar-w);min-width:var(--sidebar-w);background:var(--surface);border-right:1px solid var(--border);
                  display:flex;flex-direction:column;position:fixed;left:0;top:0;bottom:0;z-index:100;transition:transform .25s;}
        .sidebar-logo{display:flex;align-items:center;gap:.75rem;padding:1.4rem 1.2rem 1.2rem;border-bottom:1px solid var(--border);}
        .logo-icon{width:36px;height:36px;background:linear-gradient(135deg,var(--teal),#1a9aab);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1rem;box-shadow:0 4px 12px rgba(41,182,200,.3);flex-shrink:0;}
        .logo-title{font-size:.95rem;font-weight:700;letter-spacing:-.02em;display:block;}
        .logo-sub{font-size:.6rem;color:var(--text-muted);font-weight:400;letter-spacing:.07em;text-transform:uppercase;}
        .user-section{padding:1rem 1.2rem;border-bottom:1px solid var(--border);}
        .user-chip{display:flex;align-items:center;gap:.55rem;}
        .user-avatar{width:28px;height:28px;background:linear-gradient(135deg,var(--accent),#e8920f);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:700;color:#0a0e17;flex-shrink:0;}
        .user-name{font-size:.82rem;font-weight:600;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
        .role-badge{font-size:.58rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;padding:.12rem .45rem;border-radius:50px;background:var(--accent-dim);color:var(--accent);border:1px solid rgba(245,166,35,.2);white-space:nowrap;}
        .nav{flex:1;padding:.75rem 0;overflow-y:auto;}
        .nav-label{font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text-faint);padding:.5rem 1.2rem .3rem;margin-top:.25rem;}
        .nav-item{display:flex;align-items:center;gap:.7rem;padding:.6rem 1.2rem;font-size:.83rem;font-weight:500;color:var(--text-muted);cursor:pointer;text-decoration:none;transition:.15s;border-left:2px solid transparent;margin:1px 0;}
        .nav-item:hover{background:var(--surface-2);color:var(--text);}
        .nav-item.active{background:rgba(41,182,200,.08);color:var(--teal);border-left-color:var(--teal);font-weight:600;}
        .nav-icon{font-size:.95rem;width:20px;text-align:center;flex-shrink:0;}
        .nav-divider{height:1px;background:var(--border);margin:.5rem 1.2rem;}
        .sidebar-footer{padding:.85rem 1.2rem;border-top:1px solid var(--border);}
        .btn-logout{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.55rem;border-radius:var(--radius-sm);background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.18);color:var(--danger);font-size:.8rem;font-weight:600;cursor:pointer;font-family:'Sora',sans-serif;transition:.15s;}
        .btn-logout:hover{background:rgba(248,113,113,.15);}
        .hamburger{display:none;position:fixed;top:.85rem;left:.85rem;z-index:200;background:var(--surface);border:1px solid var(--border);border-radius:8px;width:38px;height:38px;align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;}
        .sidebar-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:90;backdrop-filter:blur(2px);}
        #themeToggle{background:none;border:none;color:var(--text-muted);font-family:'Sora',sans-serif;font-size:.83rem;font-weight:500;cursor:pointer;padding:0;width:100%;text-align:left;}

        /* Main */
        .main{margin-left:var(--sidebar-w);flex:1;padding:1.5rem;min-width:0;}

        /* Date selector bar */
        .date-bar{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.25rem 1.75rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;}
        .date-bar-title{font-size:1.1rem;font-weight:700;letter-spacing:-.02em;flex:1;}
        .date-bar-title span{color:var(--teal);}
        .save-chip{display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .85rem;border-radius:50px;font-size:.82rem;font-weight:600;background:var(--surface-2);border:1px solid var(--border);color:var(--text-faint);transition:.3s;}
        .save-chip.saving{border-color:rgba(41,182,200,.3);color:var(--teal);background:var(--teal-dim);}
        .save-chip.saved{border-color:rgba(52,211,153,.25);color:var(--success);background:rgba(52,211,153,.08);}
        .save-chip.error{border-color:rgba(248,113,113,.25);color:var(--danger);background:rgba(248,113,113,.08);}
        .save-dot{width:7px;height:7px;border-radius:50%;background:currentColor;}
        .save-chip.saving .save-dot{animation:blink .8s infinite;}
        @keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}

        input[type="date"],select{background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);font-family:'Sora',sans-serif;font-size:.9rem;padding:.5rem .9rem;outline:none;transition:.18s;}
        input[type="date"]:focus,select:focus{border-color:var(--teal);box-shadow:0 0 0 3px var(--teal-dim);}
        select option{background:#1a2234;}

        .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1.1rem;border-radius:50px;font-size:.85rem;font-weight:600;font-family:'Sora',sans-serif;cursor:pointer;border:none;transition:.18s;white-space:nowrap;}
        .btn-teal{background:var(--teal-dim);border:1px solid rgba(41,182,200,.2);color:var(--teal);}
        .btn-teal:hover{background:rgba(41,182,200,.18);}
        .btn-primary{background:linear-gradient(135deg,var(--teal),#1a9aab);color:#0a0e17;font-weight:700;box-shadow:0 3px 12px rgba(41,182,200,.3);}
        .btn-primary:hover{box-shadow:0 6px 20px rgba(41,182,200,.4);transform:translateY(-1px);}
        .btn-ghost{background:var(--surface);border:1px solid var(--border);color:var(--text-muted);}
        .btn-ghost:hover{border-color:var(--teal);color:var(--teal);}

        /* Layout: two columns on desktop */
        .liq-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem;}
        @media(max-width:900px){.liq-grid{grid-template-columns:1fr;}}

        /* Card */
        .card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:1.25rem;}
        .section-hd{display:flex;align-items:center;gap:.75rem;padding:.9rem 1.4rem;border-bottom:1px solid var(--border);background:var(--surface-2);}
        .section-icon{font-size:1.2rem;}
        .section-title{font-size:1rem;font-weight:700;}
        .section-sub{font-size:.75rem;color:var(--text-muted);margin-left:auto;}
        .card-body{padding:1.25rem 1.4rem;}

        /* Liquidation input rows */
        .liq-row{display:flex;align-items:center;gap:.75rem;margin-bottom:.85rem;}
        .liq-row:last-child{margin-bottom:0;}
        .liq-label{flex:1;font-size:.88rem;font-weight:500;color:var(--text-muted);}
        .liq-label small{display:block;font-size:.72rem;color:var(--text-faint);font-weight:400;margin-top:.1rem;}
        .liq-input{width:150px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);font-family:'DM Mono',monospace;font-size:.95rem;padding:.5rem .8rem;text-align:right;outline:none;transition:.15s;}
        .liq-input:focus{border-color:var(--teal);box-shadow:0 0 0 3px var(--teal-dim);}
        .liq-input[readonly]{background:var(--surface-3);color:var(--accent);border-color:transparent;font-weight:700;}
        .liq-divider{height:1px;background:var(--border);margin:.75rem 0;}

        /* Result card */
        .result-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.5rem 1.75rem;margin-bottom:1.25rem;}
        .result-title{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text-faint);margin-bottom:1rem;}
        .result-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;}
        .result-item{display:flex;flex-direction:column;gap:.25rem;padding-left:.65rem;border-left:3px solid var(--border);}
        .result-item.teal{border-left-color:var(--teal);}
        .result-item.danger{border-left-color:var(--danger);}
        .result-item.success{border-left-color:var(--success);}
        .result-item.accent{border-left-color:var(--accent);}
        .result-item.purple{border-left-color:var(--purple);}
        .r-label{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-faint);}
        .r-val{font-family:'DM Mono',monospace;font-size:1.25rem;font-weight:700;color:var(--text-muted);}
        .r-val.green{color:var(--success);}
        .r-val.red{color:var(--danger);}
        .r-val.teal{color:var(--teal);}
        .r-val.amber{color:var(--accent);}

        /* Over/Short big display */
        .overshort-display{text-align:center;padding:1.75rem;border-radius:var(--radius);border:2px solid var(--border);margin-bottom:1.25rem;transition:.3s;}
        .overshort-display.over{border-color:rgba(52,211,153,.3);background:rgba(52,211,153,.05);}
        .overshort-display.short{border-color:rgba(248,113,113,.3);background:rgba(248,113,113,.05);}
        .os-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--text-faint);margin-bottom:.5rem;}
        .os-val{font-family:'DM Mono',monospace;font-size:clamp(2rem,5vw,3.5rem);font-weight:700;line-height:1;}
        .os-val.over{color:var(--success);}
        .os-val.short{color:var(--danger);}
        .os-note{font-size:.8rem;color:var(--text-muted);margin-top:.5rem;}

        /* Cash collection items */
        .cash-list{display:flex;flex-direction:column;gap:.65rem;}
        .cash-item{display:flex;align-items:center;gap:.75rem;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-sm);padding:.75rem 1rem;}
        .cash-item-label{flex:1;font-size:.88rem;font-weight:500;}
        .cash-item-input{width:130px;background:var(--surface-3);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'DM Mono',monospace;font-size:.9rem;padding:.45rem .7rem;text-align:right;outline:none;transition:.15s;}
        .cash-item-input:focus{border-color:var(--teal);box-shadow:0 0 0 2px var(--teal-dim);}
        .cash-total-bar{display:flex;justify-content:space-between;align-items:center;padding:.85rem 1rem;background:rgba(41,182,200,.05);border-top:1px solid rgba(41,182,200,.15);border-radius:0 0 var(--radius-sm) var(--radius-sm);margin-top:.25rem;}

        /* Debts / Discounts table */
        .debt-add-row{display:flex;gap:.65rem;align-items:flex-end;padding:1rem 1.4rem;border-bottom:1px solid var(--border);flex-wrap:wrap;}
        .df{display:flex;flex-direction:column;gap:.3rem;}
        .df label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-muted);}
        .debt-list{min-height:60px;}
        .debt-item{display:flex;align-items:center;gap:.75rem;padding:.75rem 1.4rem;border-bottom:1px solid var(--border);font-size:.88rem;transition:background .12s;}
        .debt-item:last-child{border-bottom:none;}
        .debt-item:hover{background:var(--surface-2);}
        .debt-name{flex:1;font-weight:500;}
        .debt-amount{font-family:'DM Mono',monospace;font-weight:700;color:var(--danger);}
        .debt-del{background:none;border:none;cursor:pointer;color:var(--text-faint);font-size:1rem;padding:.25rem .45rem;border-radius:6px;transition:.15s;}
        .debt-del:hover{color:var(--danger);background:rgba(248,113,113,.1);}
        .debt-total-bar{display:flex;justify-content:space-between;align-items:center;padding:.85rem 1.4rem;background:rgba(248,113,113,.04);border-top:1px solid rgba(248,113,113,.12);}
        .debt-empty{padding:1.5rem;text-align:center;color:var(--text-faint);font-size:.85rem;}
        .dtl{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-faint);}
        .dtv{font-family:'DM Mono',monospace;font-size:1.1rem;font-weight:700;color:var(--danger);}

        /* Puhonan / Capital section */
        .puhonan-summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.75rem;padding:1.25rem 1.4rem;}
        .puh-item{display:flex;flex-direction:column;gap:.2rem;padding-left:.6rem;border-left:2px solid var(--border);}
        .puh-label{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-faint);}
        .puh-val{font-family:'DM Mono',monospace;font-size:1.05rem;font-weight:700;color:var(--text-muted);}
        .puh-val.teal{color:var(--teal);}
        .puh-val.danger{color:var(--danger);}
        .puh-val.success{color:var(--success);}
        .puh-val.amber{color:var(--accent);}

        /* Print */
        @media print{
            .sidebar,.date-bar .btn,.debt-add-row,.hamburger{display:none!important;}
            body{background:#fff!important;color:#111!important;}
            .main{margin-left:0!important;}
        }

        /* Modal */
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(8px);display:none;justify-content:center;align-items:center;z-index:1000;padding:1rem;}
        .modal-overlay.active{display:flex;}
        .modal{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:2rem 2.25rem;max-width:420px;width:100%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,.5);animation:popIn .2s cubic-bezier(.34,1.56,.64,1);}
        @keyframes popIn{from{opacity:0;transform:scale(.9)}to{opacity:1;transform:none}}
        .modal-icon{font-size:2rem;margin-bottom:.65rem;}
        .modal-msg{font-size:.95rem;color:var(--text);margin-bottom:1.5rem;line-height:1.5;font-weight:500;}
        .modal-btns{display:flex;gap:.75rem;justify-content:center;}

        ::-webkit-scrollbar{width:7px;height:7px;}
        ::-webkit-scrollbar-track{background:transparent;}
        ::-webkit-scrollbar-thumb{background:var(--surface-3);border-radius:4px;}

        @media(max-width:768px){
            .sidebar{transform:translateX(-100%);}
            .sidebar.open{transform:translateX(0);}
            .sidebar-backdrop.open{display:block;}
            .hamburger{display:flex;}
            .main{margin-left:0;padding:1rem;padding-top:3.75rem;}
            .liq-grid{grid-template-columns:1fr;}
            .date-bar{flex-direction:column;align-items:stretch;}
        }
    </style>
</head>
<body>
<div class="app">

<button class="hamburger" id="hamburger">☰</button>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<nav class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">💵</div>
        <div>
            <span class="logo-title">Janeth's</span>
            <span class="logo-sub">Business</span>
        </div>
    </div>
    <div class="user-section">
        <div class="user-chip">
            <div class="user-avatar"><?= strtoupper(substr($username,0,1)) ?></div>
            <span class="user-name"><?= htmlspecialchars($username) ?></span>
            <span class="role-badge"><?= $user_role ?></span>
        </div>
    </div>
    <div class="nav">
        <div class="nav-label">Navigation</div>
        <a href="janeth-input.php" class="nav-item"><span class="nav-icon">✏️</span>Daily Entry</a>
        <a href="janeth-dashboard.php" class="nav-item"><span class="nav-icon">📊</span>Dashboard</a>
        <a href="janeth-liquidation.php" class="nav-item active"><span class="nav-icon">💵</span>Liquidation</a>
        <?php if ($user_role === 'admin'): ?>
        <div class="nav-divider"></div>
        <div class="nav-label">Admin</div>
        <a href="../admin/products.php" class="nav-item"><span class="nav-icon">⚙️</span>Products</a>
        <a href="../admin/users.php" class="nav-item"><span class="nav-icon">👥</span>Users</a>
        <?php endif; ?>
        <div class="nav-divider"></div>
        <div class="nav-label">Tools</div>
        <div class="nav-item" id="printBtn" style="cursor:pointer;"><span class="nav-icon">📄</span>Print</div>
        <div class="nav-divider"></div>
        <div class="nav-label">Appearance</div>
        <div class="nav-item" onclick="toggleTheme()" style="cursor:pointer;">
            <span class="nav-icon">🌓</span><button id="themeToggle">☀️ Light mode</button>
        </div>
    </div>
    <div class="sidebar-footer">
        <button class="btn-logout" id="logoutBtn">🚪 Sign out</button>
    </div>
</nav>

<div class="main">

<!-- Modal -->
<div id="modalOverlay" class="modal-overlay">
    <div class="modal">
        <div class="modal-icon" id="modalIcon">💬</div>
        <p class="modal-msg" id="modalMsg">Are you sure?</p>
        <div class="modal-btns">
            <button id="modalOk" class="btn btn-primary">OK</button>
            <button id="modalCancel" class="btn btn-ghost" style="display:none">Cancel</button>
        </div>
    </div>
</div>

<!-- Date bar -->
<div class="date-bar">
    <div>
        <div class="date-bar-title">💵 <span>Daily Liquidation</span></div>
        <div style="font-size:.78rem;color:var(--text-muted);margin-top:.2rem;">End-of-day cash settlement &amp; capital accounting</div>
    </div>
    <input type="date" id="liqDate">
    <button class="btn btn-teal" id="loadLiqBtn">↻ Load</button>
    <button class="btn btn-primary" id="saveLiqBtn">💾 Save</button>
    <div id="saveChip" class="save-chip"><span class="save-dot"></span><span id="saveLabel">Auto-saving</span></div>
</div>

<!-- Over/Short display -->
<div class="overshort-display" id="overShortDisplay">
    <div class="os-label">Final Liquidation Result</div>
    <div class="os-val" id="overShortVal">₱0.00</div>
    <div class="os-note" id="overShortNote">Load a date to calculate</div>
</div>

<!-- Summary results row -->
<div class="result-card">
    <div class="result-title">Liquidation Summary</div>
    <div class="result-grid">
        <div class="result-item teal"><div class="r-label">Stocks Amount (Selling)</div><div class="r-val teal" id="rStocksAmount">₱0.00</div></div>
        <div class="result-item accent"><div class="r-label">Puhonan / Capital</div><div class="r-val amber" id="rPuhonan">₱0.00</div></div>
        <div class="result-item success"><div class="r-label">Cash Collection</div><div class="r-val green" id="rCashCollection">₱0.00</div></div>
        <div class="result-item danger"><div class="r-label">Expenses</div><div class="r-val red" id="rExpenses">₱0.00</div></div>
        <div class="result-item danger"><div class="r-label">Payables (Debts)</div><div class="r-val red" id="rPayables">₱0.00</div></div>
        <div class="result-item purple"><div class="r-label">Today's Debts</div><div class="r-val" id="rTodayDebts" style="color:var(--purple)">₱0.00</div></div>
        <div class="result-item"><div class="r-label">Discounted Amount</div><div class="r-val" id="rDiscounted">₱0.00</div></div>
        <div class="result-item"><div class="r-label">Projected Sales</div><div class="r-val amber" id="rProjected">₱0.00</div></div>
    </div>
</div>

<div class="liq-grid">

    <!-- LEFT: Cash Collection -->
    <div>
        <div class="card">
            <div class="section-hd">
                <span class="section-icon">💰</span>
                <span class="section-title" style="color:var(--success)">Cash Collection</span>
                <span class="section-sub">What you received today</span>
            </div>
            <div class="card-body">
                <div class="cash-list">
                    <div class="cash-item">
                        <span class="cash-item-label">💵 Bills / Cash</span>
                        <input type="number" class="cash-item-input autosave-trigger" id="cashBills" placeholder="0.00" step="0.01" min="0" data-key="cash_bills">
                    </div>
                    <div class="cash-item">
                        <span class="cash-item-label">🪙 Coins</span>
                        <input type="number" class="cash-item-input autosave-trigger" id="cashCoins" placeholder="0.00" step="0.01" min="0" data-key="cash_coins">
                    </div>
                    <div class="cash-item">
                        <span class="cash-item-label">🧊 Ice</span>
                        <input type="number" class="cash-item-input autosave-trigger" id="cashIce" placeholder="0.00" step="0.01" min="0" data-key="cash_ice">
                    </div>
                    <div class="cash-item">
                        <span class="cash-item-label">🎫 Ticket</span>
                        <input type="number" class="cash-item-input autosave-trigger" id="cashTicket" placeholder="0.00" step="0.01" min="0" data-key="cash_ticket">
                    </div>
                    <div class="cash-item">
                        <span class="cash-item-label">🧂 Sugar (Suga)</span>
                        <input type="number" class="cash-item-input autosave-trigger" id="cashSuga" placeholder="0.00" step="0.01" min="0" data-key="cash_suga">
                    </div>
                    <div class="cash-item">
                        <span class="cash-item-label">🛍️ Plastic / CR</span>
                        <input type="number" class="cash-item-input autosave-trigger" id="cashPlastic" placeholder="0.00" step="0.01" min="0" data-key="cash_plastic">
                    </div>
                    <div class="cash-item">
                        <span class="cash-item-label">🍽️ Meal</span>
                        <input type="number" class="cash-item-input autosave-trigger" id="cashMeal" placeholder="0.00" step="0.01" min="0" data-key="cash_meal">
                    </div>
                    <div class="cash-item">
                        <span class="cash-item-label">🍱 Plete</span>
                        <input type="number" class="cash-item-input autosave-trigger" id="cashPlete" placeholder="0.00" step="0.01" min="0" data-key="cash_plete">
                    </div>
                    <div class="cash-item" style="border-color:rgba(52,211,153,.25);">
                        <span class="cash-item-label" style="font-weight:700;color:var(--success)">✅ Extra Cash (P.U)</span>
                        <input type="number" class="cash-item-input autosave-trigger" id="cashPU" placeholder="0.00" step="0.01" min="0" data-key="cash_pu">
                    </div>
                </div>
                <div class="cash-total-bar">
                    <span style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-faint)">Total Cash Collected</span>
                    <span style="font-family:'DM Mono',monospace;font-size:1.2rem;font-weight:700;color:var(--success)" id="cashTotal">₱0.00</span>
                </div>
            </div>
        </div>

        <!-- Capital / Puhonan summary (auto-computed from daily entry) -->
        <div class="card">
            <div class="section-hd">
                <span class="section-icon">💼</span>
                <span class="section-title" style="color:var(--accent)">Puhonan / Capital</span>
                <span class="section-sub">From daily entry cost prices</span>
            </div>
            <div class="puhonan-summary">
                <div class="puh-item" style="border-left-color:var(--teal)"><div class="puh-label">Stocks In (Cost)</div><div class="puh-val teal" id="puhStocksIn">₱0.00</div></div>
                <div class="puh-item" style="border-left-color:var(--success)"><div class="puh-label">Return (Supplier)</div><div class="puh-val success" id="puhReturn">₱0.00</div></div>
                <div class="puh-item" style="border-left-color:var(--danger)"><div class="puh-label">Payables</div><div class="puh-val danger" id="puhPayables">₱0.00</div></div>
                <div class="puh-item" style="border-left-color:var(--accent)"><div class="puh-label">Balance</div><div class="puh-val amber" id="puhBalance">₱0.00</div></div>
            </div>
            <div style="padding:.75rem 1.4rem 1rem;font-size:.78rem;color:var(--text-faint);border-top:1px solid var(--border);">
                ℹ️ Puhonan = total cost of all stocks received. Payables = what you still owe suppliers after returns.
            </div>
        </div>
    </div>

    <!-- RIGHT: Debts & Discounts -->
    <div>
        <!-- Today's Debts (customers who didn't pay) -->
        <div class="card">
            <div class="section-hd">
                <span class="section-icon">📋</span>
                <span class="section-title" style="color:var(--purple)">Today's Debts (Utang)</span>
                <span class="section-sub">Customers who owe</span>
            </div>
            <div class="debt-add-row">
                <div class="df"><label>Customer Name</label><input type="text" id="debtName" placeholder="e.g. Aling Rosa" style="width:160px;background:var(--surface-2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'Sora',sans-serif;font-size:.85rem;padding:.45rem .75rem;outline:none;"></div>
                <div class="df"><label>Amount (₱)</label><input type="number" id="debtAmt" placeholder="0.00" step="0.01" min="0" style="width:110px;background:var(--surface-2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'DM Mono',monospace;font-size:.9rem;padding:.45rem .7rem;outline:none;text-align:right;"></div>
                <button class="btn btn-teal" id="addDebtBtn" style="align-self:flex-end">+ Add</button>
            </div>
            <div class="debt-list" id="debtList"><div class="debt-empty">No debts recorded.</div></div>
            <div class="debt-total-bar">
                <span class="dtl">Total Today's Debts</span>
                <span class="dtv" id="debtTotal">₱0.00</span>
            </div>
        </div>

        <!-- Yesterday's Debts (payables carried over) -->
        <div class="card">
            <div class="section-hd">
                <span class="section-icon">📅</span>
                <span class="section-title" style="color:var(--danger)">Payables (Yesterday's Debts)</span>
                <span class="section-sub">Carried over from prev. day</span>
            </div>
            <div class="debt-add-row">
                <div class="df"><label>Name / Source</label><input type="text" id="payableName" placeholder="e.g. Aling Rosa" style="width:160px;background:var(--surface-2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'Sora',sans-serif;font-size:.85rem;padding:.45rem .75rem;outline:none;"></div>
                <div class="df"><label>Amount (₱)</label><input type="number" id="payableAmt" placeholder="0.00" step="0.01" min="0" style="width:110px;background:var(--surface-2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'DM Mono',monospace;font-size:.9rem;padding:.45rem .7rem;outline:none;text-align:right;"></div>
                <button class="btn btn-teal" id="addPayableBtn" style="align-self:flex-end">+ Add</button>
            </div>
            <div class="debt-list" id="payableList"><div class="debt-empty">No payables recorded.</div></div>
            <div class="debt-total-bar">
                <span class="dtl">Total Payables</span>
                <span class="dtv" id="payableTotal">₱0.00</span>
            </div>
        </div>

        <!-- Discounts -->
        <div class="card">
            <div class="section-hd">
                <span class="section-icon">🏷️</span>
                <span class="section-title" style="color:var(--warning)">Discounted Items</span>
                <span class="section-sub">Price reductions given</span>
            </div>
            <div class="debt-add-row">
                <div class="df"><label>Item</label><input type="text" id="discName" placeholder="e.g. Whole Chicken" style="width:160px;background:var(--surface-2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'Sora',sans-serif;font-size:.85rem;padding:.45rem .75rem;outline:none;"></div>
                <div class="df"><label>Disc. Amount (₱)</label><input type="number" id="discAmt" placeholder="0.00" step="0.01" min="0" style="width:110px;background:var(--surface-2);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'DM Mono',monospace;font-size:.9rem;padding:.45rem .7rem;outline:none;text-align:right;"></div>
                <button class="btn btn-teal" id="addDiscBtn" style="align-self:flex-end">+ Add</button>
            </div>
            <div class="debt-list" id="discList"><div class="debt-empty">No discounts recorded.</div></div>
            <div class="debt-total-bar">
                <span class="dtl">Total Discounts</span>
                <span class="dtv" id="discTotal" style="color:var(--warning)">₱0.00</span>
            </div>
        </div>
    </div>
</div>

<!-- Remaining stocks section -->
<div class="card" style="margin-bottom:1.5rem;">
    <div class="section-hd">
        <span class="section-icon">📦</span>
        <span class="section-title">Remaining Stocks Value</span>
        <span class="section-sub">Auto-loaded from daily entry</span>
    </div>
    <div style="padding:1.25rem 1.4rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;">
        <div class="puh-item" style="border-left-color:var(--teal)"><div class="puh-label">Chicken Remaining (Selling)</div><div class="puh-val teal" id="remChicken">₱0.00</div></div>
        <div class="puh-item" style="border-left-color:var(--frozen, #60a5fa)"><div class="puh-label">Frozen Remaining (Selling)</div><div class="puh-val" id="remFrozen" style="color:#60a5fa">₱0.00</div></div>
        <div class="puh-item" style="border-left-color:var(--accent)"><div class="puh-label">Total Remaining (Selling)</div><div class="puh-val amber" id="remTotal">₱0.00</div></div>
        <div class="puh-item" style="border-left-color:var(--success)"><div class="puh-label">Sold Value (Selling)</div><div class="puh-val success" id="soldTotal">₱0.00</div></div>
    </div>
</div>

<div style="padding-bottom:2rem;text-align:center;font-size:.82rem;color:var(--text-faint);font-family:'DM Mono',monospace;">
    ✦ Over/Short = Cash Collection − Expenses − Payables − Today's Debts − Discounts &nbsp;·&nbsp; Positive = Over, Negative = Short
</div>

</div><!-- /.main -->
</div><!-- /.app -->

<script>
const API = 'janeth.php';
const ROLE = '<?= $user_role ?>';

let debts = [], payables = [], discounts = [];
let liqData = {};
let autoSaveTimer = null;

// Sidebar
document.getElementById('hamburger').addEventListener('click',()=>{
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebarBackdrop').classList.add('open');
});
document.getElementById('sidebarBackdrop').addEventListener('click',()=>{
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarBackdrop').classList.remove('open');
});

function esc(s){return String(s).replace(/[&<>]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[m]));}
function peso(n){return '₱'+(parseFloat(n)||0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2});}

function modal(msg,onOk,onCancel=null,icon='💬',okLabel='OK',cancelLabel='Cancel'){
    document.getElementById('modalMsg').textContent=msg;
    document.getElementById('modalIcon').textContent=icon;
    document.getElementById('modalOk').textContent=okLabel;
    const cb=document.getElementById('modalCancel');
    cb.textContent=cancelLabel;cb.style.display=cancelLabel?'':'none';
    document.getElementById('modalOverlay').classList.add('active');
    const close=()=>document.getElementById('modalOverlay').classList.remove('active');
    const ok=document.getElementById('modalOk').cloneNode(true);
    const can=document.getElementById('modalCancel').cloneNode(true);
    document.getElementById('modalOk').replaceWith(ok);
    document.getElementById('modalCancel').replaceWith(can);
    ok.addEventListener('click',()=>{close();onOk&&onOk();});
    can.addEventListener('click',()=>{close();onCancel&&onCancel();});
}
function alert2(msg,isErr=false,autoClose=2800){
    modal(msg,null,null,isErr?'⚠️':'✅','OK','');
    setTimeout(()=>document.getElementById('modalOverlay').classList.remove('active'),autoClose);
}

function getVal(id){return parseFloat(document.getElementById(id).value)||0;}

// ── Compute & render ──────────────────────────────────────────────────────
function compute(){
    // Cash collection
    const cashFields=['cashBills','cashCoins','cashIce','cashTicket','cashSuga','cashPlastic','cashMeal','cashPlete','cashPU'];
    const cashTotal=cashFields.reduce((s,id)=>s+getVal(id),0);
    document.getElementById('cashTotal').textContent=peso(cashTotal);

    // Totals from lists
    const debtTotal=debts.reduce((s,d)=>s+d.amount,0);
    const payableTotal=payables.reduce((s,p)=>s+p.amount,0);
    const discTotal=discounts.reduce((s,d)=>s+d.amount,0);

    document.getElementById('debtTotal').textContent=peso(debtTotal);
    document.getElementById('payableTotal').textContent=peso(payableTotal);
    document.getElementById('discTotal').textContent=peso(discTotal);

    // Load from liqData (filled by loadFromEntry)
    const expenses   = parseFloat(liqData.expenses||0);
    const puhonan    = parseFloat(liqData.puhonan||0);
    const stocksAmt  = parseFloat(liqData.stocks_amount||0);
    const remChicken = parseFloat(liqData.rem_chicken||0);
    const remFrozen  = parseFloat(liqData.rem_frozen||0);
    const soldTotal  = parseFloat(liqData.sold_total||0);
    const supplierReturn = parseFloat(liqData.supplier_return||0);

    // Puhonan payables = puhonan - return
    const puhPayables = Math.max(0, puhonan - supplierReturn);

    document.getElementById('rStocksAmount').textContent=peso(stocksAmt);
    document.getElementById('rPuhonan').textContent=peso(puhonan);
    document.getElementById('rCashCollection').textContent=peso(cashTotal);
    document.getElementById('rExpenses').textContent=peso(expenses);
    document.getElementById('rPayables').textContent=peso(payableTotal);
    document.getElementById('rTodayDebts').textContent=peso(debtTotal);
    document.getElementById('rDiscounted').textContent=peso(discTotal);
    document.getElementById('rProjected').textContent=peso(stocksAmt);

    document.getElementById('puhStocksIn').textContent=peso(puhonan);
    document.getElementById('puhReturn').textContent=peso(supplierReturn);
    document.getElementById('puhPayables').textContent=peso(puhPayables);
    document.getElementById('puhBalance').textContent=peso(puhonan-supplierReturn);

    document.getElementById('remChicken').textContent=peso(remChicken);
    document.getElementById('remFrozen').textContent=peso(remFrozen);
    document.getElementById('remTotal').textContent=peso(remChicken+remFrozen);
    document.getElementById('soldTotal').textContent=peso(soldTotal);

    // Over/Short = Cash - Expenses - Payables - TodayDebts - Discounts
    const overShort = cashTotal - expenses - payableTotal - debtTotal - discTotal;
    const el=document.getElementById('overShortVal');
    const disp=document.getElementById('overShortDisplay');
    const note=document.getElementById('overShortNote');
    el.textContent=peso(Math.abs(overShort));
    if(overShort>0){
        el.className='os-val over';disp.className='overshort-display over';
        note.textContent=`+ Over by ${peso(overShort)} · You have excess cash`;
    } else if(overShort<0){
        el.className='os-val short';disp.className='overshort-display short';
        note.textContent=`− Short by ${peso(Math.abs(overShort))} · Cash is missing`;
    } else {
        el.className='os-val';disp.className='overshort-display';
        note.textContent='✓ Exactly balanced';
    }
}

// ── Load from daily entry ──────────────────────────────────────────────────
async function loadFromEntry(date){
    try{
        const [recRes,expRes,seRes]=await Promise.all([
            fetch(`${API}?date=${date}&for=dashboard`),
            fetch(`${API}?expenses=${date}`),
            fetch(`${API}?stock_entries=${date}`)
        ]);
        const recData=await recRes.json();
        const expData=await expRes.json();
        const seData=await seRes.json();

        const records=recData.records||[];
        const expenses=(expData.expenses||[]).reduce((s,e)=>s+parseFloat(e.amount||0),0);
        const stockEntries=seData.stock_entries||[];

        // Selling-price calculations
        let stocksAmt=0,remChicken=0,remFrozen=0,soldTotal=0;
        records.forEach(r=>{
            const price=parseFloat(r.price)||0;
            const sold=parseFloat(r.sold)||0;
            const rem=parseFloat(r.remaining_qty)||0;
            const stockIn=parseFloat(r.stock_in)||0;
            stocksAmt += (stockIn + parseFloat(r.yesterday_qty||0)) * price;
            soldTotal += sold * price;
            if(r.product_category==='Chicken') remChicken += rem*price;
            else remFrozen += rem*price;
        });

        // Puhonan = total cost from stock entries (Pickup/supplier cost prices)
        const puhonan=stockEntries.reduce((s,e)=>s+parseFloat(e.total_cost||0),0);
        // Supplier returns: remaining * cost_price not tracked separately — use 0 unless we have return data
        const supplierReturn=parseFloat(liqData.supplier_return||0);

        liqData = {...liqData,
            expenses, puhonan, stocks_amount: stocksAmt,
            rem_chicken: remChicken, rem_frozen: remFrozen, sold_total: soldTotal,
            supplier_return: supplierReturn
        };
        compute();
    }catch(e){console.error('Failed to load entry data:',e);}
}

// ── Load liquidation record ─────────────────────────────────────────────────
async function loadLiq(date){
    if(!date) return;
    try{
        const res=await fetch(`${API}?liquidation=${date}`);
        const data=await res.json();
        const liq=data.liquidation||{};

        // Fill cash fields
        const cashMap={cash_bills:'cashBills',cash_coins:'cashCoins',cash_ice:'cashIce',
            cash_ticket:'cashTicket',cash_suga:'cashSuga',cash_plastic:'cashPlastic',
            cash_meal:'cashMeal',cash_plete:'cashPlete',cash_pu:'cashPU'};
        Object.entries(cashMap).forEach(([k,id])=>{
            document.getElementById(id).value=liq[k]||'';
        });

        // Lists
        debts    = liq.debts     || [];
        payables = liq.payables  || [];
        discounts= liq.discounts || [];
        liqData  = {...liqData, supplier_return: parseFloat(liq.supplier_return||0)};

        renderDebts();renderPayables();renderDiscounts();
        await loadFromEntry(date);
        compute();
    }catch(e){
        // No saved liquidation — still load from entry
        debts=[];payables=[];discounts=[];
        document.querySelectorAll('.cash-item-input').forEach(el=>el.value='');
        await loadFromEntry(date);
        compute();
    }
}

// ── Render lists ────────────────────────────────────────────────────────────
function renderDebts(){
    const el=document.getElementById('debtList');
    if(!debts.length){el.innerHTML='<div class="debt-empty">No debts recorded.</div>';return;}
    el.innerHTML=debts.map((d,i)=>`
        <div class="debt-item">
            <span class="debt-name">${esc(d.name)}</span>
            <span class="debt-amount">${peso(d.amount)}</span>
            <button class="debt-del" onclick="removeItem('debts',${i})">✕</button>
        </div>`).join('');
    compute();
}
function renderPayables(){
    const el=document.getElementById('payableList');
    if(!payables.length){el.innerHTML='<div class="debt-empty">No payables recorded.</div>';return;}
    el.innerHTML=payables.map((p,i)=>`
        <div class="debt-item">
            <span class="debt-name">${esc(p.name)}</span>
            <span class="debt-amount">${peso(p.amount)}</span>
            <button class="debt-del" onclick="removeItem('payables',${i})">✕</button>
        </div>`).join('');
    compute();
}
function renderDiscounts(){
    const el=document.getElementById('discList');
    if(!discounts.length){el.innerHTML='<div class="debt-empty">No discounts recorded.</div>';return;}
    el.innerHTML=discounts.map((d,i)=>`
        <div class="debt-item">
            <span class="debt-name">${esc(d.name)}</span>
            <span class="debt-amount" style="color:var(--warning)">${peso(d.amount)}</span>
            <button class="debt-del" onclick="removeItem('discounts',${i})">✕</button>
        </div>`).join('');
    compute();
}
function removeItem(list,idx){
    if(list==='debts')    {debts.splice(idx,1);renderDebts();}
    else if(list==='payables'){payables.splice(idx,1);renderPayables();}
    else if(list==='discounts'){discounts.splice(idx,1);renderDiscounts();}
    triggerAutoSave();
}

// Add buttons
document.getElementById('addDebtBtn').addEventListener('click',()=>{
    const name=document.getElementById('debtName').value.trim();
    const amt=parseFloat(document.getElementById('debtAmt').value)||0;
    if(!name) return alert2('Enter a customer name.',true);
    if(amt<=0) return alert2('Enter a valid amount.',true);
    debts.push({name,amount:amt});
    document.getElementById('debtName').value='';document.getElementById('debtAmt').value='';
    renderDebts();triggerAutoSave();
});
document.getElementById('addPayableBtn').addEventListener('click',()=>{
    const name=document.getElementById('payableName').value.trim();
    const amt=parseFloat(document.getElementById('payableAmt').value)||0;
    if(!name) return alert2('Enter a name.',true);
    if(amt<=0) return alert2('Enter a valid amount.',true);
    payables.push({name,amount:amt});
    document.getElementById('payableName').value='';document.getElementById('payableAmt').value='';
    renderPayables();triggerAutoSave();
});
document.getElementById('addDiscBtn').addEventListener('click',()=>{
    const name=document.getElementById('discName').value.trim();
    const amt=parseFloat(document.getElementById('discAmt').value)||0;
    if(!name) return alert2('Enter an item name.',true);
    if(amt<=0) return alert2('Enter a valid amount.',true);
    discounts.push({name,amount:amt});
    document.getElementById('discName').value='';document.getElementById('discAmt').value='';
    renderDiscounts();triggerAutoSave();
});

// ── Auto-save ───────────────────────────────────────────────────────────────
function triggerAutoSave(){
    clearTimeout(autoSaveTimer);
    const chip=document.getElementById('saveChip'),lbl=document.getElementById('saveLabel');
    chip.className='save-chip saving';lbl.textContent='Saving…';
    autoSaveTimer=setTimeout(doSave,1600);
}

async function doSave(){
    const date=document.getElementById('liqDate').value;
    if(!date) return;
    const chip=document.getElementById('saveChip'),lbl=document.getElementById('saveLabel');
    chip.className='save-chip saving';lbl.textContent='Saving…';

    const cashFields={cash_bills:'cashBills',cash_coins:'cashCoins',cash_ice:'cashIce',
        cash_ticket:'cashTicket',cash_suga:'cashSuga',cash_plastic:'cashPlastic',
        cash_meal:'cashMeal',cash_plete:'cashPlete',cash_pu:'cashPU'};
    const cashData={};
    Object.entries(cashFields).forEach(([k,id])=>{cashData[k]=getVal(id);});

    const payload={save_liquidation:1,date,...cashData,
        debts,payables,discounts,
        supplier_return:parseFloat(liqData.supplier_return||0)
    };
    try{
        const res=await fetch(API,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
        const data=await res.json();
        if(data.success){
            chip.className='save-chip saved';lbl.textContent='Saved ✓';
            setTimeout(()=>{chip.className='save-chip';lbl.textContent='Auto-saving';},2500);
        }else{chip.className='save-chip error';lbl.textContent='Save failed';}
    }catch{chip.className='save-chip error';lbl.textContent='Save failed';}
}

// Cash input listeners
document.querySelectorAll('.autosave-trigger').forEach(el=>{
    el.addEventListener('input',()=>{compute();triggerAutoSave();});
});

// Load / Save buttons
document.getElementById('loadLiqBtn').addEventListener('click',async()=>{
    const date=document.getElementById('liqDate').value;
    if(!date) return alert2('Please select a date.',true);
    localStorage.setItem('janeth_liq_date',date);
    await loadLiq(date);
    alert2(`Liquidation loaded for ${date}`);
});
document.getElementById('saveLiqBtn').addEventListener('click',async()=>{
    const date=document.getElementById('liqDate').value;
    if(!date) return alert2('Please select a date first.',true);
    await doSave();
    alert2('Liquidation saved!');
});
document.getElementById('liqDate').addEventListener('change',async()=>{
    const date=document.getElementById('liqDate').value;
    localStorage.setItem('janeth_liq_date',date);
    await loadLiq(date);
});
document.getElementById('printBtn').addEventListener('click',()=>window.print());
document.getElementById('logoutBtn').addEventListener('click',()=>{
    modal('Sign out? Unsaved changes will be lost.',
        ()=>{window.location.href='logout.php';},
        null,'👋','Yes, sign out','Stay');
});

// Init
const savedDate=localStorage.getItem('janeth_liq_date')||localStorage.getItem('janeth_date')||new Date().toISOString().split('T')[0];
document.getElementById('liqDate').value=savedDate;
loadLiq(savedDate);
</script>
</body>
</html>
