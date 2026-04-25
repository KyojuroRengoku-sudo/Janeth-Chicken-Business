<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Janeth – Inventory Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
            --warning: #fbbf24;
            --warning-dim: rgba(251,191,36,0.1);
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

        .dashboard { max-width: 1400px; margin: 0 auto; }

        /* Header */
        .header {
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 1rem;
            margin-bottom: 1.75rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--border);
        }
        .logo { display: flex; align-items: center; gap: 0.75rem; }
        .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--teal), #1a9aab);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            box-shadow: 0 4px 16px rgba(41,182,200,0.3);
        }
        .logo-text { display: flex; flex-direction: column; }
        .logo-title { font-size: 1.1rem; font-weight: 700; color: var(--text); letter-spacing: -0.02em; }
        .logo-sub { font-size: 0.68rem; color: var(--text-muted); font-weight: 400; letter-spacing: 0.06em; text-transform: uppercase; }

        .header-right { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }

        .user-chip {
            display: flex; align-items: center; gap: 0.6rem;
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 50px; padding: 0.35rem 0.9rem 0.35rem 0.5rem;
        }
        .user-avatar {
            width: 26px; height: 26px;
            background: linear-gradient(135deg, var(--accent), #e8920f);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 0.65rem; font-weight: 700; color: #0d1117;
        }
        .user-name { font-size: 0.8rem; font-weight: 500; }
        .role-tag { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-faint); }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.45rem 1rem; border-radius: 50px;
            font-size: 0.78rem; font-weight: 600; font-family: 'Sora', sans-serif;
            cursor: pointer; border: none; transition: all 0.18s ease;
            text-decoration: none; white-space: nowrap; letter-spacing: 0.01em;
        }
        .btn-primary { background: linear-gradient(135deg, var(--accent), #e8920f); color: #0d1117; box-shadow: 0 3px 12px var(--accent-glow); }
        .btn-primary:hover { box-shadow: 0 6px 20px rgba(245,166,35,0.4); transform: translateY(-1px); }
        .btn-ghost { background: var(--surface); border: 1px solid var(--border); color: var(--text-muted); }
        .btn-ghost:hover { border-color: var(--teal); color: var(--teal); background: var(--teal-dim); }
        .btn-teal { background: var(--teal-dim); border: 1px solid rgba(41,182,200,0.2); color: var(--teal); }
        .btn-teal:hover { background: rgba(41,182,200,0.18); }
        .btn-danger { background: rgba(248,113,113,0.12); border: 1px solid rgba(248,113,113,0.2); color: var(--danger); }
        .btn-danger:hover { background: rgba(248,113,113,0.2); }

        /* Controls */
        .controls {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 1rem 1.25rem;
            display: flex; flex-wrap: wrap; justify-content: space-between;
            align-items: center; gap: 1rem; margin-bottom: 1.25rem;
        }
        .controls-group { display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap; }
        .control-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); font-weight: 600; }

        input[type="text"], select {
            background: var(--surface-2); border: 1px solid var(--border);
            border-radius: var(--radius-sm); color: var(--text);
            font-family: 'Sora', sans-serif; font-size: 0.8rem;
            padding: 0.45rem 0.85rem; outline: none; transition: 0.18s;
        }
        input[type="text"]:focus, select:focus { border-color: var(--teal); box-shadow: 0 0 0 3px var(--teal-dim); }
        select option { background: #1e2633; }

        /* Low-stock alert */
        .lowstock-alert {
            background: rgba(251,191,36,0.08);
            border: 1px solid rgba(251,191,36,0.2);
            border-left: 3px solid var(--warning);
            border-radius: var(--radius-sm);
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            display: none; align-items: center; gap: 0.6rem;
            font-size: 0.8rem; color: var(--warning);
        }
        .lowstock-alert.show { display: flex; }

        /* Stat cards – now 2 cards + best seller */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem; margin-bottom: 1.25rem;
        }
        @media (max-width: 700px) { .stats-grid { grid-template-columns: 1fr; } }

        .stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 1.1rem 1.25rem;
            display: flex; justify-content: space-between; align-items: flex-start;
            transition: 0.2s; cursor: default; overflow: hidden;
        }
        .stat-card:hover { border-color: var(--teal); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
        .stat-info { min-width: 0; flex: 1; }
        .stat-label { font-size: 0.67rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-faint); margin-bottom: 0.45rem; }
        .stat-number {
            font-size: clamp(1rem, 2.2vw, 1.75rem);
            font-weight: 700; line-height: 1.2;
            font-family: 'DM Mono', monospace;
            color: var(--text); word-break: break-word;
        }
        .stat-number.accent { color: var(--accent); }
        .stat-number.teal   { color: var(--teal); }
        .stat-number.green  { color: var(--success); }
        .stat-name { font-size: 0.82rem; font-weight: 600; color: var(--text); line-height: 1.3; margin-top: 0.25rem; }
        .stat-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
        .stat-icon.blue  { background: var(--teal-dim); }
        .stat-icon.amber { background: var(--accent-dim); }
        .stat-icon.star  { background: rgba(251,191,36,0.1); }

        /* Chart */
        .chart-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 1.25rem;
            margin-bottom: 1.25rem;
        }
        .chart-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;
        }
        .chart-title { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-faint); }

        /* Chart category toggle */
        .chart-tabs { display: flex; gap: 0.4rem; }
        .chart-tab {
            padding: 0.3rem 0.85rem; border-radius: 50px;
            font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em;
            cursor: pointer; border: 1px solid var(--border);
            background: var(--surface-2); color: var(--text-muted);
            font-family: 'Sora', sans-serif; transition: 0.15s;
        }
        .chart-tab.active-chicken { background: rgba(251,191,36,0.15); color: var(--chicken); border-color: rgba(251,191,36,0.3); }
        .chart-tab.active-frozen  { background: rgba(96,165,250,0.12); color: var(--frozen);  border-color: rgba(96,165,250,0.25); }
        .chart-tab:hover:not(.active-chicken):not(.active-frozen) { border-color: var(--teal); color: var(--teal); }

        .chart-container { position: relative; height: 240px; width: 100%; }
        .chart-container canvas { max-height: 240px; }

        /* Product table with category tabs */
        .table-wrap {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); overflow: hidden; margin-bottom: 1.25rem;
        }
        .table-header-bar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border);
            background: var(--surface-2); flex-wrap: wrap; gap: 0.75rem;
        }
        .table-title { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.07em; }
        .record-count { font-family: 'DM Mono', monospace; font-size: 0.72rem; color: var(--text-faint); }

        /* Category tabs inside table */
        .cat-tabs { display: flex; gap: 0; border-bottom: 1px solid var(--border); }
        .cat-tab {
            padding: 0.7rem 1.5rem; font-size: 0.78rem; font-weight: 700;
            cursor: pointer; border: none; font-family: 'Sora', sans-serif;
            background: transparent; color: var(--text-muted); transition: 0.15s;
            border-bottom: 2px solid transparent; margin-bottom: -1px;
            letter-spacing: 0.04em;
        }
        .cat-tab:hover { color: var(--text); }
        .cat-tab.active-chicken { color: var(--chicken); border-bottom-color: var(--chicken); }
        .cat-tab.active-frozen  { color: var(--frozen);  border-bottom-color: var(--frozen); }

        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        thead tr { border-bottom: 1px solid var(--border); }
        th {
            padding: 0.7rem 1rem; text-align: right;
            font-size: 0.67rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.09em; color: var(--text-faint); background: var(--surface-2);
            white-space: nowrap; cursor: pointer; user-select: none; transition: 0.15s;
        }
        th:hover { color: var(--teal); }
        th:first-child { text-align: left; }
        th:nth-child(2) { text-align: right; }

        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.14s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--surface-2); }

        td { padding: 0.65rem 1rem; font-size: 0.81rem; color: var(--text); text-align: right; vertical-align: middle; }
        td:first-child { text-align: left; }

        .mono { font-family: 'DM Mono', monospace; font-size: 0.8rem; font-weight: 600; }
        .peso-sold  { color: var(--success); }
        .peso-rem   { color: var(--accent); }
        .qty-sold   { color: var(--teal); }
        .qty-rem-green  { color: var(--success); }
        .qty-rem-yellow { color: var(--warning); }
        .qty-rem-red    { color: var(--danger); }
        .qty-rem-gray   { color: var(--text-faint); }

        /* Totals row */
        .totals-bar {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border-top: 1px solid var(--border);
        }
        .total-cell {
            padding: 1rem 1.25rem;
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 0.5rem;
        }
        .total-cell:first-child { border-right: 1px solid var(--border); }
        .total-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-faint); }
        .total-amount { font-family: 'DM Mono', monospace; font-size: 1.1rem; font-weight: 700; }
        .total-amount.sold-color { color: var(--success); }
        .total-amount.rem-color  { color: var(--accent); }

        /* Pagination */
        .pagination {
            display: flex; justify-content: center; gap: 0.4rem;
            padding: 0.85rem; border-top: 1px solid var(--border); flex-wrap: wrap;
        }
        .page-btn {
            min-width: 32px; height: 32px; display: flex; align-items: center;
            justify-content: center; padding: 0 0.65rem;
            border-radius: var(--radius-sm); background: var(--surface-2);
            border: 1px solid var(--border); color: var(--text-muted);
            font-size: 0.78rem; font-weight: 600; cursor: pointer; transition: 0.15s;
            font-family: 'Sora', sans-serif;
        }
        .page-btn:hover { border-color: var(--teal); color: var(--teal); }
        .page-btn.active { background: var(--accent); color: #0d1117; border-color: var(--accent); }

        .footer {
            text-align: center; font-size: 0.7rem;
            color: var(--text-faint); font-family: 'DM Mono', monospace;
            margin-top: 0.5rem; padding-bottom: 1rem;
        }

        .state-row td { padding: 3rem; text-align: center; color: var(--text-faint); font-size: 0.85rem; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--surface-3); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-faint); }

        @media (max-width: 640px) {
            body { padding: 1rem; }
            .controls { flex-direction: column; align-items: stretch; }
            .totals-bar { grid-template-columns: 1fr; }
            .total-cell:first-child { border-right: none; border-bottom: 1px solid var(--border); }
        }
    </style>
</head>
<body>
<div class="dashboard">

    <!-- Header -->
    <div class="header">
        <div class="logo">
            <div class="logo-icon">📊</div>
            <div class="logo-text">
                <span class="logo-title">Janeth Business</span>
                <span class="logo-sub">Inventory Dashboard</span>
            </div>
        </div>
        <div class="header-right">
            <div class="user-chip">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <span class="role-tag"><?php echo $user_role; ?></span>
            </div>
            <?php if ($user_role === 'admin'): ?>
                <a href="admin/products.php" class="btn btn-ghost">⚙️ Products</a>
            <?php endif; ?>
            <button class="btn btn-danger" onclick="window.location.href='../backend/logout.php'">Sign out</button>
        </div>
    </div>

    <!-- Controls -->
    <div class="controls">
        <div class="controls-group">
            <span class="control-label">Date</span>
            <select id="dateSelect"></select>
            <button class="btn btn-teal" id="loadDashboardBtn">↻ Load</button>
        </div>
        <div class="controls-group">
            <input type="text" id="searchInput" placeholder="🔍 Search product…" style="width:180px;">
        </div>
        <div class="controls-group">
            <button class="btn btn-ghost" id="exportBtn">📎 Export CSV</button>
            <a href="janeth-input.php" class="btn btn-ghost">✏️ Entry</a>
        </div>
    </div>

    <!-- Low-stock alert -->
    <div id="lowStockWidget" class="lowstock-alert">
        ⚠️ <span id="lowStockMsg"></span>
    </div>

    <!-- Stats: Total Sales (₱), Best Seller -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Sales</div>
                <div class="stat-number teal" id="totalSoldPeso">₱0.00</div>
            </div>
            <div class="stat-icon blue">💰</div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Remaining Value</div>
                <div class="stat-number accent" id="totalRemainingPeso">₱0.00</div>
            </div>
            <div class="stat-icon amber">🏷️</div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Best Seller</div>
                <div class="stat-name" id="bestSeller">—</div>
            </div>
            <div class="stat-icon star">⭐</div>
        </div>
    </div>

    <!-- Chart with Chicken/Frozen tab -->
    <div class="chart-card">
        <div class="chart-header">
            <div class="chart-title">📊 Sales per product (₱ sold)</div>
            <div class="chart-tabs">
                <button class="chart-tab active-chicken" id="chartTabChicken" onclick="switchChartTab('Chicken')">🐔 Chicken</button>
                <button class="chart-tab" id="chartTabFrozen"  onclick="switchChartTab('Frozen')">❄️ Frozen</button>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Product Records Table with Chicken/Frozen tabs -->
    <div class="table-wrap">
        <div class="table-header-bar">
            <span class="table-title">Product Records</span>
            <span class="record-count" id="recordCount">— items</span>
        </div>

        <!-- Category tabs -->
        <div class="cat-tabs">
            <button class="cat-tab active-chicken" id="tabChicken" onclick="switchTableTab('Chicken')">🐔 Chicken</button>
            <button class="cat-tab" id="tabFrozen" onclick="switchTableTab('Frozen')">❄️ Frozen</button>
        </div>

        <div class="table-scroll">
            <table id="dataTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price (₱)</th>
                        <th>Qty Sold</th>
                        <th>Sold (₱)</th>
                        <th>Qty Remaining</th>
                        <th>Remaining (₱)</th>
                    </tr>
                </thead>
                <tbody id="dashboardBody">
                    <tr class="state-row"><td colspan="6">Select a date and click Load</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Totals bar -->
        <div class="totals-bar">
            <div class="total-cell">
                <span class="total-label">Total Sold (₱)</span>
                <span class="total-amount sold-color" id="tableTotalSold">₱0.00</span>
            </div>
            <div class="total-cell">
                <span class="total-label">Total Remaining (₱)</span>
                <span class="total-amount rem-color" id="tableTotalRemaining">₱0.00</span>
            </div>
        </div>

        <div id="paginationControls" class="pagination"></div>
    </div>

    <div class="footer">
        Last updated: <span id="lastUpdated">—</span> &nbsp;·&nbsp; Critical ≤3 · Low ≤10 · Healthy &gt;10
    </div>

</div>

<script>
    const API_BASE = window.location.origin + '/Janeth_Business/Janeth-Chicken-Business/backend/janeth.php';
    let fullRecords = [];
    let currentTableTab = 'Chicken';
    let currentChartTab = 'Chicken';
    let currentPage = 1;
    const rowsPerPage = 15;
    let salesChart = null;

    function formatPeso(val) {
        const n = parseFloat(val) || 0;
        return '₱' + n.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatNum(val) {
        const n = parseFloat(val) || 0;
        return (n % 1 === 0) ? n.toString() : n.toFixed(2);
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>]/g, m => ({ '&':'&amp;','<':'&lt;','>':'&gt;' }[m]));
    }

    function updateLastUpdated() {
        document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();
    }

    function showError(msg) {
        document.getElementById('dashboardBody').innerHTML = `<tr class="state-row"><td colspan="6">⚠️ ${msg}</td></tr>`;
        ['totalSoldPeso','totalRemainingPeso'].forEach(id => document.getElementById(id).textContent = '₱0.00');
        document.getElementById('bestSeller').textContent = '—';
        document.getElementById('recordCount').textContent = '0 items';
        document.getElementById('tableTotalSold').textContent = '₱0.00';
        document.getElementById('tableTotalRemaining').textContent = '₱0.00';
    }

    function updateLowStockWidget(records) {
        const alerts = records.filter(r => r.remaining_qty >= 1 && r.remaining_qty <= 10);
        const w = document.getElementById('lowStockWidget');
        if (alerts.length) {
            document.getElementById('lowStockMsg').textContent =
                `Stock alert: ${alerts.map(i => `${i.product_name} (${i.remaining_qty} left)`).join(', ')}. Please reorder.`;
            w.classList.add('show');
        } else {
            w.classList.remove('show');
        }
    }

    function getQtyClass(remaining) {
        if (remaining === 0)                  return 'qty-rem-gray';
        if (remaining <= 3)                   return 'qty-rem-red';
        if (remaining <= 10)                  return 'qty-rem-yellow';
        return 'qty-rem-green';
    }

    function getSearchTerm() {
        return document.getElementById('searchInput').value.toLowerCase();
    }

    function getFilteredByTab(tab) {
        const term = getSearchTerm();
        return fullRecords.filter(r =>
            r.product_category === tab &&
            r.product_name.toLowerCase().includes(term)
        );
    }

    function renderAll() {
        // Global stats over ALL records
        const term = getSearchTerm();
        const visible = fullRecords.filter(r => r.product_name.toLowerCase().includes(term));

        let totalSoldPeso = 0, totalRemPeso = 0, best = { name: '—', sold_peso: 0 };
        visible.forEach(r => {
            const price = parseFloat(r.price) || 0;
            const soldPeso = r.sold * price;
            const remPeso  = r.remaining_qty * price;
            totalSoldPeso += soldPeso;
            totalRemPeso  += remPeso;
            if (soldPeso > best.sold_peso) best = { name: r.product_name, sold_peso: soldPeso };
        });

        document.getElementById('totalSoldPeso').textContent = formatPeso(totalSoldPeso);
        document.getElementById('totalRemainingPeso').textContent = formatPeso(totalRemPeso);

        const bs = document.getElementById('bestSeller');
        if (best.name !== '—') {
            bs.innerHTML = `<span style="font-family:'DM Mono',monospace;font-size:1.1rem;font-weight:700;color:var(--accent)">${formatPeso(best.sold_peso)}</span><br><span style="font-size:0.78rem;color:var(--text-muted)">${escapeHtml(best.name)}</span>`;
        } else {
            bs.textContent = '—';
        }

        updateLowStockWidget(visible);
        renderChart(currentChartTab);
        renderTable(currentTableTab);
    }

    function renderChart(tab) {
        const records = getFilteredByTab(tab).filter(r => r.sold > 0);
        const ctx = document.getElementById('salesChart').getContext('2d');
        if (salesChart) salesChart.destroy();

        if (!records.length) {
            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
            ctx.font = "14px Sora, sans-serif";
            ctx.fillStyle = "#3d4d63";
            ctx.textAlign = "center";
            ctx.fillText(`No ${tab} sales data for this period`, ctx.canvas.width / 2, 100);
            return;
        }

        const isChicken = tab === 'Chicken';
        const color     = isChicken ? 'rgba(251,191,36,0.75)' : 'rgba(96,165,250,0.75)';
        const border    = isChicken ? 'rgba(251,191,36,1)'    : 'rgba(96,165,250,1)';

        const labels = records.map(r => r.product_name.length > 18 ? r.product_name.slice(0, 16) + '…' : r.product_name);
        const data   = records.map(r => ((parseFloat(r.price) || 0) * r.sold).toFixed(2));

        salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Sales (₱)',
                    data,
                    backgroundColor: color,
                    borderColor: border,
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.65,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { labels: { color: '#6b7a93', font: { family: 'Sora', size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ₱' + parseFloat(ctx.parsed.y).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                        }
                    }
                },
                scales: {
                    x: { ticks: { color: '#6b7a93', font: { family: 'Sora', size: 10 } }, grid: { color: 'rgba(255,255,255,0.04)' } },
                    y: {
                        ticks: {
                            color: '#6b7a93', font: { family: 'DM Mono', size: 10 },
                            callback: v => '₱' + v.toLocaleString('en-PH')
                        },
                        grid: { color: 'rgba(255,255,255,0.04)' },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function renderTable(tab) {
        const records = getFilteredByTab(tab);
        document.getElementById('recordCount').textContent = `${records.length} item${records.length !== 1 ? 's' : ''}`;

        // Compute tab totals
        let tabSold = 0, tabRem = 0;
        records.forEach(r => {
            const price = parseFloat(r.price) || 0;
            tabSold += r.sold * price;
            tabRem  += r.remaining_qty * price;
        });
        document.getElementById('tableTotalSold').textContent = formatPeso(tabSold);
        document.getElementById('tableTotalRemaining').textContent = formatPeso(tabRem);

        if (!records.length) {
            document.getElementById('dashboardBody').innerHTML = '<tr class="state-row"><td colspan="6">No records found.</td></tr>';
            document.getElementById('paginationControls').innerHTML = '';
            return;
        }

        const totalPages = Math.ceil(records.length / rowsPerPage);
        if (currentPage > totalPages) currentPage = 1;
        const start = (currentPage - 1) * rowsPerPage;
        const pageRecords = records.slice(start, start + rowsPerPage);

        const tbody = document.getElementById('dashboardBody');
        tbody.innerHTML = pageRecords.map(rec => {
            const price    = parseFloat(rec.price) || 0;
            const soldPeso = rec.sold * price;
            const remPeso  = rec.remaining_qty * price;
            const qcls     = getQtyClass(rec.remaining_qty);

            return `<tr>
                <td><strong style="font-size:0.83rem">${escapeHtml(rec.product_name)}</strong></td>
                <td><span class="mono">${formatPeso(price)}</span></td>
                <td><span class="mono qty-sold">${formatNum(rec.sold)}</span></td>
                <td><span class="mono peso-sold">${formatPeso(soldPeso)}</span></td>
                <td><span class="mono ${qcls}">${rec.remaining_qty}</span></td>
                <td><span class="mono peso-rem">${formatPeso(remPeso)}</span></td>
            </tr>`;
        }).join('');

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        const c = document.getElementById('paginationControls');
        if (totalPages <= 1) { c.innerHTML = ''; return; }
        let html = '';
        if (currentPage > 1) {
            html += `<button class="page-btn" onclick="changePage(1)">«</button>`;
            html += `<button class="page-btn" onclick="changePage(${currentPage - 1})">‹</button>`;
        }
        for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
            html += `<button class="page-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">${i}</button>`;
        }
        if (currentPage < totalPages) {
            html += `<button class="page-btn" onclick="changePage(${currentPage + 1})">›</button>`;
            html += `<button class="page-btn" onclick="changePage(${totalPages})">»</button>`;
        }
        c.innerHTML = html;
    }

    function changePage(p) { currentPage = p; renderTable(currentTableTab); }

    function switchTableTab(tab) {
        currentTableTab = tab;
        currentPage = 1;
        document.getElementById('tabChicken').className = 'cat-tab' + (tab === 'Chicken' ? ' active-chicken' : '');
        document.getElementById('tabFrozen').className  = 'cat-tab' + (tab === 'Frozen'  ? ' active-frozen'  : '');
        renderTable(tab);
    }

    function switchChartTab(tab) {
        currentChartTab = tab;
        document.getElementById('chartTabChicken').className = 'chart-tab' + (tab === 'Chicken' ? ' active-chicken' : '');
        document.getElementById('chartTabFrozen').className  = 'chart-tab' + (tab === 'Frozen'  ? ' active-frozen'  : '');
        renderChart(tab);
    }

    async function loadDashboard() {
        const date = document.getElementById('dateSelect').value;
        if (!date) return showError('Please select a date');
        document.getElementById('dashboardBody').innerHTML = '<tr class="state-row"><td colspan="6">⏳ Loading data…</td></tr>';
        const btn = document.getElementById('loadDashboardBtn');
        btn.disabled = true;
        try {
            const res = await fetch(`${API_BASE}?date=${date}`);
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            if (!data.records || !data.records.length) {
                showError(`No records found for ${date}.`);
                fullRecords = [];
            } else {
                fullRecords = data.records;
                currentPage = 1;
                renderAll();
                updateLastUpdated();
            }
        } catch (err) {
            showError('Failed to load data from server.');
        } finally {
            btn.disabled = false;
        }
    }

    async function loadDateSelector() {
        const sel = document.getElementById('dateSelect');
        sel.innerHTML = '<option>Loading…</option>';
        try {
            const res  = await fetch(`${API_BASE}?list_dates=1`);
            if (!res.ok) throw new Error();
            const data = await res.json();
            if (data.dates && data.dates.length) {
                sel.innerHTML = '';
                data.dates.forEach(d => {
                    const o = document.createElement('option');
                    o.value = d; o.textContent = d;
                    sel.appendChild(o);
                });
                let lastDate = localStorage.getItem('janeth_last_date');
                sel.value = (lastDate && data.dates.includes(lastDate)) ? lastDate : data.dates[0];
                await loadDashboard();
            } else {
                sel.innerHTML = '<option>No saved data</option>';
                showError('No records found. Go to the Entry page to add data.');
            }
        } catch {
            sel.innerHTML = '<option>Error</option>';
            showError('Failed to load dates.');
        }
    }

    function exportToCSV() {
        if (!fullRecords.length) return alert('No data to export');
        const date = document.getElementById('dateSelect').value;
        const rows = [['Category','Product','Price','Qty Sold','Sold (PHP)','Qty Remaining','Remaining (PHP)'],
            ...fullRecords.map(r => {
                const price = parseFloat(r.price) || 0;
                return [r.product_category, r.product_name, price.toFixed(2),
                        r.sold, (r.sold * price).toFixed(2),
                        r.remaining_qty, (r.remaining_qty * price).toFixed(2)];
            })];
        const uri = 'data:text/csv;charset=utf-8,' + encodeURIComponent(rows.map(r => r.join(',')).join('\n'));
        Object.assign(document.createElement('a'), { href: uri, download: `inventory_${date}.csv` }).click();
    }

    document.getElementById('loadDashboardBtn').addEventListener('click', loadDashboard);
    document.getElementById('searchInput').addEventListener('input', () => { currentPage = 1; renderAll(); });
    document.getElementById('exportBtn').addEventListener('click', exportToCSV);

    loadDateSelector();
</script>
</body>
</html>