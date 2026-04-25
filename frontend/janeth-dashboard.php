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
        /* ========== (same CSS as your previous working dashboard, no changes needed) ========== */
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

        .dashboard { max-width: 1600px; margin: 0 auto; }

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
        .role-tag {
            font-size: 0.65rem; font-weight: 700; letter-spacing: 0.06em;
            text-transform: uppercase; color: var(--text-faint);
        }

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
        input[type="text"]:focus, select:focus {
            border-color: var(--teal); box-shadow: 0 0 0 3px var(--teal-dim);
        }
        select option { background: #1e2633; }

        .checkbox-label {
            display: flex; align-items: center; gap: 0.4rem;
            font-size: 0.78rem; color: var(--text-muted); cursor: pointer;
        }
        .checkbox-label input[type="checkbox"] {
            accent-color: var(--teal); width: 14px; height: 14px;
            padding: 0; background: none; border: none;
        }

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

        /* Stat cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem; margin-bottom: 1.25rem;
        }
        @media (max-width: 900px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .stats-grid { grid-template-columns: 1fr; } }

        .stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 1.1rem 1.25rem;
            display: flex; justify-content: space-between; align-items: flex-start;
            transition: 0.2s; cursor: default; overflow: hidden;
        }
        .stat-card:hover { border-color: var(--teal); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }

        .stat-info { min-width: 0; flex: 1; }

        .stat-label {
            font-size: 0.67rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: var(--text-faint); margin-bottom: 0.45rem;
        }
        .stat-number {
            font-size: clamp(1.2rem, 2.5vw, 2rem);
            font-weight: 700; line-height: 1.2;
            font-family: 'DM Mono', monospace;
            color: var(--text);
            overflow-x: auto;
            white-space: normal;
            word-break: break-word;
            max-width: 100%;
        }
        .stat-number.accent { color: var(--accent); }
        .stat-number.teal   { color: var(--teal); }
        .stat-number.green  { color: var(--success); }

        .stat-name {
            font-size: 0.82rem; font-weight: 600; color: var(--text); line-height: 1.3;
            margin-top: 0.25rem;
        }

        .stat-icon {
            width: 36px; height: 36px;
            border-radius: 10px; display: flex;
            align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0;
        }
        .stat-icon.blue  { background: var(--teal-dim); }
        .stat-icon.amber { background: var(--accent-dim); }
        .stat-icon.green { background: var(--success-dim); }
        .stat-icon.star  { background: rgba(251,191,36,0.1); }

        /* Chart */
        .chart-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 1.25rem;
            margin-bottom: 1.25rem;
        }
        .chart-title {
            font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: var(--text-faint); margin-bottom: 1rem;
        }
        .chart-container {
            position: relative; height: 240px; width: 100%;
        }
        .chart-container canvas { max-height: 240px; }

        /* Table */
        .table-wrap {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); overflow: hidden; margin-bottom: 1.25rem;
        }
        .table-header-bar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border);
            background: var(--surface-2);
        }
        .table-title { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.07em; }
        .record-count { font-family: 'DM Mono', monospace; font-size: 0.72rem; color: var(--text-faint); }

        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 820px; }

        thead tr { border-bottom: 1px solid var(--border); }
        th {
            padding: 0.7rem 1rem; text-align: center;
            font-size: 0.67rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.09em; color: var(--text-faint); background: var(--surface-2);
            white-space: nowrap; cursor: pointer; user-select: none; transition: 0.15s;
        }
        th:hover { color: var(--teal); }
        th:first-child, th:nth-child(2) { text-align: left; }

        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.14s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--surface-2); }
        tbody tr.zero-row { opacity: 0.45; }

        td {
            padding: 0.65rem 1rem; font-size: 0.81rem;
            color: var(--text); text-align: center; vertical-align: middle;
        }
        td:first-child, td:nth-child(2) { text-align: left; }

        .badge {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.22rem 0.65rem; border-radius: 50px;
            font-size: 0.67rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase;
        }
        .badge-chicken { background: rgba(251,191,36,0.12); color: var(--chicken); border: 1px solid rgba(251,191,36,0.2); }
        .badge-frozen  { background: rgba(96,165,250,0.1);  color: var(--frozen);  border: 1px solid rgba(96,165,250,0.2); }

        .status-pill {
            display: inline-flex; align-items: center; gap: 0.3rem;
            padding: 0.22rem 0.65rem; border-radius: 50px;
            font-size: 0.67rem; font-weight: 700; letter-spacing: 0.04em;
        }
        .status-healthy  { background: var(--success-dim);  color: var(--success); border: 1px solid rgba(52,211,153,0.2); }
        .status-low      { background: var(--warning-dim);  color: var(--warning); border: 1px solid rgba(251,191,36,0.2); }
        .status-critical { background: var(--danger-dim);   color: var(--danger);  border: 1px solid rgba(248,113,113,0.2); }
        .status-out      { background: rgba(100,116,139,0.12); color: #64748b;     border: 1px solid rgba(100,116,139,0.2); }

        .stock-val { font-family: 'DM Mono', monospace; font-size: 0.8rem; font-weight: 600; }
        .stock-green  { color: var(--success); }
        .stock-yellow { color: var(--warning); }
        .stock-red    { color: var(--danger); }

        .sold-val { font-family: 'DM Mono', monospace; font-weight: 700; color: var(--text); }
        .nosales-badge {
            display: inline-flex; align-items: center; gap: 0.2rem;
            font-size: 0.62rem; font-weight: 600; color: var(--text-faint);
            background: var(--surface-3); border-radius: 50px;
            padding: 0.15rem 0.5rem; margin-left: 0.35rem;
        }

        .view-btn {
            background: var(--surface-3); border: 1px solid var(--border);
            color: var(--text-muted); font-size: 0.7rem; font-weight: 600;
            font-family: 'Sora', sans-serif; padding: 0.25rem 0.65rem;
            border-radius: 50px; cursor: pointer; transition: 0.15s;
        }
        .view-btn:hover { border-color: var(--teal); color: var(--teal); }

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
        .page-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        .footer {
            text-align: center; font-size: 0.7rem;
            color: var(--text-faint); font-family: 'DM Mono', monospace;
            margin-top: 0.5rem; padding-bottom: 1rem;
        }

        .state-row td {
            padding: 3rem; text-align: center;
            color: var(--text-faint); font-size: 0.85rem;
        }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--surface-3); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-faint); }

        @media (max-width: 640px) {
            body { padding: 1rem; }
            .controls { flex-direction: column; align-items: stretch; }
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
            <select id="categoryFilter">
                <option value="all">All Categories</option>
                <option value="Chicken">🐔 Chicken</option>
                <option value="Frozen">❄️ Frozen</option>
            </select>
            <label class="checkbox-label">
                <input type="checkbox" id="hideZeroRows"> Hide zero rows
            </label>
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

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Sales</div>
                <div class="stat-number teal" id="totalSold">0</div>
            </div>
            <div class="stat-icon blue">📈</div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Remaining Stock</div>
                <div class="stat-number accent" id="totalRemaining">0</div>
            </div>
            <div class="stat-icon amber">🏷️</div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Available</div>
                <div class="stat-number green" id="totalStock">0</div>
            </div>
            <div class="stat-icon green">📦</div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Best Seller</div>
                <div class="stat-name" id="bestSeller">—</div>
            </div>
            <div class="stat-icon star">⭐</div>
        </div>
    </div>

    <!-- Chart -->
    <div class="chart-card">
        <div class="chart-title">📊 Sales per product (units sold)</div>
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Table -->
    <div class="table-wrap">
        <div class="table-header-bar">
            <span class="table-title">Product Records</span>
            <span class="record-count" id="recordCount">— items</span>
        </div>
        <div class="table-scroll">
            <table id="dataTable">
                <thead>
                    <tr>
                        <th data-sort="category">Category</th>
                        <th data-sort="name">Product</th>
                        <th data-sort="yesterday">Yesterday</th>
                        <th data-sort="stockin">Stock In</th>
                        <th data-sort="remaining">Remaining</th>
                        <th data-sort="sold">Sold</th>
                        <th data-sort="status">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="dashboardBody">
                    <tr class="state-row"><td colspan="8">Select a date and click Load</td></tr>
                </tbody>
            </table>
        </div>
        <div id="paginationControls" class="pagination"></div>
    </div>

    <div class="footer">
        Last updated: <span id="lastUpdated">—</span> &nbsp;·&nbsp; Critical ≤3 · Low ≤10 · Healthy &gt;10
    </div>

</div>

<script>
    const API_BASE = window.location.origin + '/Janeth_Business/Janeth-Chicken-Business/backend/janeth.php';
    let fullRecords = [], filteredRecords = [], currentPage = 1, rowsPerPage = 15;
    let currentSort = { column: 'id', direction: 'asc' };   // default sort by product_id ascending
    let salesChart = null;

    function updateLastUpdated() {
        document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();
    }

    function showError(msg) {
        const tbody = document.getElementById('dashboardBody');
        tbody.innerHTML = `<tr class="state-row"><td colspan="8">⚠️ ${msg}</td></tr>`;
        document.getElementById('totalSold').textContent = '0';
        document.getElementById('totalRemaining').textContent = '0';
        document.getElementById('totalStock').textContent = '0';
        document.getElementById('bestSeller').textContent = '—';
        document.getElementById('recordCount').textContent = '0 items';
    }

    function getStatus(remaining) {
        if (remaining === 0)                    return { text: 'Out of Stock', cls: 'status-out' };
        if (remaining >= 1 && remaining <= 3)   return { text: 'Critical',     cls: 'status-critical' };
        if (remaining >= 4 && remaining <= 10)  return { text: 'Low Stock',    cls: 'status-low' };
        return { text: 'Healthy', cls: 'status-healthy' };
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

    function sortRecords(records) {
        const { column, direction } = currentSort;
        return [...records].sort((a, b) => {
            let vA, vB;
            if      (column === 'id')        { vA = a.product_id; vB = b.product_id; }
            else if (column === 'category')  { vA = a.product_category; vB = b.product_category; }
            else if (column === 'name')      { vA = a.product_name; vB = b.product_name; }
            else if (column === 'yesterday') { vA = a.yesterday_qty; vB = b.yesterday_qty; }
            else if (column === 'stockin')   { vA = a.stock_in; vB = b.stock_in; }
            else if (column === 'remaining') { vA = a.remaining_qty; vB = b.remaining_qty; }
            else if (column === 'sold')      { vA = a.sold; vB = b.sold; }
            else if (column === 'status')    { vA = getStatus(a.remaining_qty).text; vB = getStatus(b.remaining_qty).text; }
            else return 0;
            if (vA < vB) return direction === 'asc' ? -1 : 1;
            if (vA > vB) return direction === 'asc' ? 1 : -1;
            return 0;
        });
    }

    function applyFiltersAndRender() {
        const term     = document.getElementById('searchInput').value.toLowerCase();
        const cat      = document.getElementById('categoryFilter').value;
        const hideZero = document.getElementById('hideZeroRows').checked;

        let filtered = fullRecords.filter(rec => {
            if (!rec.product_name.toLowerCase().includes(term)) return false;
            if (cat !== 'all' && rec.product_category !== cat) return false;
            if (hideZero) {
                const total = rec.yesterday_qty + rec.stock_in;
                if (total === 0 && rec.sold === 0 && rec.remaining_qty === 0) return false;
            }
            return true;
        });

        filtered = sortRecords(filtered);
        filteredRecords = filtered;

        let totalSold = 0, totalRemaining = 0, totalStock = 0;
        let best = { name: '—', sold: 0 };
        filtered.forEach(r => {
            totalSold      += r.sold;
            totalRemaining += r.remaining_qty;
            totalStock     += r.yesterday_qty + r.stock_in;
            if (r.sold > best.sold) best = { name: r.product_name, sold: r.sold };
        });

        document.getElementById('totalSold').textContent = formatNumber(totalSold);
        document.getElementById('totalRemaining').textContent = formatNumber(totalRemaining);
        document.getElementById('totalStock').textContent = formatNumber(totalStock);
        const bs = document.getElementById('bestSeller');
        if (best.name !== '—') {
            bs.innerHTML = `<span style="font-family:'DM Mono',monospace;font-size:1.2rem;font-weight:700;color:var(--accent)">${formatNumber(best.sold)}</span><br><span style="font-size:0.78rem;color:var(--text-muted)">${escapeHtml(best.name)}</span>`;
        } else {
            bs.textContent = '—';
        }

        document.getElementById('recordCount').textContent = `${filtered.length} item${filtered.length !== 1 ? 's' : ''}`;

        updateChart(filtered.filter(r => r.sold > 0));
        updateLowStockWidget(filtered);

        const totalPages = Math.ceil(filtered.length / rowsPerPage);
        if (currentPage > totalPages) currentPage = Math.max(1, totalPages);
        const start = (currentPage - 1) * rowsPerPage;
        renderTableRows(filtered.slice(start, start + rowsPerPage));
        renderPagination(totalPages);
    }

    function formatNumber(value) {
        if (value === undefined || value === null) return '0';
        let num = parseFloat(value);
        if (isNaN(num)) return '0';
        return (num % 1 === 0) ? num.toString() : num.toFixed(2);
    }

    function renderTableRows(rows) {
        const tbody = document.getElementById('dashboardBody');
        if (!rows.length) {
            tbody.innerHTML = '<tr class="state-row"><td colspan="8">No matching records found.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(rec => {
            const sold       = rec.sold;
            const remaining  = rec.remaining_qty;
            const totalAvail = rec.yesterday_qty + rec.stock_in;
            const status     = getStatus(remaining);
            const isZero     = totalAvail === 0 && sold === 0 && remaining === 0;
            const noSales    = sold === 0 && totalAvail > 0;

            const stockCls = remaining === 0 ? 'stock-red' : remaining <= 3 ? 'stock-red' : remaining <= 10 ? 'stock-yellow' : 'stock-green';
            const catLabel = rec.product_category === 'Chicken' ? '🐔 Chicken' : '❄️ Frozen';
            const catCls   = rec.product_category === 'Chicken' ? 'badge-chicken' : 'badge-frozen';
            const noSalesBadge = noSales ? '<span class="nosales-badge">⚠️ No sales</span>' : '';

            return `<tr${isZero ? ' class="zero-row"' : ''}>
                <td><span class="badge ${catCls}">${catLabel}</span></td>
                <td><strong style="font-size:0.83rem">${escapeHtml(rec.product_name)}</strong></td>
                <td><span class="stock-val">${rec.yesterday_qty}</span></td>
                <td><span class="stock-val">${rec.stock_in}</span></td>
                <td><span class="stock-val ${stockCls}">${remaining}</span></td>
                <td><span class="sold-val">${formatNumber(sold)}</span>${noSalesBadge}</td>
                <td><span class="status-pill ${status.cls}">${status.text}</span></td>
                <td><button class="view-btn" onclick="alert('📋 History for ${escapeHtml(rec.product_name)} (coming soon)')">View</button></td>
            </tr>`;
        }).join('');
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

    function changePage(p) { currentPage = p; applyFiltersAndRender(); }

    function updateChart(records) {
        const ctx = document.getElementById('salesChart').getContext('2d');
        if (salesChart) salesChart.destroy();
        if (!records.length) {
            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
            ctx.font = "14px Sora, sans-serif";
            ctx.fillStyle = "#3d4d63";
            ctx.textAlign = "center";
            ctx.fillText("No sales data for this period", ctx.canvas.width / 2, 100);
            return;
        }
        const labels = records.map(r => r.product_name.length > 18 ? r.product_name.slice(0, 16) + '…' : r.product_name);
        const data   = records.map(r => r.sold);

        salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Units Sold',
                    data,
                    backgroundColor: records.map(r =>
                        r.product_category === 'Chicken'
                            ? 'rgba(251,191,36,0.7)'
                            : 'rgba(96,165,250,0.7)'
                    ),
                    borderColor: records.map(r =>
                        r.product_category === 'Chicken'
                            ? 'rgba(251,191,36,1)'
                            : 'rgba(96,165,250,1)'
                    ),
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.65,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        labels: { color: '#6b7a93', font: { family: 'Sora', size: 11 } }
                    }
                },
                scales: {
                    x: {
                        ticks: { color: '#6b7a93', font: { family: 'Sora', size: 10 } },
                        grid: { color: 'rgba(255,255,255,0.04)' }
                    },
                    y: {
                        ticks: { color: '#6b7a93', font: { family: 'DM Mono', size: 10 } },
                        grid: { color: 'rgba(255,255,255,0.04)' },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    async function loadDashboard() {
        const date = document.getElementById('dateSelect').value;
        if (!date) return showError('Please select a date');
        document.getElementById('dashboardBody').innerHTML = '<tr class="state-row"><td colspan="8">⏳ Loading data…</td></tr>';
        const btn = document.getElementById('loadDashboardBtn');
        btn.disabled = true;
        try {
            const res = await fetch(`${API_BASE}?date=${date}`);
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            if (!data.records || !data.records.length) {
                showError(`No records found for ${date}.`);
                fullRecords = []; filteredRecords = [];
                applyFiltersAndRender();
            } else {
                fullRecords = data.records;
                currentPage = 1;
                applyFiltersAndRender();
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
                // Try to get last used date from localStorage (set by input page)
                let lastDate = localStorage.getItem('janeth_last_date');
                if (lastDate && data.dates.includes(lastDate)) {
                    sel.value = lastDate;
                } else {
                    sel.value = data.dates[0];
                }
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
        if (!filteredRecords.length) return alert('No data to export');
        const date = document.getElementById('dateSelect').value;
        const rows = [['Category','Product','Yesterday','Stock In','Remaining','Sold'],
            ...filteredRecords.map(r => [r.product_category, r.product_name, r.yesterday_qty, r.stock_in, r.remaining_qty, r.sold])];
        const uri = 'data:text/csv;charset=utf-8,' + encodeURIComponent(rows.map(r => r.join(',')).join('\n'));
        Object.assign(document.createElement('a'), { href: uri, download: `inventory_${date}.csv` }).click();
    }

    function escapeHtml(s) { return String(s).replace(/[&<>]/g, m => ({ '&':'&amp;','<':'&lt;','>':'&gt;' }[m])); }

    // Sort headers
    document.querySelectorAll('th[data-sort]').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.getAttribute('data-sort');
            if (currentSort.column === col) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.column = col;
                currentSort.direction = 'asc';
            }
            currentPage = 1;
            applyFiltersAndRender();
            // (Optional) visually indicate sort direction
        });
    });

    document.getElementById('loadDashboardBtn').addEventListener('click', loadDashboard);
    document.getElementById('searchInput').addEventListener('input',    () => { currentPage=1; applyFiltersAndRender(); });
    document.getElementById('categoryFilter').addEventListener('change',() => { currentPage=1; applyFiltersAndRender(); });
    document.getElementById('hideZeroRows').addEventListener('change',  () => { currentPage=1; applyFiltersAndRender(); });
    document.getElementById('exportBtn').addEventListener('click', exportToCSV);

    loadDateSelector();
</script>
</body>
</html>