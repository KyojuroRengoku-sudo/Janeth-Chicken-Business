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
    <title>Dashboard · Janeth's Business</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <style>
        :root {
            --bg:#0a0e17;--surface:#111827;--surface-2:#1a2234;--surface-3:#222d42;
            --border:rgba(255,255,255,0.07);
            --accent:#f5a623;--accent-dim:rgba(245,166,35,.12);--accent-glow:rgba(245,166,35,.25);
            --teal:#29b6c8;--teal-dim:rgba(41,182,200,.1);
            --text:#e8edf5;--text-muted:#6b7a93;--text-faint:#3d4d63;
            --danger:#f87171;--success:#34d399;--warning:#fbbf24;--purple:#a78bfa;
            --chicken:#fbbf24;--frozen:#60a5fa;--expense:#a78bfa;
            --radius:14px;--radius-sm:9px;
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Sora',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;padding:1.5rem;
             background-image:radial-gradient(ellipse 70% 50% at 10% -10%,rgba(41,182,200,.06) 0%,transparent 60%),
                              radial-gradient(ellipse 60% 40% at 90% 110%,rgba(245,166,35,.04) 0%,transparent 60%);}
        .dash{max-width:1440px;margin:0 auto;}
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
        .btn-ghost{background:var(--surface);border:1px solid var(--border);color:var(--text-muted);}
        .btn-ghost:hover{border-color:var(--teal);color:var(--teal);background:var(--teal-dim);}
        .btn-teal{background:var(--teal-dim);border:1px solid rgba(41,182,200,.2);color:var(--teal);}
        .btn-teal:hover{background:rgba(41,182,200,.18);}
        .btn-danger{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);color:var(--danger);}
        .btn-danger:hover{background:rgba(248,113,113,.2);}
        .btn-success{background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.2);color:var(--success);}
        .btn-success:hover{background:rgba(52,211,153,.2);}
        .btn-purple{background:rgba(167,139,250,.12);border:1px solid rgba(167,139,250,.2);color:var(--purple);}
        .btn-purple:hover{background:rgba(167,139,250,.2);}

        /* Controls */
        .controls{display:flex;flex-wrap:wrap;gap:.65rem;align-items:center;justify-content:space-between;
                  background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
                  padding:.85rem 1.2rem;margin-bottom:1.1rem;}
        .ctrl-group{display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;}
        .ctrl-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);}
        input[type="text"],input[type="date"],select{
            background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-sm);
            color:var(--text);font-family:'Sora',sans-serif;font-size:.78rem;
            padding:.42rem .85rem;outline:none;transition:.18s;}
        input:focus,select:focus{border-color:var(--teal);box-shadow:0 0 0 3px var(--teal-dim);}
        select option{background:#1a2234;}

        /* KPI grid */
        .kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem;margin-bottom:1.1rem;}
        .kpi{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
             padding:1.1rem 1.25rem;display:flex;justify-content:space-between;align-items:flex-start;transition:.2s;}
        .kpi:hover{border-color:var(--teal);transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2);}
        .kpi-info{min-width:0;flex:1;}
        .kpi-label{font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:var(--text-faint);margin-bottom:.4rem;}
        .kpi-val{font-size:clamp(1rem,2vw,1.65rem);font-weight:700;line-height:1.2;font-family:'DM Mono',monospace;word-break:break-word;}
        .kpi-sub{font-size:.73rem;font-weight:500;color:var(--text-muted);margin-top:.2rem;line-height:1.3;}
        .kpi-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
        .ic-teal{background:var(--teal-dim);}
        .ic-amber{background:var(--accent-dim);}
        .ic-green{background:rgba(52,211,153,.1);}
        .ic-red{background:rgba(248,113,113,.1);}
        .ic-star{background:rgba(251,191,36,.1);}
        .ic-skull{background:rgba(248,113,113,.1);}
        .kpi-val.teal{color:var(--teal);}.kpi-val.amber{color:var(--accent);}
        .kpi-val.green{color:var(--success);}.kpi-val.red{color:var(--danger);}

        .lowstock-bar{display:none;align-items:center;gap:.6rem;background:rgba(251,191,36,.07);
                      border:1px solid rgba(251,191,36,.2);border-left:3px solid var(--warning);
                      border-radius:var(--radius-sm);padding:.75rem 1rem;margin-bottom:1rem;
                      font-size:.78rem;color:var(--warning);}
        .lowstock-bar.show{display:flex;}

        /* Charts grid */
        .charts-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.1rem;}
        @media(max-width:900px){.charts-grid{grid-template-columns:1fr;}}
        .chart-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:1.1rem;}
        .chart-hd{display:flex;justify-content:space-between;align-items:center;margin-bottom:.9rem;flex-wrap:wrap;gap:.5rem;}
        .chart-title{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:var(--text-faint);}
        .chart-tabs{display:flex;gap:.35rem;}
        .ctab{padding:.27rem .75rem;border-radius:50px;font-size:.7rem;font-weight:700;cursor:pointer;
              border:1px solid var(--border);background:var(--surface-2);color:var(--text-muted);
              font-family:'Sora',sans-serif;transition:.15s;letter-spacing:.04em;}
        .ctab.ac{background:rgba(251,191,36,.15);color:var(--chicken);border-color:rgba(251,191,36,.3);}
        .ctab.af{background:rgba(96,165,250,.12);color:var(--frozen);border-color:rgba(96,165,250,.25);}
        .ctab.aw{background:rgba(41,182,200,.12);color:var(--teal);border-color:rgba(41,182,200,.25);}
        .ctab.am{background:rgba(167,139,250,.12);color:var(--purple);border-color:rgba(167,139,250,.25);}
        .chart-container{position:relative;height:220px;width:100%;}
        .chart-container canvas{max-height:220px;}

        /* Product table */
        .table-wrap{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:1.1rem;}
        .table-hd-bar{display:flex;justify-content:space-between;align-items:center;padding:.85rem 1.2rem;
                      border-bottom:1px solid var(--border);background:var(--surface-2);flex-wrap:wrap;gap:.65rem;}
        .table-title{font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;}
        .rec-count{font-family:'DM Mono',monospace;font-size:.7rem;color:var(--text-faint);}
        .cat-tabs{display:flex;border-bottom:1px solid var(--border);}
        .ctab2{padding:.65rem 1.4rem;font-size:.76rem;font-weight:700;cursor:pointer;border:none;
               font-family:'Sora',sans-serif;background:transparent;color:var(--text-muted);transition:.15s;
               border-bottom:2px solid transparent;margin-bottom:-1px;letter-spacing:.04em;}
        .ctab2:hover{color:var(--text);}
        .ctab2.ac2-ch{color:var(--chicken);border-bottom-color:var(--chicken);}
        .ctab2.ac2-fr{color:var(--frozen);border-bottom-color:var(--frozen);}
        .tbl-scroll{overflow-x:auto;}
        table{width:100%;border-collapse:collapse;min-width:720px;}
        thead tr{border-bottom:1px solid var(--border);}
        th{padding:.65rem 1rem;text-align:right;font-size:.64rem;font-weight:700;text-transform:uppercase;
           letter-spacing:.09em;color:var(--text-faint);background:var(--surface-2);white-space:nowrap;cursor:pointer;user-select:none;transition:.15s;}
        th:hover{color:var(--teal);}
        th:first-child,th:nth-child(2){text-align:left;}
        tbody tr{border-bottom:1px solid var(--border);transition:background .12s;}
        tbody tr:last-child{border-bottom:none;}
        tbody tr:hover{background:var(--surface-2);}
        td{padding:.62rem 1rem;font-size:.8rem;color:var(--text);text-align:right;vertical-align:middle;}
        td:first-child,td:nth-child(2){text-align:left;}
        .mono{font-family:'DM Mono',monospace;font-size:.79rem;font-weight:600;}
        .c-sold{color:var(--success);}.c-rem{color:var(--accent);}.c-qs{color:var(--teal);}
        .c-rg{color:var(--success);}.c-ry{color:var(--warning);}.c-rr{color:var(--danger);}.c-rz{color:var(--text-faint);}
        .prod-id-badge{font-family:'DM Mono',monospace;font-size:.62rem;color:var(--text-faint);
                       background:var(--surface-3);padding:.1rem .4rem;border-radius:4px;}
        .rank-badge{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;
                    border-radius:50%;font-size:.65rem;font-weight:700;margin-right:.4rem;}
        .rank-1{background:rgba(251,191,36,.2);color:var(--warning);}
        .rank-2{background:rgba(148,163,184,.15);color:#94a3b8;}
        .rank-3{background:rgba(180,130,90,.15);color:#b4825a;}
        .rank-worst{background:rgba(248,113,113,.12);color:var(--danger);}
        .totals-bar{display:grid;grid-template-columns:1fr 1fr 1fr;gap:0;border-top:1px solid var(--border);}
        .total-cell{padding:.9rem 1.2rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.4rem;}
        .total-cell:not(:last-child){border-right:1px solid var(--border);}
        .total-label{font-size:.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-faint);}
        .total-amount{font-family:'DM Mono',monospace;font-size:1.05rem;font-weight:700;}
        .ta-sold{color:var(--success);}.ta-rem{color:var(--accent);}.ta-exp{color:var(--danger);}
        .pagination{display:flex;justify-content:center;gap:.4rem;padding:.8rem;border-top:1px solid var(--border);flex-wrap:wrap;}
        .pg-btn{min-width:30px;height:30px;display:flex;align-items:center;justify-content:center;padding:0 .6rem;
                border-radius:var(--radius-sm);background:var(--surface-2);border:1px solid var(--border);
                color:var(--text-muted);font-size:.76rem;font-weight:600;cursor:pointer;transition:.15s;font-family:'Sora',sans-serif;}
        .pg-btn:hover{border-color:var(--teal);color:var(--teal);}
        .pg-btn.active{background:var(--accent);color:#0a0e17;border-color:var(--accent);}
        .state-row td{padding:3rem;text-align:center;color:var(--text-faint);font-size:.83rem;}
        .footer{text-align:center;font-size:.68rem;color:var(--text-faint);font-family:'DM Mono',monospace;margin-top:.5rem;padding-bottom:1rem;}

        /* Analytics range controls */
        .analytics-bar{display:flex;flex-wrap:wrap;gap:.65rem;align-items:center;background:var(--surface);
                       border:1px solid var(--border);border-radius:var(--radius);padding:.8rem 1.2rem;margin-bottom:1.1rem;}
        .a-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);}

        ::-webkit-scrollbar{width:6px;height:6px;}
        ::-webkit-scrollbar-track{background:transparent;}
        ::-webkit-scrollbar-thumb{background:var(--surface-3);border-radius:3px;}
        @keyframes popIn{from{opacity:0;transform:scale(.9)}to{opacity:1;transform:none}}
        #modalOverlay.active{display:flex!important;}
        @media(max-width:640px){body{padding:1rem;}.controls{flex-direction:column;}.totals-bar{grid-template-columns:1fr;}.total-cell:not(:last-child){border-right:none;border-bottom:1px solid var(--border);}.charts-grid{grid-template-columns:1fr;}}
    </style>
</head>
<body>
<div class="dash">

<!-- Header -->
<div class="header">
    <div class="logo">
        <div class="logo-icon">📊</div>
        <div class="logo-text">
            <span class="logo-title">Janeth's Business</span>
            <span class="logo-sub">Analytics Dashboard</span>
        </div>
    </div>
    <div class="header-right">
        <div class="user-chip">
            <div class="user-avatar"><?= strtoupper(substr($username,0,1)) ?></div>
            <span class="user-name"><?= htmlspecialchars($username) ?></span>
            <span class="role-badge"><?= $user_role ?></span>
        </div>
        <?php if ($user_role==='admin'): ?>
        <a href="products.php" class="btn btn-ghost">⚙️ Products</a>
        <a href="users.php" class="btn btn-ghost">👥 Users</a>
        <?php endif; ?>
        <a href="janeth-input.php" class="btn btn-ghost">✏️ Entry</a>
        <a href="liquidation.php" class="btn btn-purple">💵 Liquidation</a>
        <button class="btn btn-danger" id="logoutBtn">Sign out</button>
    </div>
</div>

<!-- Daily view controls -->
<div class="controls">
    <div class="ctrl-group">
        <span class="ctrl-label">Date</span>
        <select id="dateSelect"></select>
        <button class="btn btn-teal" id="loadBtn">↻ Load</button>
    </div>
    <div class="ctrl-group">
        <input type="text" id="searchInput" placeholder="🔍 Search product…" style="width:175px">
    </div>
    <div class="ctrl-group">
        <button class="btn btn-ghost" id="exportPdfBtn">📄 Export PDF</button>
        <button class="btn btn-ghost" id="exportCsvBtn">📎 Export CSV</button>
    </div>
</div>

<!-- Analytics range -->
<div class="analytics-bar">
    <span class="a-label">Analytics Range</span>
    <input type="date" id="fromDate">
    <span style="color:var(--text-muted);font-size:.8rem">to</span>
    <input type="date" id="toDate">
    <button class="btn btn-teal" id="loadAnalyticsBtn">↻ Load Analytics</button>
    <button class="btn btn-ghost" id="thisWeekBtn">This Week</button>
    <button class="btn btn-ghost" id="thisMonthBtn">This Month</button>
    <button class="btn btn-ghost" id="lastMonthBtn">Last Month</button>
</div>

<!-- Low stock -->
<div id="lowStockBar" class="lowstock-bar">⚠️ <span id="lowStockMsg"></span></div>

<!-- KPI cards -->
<div class="kpi-grid">
    <div class="kpi"><div class="kpi-info"><div class="kpi-label">Daily Sales</div><div class="kpi-val teal" id="kpiSales">₱0.00</div></div><div class="kpi-icon ic-teal">💰</div></div>
    <div class="kpi"><div class="kpi-info"><div class="kpi-label">Remaining Stock Value</div><div class="kpi-val amber" id="kpiRem">₱0.00</div></div><div class="kpi-icon ic-amber">🏷️</div></div>
    <div class="kpi"><div class="kpi-info"><div class="kpi-label">Daily Expenses</div><div class="kpi-val red" id="kpiExp">₱0.00</div></div><div class="kpi-icon ic-red">💸</div></div>
    <div class="kpi"><div class="kpi-info"><div class="kpi-label">Est. Net Income</div><div class="kpi-val" id="kpiNet">₱0.00</div></div><div class="kpi-icon ic-green">📈</div></div>
    <div class="kpi"><div class="kpi-info"><div class="kpi-label">🏆 Best Seller</div><div class="kpi-sub" id="kpiBest">—</div></div><div class="kpi-icon ic-star">⭐</div></div>
    <div class="kpi"><div class="kpi-info"><div class="kpi-label">📉 Least Sold</div><div class="kpi-sub" id="kpiWorst">—</div></div><div class="kpi-icon ic-skull">⚠️</div></div>
    <div class="kpi"><div class="kpi-info"><div class="kpi-label">Weekly Sales</div><div class="kpi-val teal" id="kpiWeekly">₱0.00</div><div class="kpi-sub" id="kpiWeeklySub">Last 7 days in range</div></div><div class="kpi-icon ic-teal">📅</div></div>
    <div class="kpi"><div class="kpi-info"><div class="kpi-label">Monthly Sales</div><div class="kpi-val amber" id="kpiMonthly">₱0.00</div><div class="kpi-sub" id="kpiMonthlySub">This month in range</div></div><div class="kpi-icon ic-amber">📆</div></div>
</div>

<!-- Charts -->
<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-hd">
            <div class="chart-title">Daily Sales (₱) by Product</div>
            <div class="chart-tabs">
                <button class="ctab ac" id="ctC" onclick="switchChart('Chicken')">🐔</button>
                <button class="ctab"    id="ctF" onclick="switchChart('Frozen')">❄️</button>
            </div>
        </div>
        <div class="chart-container"><canvas id="salesChart"></canvas></div>
    </div>
    <div class="chart-card">
        <div class="chart-hd">
            <div class="chart-title">Qty Sold vs Remaining</div>
            <div class="chart-tabs">
                <button class="ctab ac" id="ct2C" onclick="switchChart2('Chicken')">🐔</button>
                <button class="ctab"    id="ct2F" onclick="switchChart2('Frozen')">❄️</button>
            </div>
        </div>
        <div class="chart-container"><canvas id="qtyChart"></canvas></div>
    </div>
    <div class="chart-card">
        <div class="chart-hd">
            <div class="chart-title">Weekly Sales Trend</div>
        </div>
        <div class="chart-container"><canvas id="weeklyChart"></canvas></div>
    </div>
    <div class="chart-card">
        <div class="chart-hd">
            <div class="chart-title">Monthly Sales Trend</div>
        </div>
        <div class="chart-container"><canvas id="monthlyChart"></canvas></div>
    </div>
</div>

<!-- Product table -->
<div class="table-wrap">
    <div class="table-hd-bar">
        <span class="table-title">Product Records</span>
        <span class="rec-count" id="recCount">— items</span>
    </div>
    <div class="cat-tabs">
        <button class="ctab2 ac2-ch" id="tab2C" onclick="switchTab('Chicken')">🐔 Chicken</button>
        <button class="ctab2"        id="tab2F" onclick="switchTab('Frozen')">❄️ Frozen</button>
    </div>
    <div class="tbl-scroll">
        <table>
            <thead><tr>
                <th onclick="sortBy('rank')" style="text-align:left">Rank</th>
                <th onclick="sortBy('product_name')" style="text-align:left">Product</th>
                <th onclick="sortBy('price')">Price (₱)</th>
                <th onclick="sortBy('sold')">Qty Sold</th>
                <th onclick="sortBy('sold_peso')">Sales (₱)</th>
                <th onclick="sortBy('remaining_qty')">Remaining</th>
                <th onclick="sortBy('rem_peso')">Rem. Value (₱)</th>
            </tr></thead>
            <tbody id="dashBody"><tr class="state-row"><td colspan="7">Select a date and click Load</td></tr></tbody>
        </table>
    </div>
    <div class="totals-bar">
        <div class="total-cell"><span class="total-label">Total Sales</span><span class="total-amount ta-sold" id="ttSold">₱0.00</span></div>
        <div class="total-cell"><span class="total-label">Total Remaining</span><span class="total-amount ta-rem" id="ttRem">₱0.00</span></div>
        <div class="total-cell"><span class="total-label">Total Expenses</span><span class="total-amount ta-exp" id="ttExp">₱0.00</span></div>
    </div>
    <div id="pgCtrl" class="pagination"></div>
</div>

<div class="footer">Last updated: <span id="lastUpdated">—</span> &nbsp;·&nbsp; ⚠️ Low ≤ threshold &nbsp;·&nbsp; 🏆 = Highest ₱ sold</div>
</div>

<script>
const API  = 'janeth.php';
const ROLE = '<?= $user_role ?>';
let fullRecords = [];
let expenses    = [];
let analyticsData = null;
let curTab      = 'Chicken';
let curChartTab = 'Chicken';
let curChart2Tab= 'Chicken';
let curPage     = 1;
const PER_PAGE  = 15;
let salesChart=null, qtyChart=null, weeklyChart=null, monthlyChart=null;
let sortCol='sold_peso', sortDir=-1;

const peso = n => '₱'+(parseFloat(n)||0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2});
const num  = n => { const v=parseFloat(n)||0; return v%1===0?v.toString():v.toFixed(2); };
const esc  = s => String(s).replace(/[&<>]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[m]));

// ── Modal ──
function modal(msg, onOk, onCancel=null, icon='💬', okLabel='OK', cancelLabel='Cancel') {
    const ov=document.getElementById('modalOverlay');
    if(!ov){ if(confirm(msg)) onOk && onOk(); return; }
    document.getElementById('modalMsg').textContent=msg;
    document.getElementById('modalIcon').textContent=icon;
    document.getElementById('modalOk').textContent=okLabel;
    document.getElementById('modalCancel').textContent=cancelLabel;
    ov.classList.add('active');
    const close=()=>ov.classList.remove('active');
    const ok=document.getElementById('modalOk').cloneNode(true);
    const can=document.getElementById('modalCancel').cloneNode(true);
    document.getElementById('modalOk').replaceWith(ok);
    document.getElementById('modalCancel').replaceWith(can);
    ok.addEventListener('click',()=>{close();onOk&&onOk();});
    can.addEventListener('click',()=>{close();onCancel&&onCancel();});
}

function showError(msg) {
    document.getElementById('dashBody').innerHTML=`<tr class="state-row"><td colspan="7">⚠️ ${msg}</td></tr>`;
    ['kpiSales','kpiRem','kpiExp','kpiNet'].forEach(id=>document.getElementById(id).textContent='₱0.00');
    ['kpiBest','kpiWorst'].forEach(id=>document.getElementById(id).textContent='—');
    ['ttSold','ttRem','ttExp'].forEach(id=>document.getElementById(id).textContent='₱0.00');
}

function getRankCls(i,n){if(i===0)return 'rank-1';if(i===1)return 'rank-2';if(i===2)return 'rank-3';if(i===n-1)return 'rank-worst';return '';}
function getRankLabel(i,n){if(i===0)return '🥇';if(i===1)return '🥈';if(i===2)return '🥉';if(i===n-1)return '📉';return `#${i+1}`;}
function getQtyCls(rem,threshold){if(rem===0)return 'c-rz';if(rem<=Math.max(3,Math.floor(threshold*0.3)))return 'c-rr';if(rem<=threshold)return 'c-ry';return 'c-rg';}

function enrichRecords() {
    return fullRecords.map(r=>({...r,sold:+r.sold,remaining_qty:+r.remaining_qty,price:+r.price,
        sold_peso:(+r.sold)*(+r.price),rem_peso:(+r.remaining_qty)*(+r.price),rank:0}));
}

function renderAll() {
    const term    = document.getElementById('searchInput').value.toLowerCase();
    const visible = enrichRecords().filter(r=>r.product_name.toLowerCase().includes(term));
    let totalSold=0, totalRem=0;
    visible.forEach(r=>{totalSold+=r.sold_peso;totalRem+=r.rem_peso;});
    const expTotal = expenses.reduce((s,e)=>s+parseFloat(e.amount||0),0);
    const net = totalSold - expTotal;
    document.getElementById('kpiSales').textContent = peso(totalSold);
    document.getElementById('kpiRem').textContent   = peso(totalRem);
    document.getElementById('kpiExp').textContent   = peso(expTotal);
    const netEl = document.getElementById('kpiNet');
    netEl.textContent = peso(net);
    netEl.className   = 'kpi-val '+(net>=0?'green':'red');
    const ranked = [...visible].sort((a,b)=>b.sold_peso-a.sold_peso);
    if (ranked.length) {
        const best=ranked[0], worst=ranked[ranked.length-1];
        document.getElementById('kpiBest').innerHTML  = `<b style="font-size:1.1rem;font-family:'DM Mono',monospace;color:var(--success)">${peso(best.sold_peso)}</b><br><span style="color:var(--text-muted);font-size:.74rem">${esc(best.product_name)}</span>`;
        document.getElementById('kpiWorst').innerHTML = `<b style="font-size:1.1rem;font-family:'DM Mono',monospace;color:var(--danger)">${peso(worst.sold_peso)}</b><br><span style="color:var(--text-muted);font-size:.74rem">${esc(worst.product_name)}</span>`;
    }
    const low = visible.filter(r=>r.remaining_qty>0&&r.remaining_qty<=(r.low_stock_threshold||10));
    const lsBar = document.getElementById('lowStockBar');
    if (low.length){document.getElementById('lowStockMsg').textContent=`Stock alert: ${low.map(r=>`${r.product_name} (${r.remaining_qty} left)`).join(', ')}`;lsBar.classList.add('show');}
    else lsBar.classList.remove('show');
    renderChart(curChartTab); renderChart2(curChart2Tab); renderTable(curTab);
}

function getTabRecords(tab){
    const term=document.getElementById('searchInput').value.toLowerCase();
    return enrichRecords().filter(r=>r.product_category===tab&&r.product_name.toLowerCase().includes(term));
}

function renderChart(tab){
    const recs=getTabRecords(tab).filter(r=>r.sold>0).sort((a,b)=>b.sold_peso-a.sold_peso);
    const ctx=document.getElementById('salesChart').getContext('2d');
    if(salesChart)salesChart.destroy();
    if(!recs.length){ctx.clearRect(0,0,ctx.canvas.width,ctx.canvas.height);ctx.font='13px Sora,sans-serif';ctx.fillStyle='#3d4d63';ctx.textAlign='center';ctx.fillText(`No ${tab} sales`,ctx.canvas.width/2,90);return;}
    const isCh=tab==='Chicken';
    salesChart=new Chart(ctx,{type:'bar',
        data:{labels:recs.map(r=>r.product_name.length>16?r.product_name.slice(0,14)+'…':r.product_name),
              datasets:[{label:'Sales (₱)',data:recs.map(r=>r.sold_peso.toFixed(2)),
                         backgroundColor:isCh?'rgba(251,191,36,.7)':'rgba(96,165,250,.7)',
                         borderColor:isCh?'rgba(251,191,36,1)':'rgba(96,165,250,1)',
                         borderWidth:1,borderRadius:6,barPercentage:.65}]},
        options:{responsive:true,maintainAspectRatio:true,
            plugins:{legend:{labels:{color:'#6b7a93',font:{family:'Sora',size:11}}},
                     tooltip:{callbacks:{label:c=>' ₱'+parseFloat(c.parsed.y).toLocaleString('en-PH',{minimumFractionDigits:2})}}},
            scales:{x:{ticks:{color:'#6b7a93',font:{family:'Sora',size:10}},grid:{color:'rgba(255,255,255,.04)'}},
                    y:{ticks:{color:'#6b7a93',font:{family:'DM Mono',size:10},callback:v=>'₱'+v.toLocaleString('en-PH')},grid:{color:'rgba(255,255,255,.04)'},beginAtZero:true}}}});
}

function renderChart2(tab){
    const recs=getTabRecords(tab).sort((a,b)=>b.sold-a.sold);
    const ctx=document.getElementById('qtyChart').getContext('2d');
    if(qtyChart)qtyChart.destroy();
    if(!recs.length){ctx.clearRect(0,0,ctx.canvas.width,ctx.canvas.height);ctx.font='13px Sora,sans-serif';ctx.fillStyle='#3d4d63';ctx.textAlign='center';ctx.fillText(`No ${tab} data`,ctx.canvas.width/2,90);return;}
    qtyChart=new Chart(ctx,{type:'bar',
        data:{labels:recs.map(r=>r.product_name.length>14?r.product_name.slice(0,12)+'…':r.product_name),
              datasets:[{label:'Qty Sold',data:recs.map(r=>r.sold),backgroundColor:'rgba(52,211,153,.7)',borderColor:'rgba(52,211,153,1)',borderWidth:1,borderRadius:4},
                        {label:'Remaining',data:recs.map(r=>r.remaining_qty),backgroundColor:'rgba(245,166,35,.45)',borderColor:'rgba(245,166,35,.8)',borderWidth:1,borderRadius:4}]},
        options:{responsive:true,maintainAspectRatio:true,
            plugins:{legend:{labels:{color:'#6b7a93',font:{family:'Sora',size:11}}}},
            scales:{x:{ticks:{color:'#6b7a93',font:{family:'Sora',size:10}},grid:{color:'rgba(255,255,255,.04)'}},
                    y:{ticks:{color:'#6b7a93',font:{family:'DM Mono',size:10}},grid:{color:'rgba(255,255,255,.04)'},beginAtZero:true}}}});
}

function renderWeeklyChart(data){
    const ctx=document.getElementById('weeklyChart').getContext('2d');
    if(weeklyChart)weeklyChart.destroy();
    if(!data||!data.length){ctx.clearRect(0,0,ctx.canvas.width,ctx.canvas.height);ctx.font='13px Sora,sans-serif';ctx.fillStyle='#3d4d63';ctx.textAlign='center';ctx.fillText('No weekly data',ctx.canvas.width/2,90);return;}
    weeklyChart=new Chart(ctx,{type:'line',
        data:{labels:data.map(d=>d.record_date),
              datasets:[{label:'Daily Sales (₱)',data:data.map(d=>parseFloat(d.day_sales)||0),
                         borderColor:'rgba(41,182,200,1)',backgroundColor:'rgba(41,182,200,.1)',
                         fill:true,tension:.3,pointBackgroundColor:'rgba(41,182,200,1)',pointRadius:4}]},
        options:{responsive:true,maintainAspectRatio:true,
            plugins:{legend:{labels:{color:'#6b7a93',font:{family:'Sora',size:11}}},
                     tooltip:{callbacks:{label:c=>' ₱'+parseFloat(c.parsed.y).toLocaleString('en-PH',{minimumFractionDigits:2})}}},
            scales:{x:{ticks:{color:'#6b7a93',font:{family:'Sora',size:10}},grid:{color:'rgba(255,255,255,.04)'}},
                    y:{ticks:{color:'#6b7a93',font:{family:'DM Mono',size:10},callback:v=>'₱'+v.toLocaleString('en-PH')},grid:{color:'rgba(255,255,255,.04)'},beginAtZero:true}}}});
}

function renderMonthlyChart(data){
    const ctx=document.getElementById('monthlyChart').getContext('2d');
    if(monthlyChart)monthlyChart.destroy();
    if(!data||!data.length){ctx.clearRect(0,0,ctx.canvas.width,ctx.canvas.height);ctx.font='13px Sora,sans-serif';ctx.fillStyle='#3d4d63';ctx.textAlign='center';ctx.fillText('No monthly data',ctx.canvas.width/2,90);return;}
    monthlyChart=new Chart(ctx,{type:'bar',
        data:{labels:data.map(d=>d.month),
              datasets:[{label:'Monthly Sales (₱)',data:data.map(d=>parseFloat(d.month_sales)||0),
                         backgroundColor:'rgba(167,139,250,.65)',borderColor:'rgba(167,139,250,1)',
                         borderWidth:1,borderRadius:6}]},
        options:{responsive:true,maintainAspectRatio:true,
            plugins:{legend:{labels:{color:'#6b7a93',font:{family:'Sora',size:11}}},
                     tooltip:{callbacks:{label:c=>' ₱'+parseFloat(c.parsed.y).toLocaleString('en-PH',{minimumFractionDigits:2})}}},
            scales:{x:{ticks:{color:'#6b7a93',font:{family:'Sora',size:10}},grid:{color:'rgba(255,255,255,.04)'}},
                    y:{ticks:{color:'#6b7a93',font:{family:'DM Mono',size:10},callback:v=>'₱'+v.toLocaleString('en-PH')},grid:{color:'rgba(255,255,255,.04)'},beginAtZero:true}}}});
}

function sortBy(col){if(sortCol===col)sortDir*=-1;else{sortCol=col;sortDir=-1;}renderTable(curTab);}

function renderTable(tab){
    let recs=getTabRecords(tab);
    const sorted=[...recs].sort((a,b)=>b.sold_peso-a.sold_peso);
    recs=recs.map(r=>({...r,rank:sorted.findIndex(s=>s.product_id===r.product_id)}));
    recs.sort((a,b)=>(a[sortCol]>b[sortCol]?1:-1)*sortDir);
    document.getElementById('recCount').textContent=`${recs.length} item${recs.length!==1?'s':''}`;
    let tabSold=0,tabRem=0;
    recs.forEach(r=>{tabSold+=r.sold_peso;tabRem+=r.rem_peso;});
    const expTotal=expenses.reduce((s,e)=>s+parseFloat(e.amount||0),0);
    document.getElementById('ttSold').textContent=peso(tabSold);
    document.getElementById('ttRem').textContent =peso(tabRem);
    document.getElementById('ttExp').textContent =peso(expTotal);
    if(!recs.length){document.getElementById('dashBody').innerHTML='<tr class="state-row"><td colspan="7">No records found.</td></tr>';document.getElementById('pgCtrl').innerHTML='';return;}
    const n=recs.length, tot=Math.ceil(n/PER_PAGE);
    if(curPage>tot)curPage=1;
    const page=recs.slice((curPage-1)*PER_PAGE,curPage*PER_PAGE);
    document.getElementById('dashBody').innerHTML=page.map(r=>{
        const cls=getRankCls(r.rank,n), lbl=getRankLabel(r.rank,n);
        const qcls=getQtyCls(r.remaining_qty,r.low_stock_threshold||10);
        return `<tr>
            <td><span class="rank-badge ${cls}">${lbl}</span></td>
            <td><strong style="font-size:.82rem">${esc(r.product_name)}</strong> <span class="prod-id-badge">#${r.product_id}</span></td>
            <td><span class="mono">${peso(r.price)}</span></td>
            <td><span class="mono c-qs">${num(r.sold)}</span></td>
            <td><span class="mono c-sold">${peso(r.sold_peso)}</span></td>
            <td><span class="mono ${qcls}">${r.remaining_qty}</span></td>
            <td><span class="mono c-rem">${peso(r.rem_peso)}</span></td>
        </tr>`;
    }).join('');
    renderPg(tot);
}

function renderPg(tot){
    const c=document.getElementById('pgCtrl');
    if(tot<=1){c.innerHTML='';return;}
    let h='';
    if(curPage>1){h+=`<button class="pg-btn" onclick="pg(1)">«</button><button class="pg-btn" onclick="pg(${curPage-1})">‹</button>`;}
    for(let i=Math.max(1,curPage-2);i<=Math.min(tot,curPage+2);i++)
        h+=`<button class="pg-btn ${i===curPage?'active':''}" onclick="pg(${i})">${i}</button>`;
    if(curPage<tot){h+=`<button class="pg-btn" onclick="pg(${curPage+1})">›</button><button class="pg-btn" onclick="pg(${tot})">»</button>`;}
    c.innerHTML=h;
}
function pg(p){curPage=p;renderTable(curTab);}
function switchTab(t){curTab=t;curPage=1;
    document.getElementById('tab2C').className='ctab2'+(t==='Chicken'?' ac2-ch':'');
    document.getElementById('tab2F').className='ctab2'+(t==='Frozen'?' ac2-fr':'');
    renderTable(t);}
function switchChart(t){curChartTab=t;
    document.getElementById('ctC').className='ctab'+(t==='Chicken'?' ac':'');
    document.getElementById('ctF').className='ctab'+(t==='Frozen'?' af':'');
    renderChart(t);}
function switchChart2(t){curChart2Tab=t;
    document.getElementById('ct2C').className='ctab'+(t==='Chicken'?' ac':'');
    document.getElementById('ct2F').className='ctab'+(t==='Frozen'?' af':'');
    renderChart2(t);}

async function loadDashboard(){
    const date=document.getElementById('dateSelect').value;
    if(!date) return showError('Please select a date');
    document.getElementById('dashBody').innerHTML='<tr class="state-row"><td colspan="7">⏳ Loading…</td></tr>';
    const btn=document.getElementById('loadBtn'); btn.disabled=true;
    try {
        const [recRes,expRes]=await Promise.all([fetch(`${API}?date=${date}&for=dashboard`),fetch(`${API}?expenses=${date}`)]);
        const recData=await recRes.json(); const expData=await expRes.json();
        expenses=expData.expenses||[]; fullRecords=recData.records||[];
        if(!fullRecords.length){showError(`No records found for ${date}.`);return;}
        curPage=1; renderAll();
        document.getElementById('lastUpdated').textContent=new Date().toLocaleTimeString();
    } catch{showError('Failed to load data.');}
    finally{btn.disabled=false;}
}

async function loadAnalytics(){
    const from=document.getElementById('fromDate').value;
    const to  =document.getElementById('toDate').value;
    if(!from||!to){alert('Please select both dates.');return;}
    try{
        const res=await fetch(`${API}?analytics=1&from=${from}&to=${to}`);
        const data=await res.json();
        analyticsData=data;
        // Weekly KPI (sum of daily sales in range)
        const weeklyTotal = (data.weekly||[]).reduce((s,d)=>s+parseFloat(d.day_sales||0),0);
        document.getElementById('kpiWeekly').textContent = peso(weeklyTotal);
        document.getElementById('kpiWeeklySub').textContent = `${(data.weekly||[]).length} days in range`;
        // Monthly KPI (sum of current month in range)
        const monthlyTotal = (data.monthly||[]).reduce((s,d)=>s+parseFloat(d.month_sales||0),0);
        document.getElementById('kpiMonthly').textContent = peso(monthlyTotal);
        document.getElementById('kpiMonthlySub').textContent = `${(data.monthly||[]).length} month(s) in range`;
        renderWeeklyChart(data.weekly||[]);
        renderMonthlyChart(data.monthly||[]);
    } catch(e){ console.error(e); }
}

async function loadDateSelector(){
    const sel=document.getElementById('dateSelect');
    sel.innerHTML='<option>Loading…</option>';
    try{
        const res=await fetch(`${API}?list_dates=1`);
        const data=await res.json();
        if(data.dates&&data.dates.length){
            sel.innerHTML='';
            data.dates.forEach(d=>{const o=document.createElement('option');o.value=d;o.textContent=d;sel.appendChild(o);});
            const last=localStorage.getItem('janeth_date');
            sel.value=(last&&data.dates.includes(last))?last:data.dates[0];
            await loadDashboard();
        }else{sel.innerHTML='<option>No saved data</option>';showError('No records found. Go to Entry page to add data.');}
    }catch{sel.innerHTML='<option>Error</option>';showError('Failed to load dates.');}
}

// ── Date range helpers ──
function setDateRange(from, to){
    document.getElementById('fromDate').value=from;
    document.getElementById('toDate').value=to;
    loadAnalytics();
}
function todayStr(){ return new Date().toISOString().split('T')[0]; }
function monthStart(){ const d=new Date(); d.setDate(1); return d.toISOString().split('T')[0]; }
function lastMonthRange(){ const d=new Date(); d.setDate(0); const end=d.toISOString().split('T')[0]; d.setDate(1); const start=d.toISOString().split('T')[0]; return{start,end}; }
function weekStart(){ const d=new Date(); d.setDate(d.getDate()-6); return d.toISOString().split('T')[0]; }

document.getElementById('thisWeekBtn').addEventListener('click',()=>setDateRange(weekStart(),todayStr()));
document.getElementById('thisMonthBtn').addEventListener('click',()=>setDateRange(monthStart(),todayStr()));
document.getElementById('lastMonthBtn').addEventListener('click',()=>{const r=lastMonthRange();setDateRange(r.start,r.end);});

// ── Export PDF ──
document.getElementById('exportPdfBtn').addEventListener('click',()=>{
    if(!fullRecords.length) return alert('No data to export.');
    const date=document.getElementById('dateSelect').value;
    const {jsPDF}=window.jspdf;
    const doc=new jsPDF({orientation:'landscape',unit:'mm',format:'a4'});

    doc.setFontSize(16); doc.setTextColor(40,40,40);
    doc.text("Janeth's Business — Daily Report", 14, 14);
    doc.setFontSize(10); doc.setTextColor(100,100,100);
    doc.text(`Date: ${date}`, 14, 21);
    doc.text(`Generated: ${new Date().toLocaleString('en-PH')}`, 14, 27);

    const rows = enrichRecords();
    const tableData = rows.map(r=>[r.product_category,r.product_name,peso(r.price),r.sold,peso(r.sold_peso),r.remaining_qty,peso(r.rem_peso)]);

    doc.autoTable({
        head:[['Category','Product','Price','Qty Sold','Sales (₱)','Remaining','Rem. Value (₱)']],
        body:tableData,
        startY:32,
        styles:{fontSize:8,font:'helvetica'},
        headStyles:{fillColor:[41,182,200],textColor:255,fontStyle:'bold'},
        alternateRowStyles:{fillColor:[245,248,255]},
        margin:{left:14,right:14}
    });

    let y=doc.lastAutoTable.finalY+6;
    if(expenses.length){
        doc.setFontSize(11); doc.setTextColor(40,40,40);
        doc.text('Daily Expenses', 14, y); y+=4;
        doc.autoTable({
            head:[['Category','Description','Amount (₱)']],
            body:expenses.map(e=>[e.category,e.description,peso(e.amount)]),
            startY:y,
            styles:{fontSize:8},
            headStyles:{fillColor:[167,139,250],textColor:255,fontStyle:'bold'},
            margin:{left:14,right:14}
        });
        y=doc.lastAutoTable.finalY+4;
    }

    const totalSales=rows.reduce((s,r)=>s+r.sold_peso,0);
    const totalExp  =expenses.reduce((s,e)=>s+parseFloat(e.amount||0),0);
    const net       =totalSales-totalExp;
    doc.setFontSize(9); doc.setTextColor(40,40,40);
    doc.text(`Total Sales: ${peso(totalSales)}   Total Expenses: ${peso(totalExp)}   Est. Net: ${peso(net)}`, 14, y+2);

    doc.save(`janeth_report_${date}.pdf`);
});

// ── Export CSV ──
document.getElementById('exportCsvBtn').addEventListener('click',()=>{
    if(!fullRecords.length) return;
    const date=document.getElementById('dateSelect').value;
    const rows=[
        ['Category','Product','Price','Qty Sold','Sold (PHP)','Qty Remaining','Remaining (PHP)'],
        ...enrichRecords().map(r=>[r.product_category,r.product_name,r.price.toFixed(2),r.sold,r.sold_peso.toFixed(2),r.remaining_qty,r.rem_peso.toFixed(2)]),
        [],['Expenses'],['Category','Description','Amount'],
        ...expenses.map(e=>[e.category,e.description,parseFloat(e.amount).toFixed(2)])
    ];
    const uri='data:text/csv;charset=utf-8,'+encodeURIComponent(rows.map(r=>r.join(',')).join('\n'));
    Object.assign(document.createElement('a'),{href:uri,download:`janeth_${date}.csv`}).click();
});

document.getElementById('loadBtn').addEventListener('click',loadDashboard);
document.getElementById('loadAnalyticsBtn').addEventListener('click',loadAnalytics);
document.getElementById('searchInput').addEventListener('input',()=>{curPage=1;renderAll();});

// ── Logout warning ──
document.getElementById('logoutBtn').addEventListener('click',()=>{
    const ov=document.getElementById('modalOverlay');
    ov.classList.add('active');
    document.getElementById('modalIcon').textContent='👋';
    document.getElementById('modalMsg').textContent='Are you sure you want to sign out?';
    document.getElementById('modalOk').textContent='Yes, sign out';
    document.getElementById('modalCancel').textContent='Stay';
    const ok=document.getElementById('modalOk').cloneNode(true);
    const can=document.getElementById('modalCancel').cloneNode(true);
    document.getElementById('modalOk').replaceWith(ok);
    document.getElementById('modalCancel').replaceWith(can);
    ok.addEventListener('click',()=>{ov.classList.remove('active');window.location.href='logout.php';});
    can.addEventListener('click',()=>ov.classList.remove('active'));
});

// Init date range defaults
document.getElementById('fromDate').value = monthStart();
document.getElementById('toDate').value   = todayStr();

loadDateSelector().then(()=>loadAnalytics());
</script>

<!-- Modal -->
<div id="modalOverlay" style="position:fixed;inset:0;background:rgba(0,0,0,.65);backdrop-filter:blur(8px);display:none;justify-content:center;align-items:center;z-index:1000;padding:1rem;">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:2rem 2.25rem;max-width:420px;width:100%;text-align:center;box-shadow:0 25px 60px rgba(0,0,0,.5);">
        <div id="modalIcon" style="font-size:2rem;margin-bottom:.75rem">💬</div>
        <p id="modalMsg" style="font-size:.9rem;color:var(--text);margin-bottom:1.5rem;line-height:1.5;font-weight:500">Are you sure?</p>
        <div style="display:flex;gap:.75rem;justify-content:center">
            <button id="modalOk" class="btn btn-ghost">OK</button>
            <button id="modalCancel" class="btn btn-ghost">Cancel</button>
        </div>
    </div>
</div>
</body>
</html>