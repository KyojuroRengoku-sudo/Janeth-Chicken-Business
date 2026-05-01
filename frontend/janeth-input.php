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
        /* [Your existing CSS – unchanged] */
        :root {
            --bg:#0a0e17;--surface:#111827;--surface-2:#1a2234;--surface-3:#222d42;
            --border:rgba(255,255,255,0.07);--border-hi:rgba(255,255,255,0.12);
            --accent:#f5a623;--accent-dim:rgba(245,166,35,.12);--accent-glow:rgba(245,166,35,.25);
            --teal:#29b6c8;--teal-dim:rgba(41,182,200,.1);--teal-glow:rgba(41,182,200,.3);
            --text:#e8edf5;--text-muted:#6b7a93;--text-faint:#3d4d63;
            --danger:#f87171;--success:#34d399;--purple:#a78bfa;
            --chicken:#fbbf24;--frozen:#60a5fa;--expense:#a78bfa;
            --radius:14px;--radius-sm:9px;
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Sora',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding:1.5rem;
             background-image:radial-gradient(ellipse 70% 50% at 10% -10%,rgba(41,182,200,.06) 0%,transparent 60%),
                              radial-gradient(ellipse 60% 40% at 90% 110%,rgba(245,166,35,.04) 0%,transparent 60%);}
        .container{max-width:1520px;margin:0 auto;}
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
        .btn-purple{background:rgba(167,139,250,.12);border:1px solid rgba(167,139,250,.2);color:var(--purple);}
        .btn-purple:hover{background:rgba(167,139,250,.2);}
        .date-hero{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
                   padding:1.5rem 2rem;margin-bottom:1.1rem;display:flex;align-items:center;gap:2rem;flex-wrap:wrap;}
        .date-hero-left{flex:1;min-width:200px;}
        .hero-label{font-size:.67rem;text-transform:uppercase;letter-spacing:.12em;color:var(--text-muted);font-weight:600;margin-bottom:.4rem;}
        .hero-big{font-size:clamp(1.8rem,4vw,3rem);font-weight:700;letter-spacing:-.03em;line-height:1;}
        .hero-big .day-num{color:var(--accent);}
        .hero-sub{font-size:.75rem;color:var(--text-muted);margin-top:.3rem;font-family:'DM Mono',monospace;}
        .date-hero-right{display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;}
        .status-chip{display:inline-flex;align-items:center;gap:.4rem;padding:.28rem .8rem;border-radius:50px;
                     font-size:.68rem;font-weight:700;letter-spacing:.05em;}
        .s-loaded{background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.25);color:var(--success);}
        .s-empty {background:var(--accent-dim);border:1px solid rgba(245,166,35,.25);color:var(--accent);}
        .s-none  {background:var(--surface-2);border:1px solid var(--border);color:var(--text-muted);}
        .as-chip{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .7rem;border-radius:50px;
                 font-size:.67rem;font-weight:600;letter-spacing:.04em;transition:.3s;
                 background:var(--surface-2);border:1px solid var(--border);color:var(--text-faint);}
        .as-chip.saving{border-color:rgba(41,182,200,.3);color:var(--teal);background:var(--teal-dim);}
        .as-chip.saved {border-color:rgba(52,211,153,.25);color:var(--success);background:rgba(52,211,153,.08);}
        .as-chip.error {border-color:rgba(248,113,113,.25);color:var(--danger);background:rgba(248,113,113,.08);}
        .as-dot{width:6px;height:6px;border-radius:50%;background:currentColor;}
        .as-chip.saving .as-dot{animation:blink .8s infinite;}
        @keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
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
        .toggle-sw{display:inline-flex;align-items:center;gap:.45rem;background:var(--surface-2);
                   border-radius:50px;padding:.22rem .7rem .22rem .8rem;border:1px solid var(--border);}
        .toggle-sw label{font-size:.68rem;font-weight:600;color:var(--text-muted);cursor:pointer;}
        .toggle-sw input{width:30px;height:15px;appearance:none;background:var(--surface-3);border-radius:30px;
                         position:relative;cursor:pointer;transition:.2s;}
        .toggle-sw input:checked{background:var(--teal);}
        .toggle-sw input::before{content:'';width:11px;height:11px;background:#fff;border-radius:50%;
                                 position:absolute;top:2px;left:2px;transition:.2s;}
        .toggle-sw input:checked::before{left:17px;}
        .section-wrap{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
                      overflow:hidden;margin-bottom:1rem;}
        .section-hd{display:flex;align-items:center;gap:.75rem;padding:.9rem 1.2rem;
                    border-bottom:1px solid var(--border);background:var(--surface-2);}
        .section-icon{font-size:1.2rem;}
        .section-title{font-size:.88rem;font-weight:700;}
        .section-count{font-family:'DM Mono',monospace;font-size:.7rem;color:var(--text-faint);margin-left:auto;}
        .tbl-scroll{overflow-x:auto;}
        table{width:100%;border-collapse:collapse;min-width:820px;}
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
        .prod-id{font-family:'DM Mono',monospace;font-size:.65rem;color:var(--text-faint);
                 background:var(--surface-3);padding:.1rem .4rem;border-radius:4px;margin-left:.4rem;}
        .num-input{width:88px;background:var(--surface-3);border:1px solid var(--border);border-radius:var(--radius-sm);
                   color:var(--text);font-family:'DM Mono',monospace;font-size:.82rem;
                   padding:.36rem .6rem;text-align:center;outline:none;transition:.15s;}
        .num-input:focus{border-color:var(--teal);background:rgba(41,182,200,.05);box-shadow:0 0 0 3px var(--teal-dim);}
        .price-ro{font-family:'DM Mono',monospace;font-size:.8rem;color:var(--accent);}
        .yest-cell{font-family:'DM Mono',monospace;font-size:.82rem;font-weight:700;color:var(--teal);}
        .sold-cell{font-family:'DM Mono',monospace;font-size:.82rem;color:var(--success);}
        .total-cell-val{font-family:'DM Mono',monospace;font-size:.8rem;color:var(--accent);}
        .sold-warn{color:var(--danger);}
        .sold-ok  {color:var(--success);}
        .total-row td{background:rgba(245,166,35,.06)!important;border-top:1px solid rgba(245,166,35,.18)!important;
                      color:var(--accent)!important;font-family:'DM Mono',monospace;font-size:.83rem;font-weight:700;}
        .total-row .total-label{text-align:right!important;color:var(--text-muted)!important;
                                font-family:'Sora',sans-serif!important;font-size:.7rem!important;
                                font-weight:600!important;text-transform:uppercase;letter-spacing:.07em;padding-right:1.5rem!important;}
        .empty-section{padding:2.5rem;text-align:center;color:var(--text-faint);font-size:.83rem;}
        .chooser-panel{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
                       overflow:hidden;margin-bottom:1rem;display:none;}
        .chooser-panel.open{display:block;}
        .chooser-hd{display:flex;align-items:center;gap:.75rem;padding:.9rem 1.2rem;
                    border-bottom:1px solid var(--border);background:var(--surface-2);}
        .chooser-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:.75rem;padding:1.2rem;}
        .chooser-item{display:flex;align-items:center;gap:.75rem;background:var(--surface-2);border:1px solid var(--border);
                      border-radius:var(--radius-sm);padding:.65rem .9rem;cursor:pointer;transition:.15s;}
        .chooser-item:hover{border-color:var(--teal);background:var(--teal-dim);}
        .chooser-item.active{border-color:var(--success);background:rgba(52,211,153,.07);}
        .chooser-cb{width:16px;height:16px;accent-color:var(--teal);cursor:pointer;flex-shrink:0;}
        .chooser-name{font-size:.78rem;font-weight:600;flex:1;}
        .chooser-cat{font-size:.63rem;padding:.1rem .45rem;border-radius:50px;font-weight:700;}
        .cc-chicken{background:rgba(251,191,36,.15);color:var(--chicken);}
        .cc-frozen {background:rgba(96,165,250,.12); color:var(--frozen);}
        .se-section{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
                    overflow:hidden;margin-bottom:1rem;}
        .se-hd{display:flex;align-items:center;gap:.75rem;padding:.9rem 1.2rem;
               border-bottom:1px solid var(--border);background:var(--surface-2);}
        .se-add-row{display:flex;flex-wrap:wrap;gap:.65rem;align-items:flex-end;padding:1rem 1.2rem;
                    border-bottom:1px solid var(--border);}
        .se-field{display:flex;flex-direction:column;gap:.3rem;}
        .se-field label{font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);}
        .se-list{padding:0;}
        .se-item{display:flex;align-items:center;gap:.75rem;padding:.7rem 1.2rem;border-bottom:1px solid var(--border);
                 font-size:.8rem;flex-wrap:wrap;transition:background .12s;}
        .se-item:last-child{border-bottom:none;}
        .se-item:hover{background:var(--surface-2);}
        .se-supplier-badge{padding:.2rem .65rem;border-radius:50px;font-size:.65rem;font-weight:700;
                           background:rgba(41,182,200,.12);color:var(--teal);border:1px solid rgba(41,182,200,.2);white-space:nowrap;}
        .se-product{font-weight:600;flex:1;min-width:120px;}
        .se-detail{font-family:'DM Mono',monospace;font-size:.76rem;color:var(--text-muted);}
        .se-cost{font-family:'DM Mono',monospace;font-weight:700;color:var(--danger);white-space:nowrap;}
        .se-del{background:none;border:none;cursor:pointer;color:var(--text-faint);font-size:1rem;
                padding:.2rem .4rem;border-radius:6px;transition:.15s;}
        .se-del:hover{color:var(--danger);background:rgba(248,113,113,.1);}
        .se-total-bar{display:flex;justify-content:space-between;align-items:center;padding:.85rem 1.2rem;
                      background:rgba(248,113,113,.05);border-top:1px solid rgba(248,113,113,.15);}
        .se-empty{padding:1.5rem;text-align:center;color:var(--text-faint);font-size:.82rem;}
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
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(8px);
                       display:none;justify-content:center;align-items:center;z-index:1000;padding:1rem;}
        .modal-overlay.active{display:flex;}
        .modal{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
               padding:2rem 2.25rem;max-width:420px;width:100%;text-align:center;
               box-shadow:0 25px 60px rgba(0,0,0,.5);animation:popIn .2s cubic-bezier(.34,1.56,.64,1);}
        @keyframes popIn{from{opacity:0;transform:scale(.9)}to{opacity:1;transform:none}}
        .modal-icon{font-size:2rem;margin-bottom:.75rem;}
        .modal-msg{font-size:.9rem;color:var(--text);margin-bottom:1.5rem;line-height:1.5;font-weight:500;}
        .modal-btns{display:flex;gap:.75rem;justify-content:center;}
        @media print{
            .header,.controls,.date-hero-right,.modal-overlay,.exp-add-row,.exp-del,.se-add-row,.se-del,.chooser-panel{display:none!important;}
            body{background:#fff!important;color:#111!important;padding:.5rem;}
            .section-hd{background:#f5f5f5!important;}
            th{background:#efefef!important;color:#555!important;}
            td{color:#111!important;}
            .num-input{border:none!important;background:transparent!important;width:auto!important;}
        }
        ::-webkit-scrollbar{width:6px;height:6px;}
        ::-webkit-scrollbar-track{background:transparent;}
        ::-webkit-scrollbar-thumb{background:var(--surface-3);border-radius:3px;}
        @media(max-width:640px){body{padding:1rem;}.date-hero{flex-direction:column;gap:1rem;}.controls{flex-direction:column;align-items:stretch;}}
    </style>
</head>
<body>
<div class="container">

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
        <a href="products.php" class="btn btn-ghost">⚙️ Products</a>
        <?php endif; ?>
        <a href="janeth-dashboard.php" class="btn btn-ghost">📊 Dashboard</a>
        <button class="btn btn-purple" id="chooserBtn">☰ Choose Products</button>
        <button class="btn btn-danger" id="logoutBtn">Sign out</button>
    </div>
</div>

<div id="modalOverlay" class="modal-overlay">
    <div class="modal">
        <div class="modal-icon" id="modalIcon">💬</div>
        <p class="modal-msg" id="modalMsg">Are you sure?</p>
        <div class="modal-btns">
            <button id="modalOk"  class="btn btn-primary">OK</button>
            <button id="modalCancel" class="btn btn-ghost" style="display:none">Cancel</button>
        </div>
    </div>
</div>

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

<div class="controls">
    <input type="text" id="searchInput" placeholder="🔍 Search product…" style="width:175px">
    <select id="catFilter">
        <option value="">All Categories</option>
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

<div class="chooser-panel" id="chooserPanel">
    <div class="chooser-hd">
        <span class="section-icon">☰</span>
        <span class="section-title">Choose Which Products to Show</span>
        <button class="btn btn-save" id="saveChooserBtn" style="margin-left:auto">💾 Save Choices</button>
    </div>
    <div class="chooser-grid" id="chooserGrid"></div>
</div>

<div class="summary-bar" id="summaryBar">
    <div class="sum-item"><div class="sum-label">Stock In Value</div><div class="sum-val teal" id="sumStockIn">₱0.00</div></div>
    <div class="sum-item"><div class="sum-label">Supplier Cost</div><div class="sum-val red" id="sumSupplierCost">₱0.00</div></div>
    <div class="sum-item"><div class="sum-label">Sold Value (est.)</div><div class="sum-val green" id="sumSold">₱0.00</div></div>
    <div class="sum-item"><div class="sum-label">Remaining Value</div><div class="sum-val amber" id="sumRemaining">₱0.00</div></div>
    <div class="sum-item"><div class="sum-label">Daily Expenses</div><div class="sum-val red" id="sumExpenses">₱0.00</div></div>
    <div class="sum-item"><div class="sum-label">Est. Net Income</div><div class="sum-val" id="sumNet">₱0.00</div></div>
</div>

<div class="section-wrap" id="chickenSection">
    <div class="section-hd">
        <span class="section-icon">🐔</span>
        <span class="section-title" style="color:var(--chicken)">Chicken Products</span>
        <span class="section-count" id="chickenCount">0 items</span>
    </div>
    <div class="tbl-scroll">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Selling Price (₱)</th>
                    <th>Yesterday</th>
                    <th>Stock In</th>
                    <th>Remaining</th>
                    <th>Sold (calc.)</th>
                    <th>Sales Value (₱)</th>
                </tr>
            </thead>
            <tbody id="chickenBody"></tbody>
        </table>
    </div>
</div>

<div class="section-wrap" id="frozenSection">
    <div class="section-hd">
        <span class="section-icon">❄️</span>
        <span class="section-title" style="color:var(--frozen)">Frozen Products</span>
        <span class="section-count" id="frozenCount">0 items</span>
    </div>
    <div class="tbl-scroll">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Selling Price (₱)</th>
                    <th>Yesterday</th>
                    <th>Stock In</th>
                    <th>Remaining</th>
                    <th>Sold (calc.)</th>
                    <th>Sales Value (₱)</th>
                </tr>
            </thead>
            <tbody id="frozenBody"></tbody>
        </table>
    </div>
</div>

<div class="se-section">
    <div class="se-hd">
        <span class="section-icon">🚚</span>
        <span class="section-title" style="color:var(--teal)">Supplier Stock Received</span>
        <span class="section-count" id="seCount">0 entries</span>
    </div>
    <div class="se-add-row">
        <div class="se-field">
            <label>Product</label>
            <select id="seProduct" style="width:180px"><option value="">Select…</option></select>
        </div>
        <div class="se-field">
            <label>Supplier</label>
            <select id="seSupplier" style="width:160px"><option value="">Select…</option></select>
        </div>
        <div class="se-field">
            <label>Qty</label>
            <input type="number" id="seQty" placeholder="0" min="1" style="width:80px">
        </div>
        <div class="se-field">
            <label>Cost Price (₱)</label>
            <input type="number" id="seCost" placeholder="0.00" step="0.01" min="0" style="width:110px">
        </div>
        <div class="se-field" style="flex:1;min-width:130px">
            <label>Notes</label>
            <input type="text" id="seNotes" placeholder="Optional" style="width:100%">
        </div>
        <button class="btn btn-teal" id="addSeBtn" style="align-self:flex-end">+ Add</button>
    </div>
    <div class="se-list" id="seList"><div class="se-empty">No stock entries recorded yet.</div></div>
    <div class="se-total-bar">
        <span class="exp-total-label">Total Supplier Cost</span>
        <span class="exp-total-val" id="seTotalVal">₱0.00</span>
    </div>
</div>

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
                <option>Utilities</option><option>Transport</option>
                <option>Food & Snacks</option><option>Supplies</option>
                <option>Labour</option><option>Other</option>
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
const API  = 'janeth.php';
const ROLE = '<?= $user_role ?>';

let masterProducts = [];
let allProducts    = [];
let masterRecords  = [];
let expenses       = [];
let stockEntries   = [];
let suppliers      = [];
let navGrid        = [];
let asTimer        = null;
let asEnabled      = false;

function modal(msg, onOk, onCancel=null, icon='💬', okLabel='OK', cancelLabel='Cancel') {
    document.getElementById('modalMsg').textContent  = msg;
    document.getElementById('modalIcon').textContent = icon;
    document.getElementById('modalOk').textContent   = okLabel;
    const cancelBtn = document.getElementById('modalCancel');
    cancelBtn.textContent = cancelLabel;
    cancelBtn.style.display = cancelLabel ? '' : 'none';
    document.getElementById('modalOverlay').classList.add('active');
    const close = () => document.getElementById('modalOverlay').classList.remove('active');
    const ok  = document.getElementById('modalOk').cloneNode(true);
    const can = document.getElementById('modalCancel').cloneNode(true);
    document.getElementById('modalOk').replaceWith(ok);
    document.getElementById('modalCancel').replaceWith(can);
    ok.addEventListener('click',  () => { close(); onOk    && onOk(); });
    can.addEventListener('click', () => { close(); onCancel && onCancel(); });
}
function alert2(msg, isErr=false, autoClose=2500) {
    modal(msg, null, null, isErr?'⚠️':'✅', 'OK', '');
    setTimeout(() => document.getElementById('modalOverlay').classList.remove('active'), autoClose);
}

function prevDate(d) {
    const dt = new Date(d+'T00:00:00'); dt.setDate(dt.getDate()-1);
    return dt.toISOString().split('T')[0];
}
function peso(n) { return '₱'+(parseFloat(n)||0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2}); }
function setStatus(t) {
    const c = document.getElementById('statusChip');
    c.className='status-chip';
    if (t==='loaded'){c.classList.add('s-loaded');c.textContent='● Data loaded';}
    else if(t==='empty'){c.classList.add('s-empty');c.textContent='● New entry';}
    else{c.classList.add('s-none');c.textContent='● No data loaded';}
}
function setHero(d) {
    if (!d){document.getElementById('heroDay').innerHTML='—';document.getElementById('heroFull').textContent='Pick a date and click Load';return;}
    const dt=new Date(d+'T00:00:00');
    const days=['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const months=['January','February','March','April','May','June','July','August','September','October','November','December'];
    document.getElementById('heroDay').innerHTML=`${days[dt.getDay()]}, <span class="day-num">${dt.getDate()}</span>`;
    document.getElementById('heroFull').textContent=`${months[dt.getMonth()]} ${dt.getFullYear()}`;
}

function calcSold(r) { return Math.max(0,(r.yesterday+r.stockIn)-r.remaining); }

document.getElementById('asToggle').addEventListener('change',e=>{
    asEnabled=e.target.checked;
    const chip=document.getElementById('asChip'), lbl=document.getElementById('asLabel');
    chip.className='as-chip';
    lbl.textContent=asEnabled?'Auto-save on':'Auto-save off';
});
function triggerAutoSave() {
    if (!asEnabled) return;
    clearTimeout(asTimer);
    asTimer=setTimeout(doSave,1800);
    const chip=document.getElementById('asChip'), lbl=document.getElementById('asLabel');
    chip.className='as-chip saving'; lbl.textContent='Saving…';
}
async function doSave() {
    const date=document.getElementById('recordDate').value;
    if (!date||!masterRecords.length) return;
    const chip=document.getElementById('asChip'), lbl=document.getElementById('asLabel');
    chip.className='as-chip saving'; lbl.textContent='Saving…';
    try {
        const res=await fetch(API,{method:'POST',headers:{'Content-Type':'application/json'},
            body:JSON.stringify({date, records: masterRecords.map(r=>({
                product_id:r.id,yesterday_qty:r.yesterday,stock_in:r.stockIn,remaining_qty:r.remaining
            }))})});
        const data=await res.json();
        if(data.success){chip.className='as-chip saved';lbl.textContent='Saved ✓';setTimeout(()=>{chip.className='as-chip';lbl.textContent='Auto-save on';},2500);}
        else{chip.className='as-chip error';lbl.textContent='Save failed';}
    } catch{chip.className='as-chip error';lbl.textContent='Save failed';}
}

async function fetchAllProducts() {
    const [visProd, allProd, sups] = await Promise.all([
        fetch(`${API}?products=1&page=input`).then(r=>r.json()),
        fetch(`${API}?products=1`).then(r=>r.json()),
        fetch(`${API}?suppliers=1`).then(r=>r.json())
    ]);
    masterProducts = visProd.products || [];
    allProducts    = allProd.products  || [];
    suppliers      = sups.suppliers    || [];
    buildChooser();
    buildSeDropdowns();
    masterRecords = masterProducts.map(p=>({...p,price:parseFloat(p.price)||0,yesterday:0,stockIn:0,remaining:0,yesterdayFromPrev:false}));
    renderSections();
    const saved = localStorage.getItem('janeth_date');
    if (saved) { document.getElementById('recordDate').value=saved; await loadDate(saved,true); }
}

function buildChooser() {
    const grid = document.getElementById('chooserGrid');
    grid.innerHTML = allProducts.map(p=>{
        const checked = p.visible_input==1;
        const catCls  = p.category==='Chicken'?'cc-chicken':'cc-frozen';
        return `<div class="chooser-item${checked?' active':''}" data-id="${p.id}">
            <input type="checkbox" class="chooser-cb" id="ch_${p.id}" ${checked?'checked':''}>
            <span class="chooser-name">${esc(p.name)}</span>
            <span class="chooser-cat ${catCls}">${p.category}</span>
        </div>`;
    }).join('');
    grid.querySelectorAll('.chooser-item').forEach(item=>{
        const cb = item.querySelector('input');
        cb.addEventListener('change',()=>item.classList.toggle('active',cb.checked));
    });
}
document.getElementById('chooserBtn').addEventListener('click',()=>{
    document.getElementById('chooserPanel').classList.toggle('open');
});
document.getElementById('saveChooserBtn').addEventListener('click', async()=>{
    const items = document.querySelectorAll('.chooser-item');
    const promises = [];
    items.forEach(item=>{
        const id      = item.dataset.id;
        const checked = item.querySelector('input').checked ? 1 : 0;
        promises.push(fetch(API,{method:'POST',headers:{'Content-Type':'application/json'},
            body:JSON.stringify({update_visibility:1,product_id:id,visible_input:checked,visible_dashboard:checked})}));
    });
    await Promise.all(promises);
    alert2('Product visibility saved!');
    document.getElementById('chooserPanel').classList.remove('open');
    await fetchAllProducts();
});

function buildSeDropdowns() {
    const pSel = document.getElementById('seProduct');
    const sSel = document.getElementById('seSupplier');
    pSel.innerHTML = '<option value="">Select product…</option>' +
        allProducts.filter(p=>p.visible_input==1).map(p=>`<option value="${p.id}">${esc(p.name)}</option>`).join('');
    sSel.innerHTML = '<option value="">Select supplier…</option>' +
        suppliers.map(s=>`<option value="${s.id}">${esc(s.name)}</option>`).join('');
}

async function loadDate(d, silent=false) {
    if (!d) return;
    setHero(d);
    try {
        const [res, expRes, seRes] = await Promise.all([
            fetch(`${API}?date=${d}&for=input`),
            fetch(`${API}?expenses=${d}`),
            fetch(`${API}?stock_entries=${d}`)
        ]);
        const data   = await res.json();
        const expData = await expRes.json();
        const seData  = await seRes.json();

        expenses     = expData.expenses     || [];
        stockEntries = seData.stock_entries || [];

        if (data.records && data.records.length) {
            const hasData = data.records.some(r=>+r.sold>0||+r.stock_in>0||+r.remaining_qty>0);
            if (hasData) {
                masterRecords = data.records.map(r=>({
                    ...r, id:r.product_id, name:r.product_name, category:r.product_category,
                    price:parseFloat(r.price)||0,
                    yesterday:parseInt(r.yesterday_qty)||0,
                    stockIn:parseInt(r.stock_in)||0,
                    remaining:parseInt(r.remaining_qty)||0,
                    yesterdayFromPrev:false
                }));
                setStatus('loaded');
            } else {
                const prev = await fetchPrev(d);
                masterRecords = masterProducts.map(p=>({
                    ...p, price:parseFloat(p.price)||0,
                    yesterday:0, stockIn:0, remaining:0, yesterdayFromPrev:false
                }));
                if (prev) applyPrev(prev);
                setStatus('empty');
            }
        } else {
            masterRecords = masterProducts.map(p=>({...p,price:parseFloat(p.price)||0,yesterday:0,stockIn:0,remaining:0,yesterdayFromPrev:false}));
            setStatus('empty');
        }
        renderSections();
        renderExpenses();
        renderStockEntries();
        updateSummary();
        if (!silent) alert2(`Loaded data for ${d}`);
    } catch(e) { alert2('Failed to load data.', true); }
}

async function fetchPrev(d) {
    try {
        const res = await fetch(`${API}?date=${prevDate(d)}&for=input`);
        const data = await res.json();
        return data.records?.some(r=>+r.remaining_qty>0) ? data.records : null;
    } catch { return null; }
}
function applyPrev(prev) {
    masterRecords.forEach(r=>{
        const p = prev.find(x=>x.product_id===r.id||x.product_id==r.id);
        if (p && +p.remaining_qty>0) { r.yesterday=+p.remaining_qty; r.yesterdayFromPrev=true; }
    });
}

function renderSections() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const cat    = document.getElementById('catFilter').value;
    const chicken = masterRecords.filter(r=>r.category==='Chicken'&&r.name.toLowerCase().includes(search)&&(!cat||cat==='Chicken'));
    const frozen  = masterRecords.filter(r=>r.category==='Frozen' &&r.name.toLowerCase().includes(search)&&(!cat||cat==='Frozen'));
    document.getElementById('chickenCount').textContent = `${chicken.length} item${chicken.length!==1?'s':''}`;
    document.getElementById('frozenCount').textContent  = `${frozen.length} item${frozen.length!==1?'s':''}`;
    renderBody('chickenBody', chicken, 0);
    renderBody('frozenBody',  frozen,  chicken.length);
    rebuildNav();
    updateSummary();
}

let _rowIdx = 0;
function renderBody(bodyId, recs, startRow) {
    const tbody = document.getElementById(bodyId);
    if (!recs.length) { tbody.innerHTML=`<tr><td colspan="7" class="empty-section">No products to show.</td></tr>`; return; }
    let totalStockIn=0, totalSold=0, totalVal=0;
    const rows = recs.map((r,i)=>{
        const row   = startRow+i;
        const sold  = calcSold(r);
        const val   = sold * r.price;
        totalStockIn += r.stockIn * r.price;
        totalSold    += sold;
        totalVal     += val;
        const soldCls = sold<0?'sold-warn':'sold-ok';
        return `<tr>
            <td><span class="prod-name">${esc(r.name)}</span><span class="prod-id">#${r.id}</span></td>
            <td><span class="price-ro">₱${(r.price||0).toFixed(2)}</span></td>
            <td class="yest-cell${r.yesterdayFromPrev?' manual':''}">${r.yesterday}</td>
            <td><input class="num-input" type="number" min="0" value="${r.stockIn}" data-pid="${r.id}" data-field="stockIn" data-row="${row}" data-col="1"></td>
            <td><input class="num-input" type="number" min="0" value="${r.remaining}" data-pid="${r.id}" data-field="remaining" data-row="${row}" data-col="2"></td>
            <td class="sold-cell ${soldCls}">${sold}</td>
            <td class="total-cell-val">${peso(val)}</td>
        </tr>`;
    });
    rows.push(`<tr class="total-row"><td colspan="5" class="total-label">Totals</td>
        <td>${totalSold}</td><td>${peso(totalVal)}</td></tr>`);
    tbody.innerHTML = rows.join('');
    tbody.querySelectorAll('.num-input').forEach(inp=>{
        inp.addEventListener('input',()=>{
            const pid=inp.dataset.pid, field=inp.dataset.field, val=Math.max(0,parseInt(inp.value)||0);
            const r=masterRecords.find(x=>x.id==pid||x.product_id==pid);
            if(r){r[field]=val;renderSections();rebuildNav();triggerAutoSave();}
        });
    });
}

function rebuildNav() {
    navGrid = Array.from(document.querySelectorAll('.num-input')).map(i=>({el:i,row:+i.dataset.row,col:+i.dataset.col,tbody:i.closest('tbody')}));
}
document.addEventListener('keydown',e=>{
    if(e.key!=='ArrowDown'&&e.key!=='ArrowUp')return;
    const a=document.activeElement;
    if(!a||!a.classList.contains('num-input'))return;
    e.preventDefault();
    const next=navGrid.find(n=>n.col===+a.dataset.col&&n.row===+a.dataset.row+(e.key==='ArrowDown'?1:-1)&&n.tbody===a.closest('tbody'));
    if(next){next.el.focus();next.el.select();}
});

function updateSummary() {
    let stockIn=0, sold=0, remaining=0;
    masterRecords.forEach(r=>{stockIn+=r.stockIn*r.price;sold+=calcSold(r)*r.price;remaining+=r.remaining*r.price;});
    const expTotal = expenses.reduce((s,e)=>s+parseFloat(e.amount||0),0);
    const seTotal  = stockEntries.reduce((s,e)=>s+parseFloat(e.total_cost||0),0);
    const net      = sold - expTotal - seTotal;
    document.getElementById('sumStockIn').textContent      = peso(stockIn);
    document.getElementById('sumSupplierCost').textContent = peso(seTotal);
    document.getElementById('sumSold').textContent         = peso(sold);
    document.getElementById('sumRemaining').textContent    = peso(remaining);
    document.getElementById('sumExpenses').textContent     = peso(expTotal);
    const netEl = document.getElementById('sumNet');
    netEl.textContent = peso(net);
    netEl.className   = 'sum-val '+(net>=0?'green':'red');
}

async function loadStockEntries(date) {
    try { const r=await fetch(`${API}?stock_entries=${date}`); const d=await r.json(); stockEntries=d.stock_entries||[]; }
    catch{ stockEntries=[]; }
    renderStockEntries();
}
function renderStockEntries() {
    const list=document.getElementById('seList');
    const total=stockEntries.reduce((s,e)=>s+parseFloat(e.total_cost||0),0);
    document.getElementById('seTotalVal').textContent=peso(total);
    document.getElementById('seCount').textContent=`${stockEntries.length} entr${stockEntries.length!==1?'ies':'y'}`;
    updateSummary();
    if(!stockEntries.length){list.innerHTML='<div class="se-empty">No stock entries recorded yet.</div>';return;}
    list.innerHTML=stockEntries.map(e=>`
        <div class="se-item">
            <span class="se-supplier-badge">${esc(e.supplier_name)}</span>
            <span class="se-product">${esc(e.product_name)}</span>
            <span class="se-detail">×${e.qty} @ ₱${parseFloat(e.cost_price).toFixed(2)}</span>
            <span class="se-cost">−${peso(e.total_cost)}</span>
            <button class="se-del" onclick="delStockEntry(${e.id})" title="Delete">✕</button>
        </div>`).join('');
}
document.getElementById('addSeBtn').addEventListener('click',async()=>{
    const date=document.getElementById('recordDate').value;
    const pid =document.getElementById('seProduct').value;
    const sid =document.getElementById('seSupplier').value;
    const qty =parseInt(document.getElementById('seQty').value)||0;
    const cost=parseFloat(document.getElementById('seCost').value)||0;
    const notes=document.getElementById('seNotes').value;
    if(!date) return alert2('Please load a date first.',true);
    if(!pid)  return alert2('Select a product.',true);
    if(!sid)  return alert2('Select a supplier.',true);
    if(qty<=0) return alert2('Qty must be greater than 0.',true);
    if(cost<0) return alert2('Cost price cannot be negative.',true);
    const res=await fetch(API,{method:'POST',headers:{'Content-Type':'application/json'},
        body:JSON.stringify({save_stock_entry:1,entry_date:date,product_id:pid,supplier_id:sid,qty,cost_price:cost,notes})});
    const data=await res.json();
    if(data.success){
        document.getElementById('seQty').value='';
        document.getElementById('seCost').value='';
        document.getElementById('seNotes').value='';
        await loadStockEntries(date);
        alert2('Stock entry saved!');
    } else alert2('Failed to save stock entry.',true);
});
async function delStockEntry(id){
    modal('Delete this stock entry?',async()=>{
        const res=await fetch(API,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({delete_stock_entry:id})});
        const data=await res.json();
        if(data.success){stockEntries=stockEntries.filter(e=>e.id!=id);renderStockEntries();}
        else alert2('Failed to delete.',true);
    },null,'🗑️','Delete','Cancel');
}

async function loadExpenses(date) {
    try{const r=await fetch(`${API}?expenses=${date}`);const d=await r.json();expenses=d.expenses||[];}
    catch{expenses=[];}
    renderExpenses();
}
function renderExpenses() {
    const list=document.getElementById('expList');
    const total=expenses.reduce((s,e)=>s+parseFloat(e.amount||0),0);
    document.getElementById('expTotalVal').textContent=peso(total);
    document.getElementById('expCount').textContent=`${expenses.length} item${expenses.length!==1?'s':''}`;
    updateSummary();
    if(!expenses.length){list.innerHTML='<div class="exp-empty">No expenses recorded yet.</div>';return;}
    list.innerHTML=expenses.map(e=>`
        <div class="exp-item">
            <span class="exp-cat-badge">${esc(e.category)}</span>
            <span class="exp-desc">${esc(e.description)}</span>
            <span class="exp-amount">${peso(e.amount)}</span>
            <button class="exp-del" onclick="delExpense(${e.id})" title="Delete">✕</button>
        </div>`).join('');
}
document.getElementById('addExpBtn').addEventListener('click',async()=>{
    const date=document.getElementById('recordDate').value;
    const cat=document.getElementById('expCat').value;
    const desc=document.getElementById('expDesc').value.trim();
    const amount=parseFloat(document.getElementById('expAmount').value);
    if(!date)          return alert2('Please load a date first.',true);
    if(!desc)          return alert2('Please enter a description.',true);
    if(!amount||amount<=0) return alert2('Please enter a valid amount.',true);
    const res=await fetch(API,{method:'POST',headers:{'Content-Type':'application/json'},
        body:JSON.stringify({save_expense:1,expense_date:date,category:cat,description:desc,amount})});
    const data=await res.json();
    if(data.success){
        document.getElementById('expDesc').value='';
        document.getElementById('expAmount').value='';
        expenses.unshift({id:data.id,category:cat,description:desc,amount});
        renderExpenses();
    } else alert2('Failed to save expense.',true);
});
async function delExpense(id){
    modal('Delete this expense?',async()=>{
        const res=await fetch(API,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({delete_expense:id})});
        const data=await res.json();
        if(data.success){expenses=expenses.filter(e=>e.id!==id);renderExpenses();}
        else alert2('Failed to delete.',true);
    },null,'🗑️','Delete','Cancel');
}

document.getElementById('recordDate').addEventListener('change',async()=>{
    const d=document.getElementById('recordDate').value;
    localStorage.setItem('janeth_date',d);
    await loadDate(d,true);
});
document.getElementById('loadBtn').addEventListener('click',async()=>{
    const d=document.getElementById('recordDate').value;
    if(!d) return alert2('Please select a date.',true);
    await loadDate(d,false);
});
document.getElementById('manualSaveBtn').addEventListener('click',async()=>{
    const date=document.getElementById('recordDate').value;
    if(!date) return alert2('Please select a date first.',true);
    await doSave();
    alert2('Data saved successfully!');
});
document.getElementById('resetBtn').addEventListener('click',()=>{
    modal('Clear all entries? Unsaved changes will be lost.',async()=>{
        const d=document.getElementById('recordDate').value;
        const prev=await fetchPrev(d);
        masterRecords=masterProducts.map(p=>({...p,price:parseFloat(p.price)||0,yesterday:0,stockIn:0,remaining:0,yesterdayFromPrev:false}));
        if(prev)applyPrev(prev);
        setStatus('empty');renderSections();alert2('Form reset.');
    },null,'⚠️','Reset','Cancel');
});
document.getElementById('printBtn').addEventListener('click',()=>{
    if(!document.getElementById('recordDate').value) return alert2('Please select a date first.',true);
    window.print();
});
document.getElementById('logoutBtn').addEventListener('click',()=>{
    modal('Are you sure you want to sign out?\nAny unsaved changes will be lost.',
        ()=>{ window.location.href='logout.php'; },
        null,'👋','Yes, sign out','Stay');
});
document.getElementById('searchInput').addEventListener('input',renderSections);
document.getElementById('catFilter').addEventListener('change',renderSections);

function esc(s){return String(s).replace(/[&<>]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[m]));}

fetchAllProducts();
</script>
</body>
</html>