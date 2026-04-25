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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Janeth – Daily Entry</title>
    <style>
        * { margin:0; padding:0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #f1f5f9; padding: 1.5rem; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header-bar { background: white; border-radius: 1.5rem; padding: 1rem 1.8rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 1.8rem; }
        .title-section h1 { font-size: 1.6rem; background: linear-gradient(135deg, #1e4b5e, #0f7b8a); background-clip: text; -webkit-background-clip: text; color: transparent; }
        .branch-badge { background: #e6f0f2; padding: 0.25rem 1rem; border-radius: 30px; font-size: 0.8rem; color: #0a7e8c; }
        .user-info { display: flex; align-items: center; gap: 1rem; background: #f8fafc; padding: 0.4rem 1rem 0.4rem 1.2rem; border-radius: 40px; }
        .logout-btn { background: #ef4444; border: none; padding: 0.4rem 1rem; border-radius: 30px; color: white; cursor: pointer; }
        .toolbar { background: white; padding: 1rem 1.5rem; border-radius: 1.2rem; display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 1.8rem; }
        .date-box { display: flex; gap: 0.8rem; align-items: baseline; background: #f1f5f9; padding: 0.4rem 1rem; border-radius: 40px; }
        button { background: #0a7e8c; border: none; padding: 0.5rem 1.2rem; border-radius: 40px; font-weight: 600; color: white; cursor: pointer; transition: 0.2s; }
        .btn-outline { background: transparent; border: 1px solid #0a7e8c; color: #0a7e8c; }
        .table-wrapper { background: white; border-radius: 1.5rem; padding: 1rem; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th, td { padding: 1rem 0.8rem; text-align: center; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; }
        td input { width: 100px; padding: 0.5rem; text-align: center; border: 1px solid #cbd5e1; border-radius: 12px; }
        .readonly-cell { background: #f8fafc; font-weight: 600; }
        .error-message { background: #fee2e2; border-left: 4px solid #ef4444; padding: 0.8rem; border-radius: 1rem; margin-bottom: 1rem; display: none; }
        .action-buttons { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem; }
        .info { text-align: center; font-size: 0.75rem; color: #64748b; margin-top: 1.5rem; }
        .category-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .category-chicken { background: #dbeafe; color: #1e40af; }
        .category-frozen { background: #e0e7ff; color: #3730a3; }
    </style>
</head>
<body>
<div class="container">
    <div class="header-bar">
        <div class="title-section">
            <h1>📦 Daily Inventory & Sales</h1>
            <div class="branch-badge">📍 Janeth Branch</div>
        </div>
        <div class="user-info">
            👤 <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo $user_role; ?>)
            <button class="logout-btn" onclick="window.location.href='../backend/logout.php'">🚪 Logout</button>
            <?php if ($user_role === 'admin'): ?>
                <a href="admin/products.php"><button type="button" class="btn-outline" style="margin-left: 0.5rem;">⚙️ Products</button></a>
            <?php endif; ?>
        </div>
    </div>

    <div class="toolbar">
        <div class="date-box">
            <label>📅 Date</label>
            <input type="date" id="recordDate" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div>
            <button id="loadDateBtn" class="btn-outline">↻ Load existing data</button>
        </div>
    </div>

    <div id="errorMsg" class="error-message"></div>

    <div class="table-wrapper">
        <table id="inventoryTable">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Product</th>
                    <th>Yesterday Qty</th>
                    <th>Stock In</th>
                    <th>Remaining (end of day)</th>
                    <th>Sold (auto-calc)</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>

    <div class="action-buttons">
        <button id="saveBtn">💾 Save record</button>
        <button id="resetBtn" class="btn-outline">⟳ Reset form</button>
        <a href="janeth-dashboard.php"><button type="button" class="btn-outline">📊 Dashboard</button></a>
    </div>
    <div class="info">✅ Sold = (Yesterday+StockIn) - Remaining</div>
</div>

<script>
    const API_BASE = window.location.origin + '/Janeth_Business/Janeth-Chicken-Business/backend/janeth.php';
    let PRODUCTS = [];
    let records = []; // each: { productId, productName, category, yesterday, stockIn, remaining }

    function computeSold(rec) {
        return (rec.yesterday + rec.stockIn) - rec.remaining;
    }

    function updateSoldCell(rowIdx) {
        const rec = records[rowIdx];
        const sold = computeSold(rec);
        const row = document.getElementById('tableBody').rows[rowIdx];
        if (row) {
            row.cells[5].textContent = sold;
        }
    }

    function renderTable() {
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';
        if (!records.length) {
            tbody.innerHTML = '<tr><td colspan="6">No products available<\/tr>';
            return;
        }

        records.forEach((rec, idx) => {
            const row = tbody.insertRow();

            const cellCat = row.insertCell(0);
            const catText = rec.category === 'Chicken' ? '🐔 Chicken' : '❄️ Frozen';
            const catClass = rec.category === 'Chicken' ? 'category-chicken' : 'category-frozen';
            cellCat.innerHTML = `<span class="category-badge ${catClass}">${catText}</span>`;
            cellCat.style.textAlign = 'center';

            const cellProd = row.insertCell(1);
            cellProd.textContent = rec.productName;
            cellProd.style.fontWeight = '600';

            function makeNumberInput(fieldName, initialValue) {
                const input = document.createElement('input');
                input.type = 'number';
                input.value = initialValue;
                input.step = '1';
                input.addEventListener('input', (e) => {
                    let newVal = parseInt(e.target.value, 10);
                    if (isNaN(newVal)) newVal = 0;
                    records[idx][fieldName] = newVal;
                    updateSoldCell(idx);
                });
                return input;
            }

            const cellYest = row.insertCell(2);
            cellYest.appendChild(makeNumberInput('yesterday', rec.yesterday));

            const cellStock = row.insertCell(3);
            cellStock.appendChild(makeNumberInput('stockIn', rec.stockIn));

            const cellRemaining = row.insertCell(4);
            cellRemaining.appendChild(makeNumberInput('remaining', rec.remaining));

            const cellSold = row.insertCell(5);
            cellSold.className = 'readonly-cell';
            cellSold.textContent = computeSold(rec);
        });
    }

    function showError(msg) {
        const errDiv = document.getElementById('errorMsg');
        errDiv.textContent = msg;
        errDiv.style.display = 'block';
        setTimeout(() => { errDiv.style.display = 'none'; }, 4000);
    }

    function initEmptyRecords() {
        records = PRODUCTS.map(p => ({
            productId: p.id,
            productName: p.name,
            category: p.category,
            yesterday: 0,
            stockIn: 0,
            remaining: 0
        }));
        renderTable();
    }

    async function fetchProducts() {
        try {
            const response = await fetch(`${API_BASE}?products=1`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();
            if (data.products && Array.isArray(data.products)) {
                PRODUCTS = data.products.map(p => ({ id: p.id, name: p.name, category: p.category }));
                initEmptyRecords();
            } else {
                throw new Error('Invalid products response');
            }
        } catch (err) {
            console.error(err);
            showError('Failed to load products. Using fallback.');
            PRODUCTS = [
                { id: 1, name: "Fresh Whole Chicken", category: "Chicken" },
                { id: 2, name: "Chicken Breast Fillet", category: "Chicken" },
                { id: 3, name: "Frozen Spring Rolls", category: "Frozen" },
                { id: 4, name: "Frozen Fish Fillet", category: "Frozen" }
            ];
            initEmptyRecords();
        }
    }

    async function saveToBackend() {
        const date = document.getElementById('recordDate').value;
        if (!date) { showError('Please select a date'); return; }
        const payload = {
            date: date,
            records: records.map(rec => ({
                product_id: rec.productId,
                yesterday_qty: rec.yesterday,
                stock_in: rec.stockIn,
                remaining_qty: rec.remaining
            }))
        };
        try {
            const response = await fetch(API_BASE, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (data.success) alert('✅ ' + data.message);
            else showError('Save error: ' + (data.error || 'Unknown error'));
        } catch (err) {
            console.error(err);
            showError('Network error while saving');
        }
    }

    async function loadFromBackend() {
        const date = document.getElementById('recordDate').value;
        if (!date) { showError('Please select a date'); return; }
        try {
            const response = await fetch(`${API_BASE}?date=${date}`);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();
            if (data.records) {
                const newRecords = data.records.map(r => ({
                    productId: r.product_id,
                    productName: r.product_name,
                    category: r.product_category,
                    yesterday: r.yesterday_qty,
                    stockIn: r.stock_in,
                    remaining: r.remaining_qty
                }));
                records = newRecords;
                renderTable();
                alert(`📋 Loaded data for ${date}`);
            } else {
                if (confirm(`No data found for ${date}. Start empty?`)) {
                    initEmptyRecords();
                }
            }
        } catch (err) {
            console.error(err);
            showError('Failed to load data from server');
        }
    }

    function resetForm() {
        if (confirm('Clear all entries? Unsaved changes will be lost.')) {
            initEmptyRecords();
        }
    }

    document.getElementById('saveBtn').addEventListener('click', saveToBackend);
    document.getElementById('resetBtn').addEventListener('click', resetForm);
    document.getElementById('loadDateBtn').addEventListener('click', loadFromBackend);

    fetchProducts();
</script>
</body>
</html>