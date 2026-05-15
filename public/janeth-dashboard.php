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
    <title>Dashboard · Janeth's Business</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <!-- BUG FIX: theme.js was listed twice (head + bottom of body), causing double-apply
         and the toggle to fire on a stale reference. Load it ONCE here in head. -->
    <script src="../public/assets/js/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <style>
        :root {
            --bg:#080c14;--surface:#0f1724;--surface-2:#161f30;--surface-3:#1c2840;
            --border:rgba(255,255,255,0.07);--border-hi:rgba(255,255,255,0.13);
            --accent:#f5a623;--accent-dim:rgba(245,166,35,.12);--accent-glow:rgba(245,166,35,.25);
            --teal:#29b6c8;--teal-dim:rgba(41,182,200,.1);--teal-glow:rgba(41,182,200,.3);
            --text:#e2e8f4;--text-muted:#8a9bbf;--text-faint:#4a5a72;
            --shadow:rgba(0,0,0,0.5);--modal-bg:rgba(0,0,0,0.65);
            --danger:#f87171;--success:#34d399;--warning:#fbbf24;--purple:#a78bfa;
            --chicken:#fbbf24;--frozen:#60a5fa;--expense:#a78bfa;
            --radius:16px;--radius-sm:10px;
            --sidebar-w:220px;
        }
        /* Light mode overrides — set by theme.js via data-theme attribute */
        [data-theme="light"] {
            --bg:#f0f4f9;--surface:#ffffff;--surface-2:#e8eef5;--surface-3:#d0daea;
            --border:rgba(0,0,0,0.09);--text:#0d1b2a;--text-muted:#3a5070;--text-faint:#6080a0;
            --modal-bg:rgba(0,0,0,0.45);
        }
        [data-theme="light"] body {
            background-image:radial-gradient(ellipse 70% 50% at 10% -10%,rgba(41,182,200,.04) 0%,transparent 60%),
                             radial-gradient(ellipse 60% 40% at 90% 110%,rgba(245,166,35,.03) 0%,transparent 60%);
        }
        /* BUG FIX: select option background was hardcoded to dark (#1a2234) — broke light mode */
        [data-theme="light"] select option { background:#e8eef5 !important; color:#0d1b2a !important; }
        [data-theme="light"] input[type="text"],
        [data-theme="light"] input[type="date"],
        [data-theme="light"] select { background:var(--surface-2) !important; color:var(--text) !important; border-color:var(--border) !important; }
        [data-theme="light"] tbody tr:hover { background:var(--surface-2); }
        [data-theme="light"] th { background:var(--surface-2) !important; color:var(--text-faint) !important; }
        [data-theme="light"] .controls, [data-theme="light"] .analytics-bar { background:var(--surface); }
        [data-theme="light"] .kpi:hover { box-shadow:0 8px 24px rgba(0,0,0,0.1); }
        [data-theme="light"] .chart-card { background:var(--surface); border-color:var(--border); }
        [data-theme="light"] .lowstock-bar { background:rgba(251,191,36,0.06); }
        [data-theme="light"] .sidebar { background:var(--surface); border-right-color:var(--border); }
        [data-theme="light"] .nav-item:hover { background:var(--surface-2); }
        [data-theme="light"] .nav-item.active { background:rgba(41,182,200,.1); }
        [data-theme="light"] .btn-ghost { background:#ffffff; border-color:rgba(0,0,0,.12); color:#3a5070; }
        [data-theme="light"] .btn-ghost:hover { background:rgba(41,182,200,.08); border-color:#29b6c8; color:#29b6c8; }
        [data-theme="light"] .pg-btn { background:var(--surface-2); }
        [data-theme="light"] .ctab { background:var(--surface-2); color:var(--text-muted); }
        [data-theme="light"] .ctab2 { color:var(--text-muted); }
        [data-theme="light"] .prod-id-badge { background:var(--surface-3); }
        [data-theme="light"] .total-cell { background:var(--surface); }
        [data-theme="light"] .table-hd-bar { background:var(--surface-2); }
        [data-theme="light"] .modal-inner { background:#ffffff; }
        [data-theme="light"] ::-webkit-scrollbar-thumb { background:#c0cfe0; }

        *{margin:0;padding:0;box-sizing:border-box;}
        html{font-size:17px;}
        body{font-family:'Sora',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;font-size:1rem;
             background-image:radial-gradient(ellipse 70% 50% at 10% -10%,rgba(41,182,200,.06) 0%,transparent 60%),
                              radial-gradient(ellipse 60% 40% at 90% 110%,rgba(245,166,35,.04) 0%,transparent 60%);}
        .app{display:flex;min-height:100vh;}

        /* ── Sidebar ── */
        .sidebar{width:var(--sidebar-w);min-width:var(--sidebar-w);background:var(--surface);border-right:1px solid var(--border);
                 display:flex;flex-direction:column;position:fixed;left:0;top:0;bottom:0;z-index:100;
                 transition:transform .25s cubic-bezier(.4,0,.2,1);}
        .sidebar-logo{display:flex;align-items:center;gap:.75rem;padding:1.4rem 1.2rem 1.2rem;border-bottom:1px solid var(--border);}
        .logo-icon{width:36px;height:36px;background:linear-gradient(135deg,var(--teal),#1a9aab);border-radius:9px;
                   display:flex;align-items:center;justify-content:center;font-size:1rem;box-shadow:0 4px 12px rgba(41,182,200,.3);flex-shrink:0;}
        .logo-title{font-size:.95rem;font-weight:700;letter-spacing:-.02em;display:block;}
        .logo-sub{font-size:.6rem;color:var(--text-muted);font-weight:400;letter-spacing:.07em;text-transform:uppercase;}
        .user-section{padding:1rem 1.2rem;border-bottom:1px solid var(--border);}
        .user-chip{display:flex;align-items:center;gap:.55rem;}
        .user-avatar{width:28px;height:28px;background:linear-gradient(135deg,var(--accent),#e8920f);border-radius:50%;
                     display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:700;color:#0a0e17;flex-shrink:0;}
        .user-name{font-size:.82rem;font-weight:600;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
        .role-badge{font-size:.58rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;padding:.12rem .45rem;
                    border-radius:50px;background:var(--accent-dim);color:var(--accent);border:1px solid rgba(245,166,35,.2);white-space:nowrap;}
        .nav{flex:1;padding:.75rem 0;overflow-y:auto;}
        .nav-label{font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;
                   color:var(--text-faint);padding:.5rem 1.2rem .3rem;margin-top:.25rem;}
        .nav-item{display:flex;align-items:center;gap:.7rem;padding:.6rem 1.2rem;font-size:.83rem;font-weight:500;
                  color:var(--text-muted);cursor:pointer;text-decoration:none;transition:.15s;border-left:2px solid transparent;margin:1px 0;}
        .nav-item:hover{background:var(--surface-2);color:var(--text);}
        .nav-item.active{background:rgba(41,182,200,.08);color:var(--teal);border-left-color:var(--teal);font-weight:600;}
        .nav-icon{font-size:.95rem;width:20px;text-align:center;flex-shrink:0;}
        .nav-divider{height:1px;background:var(--border);margin:.5rem 1.2rem;}
        .sidebar-footer{padding:.85rem 1.2rem;border-top:1px solid var(--border);}
        .btn-logout{width:100%;display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.55rem;
                    border-radius:var(--radius-sm);background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.18);
                    color:var(--danger);font-size:.8rem;font-weight:600;cursor:pointer;font-family:'Sora',sans-serif;transition:.15s;}
        .btn-logout:hover{background:rgba(248,113,113,.15);}
        .hamburger{display:none;position:fixed;top:.85rem;left:.85rem;z-index:200;background:var(--surface);
                   border:1px solid var(--border);border-radius:8px;width:38px;height:38px;
                   align-items:center;justify-content:center;cursor:pointer;font-size:1.1rem;}
        .sidebar-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:90;backdrop-filter:blur(2px);}
        #themeToggle{background:none;border:none;color:var(--text-muted);font-family:'Sora',sans-serif;
                     font-size:.83rem;font-weight:500;cursor:pointer;padding:0;width:100%;text-align:left;}

        /* ── Main ── */
        .main{margin-left:var(--sidebar-w);flex:1;padding:1.5rem;min-width:0;}
        .page-hd{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;
                 margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid var(--border);}
        .page-title{font-size:1.15rem;font-weight:700;letter-spacing:-.02em;}
        .page-title span{color:var(--teal);}
        .hd-actions{display:flex;align-items:center;gap:.55rem;flex-wrap:wrap;}
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:50px;
             font-size:.95rem;font-weight:600;font-family:'Sora',sans-serif;cursor:pointer;border:none;
             transition:.18s;text-decoration:none;white-space:nowrap;}
        .btn-ghost{background:var(--surface-2);border:1px solid var(--border);color:var(--text-muted);}
        .btn-ghost:hover{border-color:var(--teal);color:var(--teal);background:var(--teal-dim);transform:translateY(-1px);}
        .btn-teal{background:var(--teal-dim);border:1px solid rgba(41,182,200,.2);color:var(--teal);font-weight:700;}
        .btn-teal:hover{background:rgba(41,182,200,.18);}

        /* ── Controls ── */
        .controls{display:flex;flex-wrap:wrap;gap:.65rem;align-items:center;justify-content:space-between;
                  background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
                  padding:.85rem 1.2rem;margin-bottom:1.1rem;}
        .ctrl-group{display:flex;align-items:center;gap:.65rem;flex-wrap:wrap;}
        .ctrl-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);}
        input[type="text"],input[type="date"],select{background:var(--surface-2);border:1px solid var(--border);
            border-radius:var(--radius-sm);color:var(--text);font-family:'Sora',sans-serif;
            font-size:.95rem;padding:.55rem 1rem;outline:none;transition:.18s;}
        input:focus,select:focus{border-color:var(--teal);box-shadow:0 0 0 3px var(--teal-dim);}
        /* BUG FIX: was hardcoded to dark color, now uses CSS variable */
        select option{background:var(--surface-2);color:var(--text);}

        /* ── KPI ── */
        .kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:1rem;margin-bottom:1.1rem;}
        .kpi{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
             padding:1.1rem 1.25rem;display:flex;justify-content:space-between;align-items:flex-start;
             transition:.2s;position:relative;overflow:hidden;}
        .kpi::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;
                     background:linear-gradient(90deg,var(--teal),var(--accent));
                     transform:scaleX(0);transform-origin:left;transition:transform .25s ease;}
        .kpi:hover::before{transform:scaleX(1);}
        .kpi:hover{border-color:var(--teal);transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2);}
        .kpi-info{min-width:0;flex:1;}
        .kpi-label{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:var(--text-faint);margin-bottom:.4rem;}
        .kpi-val{font-size:clamp(1rem,2vw,1.65rem);font-weight:700;line-height:1.2;font-family:'DM Mono',monospace;word-break:break-word;}
        .kpi-sub{font-size:.73rem;font-weight:500;color:var(--text-muted);margin-top:.2rem;line-height:1.3;}
        .kpi-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
        .ic-teal{background:var(--teal-dim);}.ic-amber{background:var(--accent-dim);}
        .ic-green{background:rgba(52,211,153,.1);}.ic-red{background:rgba(248,113,113,.1);}
        .ic-star{background:rgba(251,191,36,.1);}.ic-skull{background:rgba(248,113,113,.1);}
        .kpi-val.teal{color:var(--teal);}.kpi-val.amber{color:var(--accent);}
        .kpi-val.green{color:var(--success);}.kpi-val.red{color:var(--danger);}
        .lowstock-bar{display:none;align-items:center;gap:.6rem;background:rgba(251,191,36,.07);
                      border:1px solid rgba(251,191,36,.2);border-left:3px solid var(--warning);
                      border-radius:var(--radius-sm);padding:.75rem 1rem;margin-bottom:1rem;font-size:.78rem;color:var(--warning);}
        .lowstock-bar.show{display:flex;}

        /* ── Analytics bar ── */
        .analytics-bar{display:flex;flex-wrap:wrap;gap:.65rem;align-items:center;background:var(--surface);
                       border:1px solid var(--border);border-radius:var(--radius);padding:.8rem 1.2rem;margin-bottom:1.1rem;}
        .a-label{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);}

        /* ── Charts ── */
        .charts-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.1rem;}
        @media(max-width:900px){.charts-grid{grid-template-columns:1fr;}}
        .chart-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
                    padding:1.1rem;transition:box-shadow .2s;}
        .chart-card:hover{box-shadow:0 8px 32px rgba(0,0,0,.25);}
        .chart-hd{display:flex;justify-content:space-between;align-items:center;margin-bottom:.9rem;flex-wrap:wrap;gap:.5rem;}
        .chart-title{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:var(--text-faint);}
        .chart-tabs{display:flex;gap:.35rem;}
        .ctab{padding:.27rem .75rem;border-radius:50px;font-size:.7rem;font-weight:700;cursor:pointer;
              border:1px solid var(--border);background:var(--surface-2);color:var(--text-muted);
              font-family:'Sora',sans-serif;transition:.15s;}
        .ctab.ac{background:rgba(251,191,36,.15);color:var(--chicken);border-color:rgba(251,191,36,.3);}
        .ctab.af{background:rgba(96,165,250,.12);color:var(--frozen);border-color:rgba(96,165,250,.25);}
        .chart-container{position:relative;height:220px;width:100%;}
        .chart-container canvas{max-height:220px;}

        /* ── Table ── */
        .table-wrap{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:1.1rem;}
        .table-hd-bar{display:flex;justify-content:space-between;align-items:center;padding:.85rem 1.2rem;
                      border-bottom:1px solid var(--border);background:var(--surface-2);flex-wrap:wrap;gap:.65rem;}
        .table-title{font-size:.78rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.07em;}
        .rec-count{font-family:'DM Mono',monospace;font-size:.7rem;color:var(--text-faint);}
        .cat-tabs{display:flex;border-bottom:1px solid var(--border);}
        .ctab2{padding:.65rem 1.4rem;font-size:.76rem;font-weight:700;cursor:pointer;border:none;
               font-family:'Sora',sans-serif;background:transparent;color:var(--text-muted);transition:.15s;
               border-bottom:2px solid transparent;margin-bottom:-1px;}
        .ctab2:hover{color:var(--text);}
        .ctab2.ac2-ch{color:var(--chicken);border-bottom-color:var(--chicken);}
        .ctab2.ac2-fr{color:var(--frozen);border-bottom-color:var(--frozen);}
        .tbl-scroll{overflow-x:auto;}
        table{width:100%;border-collapse:collapse;min-width:720px;}
        thead tr{border-bottom:1px solid var(--border);}
        th{padding:.65rem 1rem;text-align:right;font-size:.64rem;font-weight:700;text-transform:uppercase;
           letter-spacing:.09em;color:var(--text-faint);background:var(--surface-2);white-space:nowrap;
           cursor:pointer;user-select:none;transition:.15s;position:relative;}
        th:hover{color:var(--teal);background:var(--surface-3);}
        th:first-child,th:nth-child(2){text-align:left;}
        tbody tr{border-bottom:1px solid var(--border);transition:background .12s;}
        tbody tr:last-child{border-bottom:none;}
        tbody tr:hover{background:var(--surface-2);}
        td{padding:.62rem 1rem;font-size:.8rem;color:var(--text);text-align:right;vertical-align:middle;}
        td:first-child,td:nth-child(2){text-align:left;}
        .mono{font-family:'DM Mono',monospace;font-size:.79rem;font-weight:600;}
        .c-sold{color:var(--success);}.c-rem{color:var(--accent);}.c-qs{color:var(--teal);}
        .c-rg{color:var(--success);}.c-ry{color:var(--warning);}.c-rr{color:var(--danger);}.c-rz{color:var(--text-faint);}
        .prod-id-badge{font-family:'DM Mono',monospace;font-size:.62rem;color:var(--text-faint);background:var(--surface-3);padding:.1rem .4rem;border-radius:4px;}
        .rank-badge{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;font-size:.65rem;font-weight:700;margin-right:.4rem;}
        .rank-1{background:rgba(251,191,36,.2);color:var(--warning);}
        .rank-2{background:rgba(148,163,184,.15);color:#94a3b8;}
        .rank-3{background:rgba(180,130,90,.15);color:#b4825a;}
        .rank-worst{background:rgba(248,113,113,.12);color:var(--danger);}
        .totals-bar{display:grid;grid-template-columns:1fr 1fr 1fr;gap:0;border-top:1px solid var(--border);}
        .total-cell{padding:.9rem 1.2rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.4rem;background:var(--surface);}
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

        /* ── Toast ── */
        #toast{position:fixed;bottom:1.5rem;right:1.5rem;z-index:2000;display:flex;align-items:center;gap:.6rem;
               padding:.75rem 1.25rem;border-radius:50px;font-size:.88rem;font-weight:600;font-family:'Sora',sans-serif;
               box-shadow:0 8px 24px rgba(0,0,0,.3);transform:translateY(120%);opacity:0;
               transition:transform .3s cubic-bezier(.34,1.56,.64,1),opacity .3s ease;pointer-events:none;}
        #toast.show{transform:translateY(0);opacity:1;}
        #toast.ok{background:rgba(52,211,153,.15);border:1px solid rgba(52,211,153,.3);color:var(--success);}
        #toast.err{background:rgba(248,113,113,.15);border:1px solid rgba(248,113,113,.3);color:var(--danger);}

        /* ── Modal ── */
        #modalOverlay{position:fixed;inset:0;background:var(--modal-bg,rgba(0,0,0,.65));backdrop-filter:blur(8px);
                      display:none;justify-content:center;align-items:center;z-index:1000;padding:1rem;}
        #modalOverlay.active{display:flex !important;}
        .modal-inner{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
                     padding:2rem 2.25rem;max-width:420px;width:100%;text-align:center;
                     box-shadow:0 25px 60px rgba(0,0,0,.5);animation:popIn .2s cubic-bezier(.34,1.56,.64,1);}
        @keyframes popIn{from{opacity:0;transform:scale(.9)}to{opacity:1;transform:none}}

        ::-webkit-scrollbar{width:6px;height:6px;}
        ::-webkit-scrollbar-track{background:transparent;}
        ::-webkit-scrollbar-thumb{background:var(--surface-3);border-radius:3px;}

        @media(max-width:768px){
            .sidebar{transform:translateX(-100%);}
            .sidebar.open{transform:translateX(0);}
            .sidebar-backdrop.open{display:block;}
            .hamburger{display:flex;}
            .main{margin-left:0;padding:1rem;padding-top:3.75rem;}
            .totals-bar{grid-template-columns:1fr;}
            .total-cell:not(:last-child){border-right:none;border-bottom:1px solid var(--border);}
            .charts-grid{grid-template-columns:1fr;}
            .controls,.analytics-bar{flex-direction:column;align-items:stretch;}
        }
    </style>
</head>
<body>
<div class="app">

<div id="toast"></div>

<button class="hamburger" id="hamburger">☰</button>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<nav class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">📊</div>
        <div><span class="logo-title">Janeth's</span><span class="logo-sub">Business</span></div>
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
        <a href="janeth-input.php"     class="nav-item"><span class="nav-icon">✏️</span>Daily Entry</a>
        <a href="janeth-dashboard.php" class="nav-item active"><span class="nav-icon">📊</span>Dashboard</a>
        <a href="janeth-liquidation.php" class="nav-item"><span class="nav-icon">💵</span>Liquidation</a>
        <?php if ($user_role==='admin'): ?>
        <div class="nav-divider"></div>
        <div class="nav-label">Admin</div>
        <a href="../admin/products.php" class="nav-item"><span class="nav-icon">⚙️</span>Products</a>
        <a href="../admin/users.php"    class="nav-item"><span class="nav-icon">👥</span>Users</a>
        <?php endif; ?>
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
    <div class="page-hd">
        <div class="page-title">Analytics <span>Dashboard</span></div>
        <div class="hd-actions">
            <button class="btn btn-ghost" id="exportPdfBtn">📄 Export PDF</button>
            <button class="btn btn-ghost" id="exportCsvBtn">📎 Export CSV</button>
        </div>
    </div>

    <div class="controls">
        <div class="ctrl-group">
            <span class="ctrl-label">Date</span>
            <select id="dateSelect"></select>
            <button class="btn btn-teal" id="loadBtn">↻ Load</button>
        </div>
        <div class="ctrl-group">
            <input type="text" id="searchInput" placeholder="🔍 Search product…" style="width:175px">
        </div>
    </div>

    <div class="analytics-bar">
        <span class="a-label">Analytics Range</span>
        <input type="date" id="fromDate">
        <span style="color:var(--text-muted);font-size:.8rem">to</span>
        <input type="date" id="toDate">
        <button class="btn btn-teal" id="loadAnalyticsBtn">↻ Load</button>
        <button class="btn btn-ghost" id="thisWeekBtn">This Week</button>
        <button class="btn btn-ghost" id="thisMonthBtn">This Month</button>
        <button class="btn btn-ghost" id="lastMonthBtn">Last Month</button>
    </div>

    <div id="lowStockBar" class="lowstock-bar">⚠️ <span id="lowStockMsg"></span></div>

    <div class="kpi-grid">
        <div class="kpi"><div class="kpi-info"><div class="kpi-label">Daily Sales</div><div class="kpi-val teal" id="kpiSales">₱0.00</div></div><div class="kpi-icon ic-teal">💰</div></div>
        <div class="kpi"><div class="kpi-info"><div class="kpi-label">Remaining Stock Value</div><div class="kpi-val amber" id="kpiRem">₱0.00</div></div><div class="kpi-icon ic-amber">🏷️</div></div>
        <div class="kpi"><div class="kpi-info"><div class="kpi-label">Daily Expenses</div><div class="kpi-val red" id="kpiExp">₱0.00</div></div><div class="kpi-icon ic-red">💸</div></div>
        <div class="kpi"><div class="kpi-info"><div class="kpi-label">Supplier Stock Cost</div><div class="kpi-val red" id="kpiStockCost">₱0.00</div></div><div class="kpi-icon ic-red">🚚</div></div>
        <div class="kpi"><div class="kpi-info"><div class="kpi-label">Est. Net Income</div><div class="kpi-val" id="kpiNet">₱0.00</div></div><div class="kpi-icon ic-green">📈</div></div>
        <div class="kpi"><div class="kpi-info"><div class="kpi-label">🏆 Best Seller</div><div class="kpi-sub" id="kpiBest">—</div></div><div class="kpi-icon ic-star">⭐</div></div>
        <div class="kpi"><div class="kpi-info"><div class="kpi-label">📉 Least Sold</div><div class="kpi-sub" id="kpiWorst">—</div></div><div class="kpi-icon ic-skull">⚠️</div></div>
        <div class="kpi"><div class="kpi-info"><div class="kpi-label">Weekly Sales</div><div class="kpi-val teal" id="kpiWeekly">₱0.00</div><div class="kpi-sub" id="kpiWeeklySub">Last 7 days</div></div><div class="kpi-icon ic-teal">📅</div></div>
        <div class="kpi"><div class="kpi-info"><div class="kpi-label">Monthly Sales</div><div class="kpi-val amber" id="kpiMonthly">₱0.00</div><div class="kpi-sub" id="kpiMonthlySub">This month</div></div><div class="kpi-icon ic-amber">📆</div></div>
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-hd">
                <div class="chart-title">Daily Sales (₱) by Product</div>
                <div class="chart-tabs">
                    <button class="ctab ac" id="ctC" onclick="switchChart('Chicken')">🐔 Chicken</button>
                    <button class="ctab"    id="ctF" onclick="switchChart('Frozen')">❄️ Frozen</button>
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
            <div class="chart-hd"><div class="chart-title">Weekly Sales Trend</div></div>
            <div class="chart-container"><canvas id="weeklyChart"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="chart-hd"><div class="chart-title">Monthly Sales Trend</div></div>
            <div class="chart-container"><canvas id="monthlyChart"></canvas></div>
        </div>
    </div>

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
                <thead>
                    <tr>
                        <th onclick="sortBy('rank')"         style="text-align:left">Rank</th>
                        <th onclick="sortBy('product_name')" style="text-align:left">Product</th>
                        <th onclick="sortBy('price')">Price (₱)</th>
                        <th onclick="sortBy('sold')">Qty Sold</th>
                        <th onclick="sortBy('sold_peso')">Sales (₱)</th>
                        <th onclick="sortBy('remaining_qty')">Remaining</th>
                        <th onclick="sortBy('rem_peso')">Rem. Value (₱)</th>
                    </tr>
                </thead>
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
</div>

<!-- Modal -->
<div id="modalOverlay">
    <div class="modal-inner">
        <div id="modalIcon" style="font-size:2rem;margin-bottom:.75rem">💬</div>
        <p id="modalMsg" style="font-size:.9rem;color:var(--text);margin-bottom:1.5rem;line-height:1.5;font-weight:500">Are you sure?</p>
        <div style="display:flex;gap:.75rem;justify-content:center">
            <button id="modalOk"     class="btn btn-ghost">OK</button>
            <button id="modalCancel" class="btn btn-ghost">Cancel</button>
        </div>
    </div>
</div>

<script>
const API  = 'janeth.php';
const ROLE = '<?= $user_role ?>';

let fullRecords  = [];
let stockEntries = [];   // BUG FIX: was missing — needed for correct net income calc
let expenses     = [];
let analyticsData = null;

// ── Chart theme colors (adapts to light/dark) ────────────────────────────────
function chartColors() {
    const isLight = document.documentElement.getAttribute('data-theme') === 'light';
    return {
        grid:   isLight ? 'rgba(0,0,0,0.07)'  : 'rgba(255,255,255,0.05)',
        tick:   isLight ? '#3a5070'            : '#6b7a93',
        legend: isLight ? '#3a5070'            : '#6b7a93',
    };
}

let curTab = 'Chicken', curChartTab = 'Chicken', curChart2Tab = 'Chicken';
let curPage = 1;
const PER_PAGE = 15;
let salesChart = null, qtyChart = null, weeklyChart = null, monthlyChart = null;
let sortCol = 'sold_peso', sortDir = -1;

const peso = n => '₱' + (parseFloat(n)||0).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
const num  = n => { const v = parseFloat(n)||0; return v%1===0 ? v.toString() : v.toFixed(2); };
const esc  = s => String(s).replace(/[&<>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[m]));

// ── Toast ────────────────────────────────────────────────────────────────────
let toastTimer = null;
function toast(msg, isErr = false) {
    const el = document.getElementById('toast');
    el.textContent = (isErr ? '⚠ ' : '✓ ') + msg;
    el.className   = 'show ' + (isErr ? 'err' : 'ok');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { el.className = isErr ? 'err' : 'ok'; }, 2800);
}

// ── Sidebar ──────────────────────────────────────────────────────────────────
document.getElementById('hamburger').addEventListener('click', () => {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebarBackdrop').classList.add('open');
});
document.getElementById('sidebarBackdrop').addEventListener('click', () => {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarBackdrop').classList.remove('open');
});

// ── Modal ────────────────────────────────────────────────────────────────────
function modal(msg, onOk, onCancel=null, icon='💬', okLabel='OK', cancelLabel='Cancel') {
    const ov = document.getElementById('modalOverlay');
    document.getElementById('modalMsg').textContent  = msg;
    document.getElementById('modalIcon').textContent = icon;
    const okEl  = document.getElementById('modalOk');
    const canEl = document.getElementById('modalCancel');
    okEl.textContent  = okLabel;
    canEl.textContent = cancelLabel;
    canEl.style.display = cancelLabel ? '' : 'none';
    ov.classList.add('active');
    const close = () => ov.classList.remove('active');
    const ok  = okEl.cloneNode(true);
    const can = canEl.cloneNode(true);
    okEl.replaceWith(ok); canEl.replaceWith(can);
    ok.addEventListener('click',  () => { close(); onOk  && onOk();  });
    can.addEventListener('click', () => { close(); onCancel && onCancel(); });
}

function showError(msg) {
    document.getElementById('dashBody').innerHTML = `<tr class="state-row"><td colspan="7">⚠️ ${msg}</td></tr>`;
    ['kpiSales','kpiRem','kpiExp','kpiStockCost','kpiNet'].forEach(id => document.getElementById(id).textContent = '₱0.00');
    ['kpiBest','kpiWorst'].forEach(id => document.getElementById(id).textContent = '—');
    ['ttSold','ttRem','ttExp'].forEach(id => document.getElementById(id).textContent = '₱0.00');
}

// ── Rank helpers ─────────────────────────────────────────────────────────────
function getRankCls(i, n)   { if(i===0) return 'rank-1'; if(i===1) return 'rank-2'; if(i===2) return 'rank-3'; if(i===n-1&&n>3) return 'rank-worst'; return ''; }
function getRankLabel(i, n) { if(i===0) return '🥇'; if(i===1) return '🥈'; if(i===2) return '🥉'; if(i===n-1&&n>3) return '📉'; return `#${i+1}`; }
function getQtyCls(rem, thr){ if(rem===0) return 'c-rz'; if(rem<=Math.max(3,Math.floor(thr*0.3))) return 'c-rr'; if(rem<=thr) return 'c-ry'; return 'c-rg'; }

function enrichRecords() {
    return fullRecords.map(r => ({
        ...r,
        sold:         +r.sold,
        remaining_qty:+r.remaining_qty,
        price:        +r.price,
        sold_peso:    (+r.sold) * (+r.price),
        rem_peso:     (+r.remaining_qty) * (+r.price),
        rank: 0
    }));
}

// ── Render all KPIs + charts + table ─────────────────────────────────────────
function renderAll() {
    const term    = document.getElementById('searchInput').value.toLowerCase();
    const visible = enrichRecords().filter(r => r.product_name.toLowerCase().includes(term));
    let totalSold = 0, totalRem = 0;
    visible.forEach(r => { totalSold += r.sold_peso; totalRem += r.rem_peso; });

    const expTotal      = expenses.reduce((s,e) => s + parseFloat(e.amount||0), 0);
    // BUG FIX: net income was ignoring stock entry costs — now deducts both
    const stockCostTotal= stockEntries.reduce((s,e) => s + parseFloat(e.total_cost||0), 0);
    const net           = totalSold - expTotal - stockCostTotal;

    document.getElementById('kpiSales').textContent     = peso(totalSold);
    document.getElementById('kpiRem').textContent       = peso(totalRem);
    document.getElementById('kpiExp').textContent       = peso(expTotal);
    document.getElementById('kpiStockCost').textContent = peso(stockCostTotal);

    // BUG FIX: kpiNet className must be fully reset each time, not appended
    const netEl     = document.getElementById('kpiNet');
    netEl.textContent = peso(net);
    netEl.className   = 'kpi-val ' + (net >= 0 ? 'green' : 'red');

    const ranked = [...visible].sort((a,b) => b.sold_peso - a.sold_peso);
    if (ranked.length) {
        const best  = ranked[0];
        const worst = ranked[ranked.length-1];
        document.getElementById('kpiBest').innerHTML  = `<b style="font-size:1.1rem;font-family:'DM Mono',monospace;color:var(--success)">${peso(best.sold_peso)}</b><br><span style="color:var(--text-muted);font-size:.74rem">${esc(best.product_name)}</span>`;
        document.getElementById('kpiWorst').innerHTML = `<b style="font-size:1.1rem;font-family:'DM Mono',monospace;color:var(--danger)">${peso(worst.sold_peso)}</b><br><span style="color:var(--text-muted);font-size:.74rem">${esc(worst.product_name)}</span>`;
    }

    const low    = visible.filter(r => r.remaining_qty > 0 && r.remaining_qty <= (r.low_stock_threshold||10));
    const lsBar  = document.getElementById('lowStockBar');
    if (low.length) {
        document.getElementById('lowStockMsg').textContent = `Stock alert: ${low.map(r=>`${r.product_name} (${r.remaining_qty} left)`).join(', ')}`;
        lsBar.classList.add('show');
    } else {
        lsBar.classList.remove('show');
    }

    renderChart(curChartTab);
    renderChart2(curChart2Tab);
    renderTable(curTab);
}

function getTabRecords(tab) {
    const term = document.getElementById('searchInput').value.toLowerCase();
    return enrichRecords().filter(r => r.product_category === tab && r.product_name.toLowerCase().includes(term));
}

// ── Charts ───────────────────────────────────────────────────────────────────
function renderChart(tab) {
    const recs = getTabRecords(tab).filter(r => r.sold > 0).sort((a,b) => b.sold_peso - a.sold_peso);
    const ctx  = document.getElementById('salesChart').getContext('2d');
    if (salesChart) salesChart.destroy();
    const cc   = chartColors();
    if (!recs.length) { drawEmpty(ctx, `No ${tab} sales`); return; }
    const isCh = tab === 'Chicken';
    salesChart = new Chart(ctx, { type:'bar',
        data:{ labels: recs.map(r => r.product_name.length>16 ? r.product_name.slice(0,14)+'…' : r.product_name),
               datasets:[{ label:'Sales (₱)', data:recs.map(r => r.sold_peso.toFixed(2)),
                           backgroundColor: isCh ? 'rgba(251,191,36,.7)' : 'rgba(96,165,250,.7)',
                           borderColor:     isCh ? 'rgba(251,191,36,1)'  : 'rgba(96,165,250,1)',
                           borderWidth:1, borderRadius:6, barPercentage:.65 }]},
        options: chartOpts(cc, true) });
}

function renderChart2(tab) {
    const recs = getTabRecords(tab).sort((a,b) => b.sold - a.sold);
    const ctx  = document.getElementById('qtyChart').getContext('2d');
    if (qtyChart) qtyChart.destroy();
    const cc   = chartColors();
    if (!recs.length) { drawEmpty(ctx, `No ${tab} data`); return; }
    qtyChart = new Chart(ctx, { type:'bar',
        data:{ labels: recs.map(r => r.product_name.length>14 ? r.product_name.slice(0,12)+'…' : r.product_name),
               datasets:[
                   { label:'Qty Sold',   data:recs.map(r=>r.sold),          backgroundColor:'rgba(52,211,153,.7)',  borderColor:'rgba(52,211,153,1)',  borderWidth:1, borderRadius:4 },
                   { label:'Remaining',  data:recs.map(r=>r.remaining_qty), backgroundColor:'rgba(245,166,35,.45)', borderColor:'rgba(245,166,35,.8)', borderWidth:1, borderRadius:4 }]},
        options: chartOpts(cc, false) });
}

function renderWeeklyChart(data) {
    const ctx = document.getElementById('weeklyChart').getContext('2d');
    if (weeklyChart) weeklyChart.destroy();
    const cc  = chartColors();
    if (!data || !data.length) { drawEmpty(ctx, 'No weekly data'); return; }
    weeklyChart = new Chart(ctx, { type:'line',
        data:{ labels: data.map(d => d.record_date),
               datasets:[{ label:'Daily Sales (₱)', data:data.map(d => parseFloat(d.day_sales)||0),
                           borderColor:'rgba(41,182,200,1)', backgroundColor:'rgba(41,182,200,.1)',
                           fill:true, tension:.3, pointBackgroundColor:'rgba(41,182,200,1)', pointRadius:4 }]},
        options: chartOpts(cc, true) });
}

function renderMonthlyChart(data) {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    if (monthlyChart) monthlyChart.destroy();
    const cc  = chartColors();
    if (!data || !data.length) { drawEmpty(ctx, 'No monthly data'); return; }
    monthlyChart = new Chart(ctx, { type:'bar',
        data:{ labels: data.map(d => d.month),
               datasets:[{ label:'Monthly Sales (₱)', data:data.map(d => parseFloat(d.month_sales)||0),
                           backgroundColor:'rgba(167,139,250,.65)', borderColor:'rgba(167,139,250,1)', borderWidth:1, borderRadius:6 }]},
        options: chartOpts(cc, true) });
}

function chartOpts(cc, showPeso) {
    return {
        responsive:true, maintainAspectRatio:true,
        plugins:{ legend:{ labels:{ color:cc.legend, font:{family:'Sora',size:11} } },
                  tooltip:{ callbacks:{ label: showPeso ? c => ' ₱'+parseFloat(c.parsed.y).toLocaleString('en-PH',{minimumFractionDigits:2}) : undefined } } },
        scales:{ x:{ ticks:{color:cc.tick,font:{family:'Sora',size:10}}, grid:{color:cc.grid} },
                 y:{ ticks:{color:cc.tick,font:{family:'DM Mono',size:10}, callback: showPeso ? v=>'₱'+v.toLocaleString('en-PH') : undefined}, grid:{color:cc.grid}, beginAtZero:true } }
    };
}

function drawEmpty(ctx, msg) {
    ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
    const cc = chartColors();
    ctx.font      = '13px Sora,sans-serif';
    ctx.fillStyle = cc.tick;
    ctx.textAlign = 'center';
    ctx.fillText(msg, ctx.canvas.width/2, 90);
}

// ── Table ────────────────────────────────────────────────────────────────────
// BUG FIX: sortBy('rank') was broken because rank=0 means BEST but
// numeric sort ascending put rank-0 last. Fixed by inverting rank for sort.
function sortBy(col) {
    if (sortCol === col) sortDir *= -1;
    else { sortCol = col; sortDir = col === 'rank' ? 1 : -1; }
    renderTable(curTab);
}

function renderTable(tab) {
    let recs   = getTabRecords(tab);
    const sorted = [...recs].sort((a,b) => b.sold_peso - a.sold_peso);
    // rank = index in sold_peso-desc order (0=best)
    recs = recs.map(r => ({ ...r, rank: sorted.findIndex(s => s.product_id === r.product_id) }));
    recs.sort((a,b) => {
        const av = a[sortCol], bv = b[sortCol];
        if (av === bv) return 0;
        return (av > bv ? 1 : -1) * sortDir;
    });

    document.getElementById('recCount').textContent = `${recs.length} item${recs.length!==1?'s':''}`;
    let tabSold = 0, tabRem = 0;
    recs.forEach(r => { tabSold += r.sold_peso; tabRem += r.rem_peso; });
    const expTotal = expenses.reduce((s,e) => s + parseFloat(e.amount||0), 0);
    document.getElementById('ttSold').textContent = peso(tabSold);
    document.getElementById('ttRem').textContent  = peso(tabRem);
    document.getElementById('ttExp').textContent  = peso(expTotal);

    if (!recs.length) {
        document.getElementById('dashBody').innerHTML = '<tr class="state-row"><td colspan="7">No records found.</td></tr>';
        document.getElementById('pgCtrl').innerHTML   = '';
        return;
    }
    const n   = recs.length;
    const tot = Math.ceil(n / PER_PAGE);
    if (curPage > tot) curPage = 1;
    const page = recs.slice((curPage-1)*PER_PAGE, curPage*PER_PAGE);
    document.getElementById('dashBody').innerHTML = page.map(r => {
        const cls  = getRankCls(r.rank, n);
        const lbl  = getRankLabel(r.rank, n);
        const qcls = getQtyCls(r.remaining_qty, r.low_stock_threshold||10);
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

function renderPg(tot) {
    const c = document.getElementById('pgCtrl');
    if (tot <= 1) { c.innerHTML=''; return; }
    let h = '';
    if (curPage > 1) h += `<button class="pg-btn" onclick="pg(1)">«</button><button class="pg-btn" onclick="pg(${curPage-1})">‹</button>`;
    for (let i = Math.max(1,curPage-2); i <= Math.min(tot,curPage+2); i++)
        h += `<button class="pg-btn ${i===curPage?'active':''}" onclick="pg(${i})">${i}</button>`;
    if (curPage < tot) h += `<button class="pg-btn" onclick="pg(${curPage+1})">›</button><button class="pg-btn" onclick="pg(${tot})">»</button>`;
    c.innerHTML = h;
}
function pg(p) { curPage = p; renderTable(curTab); }

function switchTab(t) {
    curTab = t; curPage = 1;
    document.getElementById('tab2C').className = t==='Chicken' ? 'ctab2 ac2-ch' : 'ctab2';
    document.getElementById('tab2F').className = t==='Frozen'  ? 'ctab2 ac2-fr' : 'ctab2';
    renderTable(t);
}
function switchChart(t) {
    curChartTab = t;
    document.getElementById('ctC').className = t==='Chicken' ? 'ctab ac' : 'ctab';
    document.getElementById('ctF').className = t==='Frozen'  ? 'ctab af' : 'ctab';
    renderChart(t);
}
function switchChart2(t) {
    curChart2Tab = t;
    document.getElementById('ct2C').className = t==='Chicken' ? 'ctab ac' : 'ctab';
    document.getElementById('ct2F').className = t==='Frozen'  ? 'ctab af' : 'ctab';
    renderChart2(t);
}

// ── Load dashboard data ───────────────────────────────────────────────────────
async function loadDashboard() {
    const date = document.getElementById('dateSelect').value;
    if (!date) return showError('Please select a date');
    document.getElementById('dashBody').innerHTML = '<tr class="state-row"><td colspan="7">⏳ Loading…</td></tr>';
    const btn = document.getElementById('loadBtn');
    btn.disabled = true;
    try {
        // BUG FIX: also fetch stock_entries so net income includes supplier costs
        const [recRes, expRes, seRes] = await Promise.all([
            fetch(`${API}?date=${date}&for=dashboard`),
            fetch(`${API}?expenses=${date}`),
            fetch(`${API}?stock_entries=${date}`)
        ]);
        const recData = await recRes.json();
        const expData = await expRes.json();
        const seData  = await seRes.json();
        expenses     = expData.expenses      || [];
        stockEntries = seData.stock_entries  || [];
        fullRecords  = recData.records       || [];
        if (!fullRecords.length) { showError(`No records found for ${date}.`); return; }
        curPage = 1;
        renderAll();
        document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();
    } catch(e) {
        console.error(e);
        showError('Failed to load data.');
    } finally {
        btn.disabled = false;
    }
}

// ── Load analytics ────────────────────────────────────────────────────────────
async function loadAnalytics() {
    const from = document.getElementById('fromDate').value;
    const to   = document.getElementById('toDate').value;
    if (!from || !to) return;
    try {
        const res  = await fetch(`${API}?analytics=1&from=${from}&to=${to}`);
        const data = await res.json();
        analyticsData = data;
        const weeklyTotal  = (data.weekly||[]).reduce((s,d) => s + parseFloat(d.day_sales||0), 0);
        const monthlyTotal = (data.monthly||[]).reduce((s,d) => s + parseFloat(d.month_sales||0), 0);
        document.getElementById('kpiWeekly').textContent     = peso(weeklyTotal);
        document.getElementById('kpiWeeklySub').textContent  = `${(data.weekly||[]).length} day(s) in last 7`;
        document.getElementById('kpiMonthly').textContent    = peso(monthlyTotal);
        document.getElementById('kpiMonthlySub').textContent = `${(data.monthly||[]).length} month(s) in range`;
        renderWeeklyChart(data.weekly||[]);
        renderMonthlyChart(data.monthly||[]);
    } catch(e) { console.error(e); }
}

// ── Date selector ─────────────────────────────────────────────────────────────
async function loadDateSelector() {
    const sel  = document.getElementById('dateSelect');
    const last = localStorage.getItem('janeth_date');
    sel.innerHTML = '<option>Loading…</option>';
    try {
        const res  = await fetch(`${API}?list_dates=1`);
        const data = await res.json();
        const dates = data.dates || [];

        // BUG FIX: if the saved date isn't in the list (no records yet),
        // add it anyway so the dashboard matches the input page's date.
        if (last && !dates.includes(last)) {
            dates.unshift(last);
        }

        if (dates.length) {
            sel.innerHTML = '';
            dates.forEach(d => {
                const o = document.createElement('option');
                o.value = d; o.textContent = d;
                sel.appendChild(o);
            });
            sel.value = last && dates.includes(last) ? last : dates[0];
            await loadDashboard();
            await loadAnalytics();
        } else {
            sel.innerHTML = '<option>No saved data</option>';
            showError('No records found. Go to Daily Entry page to add data.');
        }
    } catch(e) {
        sel.innerHTML = '<option>Error</option>';
        showError('Failed to load dates. Check server connection.');
    }
}

// ── Date range helpers ────────────────────────────────────────────────────────
function todayStr()     { return new Date().toISOString().split('T')[0]; }
function monthStart()   { const d=new Date(); d.setDate(1); return d.toISOString().split('T')[0]; }
function weekStart()    { const d=new Date(); d.setDate(d.getDate()-6); return d.toISOString().split('T')[0]; }
function lastMonthRange() {
    const d = new Date(); d.setDate(0);
    const end = d.toISOString().split('T')[0];
    d.setDate(1);
    return { start: d.toISOString().split('T')[0], end };
}
function setDateRange(from, to) {
    document.getElementById('fromDate').value = from;
    document.getElementById('toDate').value   = to;
    loadAnalytics();
}

// ── Event listeners ───────────────────────────────────────────────────────────
document.getElementById('thisWeekBtn').addEventListener('click',  () => setDateRange(weekStart(), todayStr()));
document.getElementById('thisMonthBtn').addEventListener('click', () => setDateRange(monthStart(), todayStr()));
document.getElementById('lastMonthBtn').addEventListener('click', () => { const r=lastMonthRange(); setDateRange(r.start,r.end); });
document.getElementById('loadBtn').addEventListener('click', loadDashboard);
document.getElementById('loadAnalyticsBtn').addEventListener('click', loadAnalytics);
document.getElementById('searchInput').addEventListener('input', () => { curPage=1; renderAll(); });

// BUG FIX: date select change had no listener — selecting a date did nothing
document.getElementById('dateSelect').addEventListener('change', loadDashboard);

// BUG FIX: PDF export used native alert() — replaced with modal()
document.getElementById('exportPdfBtn').addEventListener('click', () => {
    if (!fullRecords.length) return modal('No data to export. Please load a date first.', null, null, '⚠️', 'OK', '');
    const date    = document.getElementById('dateSelect').value;
    const {jsPDF} = window.jspdf;
    const doc     = new jsPDF({orientation:'landscape', unit:'mm', format:'a4'});
    doc.setFontSize(16); doc.setTextColor(40,40,40);
    doc.text("Janeth's Business — Daily Report", 14, 14);
    doc.setFontSize(10); doc.setTextColor(100,100,100);
    doc.text(`Date: ${date}`, 14, 21);
    doc.text(`Generated: ${new Date().toLocaleString('en-PH')}`, 14, 27);
    const rows = enrichRecords();
    doc.autoTable({
        head:[['Category','Product','Price','Qty Sold','Sales (₱)','Remaining','Rem. Value (₱)']],
        body:rows.map(r=>[r.product_category,r.product_name,peso(r.price),r.sold,peso(r.sold_peso),r.remaining_qty,peso(r.rem_peso)]),
        startY:32, styles:{fontSize:8,font:'helvetica'},
        headStyles:{fillColor:[41,182,200],textColor:255,fontStyle:'bold'},
        alternateRowStyles:{fillColor:[245,248,255]}, margin:{left:14,right:14}
    });
    let y = doc.lastAutoTable.finalY + 6;
    if (expenses.length) {
        doc.setFontSize(11); doc.setTextColor(40,40,40); doc.text('Daily Expenses', 14, y); y += 4;
        doc.autoTable({
            head:[['Category','Description','Amount (₱)']],
            body:expenses.map(e=>[e.category,e.description,peso(e.amount)]),
            startY:y, styles:{fontSize:8},
            headStyles:{fillColor:[167,139,250],textColor:255,fontStyle:'bold'}, margin:{left:14,right:14}
        });
        y = doc.lastAutoTable.finalY + 4;
    }
    const ts = rows.reduce((s,r) => s+r.sold_peso, 0);
    const te = expenses.reduce((s,e) => s+parseFloat(e.amount||0), 0);
    const sc = stockEntries.reduce((s,e) => s+parseFloat(e.total_cost||0), 0);
    doc.setFontSize(9); doc.setTextColor(40,40,40);
    doc.text(`Total Sales: ${peso(ts)}   Expenses: ${peso(te)}   Stock Cost: ${peso(sc)}   Est. Net: ${peso(ts-te-sc)}`, 14, y+2);
    doc.save(`janeth_report_${date}.pdf`);
    toast('PDF exported!');
});

document.getElementById('exportCsvBtn').addEventListener('click', () => {
    if (!fullRecords.length) return toast('No data to export.', true);
    const date = document.getElementById('dateSelect').value;
    const rows = [
        ['Category','Product','Price','Qty Sold','Sold (PHP)','Qty Remaining','Remaining (PHP)'],
        ...enrichRecords().map(r=>[r.product_category,r.product_name,r.price.toFixed(2),r.sold,r.sold_peso.toFixed(2),r.remaining_qty,r.rem_peso.toFixed(2)]),
        [],['Expenses'],['Category','Description','Amount'],
        ...expenses.map(e=>[e.category,e.description,parseFloat(e.amount).toFixed(2)])
    ];
    const uri = 'data:text/csv;charset=utf-8,' + encodeURIComponent(rows.map(r=>r.join(',')).join('\n'));
    Object.assign(document.createElement('a'), {href:uri, download:`janeth_${date}.csv`}).click();
    toast('CSV exported!');
});

document.getElementById('logoutBtn').addEventListener('click', () => {
    modal('Are you sure you want to sign out?',
        () => { window.location.href = 'logout.php'; },
        null, '👋', 'Yes, sign out', 'Stay');
});

// ── Theme bridge ──────────────────────────────────────────────────────────────
// BUG FIX: the old code wrapped toggleTheme() at script-run time, but
// theme.js hadn't defined it yet (it was at the bottom of body).
// Now theme.js is in <head>, so it's always defined before this runs.
// We wrap it here (after DOM ready) so chart re-renders work correctly.
document.addEventListener('DOMContentLoaded', () => {
    const _orig = window.toggleTheme;
    window.toggleTheme = function() {
        _orig();
        // Re-render charts with updated colors after theme switch
        setTimeout(() => {
            if (fullRecords.length)  { renderChart(curChartTab); renderChart2(curChart2Tab); }
            if (analyticsData)       { renderWeeklyChart(analyticsData.weekly||[]); renderMonthlyChart(analyticsData.monthly||[]); }
        }, 50); // small delay lets CSS variables update first
    };
});

// ── Boot ──────────────────────────────────────────────────────────────────────
document.getElementById('fromDate').value = monthStart();
document.getElementById('toDate').value   = todayStr();
loadDateSelector();
</script>
</body>
</html>