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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #f1f5f9; padding: 1.5rem; color: #0f172a; }
        .dashboard { max-width: 1600px; margin: 0 auto; }
        .top-bar { background: white; border-radius: 1.25rem; padding: 0.75rem 1.5rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .logo h1 { font-size: 1.3rem; font-weight: 700; background: linear-gradient(135deg, #1e4b5e, #0f7b8a); background-clip: text; -webkit-background-clip: text; color: transparent; }
        .user-area { display: flex; align-items: center; gap: 1rem; background: #f8fafc; padding: 0.3rem 1rem 0.3rem 1.2rem; border-radius: 60px; }
        .btn-logout { background: #ef4444; border: none; padding: 0.4rem 1rem; border-radius: 40px; color: white; cursor: pointer; }
        .btn-icon { background: #eef2ff; border: none; padding: 0.4rem 0.9rem; border-radius: 40px; cursor: pointer; }
        .controls-card { background: white; border-radius: 1.25rem; padding: 1rem 1.5rem; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
        .date-group, .search-group, .action-group { display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; }
        select, input, button { padding: 0.5rem 1rem; border-radius: 2rem; border: 1px solid #e2e8f0; background: white; font-size: 0.85rem; }
        button { background: #0f7b8a; color: white; border: none; cursor: pointer; transition: 0.2s; }
        button:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-outline { background: transparent; border: 1px solid #0f7b8a; color: #0f7b8a; }
        .lowstock-widget { background: #fffbeb; border-radius: 1rem; padding: 0.7rem 1.2rem; margin-bottom: 1.5rem; display: none; align-items: center; gap: 1rem; flex-wrap: wrap; border-left: 4px solid #f59e0b; }
        .lowstock-widget.show { display: flex; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: white; border-radius: 1.25rem; padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .stat-info h3 { font-size: 0.7rem; text-transform: uppercase; color: #5b6e8c; }
        .stat-number { font-size: 1.8rem; font-weight: 800; color: #0f172a; }
        .chart-card { background: white; border-radius: 1.25rem; padding: 1rem; margin-bottom: 1.5rem; }
        canvas { max-height: 260px; width: 100% !important; }
        .table-card { background: white; border-radius: 1.25rem; padding: 1rem; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
        th { text-align: left; padding: 0.9rem 0.6rem; background: #f9fafb; font-weight: 600; border-bottom: 1px solid #e9edf2; cursor: pointer; }
        th:hover { background: #f1f5f9; }
        td { padding: 0.8rem 0.6rem; border-bottom: 1px solid #f0f2f5; }
        tr:hover td { background: #fafcff; }
        .zero-row { opacity: 0.6; background-color: #fefce8; }
        .category-badge { display: inline-block; padding: 0.2rem 0.7rem; border-radius: 50px; font-size: 0.7rem; font-weight: 600; }
        .badge-chicken { background: #dbeafe; color: #1e40af; }
        .badge-frozen { background: #e0e7ff; color: #4338ca; }
        .stock-green { color: #15803d; font-weight: 600; }
        .stock-yellow { color: #b45309; font-weight: 600; }
        .stock-red { color: #dc2626; font-weight: 600; }
        .status-pill { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 50px; font-size: 0.7rem; font-weight: 500; margin-right: 0.4rem; }
        .status-critical { background: #fef3c7; color: #b45309; }
        .status-low { background: #ffedd5; color: #b45309; }
        .status-out { background: #fee2e2; color: #b91c1c; }
        .status-healthy { background: #dcfce7; color: #15803d; }
        .status-none { background: #e2e8f0; color: #475569; }
        .nosales-badge { background: #f1f5f9; color: #475569; font-size: 0.65rem; padding: 0.1rem 0.4rem; border-radius: 30px; margin-left: 0.3rem; }
        .action-btn { background: transparent; border: none; color: #0f7b8a; cursor: pointer; font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 30px; }
        .footer { text-align: center; margin-top: 1.5rem; font-size: 0.7rem; color: #94a3b8; }
        .pagination { display: flex; justify-content: center; gap: 0.5rem; margin-top: 1rem; }
        .page-btn { padding: 0.3rem 0.7rem; border-radius: 30px; background: white; border: 1px solid #cbd5e1; cursor: pointer; }
        .page-btn.active { background: #0f7b8a; color: white; border: none; }
        .loading-state { text-align: center; padding: 2rem; color: #5b6e8c; }
    </style>
</head>
<body>
<div class="dashboard">
    <div class="top-bar">
        <div class="logo"><h1>📦 Janeth · Inventory Dashboard</h1></div>
        <div class="user-area">
            <span>👤 <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo $user_role; ?>)</span>
            <?php if ($user_role === 'admin'): ?>
                <a href="admin/products.php"><button class="btn-icon">⚙️ Products</button></a>
            <?php endif; ?>
            <button class="btn-logout" onclick="window.location.href='../backend/logout.php'">Logout</button>
        </div>
    </div>

    <div class="controls-card">
        <div class="date-group">
            <label>📅 Date</label>
            <select id="dateSelect"></select>
            <button id="loadDashboardBtn">Load</button>
        </div>
        <div class="search-group">
            <input type="text" id="searchInput" placeholder="🔍 Search product">
            <select id="categoryFilter">
                <option value="all">All categories</option>
                <option value="Chicken">🐔 Chicken</option>
                <option value="Frozen">❄️ Frozen</option>
            </select>
            <label><input type="checkbox" id="hideZeroRows"> Hide zero rows</label>
        </div>
        <div class="action-group">
            <button id="exportBtn" class="btn-outline">📎 Export CSV</button>
            <a href="janeth-input.php"><button class="btn-outline">✏️ Entry</button></a>
        </div>
    </div>

    <div id="lowStockWidget" class="lowstock-widget">
        <span>⚠️</span> <span id="lowStockMsg"></span>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-info"><h3>📈 Total Sales</h3><div class="stat-number" id="totalSold">0</div></div><div class="stat-icon">📊</div></div>
        <div class="stat-card"><div class="stat-info"><h3>📦 Remaining Stock</h3><div class="stat-number" id="totalRemaining">0</div></div><div class="stat-icon">🏷️</div></div>
        <div class="stat-card"><div class="stat-info"><h3>📦 Total Available</h3><div class="stat-number" id="totalStock">0</div></div><div class="stat-icon">📦</div></div>
        <div class="stat-card"><div class="stat-info"><h3>🏆 Best Seller</h3><div class="stat-number" id="bestSeller" style="font-size:1rem;">-</div></div><div class="stat-icon">⭐</div></div>
    </div>

    <div class="chart-card">
        <h3 style="font-size:0.85rem; margin-bottom:0.5rem;">📊 Sales per product (units sold)</h3>
        <canvas id="salesChart" width="400" height="200"></canvas>
    </div>

    <div class="table-card">
        <table id="dataTable">
            <thead>
                <tr><th data-sort="category">Category ↕</th><th data-sort="name">Product ↕</th><th data-sort="yesterday">Yesterday</th><th data-sort="stockin">Stock In</th><th data-sort="remaining">Remaining</th><th data-sort="sold">Sold</th><th data-sort="status">Status</th><th></th></tr>
            </thead>
            <tbody id="dashboardBody">
                <tr><td colspan="8" style="text-align:center;">Select a date and click Load</td></tr>
            </tbody>
        </table>
        <div id="paginationControls" class="pagination"></div>
    </div>
    <div class="footer">🕒 Last updated: <span id="lastUpdated">-</span> · Realistic stock status (Critical ≤3, Low ≤10, Healthy >10)</div>
</div>

<script>
    const API_BASE = window.location.origin + '/Janeth_Business/Janeth-Chicken-Business/backend/janeth.php';
    let fullRecords = [];
    let filteredRecords = [];
    let currentPage = 1;
    let rowsPerPage = 15;
    let currentSort = { column: 'name', direction: 'asc' };
    let salesChart = null;

    function updateLastUpdated() {
        document.getElementById('lastUpdated').innerText = new Date().toLocaleTimeString();
    }

    function showError(message) {
        const tbody = document.getElementById('dashboardBody');
        tbody.innerHTML = `<tr><td colspan="8" style="color:#b91c1c;">⚠️ ${message}</td></tr>`;
        document.getElementById('totalSold').innerText = '0';
        document.getElementById('totalRemaining').innerText = '0';
        document.getElementById('totalStock').innerText = '0';
        document.getElementById('bestSeller').innerHTML = '-';
    }

    // ==================== REALISTIC STATUS LOGIC ====================
    function getStatus(remaining, totalStock, sold) {
        // If remaining is 0, it's out of stock
        if (remaining === 0) {
            return { text: 'Out of Stock', class: 'status-out' };
        }
        // Critical stock: 1–3 units left
        if (remaining >= 1 && remaining <= 3) {
            return { text: 'Critical Stock', class: 'status-critical' };
        }
        // Low stock: 4–10 units left
        if (remaining >= 4 && remaining <= 10) {
            return { text: 'Low Stock', class: 'status-low' };
        }
        // Healthy: more than 10 units
        return { text: 'Healthy', class: 'status-healthy' };
    }

    // Helper to detect no sales (sold=0 but stock was available)
    function hasNoSales(sold, totalStock) {
        return (sold === 0 && totalStock > 0);
    }
    // ===============================================================

    function updateLowStockWidget(records) {
        // Show items that are Critical or Low (1–10 units)
        const alertItems = records.filter(r => r.remaining_qty >= 1 && r.remaining_qty <= 10);
        const widget = document.getElementById('lowStockWidget');
        if (alertItems.length) {
            document.getElementById('lowStockMsg').innerHTML = `⚠️ Stock alert: ${alertItems.map(i=>`${i.product_name} (${i.remaining_qty} left)`).join(', ')}. Please reorder.`;
            widget.classList.add('show');
        } else {
            widget.classList.remove('show');
        }
    }

    function sortRecords(records, column, direction) {
        return [...records].sort((a,b) => {
            let valA, valB;
            if (column === 'category') { valA = a.product_category; valB = b.product_category; }
            else if (column === 'name') { valA = a.product_name; valB = b.product_name; }
            else if (column === 'yesterday') { valA = a.yesterday_qty; valB = b.yesterday_qty; }
            else if (column === 'stockin') { valA = a.stock_in; valB = b.stock_in; }
            else if (column === 'remaining') { valA = a.remaining_qty; valB = b.remaining_qty; }
            else if (column === 'sold') { valA = a.sold; valB = b.sold; }
            else if (column === 'status') { valA = getStatus(a.remaining_qty, a.yesterday_qty+a.stock_in, a.sold).text; valB = getStatus(b.remaining_qty, b.yesterday_qty+b.stock_in, b.sold).text; }
            else return 0;
            if (valA < valB) return direction === 'asc' ? -1 : 1;
            if (valA > valB) return direction === 'asc' ? 1 : -1;
            return 0;
        });
    }

    function applyFiltersAndRender() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const category = document.getElementById('categoryFilter').value;
        const hideZero = document.getElementById('hideZeroRows').checked;
        let filtered = fullRecords.filter(rec => {
            const matchName = rec.product_name.toLowerCase().includes(searchTerm);
            const matchCat = category === 'all' || rec.product_category === category;
            if (!matchName || !matchCat) return false;
            if (hideZero) {
                const total = rec.yesterday_qty + rec.stock_in;
                if (total === 0 && rec.sold === 0 && rec.remaining_qty === 0) return false;
            }
            return true;
        });
        filtered = sortRecords(filtered, currentSort.column, currentSort.direction);
        filteredRecords = filtered;
        
        let totalSold = 0, totalRemaining = 0, totalStock = 0;
        let best = { name: '-', sold: 0 };
        filtered.forEach(rec => {
            const sold = rec.sold;
            const remaining = rec.remaining_qty;
            const stock = rec.yesterday_qty + rec.stock_in;
            totalSold += sold;
            totalRemaining += remaining;
            totalStock += stock;
            if (sold > best.sold) best = { name: rec.product_name, sold: sold };
        });
        document.getElementById('totalSold').innerText = totalSold;
        document.getElementById('totalRemaining').innerText = totalRemaining;
        document.getElementById('totalStock').innerText = totalStock;
        document.getElementById('bestSeller').innerHTML = best.name !== '-' ? `${best.name} (${best.sold})` : '-';

        const chartData = filtered.filter(r => r.sold > 0);
        updateChart(chartData);
        updateLowStockWidget(filtered);
        
        const totalPages = Math.ceil(filtered.length / rowsPerPage);
        if (currentPage > totalPages) currentPage = totalPages || 1;
        const start = (currentPage-1)*rowsPerPage;
        const paginated = filtered.slice(start, start+rowsPerPage);
        renderTableRows(paginated);
        renderPaginationControls(totalPages);
    }

    function renderTableRows(rows) {
        const tbody = document.getElementById('dashboardBody');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="8">No matching records</td></tr>';
            return;
        }
        let html = '';
        rows.forEach(rec => {
            const sold = rec.sold;
            const remaining = rec.remaining_qty;
            const stock = rec.yesterday_qty + rec.stock_in;
            const status = getStatus(remaining, stock, sold);
            let stockClass = '';
            if (remaining === 0) stockClass = 'stock-red';
            else if (remaining <= 3) stockClass = 'stock-red';      // critical
            else if (remaining <= 10) stockClass = 'stock-yellow'; // low
            else stockClass = 'stock-green';
            
            const zeroRowClass = (stock === 0 && sold === 0 && remaining === 0) ? 'zero-row' : '';
            const catLabel = rec.product_category === 'Chicken' ? '🐔 Chicken' : '❄️ Frozen';
            const catBadge = rec.product_category === 'Chicken' ? 'badge-chicken' : 'badge-frozen';
            const noSalesBadge = (hasNoSales(sold, stock)) ? '<span class="nosales-badge">⚠️ No sales</span>' : '';
            html += `<tr class="${zeroRowClass}">
                <td><span class="category-badge ${catBadge}">${catLabel}</span></td>
                <td>${escapeHtml(rec.product_name)}</td>
                <td>${rec.yesterday_qty}</td>
                <td>${rec.stock_in}</td>
                <td class="${stockClass}">${remaining}</td>
                <td><strong>${sold}</strong> ${noSalesBadge}</td>
                <td><span class="status-pill ${status.class}">${status.text}</span></td>
                <td><button class="action-btn" onclick="alert('📋 History for ${rec.product_name} (coming soon)')">View</button></td>
            </tr>`;
        });
        tbody.innerHTML = html;
    }

    function renderPaginationControls(totalPages) {
        const container = document.getElementById('paginationControls');
        if (totalPages <= 1) { container.innerHTML = ''; return; }
        let html = `<button class="page-btn" onclick="changePage(1)">« First</button>
                    <button class="page-btn" onclick="changePage(${currentPage-1})" ${currentPage===1?'disabled':''}>‹ Prev</button>`;
        for (let i = Math.max(1,currentPage-2); i <= Math.min(totalPages, currentPage+2); i++) {
            html += `<button class="page-btn ${i===currentPage?'active':''}" onclick="changePage(${i})">${i}</button>`;
        }
        html += `<button class="page-btn" onclick="changePage(${currentPage+1})" ${currentPage===totalPages?'disabled':''}>Next ›</button>
                 <button class="page-btn" onclick="changePage(${totalPages})">Last »</button>`;
        container.innerHTML = html;
    }

    function changePage(page) {
        currentPage = page;
        applyFiltersAndRender();
    }

    function updateChart(records) {
        const ctx = document.getElementById('salesChart').getContext('2d');
        if (salesChart) salesChart.destroy();
        if (records.length === 0) {
            ctx.font = "14px Inter";
            ctx.fillStyle = "#94a3b8";
            ctx.fillText("No sales data for this period", 50, 100);
            return;
        }
        const labels = records.map(r => r.product_name.length > 18 ? r.product_name.slice(0,16)+'..' : r.product_name);
        const data = records.map(r => r.sold);
        salesChart = new Chart(ctx, {
            type: 'bar',
            data: { labels: labels, datasets: [{ label: 'Sold (units)', data: data, backgroundColor: '#0f7b8a', borderRadius: 6, barPercentage: 0.7 }] },
            options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'top' } } }
        });
    }

    async function loadDashboard() {
        const date = document.getElementById('dateSelect').value;
        if (!date) { showError('Please select a date'); return; }
        const tbody = document.getElementById('dashboardBody');
        tbody.innerHTML = '<tr><td colspan="8" class="loading-state">⏳ Loading data...</td></tr>';
        const loadBtn = document.getElementById('loadDashboardBtn');
        loadBtn.disabled = true;
        try {
            const res = await fetch(`${API_BASE}?date=${date}`);
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            if (!data.records || data.records.length === 0) {
                showError(`No records for ${date}`);
                fullRecords = [];
                filteredRecords = [];
                applyFiltersAndRender();
            } else {
                fullRecords = data.records;
                currentPage = 1;
                applyFiltersAndRender();
                updateLastUpdated();
            }
        } catch (err) {
            console.error(err);
            showError('Failed to load data from server');
        } finally {
            loadBtn.disabled = false;
        }
    }

    async function loadDateSelector() {
        const select = document.getElementById('dateSelect');
        select.innerHTML = '<option>Loading dates...</option>';
        try {
            const res = await fetch(`${API_BASE}?list_dates=1`);
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            if (data.dates && data.dates.length) {
                select.innerHTML = '';
                data.dates.forEach(d => { const opt = document.createElement('option'); opt.value = d; opt.textContent = d; select.appendChild(opt); });
                select.value = data.dates[0];
                await loadDashboard();
            } else {
                select.innerHTML = '<option>No saved data</option>';
                showError('No records found. Go to Entry page to add data.');
            }
        } catch (err) {
            console.error(err);
            select.innerHTML = '<option>Error loading dates</option>';
            showError('Failed to load dates.');
        }
    }

    function exportToCSV() {
        if (!filteredRecords.length) { alert('No data to export'); return; }
        const date = document.getElementById('dateSelect').value;
        let csv = [["Category","Product","Yesterday","Stock In","Remaining","Sold"]];
        filteredRecords.forEach(r => { csv.push([r.product_category, r.product_name, r.yesterday_qty, r.stock_in, r.remaining_qty, r.sold]); });
        const csvContent = "data:text/csv;charset=utf-8," + csv.map(e => e.join(",")).join("\n");
        const link = document.createElement('a');
        link.href = encodeURI(csvContent);
        link.download = `inventory_${date}.csv`;
        link.click();
    }

    function escapeHtml(str) { return String(str).replace(/[&<>]/g, m => m==='&'?'&amp;':m==='<'?'&lt;':'&gt;'); }

    document.querySelectorAll('th[data-sort]').forEach(th => {
        th.addEventListener('click', () => {
            const column = th.getAttribute('data-sort');
            if (currentSort.column === column) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.column = column;
                currentSort.direction = 'asc';
            }
            currentPage = 1;
            applyFiltersAndRender();
        });
    });

    document.getElementById('loadDashboardBtn').addEventListener('click', loadDashboard);
    document.getElementById('searchInput').addEventListener('input', () => { currentPage = 1; applyFiltersAndRender(); });
    document.getElementById('categoryFilter').addEventListener('change', () => { currentPage = 1; applyFiltersAndRender(); });
    document.getElementById('hideZeroRows').addEventListener('change', () => { currentPage = 1; applyFiltersAndRender(); });
    document.getElementById('exportBtn').addEventListener('click', exportToCSV);
    loadDateSelector();
</script>
</body>
</html>