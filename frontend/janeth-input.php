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
    <title>Janeth – Daily Entry</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* (same CSS as the previous working version – no changes needed) */
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
            --success: #34d399;
            --chicken: #fbbf24;
            --frozen: #60a5fa;
            --radius: 14px;
            --radius-sm: 8px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Sora', sans-serif;
            background: var(--bg); color: var(--text);
            min-height: 100vh; padding: 1.5rem;
            background-image:
                radial-gradient(ellipse 80% 50% at 10% -10%, rgba(41,182,200,0.07) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 90% 110%, rgba(245,166,35,0.05) 0%, transparent 60%);
        }
        .container { max-width: 1480px; margin: 0 auto; }

        .header {
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;
            padding-bottom: 1.25rem; border-bottom: 1px solid var(--border);
        }
        .logo { display: flex; align-items: center; gap: 0.75rem; }
        .logo-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--teal), #1a9aab);
            border-radius: 10px; display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; box-shadow: 0 4px 16px rgba(41,182,200,0.3);
        }
        .logo-text { display: flex; flex-direction: column; }
        .logo-title { font-size: 1.1rem; font-weight: 700; letter-spacing: -0.02em; }
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
        .btn-danger { background: rgba(248,113,113,0.12); border: 1px solid rgba(248,113,113,0.2); color: var(--danger); }
        .btn-danger:hover { background: rgba(248,113,113,0.2); }
        .btn-teal { background: var(--teal-dim); border: 1px solid rgba(41,182,200,0.2); color: var(--teal); }
        .btn-teal:hover { background: rgba(41,182,200,0.18); }
        .btn-pdf { background: rgba(248,113,113,0.12); border: 1px solid rgba(248,113,113,0.25); color: #f87171; }
        .btn-pdf:hover { background: rgba(248,113,113,0.22); }
        .toggle-switch { display: inline-flex; align-items: center; gap: 0.5rem; background: var(--surface-2); border-radius: 50px; padding: 0.25rem 0.75rem 0.25rem 0.85rem; border: 1px solid var(--border); }
        .toggle-switch label { font-size: 0.7rem; font-weight: 600; color: var(--text-muted); cursor: pointer; }
        .toggle-switch input { width: 32px; height: 16px; appearance: none; background: var(--surface-3); border-radius: 32px; position: relative; cursor: pointer; transition: 0.2s; }
        .toggle-switch input:checked { background: var(--teal); }
        .toggle-switch input::before { content: ''; width: 12px; height: 12px; background: white; border-radius: 50%; position: absolute; top: 2px; left: 2px; transition: 0.2s; }
        .toggle-switch input:checked::before { left: 18px; }

        .date-hero {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 1.75rem 2rem;
            margin-bottom: 1.25rem; display: flex; align-items: center;
            gap: 2rem; flex-wrap: wrap;
        }
        .date-hero-left { flex: 1; min-width: 220px; }
        .date-hero-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--text-muted); font-weight: 600; margin-bottom: 0.4rem; }
        .date-hero-big { font-size: clamp(2rem, 5vw, 3.2rem); font-weight: 700; letter-spacing: -0.03em; line-height: 1; }
        .date-hero-big .day-num { color: var(--accent); }
        .date-hero-sub { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.4rem; font-family: 'DM Mono', monospace; }
        .date-hero-right { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }

        .prev-chip {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.28rem 0.75rem; border-radius: 50px;
            font-size: 0.68rem; font-weight: 600; letter-spacing: 0.04em;
            background: var(--teal-dim); border: 1px solid rgba(41,182,200,0.25); color: var(--teal);
        }
        input[type="date"], input[type="text"], select {
            background: var(--surface-2); border: 1px solid var(--border);
            border-radius: var(--radius-sm); color: var(--text);
            font-family: 'Sora', sans-serif; font-size: 0.8rem;
            padding: 0.45rem 0.85rem; outline: none; transition: 0.18s;
        }
        input:focus, select:focus { border-color: var(--teal); box-shadow: 0 0 0 3px var(--teal-dim); }
        select option { background: #1e2633; }

        .status-chip {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.3rem 0.85rem; border-radius: 50px;
            font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em;
        }
        .status-loaded { background: rgba(52,211,153,0.12); border: 1px solid rgba(52,211,153,0.25); color: var(--success); }
        .status-empty  { background: var(--accent-dim); border: 1px solid rgba(245,166,35,0.25); color: var(--accent); }
        .status-none   { background: var(--surface-2); border: 1px solid var(--border); color: var(--text-muted); }

        .autosave-chip {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.25rem 0.7rem; border-radius: 50px;
            font-size: 0.68rem; font-weight: 600; letter-spacing: 0.04em;
            transition: all 0.3s;
            background: var(--surface-2); border: 1px solid var(--border); color: var(--text-faint);
        }
        .autosave-chip.saving { border-color: rgba(41,182,200,0.3); color: var(--teal); background: var(--teal-dim); }
        .autosave-chip.saved  { border-color: rgba(52,211,153,0.25); color: var(--success); background: rgba(52,211,153,0.08); }
        .autosave-chip.error  { border-color: rgba(248,113,113,0.25); color: var(--danger); background: rgba(248,113,113,0.08); }
        .autosave-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .autosave-chip.saving .autosave-dot { animation: blink 0.8s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.25} }

        .controls {
            display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 0.85rem 1.25rem; margin-bottom: 1.25rem;
        }
        .controls-sep { flex: 1; }

        .table-wrap {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); overflow: hidden; margin-bottom: 1.5rem;
        }
        .section-header {
            display: flex; align-items: center; gap: 1rem;
            padding: 1rem 1.25rem; border-bottom: 1px solid var(--border);
            background: var(--surface-2);
        }
        .section-icon { font-size: 1.3rem; }
        .section-title { font-size: 0.9rem; font-weight: 700; }
        .section-count { font-family: 'DM Mono', monospace; font-size: 0.72rem; color: var(--text-faint); margin-left: auto; }
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 760px; }
        thead tr { border-bottom: 1px solid var(--border); }
        th {
            padding: 0.65rem 1rem; text-align: center;
            font-size: 0.67rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.09em; color: var(--text-faint);
            background: var(--surface-2); white-space: nowrap;
        }
        th:first-child { text-align: left; }
        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.14s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover:not(.total-row) { background: var(--surface-2); }
        td { padding: 0.6rem 1rem; font-size: 0.82rem; color: var(--text); text-align: center; vertical-align: middle; }
        td:first-child { text-align: left; }
        .prod-name { font-weight: 600; font-size: 0.83rem; }

        .yesterday-cell {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: 'DM Mono', monospace;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--teal);
        }
        .yesterday-cell.manual { color: var(--text-muted); }

        .num-input {
            width: 90px; background: var(--surface-3); border: 1px solid var(--border);
            border-radius: var(--radius-sm); color: var(--text);
            font-family: 'DM Mono', monospace; font-size: 0.82rem;
            padding: 0.38rem 0.6rem; text-align: center; outline: none; transition: 0.15s;
        }
        .num-input:focus { border-color: var(--teal); background: rgba(41,182,200,0.05); box-shadow: 0 0 0 3px var(--teal-dim); }
        .num-input:hover { border-color: var(--text-faint); }

        .price-cell       { font-family: 'DM Mono', monospace; font-size: 0.8rem; color: var(--accent); }
        .total-value-cell { font-family: 'DM Mono', monospace; font-size: 0.8rem; color: var(--accent); }

        .total-row td {
            background: rgba(245,166,35,0.06) !important;
            border-top: 1px solid rgba(245,166,35,0.18) !important;
            color: var(--accent) !important;
            font-family: 'DM Mono', monospace;
            font-size: 0.85rem; font-weight: 700;
        }
        .total-row .total-label {
            text-align: right !important; color: var(--text-muted) !important;
            font-family: 'Sora', sans-serif !important; font-size: 0.73rem !important;
            font-weight: 600 !important; text-transform: uppercase; letter-spacing: 0.07em;
            padding-right: 1.5rem !important;
        }
        .empty-section { padding: 2.5rem; text-align: center; color: var(--text-faint); font-size: 0.85rem; }

        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.65);
            backdrop-filter: blur(8px); display: none;
            justify-content: center; align-items: center; z-index: 1000;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 2rem 2.25rem;
            max-width: 380px; width: 90%; text-align: center;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
            animation: popIn 0.2s cubic-bezier(0.34,1.56,0.64,1);
        }
        @keyframes popIn { from{opacity:0;transform:scale(0.9) translateY(12px)} to{opacity:1;transform:scale(1) translateY(0)} }
        .modal-icon { font-size: 2rem; margin-bottom: 0.75rem; }
        .modal-message { font-size: 0.92rem; color: var(--text); margin-bottom: 1.5rem; line-height: 1.5; font-weight: 500; }
        .modal-buttons { display: flex; gap: 0.75rem; justify-content: center; }

        @media print {
            body { background: #fff !important; color: #111 !important; padding: 0.75rem; }
            .header, .controls, .modal-overlay { display: none !important; }
            .date-hero { background: #fff !important; border: none !important; box-shadow: none !important; padding: 0 0 0.75rem; margin-bottom: 0.5rem; flex-direction: column; gap: 0.25rem; }
            .date-hero-right { display: none !important; }
            .date-hero-big { color: #111 !important; font-size: 2rem !important; }
            .date-hero-big .day-num { color: #b8720f !important; }
            .date-hero-sub { color: #666 !important; }
            .status-chip, .autosave-chip, .prev-chip { display: none !important; }
            .table-wrap { border: 1px solid #ccc !important; background: #fff !important; margin-bottom: 1rem !important; page-break-inside: avoid; }
            .section-header { background: #f5f5f5 !important; border-bottom: 1px solid #ddd !important; }
            .section-title { color: #333 !important; }
            th { background: #efefef !important; color: #555 !important; border-bottom: 1px solid #ccc !important; font-size: 0.65rem !important; }
            tbody tr { border-bottom: 1px solid #eee !important; }
            td { color: #111 !important; padding: 0.45rem 0.75rem !important; }
            .num-input { border: none !important; background: transparent !important; box-shadow: none !important; color: #111 !important; font-family: 'DM Mono', monospace !important; width: auto !important; }
            .price-cell, .total-value-cell { color: #b8720f !important; }
            .yesterday-cell { color: #1a9aab !important; }
            .total-row td { background: #fffbf0 !important; color: #b8720f !important; border-top: 1px solid #e5c060 !important; }
            .total-row .total-label { color: #666 !important; }
        }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--surface-3); border-radius: 3px; }

        @media (max-width: 640px) {
            body { padding: 1rem; }
            .date-hero { flex-direction: column; gap: 1rem; }
            .date-hero-big { font-size: 2rem; }
            .controls { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Header -->
    <div class="header">
        <div class="logo">
            <div class="logo-icon">📦</div>
            <div class="logo-text">
                <span class="logo-title">Janeth Business</span>
                <span class="logo-sub">Daily Inventory & Sales</span>
            </div>
        </div>
        <div class="header-right">
            <div class="user-chip">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
            <?php if ($user_role === 'admin'): ?>
                <a href="admin/products.php" class="btn btn-ghost">⚙️ Products</a>
            <?php endif; ?>
            <button class="btn btn-danger" id="logoutBtn">Sign out</button>
        </div>
    </div>

    <!-- Modal -->
    <div id="modalOverlay" class="modal-overlay">
        <div class="modal">
            <div class="modal-icon" id="modalIcon">💬</div>
            <p class="modal-message" id="modalMessage">Are you sure?</p>
            <div class="modal-buttons">
                <button id="modalConfirmBtn" class="btn btn-primary">Confirm</button>
                <button id="modalCancelBtn" class="btn btn-ghost">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Big Date Hero -->
    <div class="date-hero">
        <div class="date-hero-left">
            <div class="date-hero-label">Selected Date</div>
            <div class="date-hero-big" id="heroDay">—</div>
            <div class="date-hero-sub" id="heroFull">Pick a date and click Load</div>
        </div>
        <div class="date-hero-right">
            <div id="prevChip" class="prev-chip" style="display:none">↩ Yesterday from <span id="prevDateLabel"></span></div>
            <div id="autosaveChip" class="autosave-chip">
                <span class="autosave-dot"></span>
                <span id="autosaveLabel">Auto-save off</span>
            </div>
            <span id="statusChip" class="status-chip status-none">● No data loaded</span>
            <input type="date" id="recordDate">
            <button class="btn btn-teal" id="loadDateBtn">↻ Load</button>
        </div>
    </div>

    <!-- Controls with Auto-save toggle -->
    <div class="controls">
        <input type="text" id="searchInput" placeholder="🔍 Search product…" style="width:200px">
        <select id="categoryFilter">
            <option value="all">All Categories</option>
            <option value="Chicken">🐔 Chicken</option>
            <option value="Frozen">❄️ Frozen</option>
        </select>
        <div class="controls-sep"></div>
        <div class="toggle-switch">
            <span>⚡ Auto-save</span>
            <input type="checkbox" id="autoSaveToggle">
            <label for="autoSaveToggle"></label>
        </div>
        <a href="janeth-dashboard.php" class="btn btn-ghost">📊 Dashboard</a>
        <button id="resetBtn" class="btn btn-ghost">⟳ Reset</button>
        <button id="exportPdfBtn" class="btn btn-pdf">📄 Export PDF</button>
    </div>

    <!-- CHICKEN section -->
    <div class="table-wrap">
        <div class="section-header">
            <span class="section-icon">🐔</span>
            <span class="section-title" style="color:var(--chicken)">Chicken Products</span>
            <span class="section-count" id="chickenCount">0 items</span>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price (₱)</th>
                        <th>Yesterday</th>
                        <th>Stock In</th>
                        <th>Remaining</th>
                        <th>Stock In Total (₱)</th>
                    </tr>
                </thead>
                <tbody id="chickenBody"></tbody>
            </table>
        </div>
    </div>

    <!-- FROZEN section -->
<div class="table-wrap">
    <div class="section-header">
        <span class="section-icon">❄️</span>
        <span class="section-title" style="color:var(--frozen)">Frozen Products</span>
        <span class="section-count" id="frozenCount">0 items</span>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price (₱)</th>
                    <th>Yesterday</th>
                    <th>Stock In</th>
                    <th>Remaining</th>
                    <th>Stock In Total (₱)</th>
                </tr>
            </thead>
            <tbody id="frozenBody"></tbody>
        </table>  <!-- ✅ Fixed: closed table properly -->
    </div>
</div>

    <div style="margin-top:0.25rem;margin-bottom:2rem">
        <span style="font-size:0.7rem;color:var(--text-faint);font-family:'DM Mono',monospace">
            ✦ Stock In Total = Stock In × Price &nbsp;·&nbsp; Yesterday = previous day's Remaining &nbsp;·&nbsp; ↑ ↓ arrow keys navigate rows
        </span>
    </div>

</div>

<script>
    const API_BASE = window.location.origin + '/Janeth_Business/Janeth-Chicken-Business/backend/janeth.php';
    let masterProducts = [];
    let masterRecords  = [];
    let navGrid        = [];

    let autoSaveTimer   = null;
    let autoSaveEnabled = false;

    // Load toggle state from localStorage
    const storedToggle = localStorage.getItem('autoSaveToggle');
    const toggle = document.getElementById('autoSaveToggle');
    if (storedToggle === 'true') {
        toggle.checked = true;
        autoSaveEnabled = true;
        document.getElementById('autosaveLabel').textContent = 'Auto-save on';
        document.getElementById('autosaveChip').className = 'autosave-chip saved';
        setTimeout(() => {
            if (document.getElementById('autosaveChip').className.includes('saved'))
                document.getElementById('autosaveChip').className = 'autosave-chip';
        }, 1500);
    } else {
        toggle.checked = false;
        autoSaveEnabled = false;
        document.getElementById('autosaveLabel').textContent = 'Auto-save off';
        document.getElementById('autosaveChip').className = 'autosave-chip';
    }

    toggle.addEventListener('change', () => {
        autoSaveEnabled = toggle.checked;
        localStorage.setItem('autoSaveToggle', autoSaveEnabled);
        if (!autoSaveEnabled) {
            document.getElementById('autosaveLabel').textContent = 'Auto-save off';
            document.getElementById('autosaveChip').className = 'autosave-chip';
        } else {
            document.getElementById('autosaveLabel').textContent = 'Auto-save on';
            document.getElementById('autosaveChip').className = 'autosave-chip saved';
            setTimeout(() => {
                if (document.getElementById('autosaveChip').className.includes('saved'))
                    document.getElementById('autosaveChip').className = 'autosave-chip';
            }, 1500);
        }
    });

    function setAutoSaveStatus(state) {
        const chip  = document.getElementById('autosaveChip');
        const label = document.getElementById('autosaveLabel');
        chip.className = 'autosave-chip' + (state ? ' ' + state : '');
        label.textContent = state === 'saving' ? 'Saving…' : state === 'saved' ? 'Saved ✓' : state === 'error' ? 'Save failed' : (autoSaveEnabled ? 'Auto-save on' : 'Auto-save off');
    }

    function scheduleAutoSave() {
        if (!autoSaveEnabled) return;
        clearTimeout(autoSaveTimer);
        setAutoSaveStatus('saving');
        autoSaveTimer = setTimeout(doSave, 1400);
    }

    async function doSave() {
        const date = document.getElementById('recordDate').value;
        if (!date) { setAutoSaveStatus('error'); return; }
        const payload = {
            date,
            records: masterRecords.map(r => ({
                product_id:    r.productId,
                yesterday_qty: r.yesterday,
                stock_in:      r.stockIn,
                remaining_qty: r.remaining
            }))
        };
        try {
            const res  = await fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
            const data = await res.json();
            if (data.success) { setStatus('loaded'); setAutoSaveStatus('saved'); }
            else setAutoSaveStatus('error');
            setTimeout(() => setAutoSaveStatus(''), 2800);
        } catch { setAutoSaveStatus('error'); setTimeout(() => setAutoSaveStatus(''), 2800); }
    }

    /* ── Modal ── */
    function showModal(message, onConfirm, onCancel = null, icon = '💬') {
        document.getElementById('modalMessage').innerText = message;
        document.getElementById('modalIcon').innerText = icon;
        const overlay = document.getElementById('modalOverlay');
        overlay.classList.add('active');
        const close = () => {
            overlay.classList.remove('active');
            document.getElementById('modalConfirmBtn').replaceWith(document.getElementById('modalConfirmBtn').cloneNode(true));
            document.getElementById('modalCancelBtn').replaceWith(document.getElementById('modalCancelBtn').cloneNode(true));
        };
        document.getElementById('modalConfirmBtn').addEventListener('click', () => { close(); onConfirm && onConfirm(); });
        document.getElementById('modalCancelBtn').addEventListener('click', () => { close(); onCancel && onCancel(); });
    }
    function showAlert(msg, isError = false) {
        showModal(msg, null, null, isError ? '⚠️' : '✅');
        setTimeout(() => document.getElementById('modalOverlay').classList.remove('active'), 2200);
    }

    /* ── Date helpers ── */
    function prevDate(dateStr) {
        if (!dateStr) return '';
        const [y, m, d] = dateStr.split('-').map(Number);
        const daysInMonth = (year, month) => new Date(year, month, 0).getDate();
        let prevYear = y, prevMonth = m, prevDay = d - 1;
        if (prevDay < 1) {
            prevMonth -= 1;
            if (prevMonth < 1) {
                prevYear -= 1;
                prevMonth = 12;
            }
            prevDay = daysInMonth(prevYear, prevMonth);
        }
        return `${prevYear}-${String(prevMonth).padStart(2,'0')}-${String(prevDay).padStart(2,'0')}`;
    }

    function setStatus(type) {
        const chip = document.getElementById('statusChip');
        chip.className = 'status-chip';
        if (type === 'loaded') { chip.classList.add('status-loaded'); chip.textContent = '● Data loaded'; }
        else if (type === 'empty') { chip.classList.add('status-empty'); chip.textContent = '● New entry'; }
        else { chip.classList.add('status-none'); chip.textContent = '● No data loaded'; }
    }

    function updateHeroDate(dateStr) {
        if (!dateStr) {
            document.getElementById('heroDay').innerHTML = '—';
            document.getElementById('heroFull').textContent = 'Pick a date and click Load';
            return;
        }
        const [y, m, d] = dateStr.split('-');
        const date = new Date(+y, +m - 1, +d);
        const monthYear = date.toLocaleDateString('en-US', { month:'long', year:'numeric' });
        const dayName   = date.toLocaleDateString('en-US', { weekday:'long' });
        document.getElementById('heroDay').innerHTML  = `<span class="day-num">${d}</span> ${monthYear}`;
        document.getElementById('heroFull').textContent = dayName;
    }

    /* ── Core data loading (always fetches previous day's remaining) ── */
    async function loadDataForDate(date, silent = false) {
        if (!date) return;
        updateHeroDate(date);
        const { map: prevMap } = await fetchPrevRemaining(date);
        try {
            const res  = await fetch(`${API_BASE}?date=${date}`);
            if (!res.ok) throw new Error();
            const data = await res.json();
            if (data.records && data.records.length) {
                const savedMap = new Map(data.records.map(r => [Number(r.product_id), {
                    yesterday: parseFloat(r.yesterday_qty) || 0,
                    stockIn:   parseFloat(r.stock_in)      || 0,
                    remaining: parseFloat(r.remaining_qty) || 0
                }]));
                masterRecords = masterProducts.map(p => ({
                    ...p,
                    yesterday: savedMap.get(Number(p.productId))?.yesterday ?? 0,
                    stockIn:   savedMap.get(Number(p.productId))?.stockIn   ?? 0,
                    remaining: savedMap.get(Number(p.productId))?.remaining ?? 0,
                    yesterdayFromPrev: false
                }));
                if (prevMap) applyPrevRemaining(prevMap);
                renderSections();
                setStatus('loaded');
                if (!silent) showAlert(`Loaded data for ${date}`);
            } else {
                if (prevMap) {
                    initEmptyWithPrev(prevMap);
                } else {
                    initEmpty();
                }
                setStatus('empty');
                if (!silent) showModal(`No data for ${date}. Start with empty form?`, null, null, '📭');
            }
        } catch (err) {
            if (!silent) showAlert('Failed to load data.', true);
        }
    }

    async function fetchPrevRemaining(date) {
        const prev = prevDate(date);
        try {
            const res  = await fetch(`${API_BASE}?date=${prev}`);
            if (!res.ok) return { map: null };
            const data = await res.json();
            if (data.records && data.records.length) {
                const map = new Map(data.records.map(r => [Number(r.product_id), parseFloat(r.remaining_qty) || 0]));
                document.getElementById('prevDateLabel').textContent = prev;
                document.getElementById('prevChip').style.display = 'inline-flex';
                return { map };
            }
        } catch {}
        document.getElementById('prevChip').style.display = 'none';
        return { map: null };
    }

    function applyPrevRemaining(prevMap) {
        masterRecords.forEach(r => {
            const val = prevMap.get(r.productId) ?? prevMap.get(String(r.productId)) ?? prevMap.get(Number(r.productId));
            if (val !== undefined) {
                r.yesterday = val;
                r.yesterdayFromPrev = true;
            } else {
                r.yesterdayFromPrev = false;
            }
        });
    }

    function initEmptyWithPrev(prevMap) {
        masterRecords = masterProducts.map(p => ({ ...p, yesterday: 0, stockIn: 0, remaining: 0, yesterdayFromPrev: false }));
        if (prevMap) applyPrevRemaining(prevMap);
        renderSections();
    }

    function initEmpty() {
        masterRecords = masterProducts.map(p => ({ ...p, yesterday: 0, stockIn: 0, remaining: 0, yesterdayFromPrev: false }));
        renderSections();
    }

    /* ── Row building ── */
    function buildRow(rec, globalIndex, rowIndex) {
        const tr = document.createElement('tr');
        // Product name
        const tdName = document.createElement('td');
        tdName.innerHTML = `<span class="prod-name">${escapeHtml(rec.productName)}</span>`;
        tr.appendChild(tdName);
        // Price
        const tdPrice = document.createElement('td');
        tdPrice.className = 'price-cell';
        tdPrice.textContent = `₱ ${Number(rec.price).toFixed(2)}`;
        tr.appendChild(tdPrice);
        // Yesterday (only number)
        const tdYesterday = document.createElement('td');
        const yVal = masterRecords[globalIndex].yesterday;
        const fromPrev = masterRecords[globalIndex].yesterdayFromPrev;
        tdYesterday.innerHTML = `<span class="yesterday-cell${fromPrev ? '' : ' manual'}">${yVal}</span>`;
        tr.appendChild(tdYesterday);
        // Editable inputs
        const makeInput = (field, colId) => {
            const td  = document.createElement('td');
            const inp = document.createElement('input');
            inp.type = 'number'; inp.step = 'any'; inp.min = '0';
            inp.value = rec[field] !== 0 ? rec[field] : '';
            inp.placeholder = '0';
            inp.className = 'num-input';
            inp.dataset.row = rowIndex;
            inp.dataset.col = colId;
            inp.addEventListener('input', e => {
    let v = e.target.value === '' ? 0 : parseFloat(e.target.value);
    if (isNaN(v)) v = 0;
    masterRecords[globalIndex][field] = v;
    const totalCell = tr.querySelector('.total-value-cell');
    const r = masterRecords[globalIndex];
    totalCell.textContent = `₱ ${(r.stockIn * r.price).toFixed(2)}`;
    updateChickenTotal();
    updateFrozenTotal();  // ✅ Added this line
    scheduleAutoSave();
});
            td.appendChild(inp);
            return td;
        };
        tr.appendChild(makeInput('stockIn',   0));
        tr.appendChild(makeInput('remaining', 1));
        // Stock In Total
        const tdTotal = document.createElement('td');
        tdTotal.className = 'total-value-cell';
        tdTotal.textContent = `₱ ${(rec.stockIn * rec.price).toFixed(2)}`;
        tr.appendChild(tdTotal);
        return tr;
    }

    function getChickenTotal() {
    return masterRecords.filter(r => r.category === 'Chicken').reduce((s, r) => s + r.stockIn * r.price, 0);
}

function getFrozenTotal() {
    return masterRecords.filter(r => r.category === 'Frozen').reduce((s, r) => s + r.stockIn * r.price, 0);
}

function updateChickenTotal() {
    const el = document.getElementById('chickenTotalCell');
    if (el) el.textContent = `₱ ${getChickenTotal().toFixed(2)}`;
}

function updateFrozenTotal() {
    const el = document.getElementById('frozenTotalCell');
    if (el) el.textContent = `₱ ${getFrozenTotal().toFixed(2)}`;
}

function buildChickenTotalRow() {
    const tr = document.createElement('tr');
    tr.className = 'total-row';
    tr.innerHTML = `<td colspan="5" class="total-label">Total Chicken Stock In</td><td id="chickenTotalCell">₱ ${getChickenTotal().toFixed(2)}</td>`;
    return tr;
}

function buildFrozenTotalRow() {
    const tr = document.createElement('tr');
    tr.className = 'total-row';
    tr.innerHTML = `<td colspan="5" class="total-label">Total Frozen Stock In</td><td id="frozenTotalCell">₱ ${getFrozenTotal().toFixed(2)}</td>`;
    return tr;
}   

    function renderSections() {
        const term = document.getElementById('searchInput').value.toLowerCase();
        const cat  = document.getElementById('categoryFilter').value;
        const tagged   = masterRecords.map((r, i) => ({ ...r, _gi: i }));
        const filtered = tagged.filter(r => r.productName.toLowerCase().includes(term) && (cat === 'all' || r.category === cat));
        const chickens = filtered.filter(r => r.category === 'Chicken');
        const frozens  = filtered.filter(r => r.category === 'Frozen');
        const renderInto = (tbodyId, items, showTotal, countId) => {
            const tbody   = document.getElementById(tbodyId);
            const countEl = document.getElementById(countId);
            tbody.innerHTML = '';
            if (!items.length) { tbody.innerHTML = `<tr><td colspan="6" class="empty-section">No products found.缓解</td>`; countEl.textContent = '0 items'; return; }
            items.forEach((rec, rowIdx) => tbody.appendChild(buildRow(rec, rec._gi, rowIdx)));
            if (showTotal) tbody.appendChild(buildChickenTotalRow());
            countEl.textContent = `${items.length} item${items.length !== 1 ? 's' : ''}`;
        };
        renderInto('chickenBody', chickens, true,  'chickenCount');
        renderInto('frozenBody',  frozens,  true, 'frozenCount');  // ✅ Changed false to true
        rebuildNavGrid();
    }

    function rebuildNavGrid() {
        navGrid = Array.from(document.querySelectorAll('.num-input')).map(inp => ({
            el: inp,
            row: +inp.dataset.row,
            col: +inp.dataset.col,
            tbody: inp.closest('tbody')  // store the parent tbody
        }));
    }

    // ✅ FIXED arrow navigation: stay within same tbody
    document.addEventListener('keydown', e => {
        if (e.key !== 'ArrowDown' && e.key !== 'ArrowUp') return;
        const active = document.activeElement;
        if (!active || !active.classList.contains('num-input')) return;
        e.preventDefault();
        const curRow = +active.dataset.row;
        const curCol = +active.dataset.col;
        const curTbody = active.closest('tbody');
        const dir = e.key === 'ArrowDown' ? 1 : -1;
        const next = navGrid.find(n =>
            n.col === curCol &&
            n.row === curRow + dir &&
            n.tbody === curTbody
        );
        if (next) {
            next.el.focus();
            next.el.select();
        }
    });

    function escapeHtml(s) {
        return String(s).replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[m]));
    }

    async function fetchProducts() {
        try {
            const res  = await fetch(`${API_BASE}?products=1`);
            const data = await res.json();
            if (data.products) {
                masterProducts = data.products
                    .map(p => ({ productId: Number(p.id), productName: p.name, category: p.category, price: parseFloat(p.price) || 0 }))
                    .sort((a, b) => a.productId - b.productId);
                const savedDate = localStorage.getItem('janeth_last_date') || new Date().toISOString().split('T')[0];
                document.getElementById('recordDate').value = savedDate;
                updateHeroDate(savedDate);
                await loadDataForDate(savedDate, true);
            } else throw new Error();
        } catch (e) {
            showAlert('Failed to load products.', true);
            masterProducts = [{ productId: 1, productName: 'Whole Chicken', category: 'Chicken', price: 0 }];
            initEmpty();
        }
    }

    document.getElementById('recordDate').addEventListener('change', async () => {
        const newDate = document.getElementById('recordDate').value;
        localStorage.setItem('janeth_last_date', newDate);
        if (masterProducts.length) {
            await loadDataForDate(newDate, true);
        }
    });

    document.getElementById('loadDateBtn').onclick = async () => {
        const date = document.getElementById('recordDate').value;
        if (!date) return showAlert('Please select a date first.', true);
        await loadDataForDate(date, false);
    };
    document.getElementById('resetBtn').onclick = async () => {
        showModal('Clear all entries? Unsaved changes will be lost.', async () => {
            const date = document.getElementById('recordDate').value;
            const { map: prevMap } = await fetchPrevRemaining(date);
            if (prevMap) initEmptyWithPrev(prevMap);
            else initEmpty();
            setStatus('empty');
            showAlert('Form reset.');
        }, null, '⚠️');
    };
    document.getElementById('exportPdfBtn').onclick = () => { if (!document.getElementById('recordDate').value) return showAlert('Please select a date first.', true); window.print(); };
    document.getElementById('logoutBtn').onclick = () => showModal('Sign out?', () => { window.location.href = '../backend/logout.php'; }, null, '👋');
    document.getElementById('searchInput').oninput = () => renderSections();
    document.getElementById('categoryFilter').onchange = () => renderSections();

    fetchProducts();
</script>
</body>
</html>