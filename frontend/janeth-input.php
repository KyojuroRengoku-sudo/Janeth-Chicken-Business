<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.html'); exit; }
$user_role = $_SESSION['role'];
$username  = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Daily Entry · Janeth's Business</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#0a0e17; --surface:#111827; --surface-2:#1a2234; --surface-3:#222d42;
            --border:rgba(255,255,255,0.07); --border-hi:rgba(255,255,255,0.12);
            --accent:#f5a623; --accent-dim:rgba(245,166,35,.12); --accent-glow:rgba(245,166,35,.25);
            --teal:#29b6c8; --teal-dim:rgba(41,182,200,.1);
            --text:#e8edf5; --text-muted:#6b7a93; --text-faint:#3d4d63;
            --danger:#f87171; --success:#34d399;
            --chicken:#fbbf24; --frozen:#60a5fa; --expense:#a78bfa;
            --radius:14px; --radius-sm:9px;
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Sora',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding:1.5rem;
             background-image:radial-gradient(ellipse 70% 50% at 10% -10%,rgba(41,182,200,.06) 0%,transparent 60%),
                              radial-gradient(ellipse 60% 40% at 90% 110%,rgba(245,166,35,.04) 0%,transparent 60%);}
        .container{max-width:1520px;margin:0 auto;}

        /* ── Header ── */
        .header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;
                margin-bottom:1.75rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border);}
        .logo{display:flex;align-items:center;gap:.75rem;}
        .logo-icon{width:40px;height:40px;background:linear-gradient(135deg,var(--teal),#1a9aab);border-radius:10px;
                   display:flex;align-items:center;justify-content:center;font-size:1.1rem;box-shadow:0 4px 16px rgba(41,182,200,.3);}
        .logo-text{display:flex;flex-direction:column;}
        .logo-title{font-size:1.1rem;font-weight:700;letter-spacing:-.02em;}
        .logo-sub{font-size:.67rem;color:var(--text-muted);font-weight:400;letter-spacing:.07em;text-transform:uppercase;}
        .header-right{display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;}
        .user-chip{display:flex;align-items:center;gap:.55rem;background:var(--surface);border:1px solid var(--border);
                   border-radius:50px;padding:.3rem .85rem .3rem .45rem;}
        .user-avatar{width:24px;height:24px;background:linear-gradient(135deg,var(--accent),#e8920f);border-radius:50%;
                     display:flex;align-items:center;justify-content:center;font-size:.62rem;font-weight:700;color:#0a0e17;}
        .user-name{font-size:.78rem;font-weight:500;}
        .role-badge{font-size:.6rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;padding:.15rem .55rem;
                    border-radius:50px;background:var(--accent-dim);color:var(--accent);border:1px solid rgba(245,166,35,.2);}

        /* Buttons */
        .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.42rem 1rem;border-radius:50px;
             font-size:.76rem;font-weight:600;font-family:'Sora',sans-serif;cursor:pointer;border:none;
             transition:.18s;text-decoration:none;white-space:nowrap;letter-spacing:.01em;}
        .btn-primary{background:linear-gradient(135deg,var(--accent),#e8920f);color:#0a0e17;box-shadow:0 3px 12px var(--accent-glow);}
        .btn-primary:hover{box-shadow:0 6px 20px rgba(245,166,35,.4);transform:translateY(-1px);}
        .btn-ghost{background:var(--surface);border:1px solid var(--border);color:var(--text-muted);}
        .btn-ghost:hover{border-color:var(--teal);color:var(--teal);background:var(--teal-dim);}
        .btn-teal{background:var(--teal-dim);border:1px solid rgba(41,182,200,.2);color:var(--teal);}
        .btn-teal:hover{background:rgba(41,182,200,.18);}
        .btn-danger{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:var(--danger);}
        .btn-danger:hover{background:rgba(248,113,113,.2);}
        .btn-save{background:linear-gradient(135deg,var(--teal),#1a9aab);color:#0a0e17;font-weight:700;
                  box-shadow:0 3px 12px rgba(41,182,200,.3);}
        .btn-save:hover{box-shadow:0 6px 20px rgba(41,182,200,.4);transform:translateY(-1px);}

        /* ── Date hero ── */
        .date-hero{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
                   padding:1.5rem 2rem;margin-bottom:1.1rem;display:flex;align-items:center;gap:2rem;flex-wrap:wrap;}
        .date-hero-left{flex:1;min-width:200px;}
        .hero-label{font-size:.67rem;text-transform:uppercase;letter-spacing:.12em;color:var(--text-muted);font-weight:600;margin-bottom:.4rem;}
        .hero-big{font-size:clamp(1.8rem,4vw,3rem);font-weight:700;letter-spacing:-.03em;line-height:1;}
        .hero-big .day-num{color:var(--accent);}
        .hero-sub{font-size:.75rem;color:var(--text-muted);margin-top:.3rem;font-family:'DM Mono',monospace;}
        .date-hero-right{display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;}

        /* Status chip */
        .status-chip{display:inline-flex;align-items:center;gap:.4rem;padding:.28rem .8rem;border-radius:50px;
                     font-size:.68rem;font-weight:700;letter-spacing:.05em;}
        .s-loaded{background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.25);color:var(--success);}
        .s-empty {background:var(--accent-dim);border:1px solid rgba(245,166,35,.25);color:var(--accent);}
        .s-none  {background:var(--surface-2);border:1px solid var(--border);color:var(--text-muted);}

        /* Autosave chip */
        .as-chip{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .7rem;border-radius:50px;
                 font-size:.67rem;font-weight:600;letter-spacing:.04em;transition:.3s;
                 background:var(--surface-2);border:1px solid var(--border);color:var(--text-faint);}
        .as-chip.saving{border-color:rgba(41,182,200,.3);color:var(--teal);background:var(--teal-dim);}
        .as-chip.saved {border-color:rgba(52,211,153,.25);color:var(--success);background:rgba(52,211,153,.08);}
        .as-chip.error {border-color:rgba(248,113,113,.25);color:var(--danger);background:rgba(248,113,113,.08);}
        .as-dot{width:6px;height:6px;border-radius:50%;background:currentColor;}
        .as-chip.saving .as-dot{animation:blink .8s infinite;}
        @keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}

        /* ── Controls bar ── */
        .controls{display:flex;flex-wrap:wrap;gap:.65rem;align-items:center;
                  background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
                  padding:.8rem 1.2rem;margin-bottom:1.1rem;}
        .controls-sep{flex:1;}
        input[type="text"],input[type="date"],input[type="number"],select{
            background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-sm);
            color:var(--text);font-family:'Sora',sans-serif;font-size:.78rem;
            padding:.42rem .85rem;outline:none;transition:.18s;}
        input:focus,select:focus{border-color:var(--teal);box-shadow:0 0 0 3px var(--teal-dim);}
        select option{background:#1a2234;}

        /* Toggle switch */
        .toggle-sw{display:inline-flex;align-items:center;gap:.45rem;background:var(--surface-2);
                   border-radius:50px;padding:.22rem .7rem .22rem .8rem;border:1px solid var(--border);}
        .toggle-sw label{font-size:.68rem;font-weight:600;color:var(--text-muted);cursor:pointer;}
        .toggle-sw input{width:30px;height:15px;appearance:none;background:var(--surface-3);border-radius:30px;
                         position:relative;cursor:pointer;transition:.2s;}
        .toggle-sw input:checked{background:var(--teal);}
        .toggle-sw input::before{content:'';width:11px;height:11px;background:#fff;border-radius:50%;
                                 position:absolute;top:2px;left:2px;transition:.2s;}
        .toggle-sw input:checked::before{left:17px;}

        /* ── Tables ── */
        .section-wrap{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
                      overflow:hidden;margin-bottom:1rem;}
        .section-hd{display:flex;align-items:center;gap:.75rem;padding:.9rem 1.2rem;
                    border-bottom:1px solid var(--border);background:var(--surface-2);}
        .section-icon{font-size:1.2rem;}
        .section-title{font-size:.88rem;font-weight:700;}
        .section-count{font-family:'DM Mono',monospace;font-size:.7rem;color:var(--text-faint);margin-left:auto;}
        .tbl-scroll{overflow-x:auto;}
        table{width:100%;border-collapse:collapse;min-width:780px;}
        thead tr{border-bottom:1px solid var(--border);}
        th{padding:.62rem 1rem;text-align:center;font-size:.65rem;font-weight:700;text-transform:uppercase;
           letter-spacing:.09em;color:var(--text-faint);background:var(--surface-2);white-space:nowrap;}
        th:first-child{text-align:left;}
        tbody tr{border-bottom:1px solid var(--border);transition:background .12s;}
        tbody tr:last-child:not(.total-row){border-bottom:none;}
        tbody tr:hover:not(.total-row){background:var(--surface-2);}
        td{padding:.58rem 1rem;font-size:.81rem;color:var(--text);text-align:center;vertical-align:middle;}
        td:first-child{text-align:left;}
        .prod-name{font-weight:600;font-size:.82rem;}

        /* Editable num input */
        .num-input{width:88px;background:var(--surface-3);border:1px solid var(--border);border-radius:var(--radius-sm);
                   color:var(--text);font-family:'DM Mono',monospace;font-size:.82rem;
                   padding:.36rem .6rem;text-align:center;outline:none;transition:.15s;}
        .num-input:focus{border-color:var(--teal);background:rgba(41,182,200,.05);box-shadow:0 0 0 3px var(--teal-dim);}
        .num-input:hover{border-color:var(--border-hi);}

        /* Price input (editable by staff) */
        .price-input{width:96px;background:var(--surface-3);border:1px solid rgba(245,166,35,.2);
                     border-radius:var(--radius-sm);color:var(--accent);font-family:'DM Mono',monospace;
                     font-size:.8rem;padding:.34rem .6rem;text-align:center;outline:none;transition:.15s;}
        .price-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim);}
        .price-ro{font-family:'DM Mono',monospace;font-size:.8rem;color:var(--accent);}

        .yest-cell{font-family:'DM Mono',monospace;font-size:.82rem;font-weight:700;color:var(--teal);}
        .yest-cell.manual{color:var(--text-muted);}
        .sold-cell{font-family:'DM Mono',monospace;font-size:.82rem;color:var(--success);}
        .total-cell-val{font-family:'DM Mono',monospace;font-size:.8rem;color:var(--accent);}

        /* Sold warning badge */
        .sold-warn{color:var(--danger);}
        .sold-ok  {color:var(--success);}

        /* Total row */
        .total-row td{background:rgba(245,166,35,.06)!important;border-top:1px solid rgba(245,166,35,.18)!important;
                      color:var(--accent)!important;font-family:'DM Mono',monospace;font-size:.83rem;font-weight:700;}
        .total-row .total-label{text-align:right!important;color:var(--text-muted)!important;
                                font-family:'Sora',sans-serif!important;font-size:.7rem!important;
                                font-weight:600!important;text-transform:uppercase;letter-spacing:.07em;padding-right:1.5rem!important;}
        .empty-section{padding:2.5rem;text-align:center;color:var(--text-faint);font-size:.83rem;}

        /* ── Expenses section ── */
        .exp-section{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
                     overflow:hidden;margin-bottom:1rem;}
        .exp-hd{display:flex;align-items:center;gap:.75rem;padding:.9rem 1.2rem;
                border-bottom:1px solid var(--border);background:var(--surface-2);}
        .exp-add-row{display:flex;flex-wrap:wrap;gap:.65rem;align-items:flex-end;padding:1rem 1.2rem;
                     border-bottom:1px solid var(--border);}
        .exp-field{display:flex;flex-direction:column;gap:.3rem;}
        .exp-field label{font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);}
        .exp-list{padding:0;}
        .exp-item{display:flex;align-items:center;gap:1rem;padding:.7rem 1.2rem;border-bottom:1px solid var(--border);
                  font-size:.8rem;transition:background .12s;}
        .exp-item:last-child{border-bottom:none;}
        .exp-item:hover{background:var(--surface-2);}
        .exp-cat-badge{padding:.2rem .65rem;border-radius:50px;font-size:.65rem;font-weight:700;
                       background:rgba(167,139,250,.12);color:var(--expense);border:1px solid rgba(167,139,250,.2);}
        .exp-desc{flex:1;font-weight:500;}
        .exp-amount{font-family:'DM Mono',monospace;font-weight:700;color:var(--danger);white-space:nowrap;}
        .exp-del{background:none;border:none;cursor:pointer;color:var(--text-faint);font-size:1rem;
                 padding:.2rem .4rem;border-radius:6px;transition:.15s;}
        .exp-del:hover{color:var(--danger);background:rgba(248,113,113,.1);}
        .exp-total-bar{display:flex;justify-content:space-between;align-items:center;padding:.85rem 1.2rem;
                       background:rgba(248,113,113,.05);border-top:1px solid rgba(248,113,113,.15);}
        .exp-total-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-faint);}
        .exp-total-val{font-family:'DM Mono',monospace;font-size:1.1rem;font-weight:700;color:var(--danger);}
        .exp-empty{padding:1.5rem;text-align:center;color:var(--text-faint);font-size:.82rem;}

        /* ── Modal ── */
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

        /* ── Net summary bar ── */
        .summary-bar{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;
                     background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
                     padding:1rem 1.5rem;margin-bottom:1rem;}
        .sum-item{display:flex;flex-direction:column;gap:.25rem;}
        .sum-label{font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:var(--text-faint);}
        .sum-val  {font-family:'DM Mono',monospace;font-size:1.15rem;font-weight:700;}
        .sum-val.green{color:var(--success);}
        .sum-val.amber{color:var(--accent);}
        .sum-val.red  {color:var(--danger);}
        .sum-val.teal {color:var(--teal);}

        @media print{
            .header,.controls,.date-hero-right,.modal-overlay,.exp-add-row,.exp-del{display:none!important;}
            body{background:#fff!important;color:#111!important;padding:.5rem;}
            .section-hd{background:#f5f5f5!important;border:none!important;}
            .section-title{color:#333!important;}
            th{background:#efefef!important;color:#555!important;}
            td{color:#111!important;}
            .num-input,.price-input{border:none!important;background:transparent!important;box-shadow:none!important;color:#111!important;width:auto!important;}
            .total-row td{background:#fffbf0!important;color:#b8720f!important;}
        }
        ::-webkit-scrollbar{width:6px;height:6px;}
        ::-webkit-scrollbar-track{background:transparent;}
        ::-webkit-scrollbar-thumb{background:var(--surface-3);border-radius:3px;}
        @media(max-width:640px){body{padding:1rem;}.date-hero{flex-direction:column;gap:1rem;}.controls{flex-direction:column;align-items:stretch;}}
    </style>
</head>
<body>
<div class="container">

<!-- Header -->
<div class="header">
    <div class="logo">
        <div class="logo-icon">📦</div>
        <div class="logo-text">
            <span class="logo-title">Janeth's Business</span>
            <span class="logo-sub">Daily Inventory & Sales</span>
        </div>
    </div>
    <div class="header-right">
        <div class="user-chip">
            <div class="user-avatar"><?= strtoupper(substr($username,0,1)) ?></div>
            <span class="user-name"><?= htmlspecialchars($username) ?></span>
            <span class="role-badge"><?= $user_role ?></span>
        </div>
        <?php if ($user_role === 'admin'): ?>
        <a href="admin/products.php" class="btn btn-ghost">⚙️ Products</a>
        <a href="janeth-dashboard.php" class="btn btn-ghost">📊 Dashboard</a>
        <?php else: ?>
        <a href="janeth-dashboard.php" class="btn btn-ghost">📊 Dashboard</a>
        <?php endif; ?>
        <button class="btn btn-danger" id="logoutBtn">Sign out</button>
    </div>
</div>

<!-- Modal -->
<div id="modalOverlay" class="modal-overlay">
    <div class="modal">
        <div class="modal-icon" id="modalIcon">💬</div>
        <p class="modal-msg" id="modalMsg">Are you sure?</p>
        <div class="modal-btns">
            <button id="modalOk"  class="btn btn-primary">OK</button>
            <button id="modalCancel" class="btn btn-ghost">Cancel</button>
        </div>
    </div>
</div>

<!-- Date Hero -->
<div class="date-hero">
    <div class="date-hero-left">
        <div class="hero-label">Selected Date</div>
        <div class="hero-big" id="heroDay">—</div>
        <div class="hero-sub" id="heroFull">Pick a date and click Load</div>
    </div>
    <div class="date-hero-right">
        <div id="asChip" class="as-chip"><span class="as-dot"></span><span id="asLabel">Auto-save off</span></div>
        <span id="statusChip" class="status-chip s-none">● No data loaded</span>
        <input type="date" id="recordDate">
        <button class="btn btn-teal" id="loadBtn">↻ Load</button>
    </div>
</div>

<!-- Controls -->
<div class="controls">
    <input type="text" id="searchInput" placeholder="🔍 Search product…" style="width:190px">
    <select id="catFilter">
        <option value="all">All Categories</option>
        <option value="Chicken">🐔 Chicken</option>
        <option value="Frozen">❄️ Frozen</option>
    </select>
    <div class="controls-sep"></div>
    <div class="toggle-sw">
        <span>⚡ Auto-save</span>
        <input type="checkbox" id="asToggle">
        <label for="asToggle"></label>
    </div>
    <button id="manualSaveBtn" class="btn btn-save">💾 Save</button>
    <button id="resetBtn" class="btn btn-ghost">⟳ Reset</button>
    <button id="printBtn" class="btn btn-ghost">📄 Print</button>
</div>

<!-- Net summary bar -->
<div class="summary-bar" id="summaryBar">
    <div class="sum-item"><div class="sum-label">Stock In Value</div><div class="sum-val teal" id="sumStockIn">₱0.00</div></div>
    <div class="sum-item"><div class="sum-label">Sold Value (est.)</div><div class="sum-val green" id="sumSold">₱0.00</div></div>
    <div class="sum-item"><div class="sum-label">Remaining Value</div><div class="sum-val amber" id="sumRemaining">₱0.00</div></div>
    <div class="sum-item"><div class="sum-label">Daily Expenses</div><div class="sum-val red" id="sumExpenses">₱0.00</div></div>
    <div class="sum-item"><div class="sum-label">Est. Net Income</div><div class="sum-val" id="sumNet">₱0.00</div></div>
</div>

<!-- Chicken section -->
<div class="section-wrap">
    <div class="section-hd">
        <span class="section-icon">🐔</span>
        <span class="section-title" style="color:var(--chicken)">Chicken Products</span>
        <span class="section-count" id="chickenCount">0 items</span>
    </div>
    <div class="tbl-scroll">
        <table>
            <thead><tr>
                <th>Product</th>
                <th>Price (₱)</th>
                <th>Yesterday</th>
                <th>Stock In</th>
                <th>Remaining</th>
                <th>Sold (calc.)</th>
                <th>Stock In Value (₱)</th>
            </tr></thead>
            <tbody id="chickenBody"></tbody>
        </table>
    </div>
</div>

<!-- Frozen section -->
<div class="section-wrap">
    <div class="section-hd">
        <span class="section-icon">❄️</span>
        <span class="section-title" style="color:var(--frozen)">Frozen Products</span>
        <span class="section-count" id="frozenCount">0 items</span>
    </div>
    <div class="tbl-scroll">
        <table>
            <thead><tr>
                <th>Product</th>
                <th>Price (₱)</th>
                <th>Yesterday</th>
                <th>Stock In</th>
                <th>Remaining</th>
                <th>Sold (calc.)</th>
                <th>Stock In Value (₱)</th>
            </tr></thead>
            <tbody id="frozenBody"></tbody>
        </table>
    </div>
</div>

<!-- Expenses section -->
<div class="exp-section">
    <div class="exp-hd">
        <span class="section-icon">💸</span>
        <span class="section-title" style="color:var(--expense)">Daily Expenses</span>
        <span class="section-count" id="expCount">0 items</span>
    </div>
    <div class="exp-add-row">
        <div class="exp-field">
            <label>Category</label>
            <select id="expCat" style="width:140px">
                <option>Utilities</option>
                <option>Transport</option>
                <option>Food & Snacks</option>
                <option>Supplies</option>
                <option>Labour</option>
                <option>Other</option>
            </select>
        </div>
        <div class="exp-field" style="flex:1;min-width:180px">
            <label>Description</label>
            <input type="text" id="expDesc" placeholder="e.g. Ice for the stall" style="width:100%">
        </div>
        <div class="exp-field">
            <label>Amount (₱)</label>
            <input type="number" id="expAmount" placeholder="0.00" step="0.01" min="0" style="width:110px">
        </div>
        <button class="btn btn-primary" id="addExpBtn" style="align-self:flex-end">+ Add</button>
    </div>
    <div class="exp-list" id="expList"><div class="exp-empty">No expenses recorded yet.</div></div>
    <div class="exp-total-bar">
        <span class="exp-total-label">Total Expenses</span>
        <span class="exp-total-val" id="expTotalVal">₱0.00</span>
    </div>
</div>

<div style="padding-bottom:2rem;text-align:center;font-size:.68rem;color:var(--text-faint);font-family:'DM Mono',monospace">
    ✦ Sold = Yesterday + Stock In − Remaining &nbsp;·&nbsp; Yesterday auto-fills from previous day's Remaining
</div>
</div>

<script>
const API  = window.location.origin + '/Janeth_Business/Janeth-Chicken-Business/backend/janeth.php';
const ROLE = '<?= $user_role ?>';

let masterProducts = [];
let masterRecords  = [];
let expenses       = [];
let navGrid        = [];
let asTimer        = null;
let asEnabled      = false;

// ─── Modal ───
function modal(msg, onOk, onCancel = null, icon = '💬', okLabel = 'OK', cancelLabel = 'Cancel') {
    document.getElementById('modalMsg').textContent  = msg;
    document.getElementById('modalIcon').textContent = icon;
    document.getElementById('modalOk').textContent   = okLabel;
    document.getElementById('modalCancel').textContent = cancelLabel;
    document.getElementById('modalOverlay').classList.add('active');
    const close = () => document.getElementById('modalOverlay').classList.remove('active');
    const ok  = document.getElementById('modalOk').cloneNode(true);
    const can = document.getElementById('modalCancel').cloneNode(true);
    document.getElementById('modalOk').replaceWith(ok);
    document.getElementById('modalCancel').replaceWith(can);
    ok.addEventListener('click',  () => { close(); onOk    && onOk(); });
    can.addEventListener('click', () => { close(); onCancel && onCancel(); });
}
function alert2(msg, isErr = false, autoClose = 2500) {
    modal(msg, null, null, isErr ? '⚠️' : '✅', 'OK', '');
    document.getElementById('modalCancel').style.display = 'none';
    setTimeout(() => document.getElementById('modalOverlay').classList.remove('active'), autoClose);
}

// ─── Date helpers ───
function prevDate(d) {
    const dt = new Date(d + 'T00:00:00');
    dt.setDate(dt.getDate() - 1);
    return dt.toISOString().split('T')[0];
}
function peso(n) { return '₱' + (parseFloat(n)||0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }

function setStatus(t) {
    const c = document.getElementById('statusChip');
    c.className = 'status-chip';
    if (t==='loaded') { c.classList.add('s-loaded'); c.textContent='● Data loaded'; }
    else if (t==='empty') { c.classList.add('s-empty');  c.textContent='● New entry'; }
    else { c.classList.add('s-none'); c.textContent='● No data loaded'; }
}
function updateHero(d) {
    if (!d) { document.getElementById('heroDay').innerHTML='—'; document.getElementById('heroFull').textContent='Pick a date and click Load'; return; }
    const [y,m,day] = d.split('-');
    const dt = new Date(+y,+m-1,+day);
    document.getElementById('heroDay').innerHTML = `<span class="day-num">${day}</span> ${dt.toLocaleDateString('en-US',{month:'long',year:'numeric'})}`;
    document.getElementById('heroFull').textContent = dt.toLocaleDateString('en-US',{weekday:'long'});
}

// ─── Auto-save ───
const asChip = document.getElementById('asChip');
const asLabel = document.getElementById('asLabel');
function setAsStatus(s) {
    asChip.className = 'as-chip' + (s?' '+s:'');
    asLabel.textContent = s==='saving'?'Saving…':s==='saved'?'Saved ✓':s==='error'?'Save failed':(asEnabled?'Auto-save on':'Auto-save off');
}
function scheduleAs() {
    if (!asEnabled) return;
    clearTimeout(asTimer);
    setAsStatus('saving');
    asTimer = setTimeout(doSave, 1400);
}

const asToggle = document.getElementById('asToggle');
asEnabled = localStorage.getItem('asEnabled') === 'true';
asToggle.checked = asEnabled;
setAsStatus(asEnabled ? '' : '');
asLabel.textContent = asEnabled ? 'Auto-save on' : 'Auto-save off';

asToggle.addEventListener('change', () => {
    asEnabled = asToggle.checked;
    localStorage.setItem('asEnabled', asEnabled);
    asLabel.textContent = asEnabled ? 'Auto-save on' : 'Auto-save off';
    setAsStatus('');
});

// ─── Save inventory ───
async function doSave() {
    const date = document.getElementById('recordDate').value;
    if (!date) { setAsStatus('error'); return; }
    const payload = { date, records: masterRecords.map(r => ({ product_id:r.productId, yesterday_qty:r.yesterday, stock_in:r.stockIn, remaining_qty:r.remaining })) };
    try {
        const res  = await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
        const data = await res.json();
        if (data.success) { setStatus('loaded'); setAsStatus('saved'); }
        else setAsStatus('error');
        setTimeout(() => setAsStatus(''), 2800);
    } catch { setAsStatus('error'); setTimeout(()=>setAsStatus(''),2800); }
}

// ─── Load records ───
async function loadDate(date, silent = false) {
    if (!date) return;
    updateHero(date);
    const prevMap = await fetchPrev(date);

    const res  = await fetch(`${API}?date=${date}`);
    const data = await res.json();

    if (data.records && data.records.length) {
        const saved = new Map(data.records.map(r => [+r.product_id, { yesterday:+r.yesterday_qty, stockIn:+r.stock_in, remaining:+r.remaining_qty, price:+r.price }]));
        masterRecords = masterProducts.map(p => ({
            ...p,
            yesterday: saved.get(p.productId)?.yesterday ?? 0,
            stockIn:   saved.get(p.productId)?.stockIn   ?? 0,
            remaining: saved.get(p.productId)?.remaining ?? 0,
            yesterdayFromPrev: false
        }));
        if (prevMap) applyPrev(prevMap);
        setStatus('loaded');
        if (!silent) alert2(`Loaded data for ${date}`);
    } else {
        masterRecords = masterProducts.map(p => ({ ...p, yesterday:0, stockIn:0, remaining:0, yesterdayFromPrev:false }));
        if (prevMap) applyPrev(prevMap);
        setStatus('empty');
        if (!silent) alert2(`No data for ${date}. Starting a new entry.`, false, 2000);
    }

    await loadExpenses(date);
    renderSections();
}

async function fetchPrev(date) {
    const prev = prevDate(date);
    try {
        const res  = await fetch(`${API}?date=${prev}`);
        const data = await res.json();
        if (data.records && data.records.length)
            return new Map(data.records.map(r => [+r.product_id, +r.remaining_qty]));
    } catch {}
    return null;
}
function applyPrev(map) {
    masterRecords.forEach(r => {
        const v = map.get(r.productId);
        if (v !== undefined) { r.yesterday = v; r.yesterdayFromPrev = true; }
    });
}

// ─── Products ───
async function fetchProducts() {
    try {
        const res  = await fetch(`${API}?products=1`);
        const data = await res.json();
        if (!data.products) throw new Error();
        masterProducts = data.products.map(p => ({ productId:+p.id, productName:p.name, category:p.category, price:+p.price }));
        const d = localStorage.getItem('janeth_date') || new Date().toISOString().split('T')[0];
        document.getElementById('recordDate').value = d;
        updateHero(d);
        await loadDate(d, true);
    } catch { alert2('Failed to load products.', true); }
}

// ─── Render ───
function calcSold(r) { return Math.max(0, r.yesterday + r.stockIn - r.remaining); }

function renderSections() {
    const term = document.getElementById('searchInput').value.toLowerCase();
    const cat  = document.getElementById('catFilter').value;
    const tagged = masterRecords.map((r,i) => ({...r, _gi:i}));
    const filtered = tagged.filter(r => r.productName.toLowerCase().includes(term) && (cat==='all'||r.category===cat));

    renderGroup('chickenBody','chickenCount', filtered.filter(r=>r.category==='Chicken'), 'chicken');
    renderGroup('frozenBody', 'frozenCount',  filtered.filter(r=>r.category==='Frozen'),  'frozen');
    rebuildNav();
    updateSummary();
}

function renderGroup(tbodyId, countId, items, section) {
    const tbody = document.getElementById(tbodyId);
    const countEl = document.getElementById(countId);
    tbody.innerHTML = '';
    if (!items.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="empty-section">No products found.</td></tr>`;
        countEl.textContent = '0 items';
        return;
    }
    items.forEach((rec, rowIdx) => tbody.appendChild(buildRow(rec, rec._gi, rowIdx)));
    tbody.appendChild(buildTotalRow(items, section));
    countEl.textContent = `${items.length} item${items.length!==1?'s':''}`;
}

function buildRow(rec, gi, rowIdx) {
    const tr = document.createElement('tr');

    // Product name
    const tdName = document.createElement('td');
    tdName.innerHTML = `<span class="prod-name">${esc(rec.productName)}</span>`;
    tr.appendChild(tdName);

    // Price – editable for staff and admin
    const tdPrice = document.createElement('td');
    const priceInp = document.createElement('input');
    priceInp.type = 'number'; priceInp.step = '0.01'; priceInp.min = '0';
    priceInp.value = rec.price.toFixed(2);
    priceInp.className = 'price-input';
    priceInp.title = 'Click to update price';
    priceInp.addEventListener('change', async e => {
        const newPrice = parseFloat(e.target.value);
        if (isNaN(newPrice) || newPrice < 0) { e.target.value = rec.price.toFixed(2); return; }
        masterRecords[gi].price = newPrice;
        masterProducts.find(p=>p.productId===rec.productId) && (masterProducts.find(p=>p.productId===rec.productId).price = newPrice);
        // Save to DB
        await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ update_price:1, product_id:rec.productId, price:newPrice }) });
        renderSections();
    });
    tdPrice.appendChild(priceInp);
    tr.appendChild(tdPrice);

    // Yesterday (read only)
    const tdYest = document.createElement('td');
    const yv = masterRecords[gi].yesterday;
    const fp = masterRecords[gi].yesterdayFromPrev;
    tdYest.innerHTML = `<span class="yest-cell${fp?'':' manual'}">${yv}</span>`;
    tr.appendChild(tdYest);

    // Stock In
    tr.appendChild(makeNumInput('stockIn', gi, rowIdx, 0, tr));
    // Remaining
    tr.appendChild(makeNumInput('remaining', gi, rowIdx, 1, tr));

    // Sold (calculated)
    const tdSold = document.createElement('td');
    const sold = calcSold(masterRecords[gi]);
    tdSold.innerHTML = `<span class="sold-cell ${sold>0?'sold-ok':''}" id="sold-${gi}">${sold}</span>`;
    tr.appendChild(tdSold);

    // Stock In Value
    const tdVal = document.createElement('td');
    const r = masterRecords[gi];
    tdVal.innerHTML = `<span class="total-cell-val" id="val-${gi}">${peso(r.stockIn * r.price)}</span>`;
    tr.appendChild(tdVal);

    return tr;
}

function makeNumInput(field, gi, rowIdx, colId, tr) {
    const td  = document.createElement('td');
    const inp = document.createElement('input');
    inp.type = 'number'; inp.step = '1'; inp.min = '0';
    inp.value = masterRecords[gi][field] !== 0 ? masterRecords[gi][field] : '';
    inp.placeholder = '0'; inp.className = 'num-input';
    inp.dataset.row = rowIdx; inp.dataset.col = colId;
    inp.addEventListener('input', e => {
        let v = e.target.value === '' ? 0 : parseInt(e.target.value,10);
        if (isNaN(v)||v<0) v=0;
        masterRecords[gi][field] = v;
        const r = masterRecords[gi];
        // Update sold
        const soldEl = document.getElementById(`sold-${gi}`);
        if (soldEl) soldEl.textContent = calcSold(r);
        // Update stock in value
        const valEl = document.getElementById(`val-${gi}`);
        if (valEl) valEl.textContent = peso(r.stockIn * r.price);
        updateSummary();
        scheduleAs();
    });
    td.appendChild(inp); return td;
}

function buildTotalRow(items, section) {
    const totalStockIn = items.reduce((s,r)=>s + masterRecords[r._gi].stockIn * masterRecords[r._gi].price, 0);
    const tr = document.createElement('tr');
    tr.className = 'total-row';
    tr.innerHTML = `<td colspan="6" class="total-label">Total ${section==='chicken'?'🐔 Chicken':'❄️ Frozen'} Stock In Value</td>
                    <td>${peso(totalStockIn)}</td>`;
    return tr;
}

function rebuildNav() {
    navGrid = Array.from(document.querySelectorAll('.num-input')).map(i => ({
        el:i, row:+i.dataset.row, col:+i.dataset.col, tbody:i.closest('tbody')
    }));
}

document.addEventListener('keydown', e => {
    if (e.key!=='ArrowDown'&&e.key!=='ArrowUp') return;
    const a = document.activeElement;
    if (!a||!a.classList.contains('num-input')) return;
    e.preventDefault();
    const next = navGrid.find(n=>n.col===+a.dataset.col&&n.row===+a.dataset.row+(e.key==='ArrowDown'?1:-1)&&n.tbody===a.closest('tbody'));
    if (next) { next.el.focus(); next.el.select(); }
});

function updateSummary() {
    let stockIn=0, sold=0, remaining=0;
    masterRecords.forEach(r => {
        stockIn   += r.stockIn   * r.price;
        sold      += calcSold(r) * r.price;
        remaining += r.remaining * r.price;
    });
    const expTotal = expenses.reduce((s,e)=>s+parseFloat(e.amount||0),0);
    const net      = sold - expTotal;
    document.getElementById('sumStockIn').textContent  = peso(stockIn);
    document.getElementById('sumSold').textContent     = peso(sold);
    document.getElementById('sumRemaining').textContent= peso(remaining);
    document.getElementById('sumExpenses').textContent = peso(expTotal);
    const netEl = document.getElementById('sumNet');
    netEl.textContent = peso(net);
    netEl.className   = 'sum-val ' + (net>=0?'green':'red');
}

// ─── Expenses ───
async function loadExpenses(date) {
    try {
        const res  = await fetch(`${API}?expenses=${date}`);
        const data = await res.json();
        expenses = data.expenses || [];
    } catch { expenses = []; }
    renderExpenses();
}

function renderExpenses() {
    const list = document.getElementById('expList');
    const count = document.getElementById('expCount');
    const total = expenses.reduce((s,e)=>s+parseFloat(e.amount||0),0);
    document.getElementById('expTotalVal').textContent = peso(total);
    count.textContent = `${expenses.length} item${expenses.length!==1?'s':''}`;
    updateSummary();
    if (!expenses.length) { list.innerHTML='<div class="exp-empty">No expenses recorded yet.</div>'; return; }
    list.innerHTML = expenses.map(e => `
        <div class="exp-item">
            <span class="exp-cat-badge">${esc(e.category)}</span>
            <span class="exp-desc">${esc(e.description)}</span>
            <span class="exp-amount">${peso(e.amount)}</span>
            <button class="exp-del" onclick="delExpense(${e.id})" title="Delete">✕</button>
        </div>`).join('');
}

document.getElementById('addExpBtn').addEventListener('click', async () => {
    const date   = document.getElementById('recordDate').value;
    const cat    = document.getElementById('expCat').value;
    const desc   = document.getElementById('expDesc').value.trim();
    const amount = parseFloat(document.getElementById('expAmount').value);
    if (!date)        return alert2('Please load a date first.', true);
    if (!desc)        return alert2('Please enter a description.', true);
    if (!amount||amount<=0) return alert2('Please enter a valid amount.', true);

    const res  = await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ save_expense:1, expense_date:date, category:cat, description:desc, amount }) });
    const data = await res.json();
    if (data.success) {
        document.getElementById('expDesc').value   = '';
        document.getElementById('expAmount').value = '';
        expenses.unshift({ id:data.id, category:cat, description:desc, amount });
        renderExpenses();
    } else alert2('Failed to save expense.', true);
});

async function delExpense(id) {
    modal('Delete this expense?', async () => {
        const res  = await fetch(API, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ delete_expense:id }) });
        const data = await res.json();
        if (data.success) { expenses = expenses.filter(e=>e.id!==id); renderExpenses(); }
        else alert2('Failed to delete expense.', true);
    }, null, '🗑️', 'Delete', 'Cancel');
}

// ─── Button events ───
document.getElementById('recordDate').addEventListener('change', async () => {
    const d = document.getElementById('recordDate').value;
    localStorage.setItem('janeth_date', d);
    await loadDate(d, true);
});
document.getElementById('loadBtn').addEventListener('click', async () => {
    const d = document.getElementById('recordDate').value;
    if (!d) return alert2('Please select a date.', true);
    await loadDate(d, false);
});
document.getElementById('manualSaveBtn').addEventListener('click', async () => {
    const date = document.getElementById('recordDate').value;
    if (!date) return alert2('Please select a date first.', true);
    await doSave();
    alert2('Data saved successfully!');
});
document.getElementById('resetBtn').addEventListener('click', () => {
    modal('Clear all entries? Unsaved changes will be lost.', async () => {
        const d = document.getElementById('recordDate').value;
        const prev = await fetchPrev(d);
        masterRecords = masterProducts.map(p=>({...p,yesterday:0,stockIn:0,remaining:0,yesterdayFromPrev:false}));
        if (prev) applyPrev(prev);
        setStatus('empty'); renderSections(); alert2('Form reset.');
    }, null, '⚠️', 'Reset', 'Cancel');
});
document.getElementById('printBtn').addEventListener('click', () => {
    if (!document.getElementById('recordDate').value) return alert2('Please select a date first.', true);
    window.print();
});
document.getElementById('logoutBtn').addEventListener('click', () => {
    modal('Sign out?', () => { window.location.href='../backend/logout.php'; }, null, '👋', 'Yes, sign out', 'Stay');
});
document.getElementById('searchInput').addEventListener('input', renderSections);
document.getElementById('catFilter').addEventListener('change', renderSections);

function esc(s) { return String(s).replace(/[&<>]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[m])); }

fetchProducts();
</script>
</body>
</html>