/**
 * theme.js  –  Shared light/dark mode for Janeth's Business
 * Fixed: properly covers all CSS variables used across all pages,
 * modal backgrounds, table headers, badges, chips, and input states.
 */
(function () {
    const DARK = {
        '--bg':          '#080c14',
        '--surface':     '#0f1724',
        '--surface-2':   '#161f30',
        '--surface-3':   '#1c2840',
        '--border':      'rgba(255,255,255,0.07)',
        '--border-hi':   'rgba(255,255,255,0.13)',
        '--text':        '#e2e8f4',
        '--text-muted':  '#8a9bbf',
        '--text-faint':  '#4a5a72',
        '--shadow':      'rgba(0,0,0,0.5)',
        '--input-bg':    '#1c2840',
        '--modal-bg':    'rgba(0,0,0,0.65)',
    };

    const LIGHT = {
        '--bg':          '#f0f4f9',
        '--surface':     '#ffffff',
        '--surface-2':   '#e8eef5',
        '--surface-3':   '#d0daea',
        '--border':      'rgba(0,0,0,0.09)',
        '--border-hi':   'rgba(0,0,0,0.16)',
        '--text':        '#0d1b2a',
        '--text-muted':  '#3a5070',
        '--text-faint':  '#6080a0',
        '--shadow':      'rgba(0,0,0,0.13)',
        '--input-bg':    '#f0f4f9',
        '--modal-bg':    'rgba(0,0,0,0.45)',
    };

    function applyTheme(mode) {
        const vars = mode === 'light' ? LIGHT : DARK;
        for (const [k, v] of Object.entries(vars)) {
            document.documentElement.style.setProperty(k, v);
        }
        document.documentElement.setAttribute('data-theme', mode);
        localStorage.setItem('jb_theme', mode);

        // Inject/remove the override style block
        if (mode === 'light') {
            injectLightFix();
        } else {
            removeLightFix();
        }

        // Update all theme toggle buttons
        document.querySelectorAll('#themeToggle').forEach(btn => {
            btn.innerHTML = mode === 'light' ? 'Dark mode' : 'Light mode';
        });
    }

    function injectLightFix() {
        let el = document.getElementById('_jb_theme_fix');
        if (!el) {
            el = document.createElement('style');
            el.id = '_jb_theme_fix';
            document.head.appendChild(el);
        }
        el.textContent = `
            /* ── Inputs & selects ── */
            input[type="number"],
            input[type="text"],
            input[type="date"],
            input[type="password"],
            select {
                background: #e8eef5 !important;
                color: #0d1b2a !important;
                border-color: rgba(0,0,0,0.12) !important;
            }
            input::placeholder { color: #6080a0 !important; }
            select option { background: #e8eef5 !important; color: #0d1b2a !important; }

            /* ── Tables ── */
            th { background: #dde6f0 !important; color: #3a5070 !important; }
            tbody tr:hover:not(.total-row) { background: #dde6f0 !important; }
            .total-row td { background: rgba(245,166,35,0.08) !important; }

            /* ── Sidebar & nav ── */
            .sidebar { background: #ffffff !important; border-right-color: rgba(0,0,0,0.09) !important; }
            .nav-item:hover { background: #dde6f0 !important; }
            .nav-item.active { background: rgba(41,182,200,0.10) !important; }

            /* ── Cards / sections ── */
            .section-hd,
            .se-hd,
            .exp-hd,
            .exp-hd,
            .chooser-hd,
            .date-hero,
            .controls,
            .summary-bar { background: #ffffff !important; }

            .section-wrap,
            .se-section,
            .exp-section { background: #ffffff !important; }

            /* ── Chips & badges ── */
            .as-chip { background: #e8eef5 !important; border-color: rgba(0,0,0,0.12) !important; }
            .as-chip.saving { background: rgba(41,182,200,0.12) !important; }
            .as-chip.saved  { background: rgba(52,211,153,0.10) !important; }
            .as-chip.error  { background: rgba(248,113,113,0.10) !important; }

            .s-none { background: #e8eef5 !important; border-color: rgba(0,0,0,0.10) !important; }

            /* ── Modal ── */
            .modal { background: #ffffff !important; box-shadow: 0 20px 60px rgba(0,0,0,0.25) !important; }
            .modal-msg { color: #0d1b2a !important; }

            /* ── Toggle switch track ── */
            .toggle-sw input:not(:checked) { background: #c0cfe0 !important; }

            /* ── Number inputs in tables ── */
            .num-input {
                background: #e8eef5 !important;
                border-color: rgba(0,0,0,0.12) !important;
                color: #0d1b2a !important;
            }
            .num-input:focus {
                background: rgba(41,182,200,0.08) !important;
                border-color: #29b6c8 !important;
            }

            /* ── Chooser items ── */
            .chooser-item { background: #e8eef5 !important; border-color: rgba(0,0,0,0.09) !important; }
            .chooser-item:hover { background: rgba(41,182,200,0.10) !important; }
            .chooser-item.active { background: rgba(52,211,153,0.09) !important; }

            /* ── Expense / stock entry rows ── */
            .se-item:hover, .exp-item:hover { background: #dde6f0 !important; }

            /* ── Total bars ── */
            .se-total-bar, .exp-total-bar {
                background: rgba(248,113,113,0.05) !important;
                border-top-color: rgba(248,113,113,0.15) !important;
            }

            /* ── Scrollbar ── */
            ::-webkit-scrollbar-thumb { background: #c0cfe0 !important; }

            /* ── Logo icon shadow ── */
            .logo-icon { box-shadow: 0 4px 12px rgba(41,182,200,0.2) !important; }

            /* ── Btn ghost ── */
            .btn-ghost {
                background: #ffffff !important;
                border-color: rgba(0,0,0,0.12) !important;
                color: #3a5070 !important;
            }
            .btn-ghost:hover {
                background: rgba(41,182,200,0.08) !important;
                border-color: #29b6c8 !important;
                color: #29b6c8 !important;
            }

            /* ── Logout button ── */
            .btn-logout {
                background: rgba(248,113,113,0.07) !important;
                border-color: rgba(248,113,113,0.20) !important;
            }
            .btn-logout:hover { background: rgba(248,113,113,0.14) !important; }

            /* ── Prod ID badge ── */
            .prod-id { background: #d0daea !important; }

            /* ── Summary bar ── */
            .sum-val { color: #3a5070 !important; }
            .sum-val.teal  { color: #1a9aab !important; }
            .sum-val.green { color: #16a36a !important; }
            .sum-val.red   { color: #dc4545 !important; }
            .sum-val.amber { color: #c47d00 !important; }
        `;
    }

    function removeLightFix() {
        const el = document.getElementById('_jb_theme_fix');
        if (el) el.remove();
    }

    window.toggleTheme = function () {
        const current = localStorage.getItem('jb_theme') || 'dark';
        applyTheme(current === 'dark' ? 'light' : 'dark');
    };

    // Apply immediately (before paint) to avoid flash
    applyTheme(localStorage.getItem('jb_theme') || 'dark');

    // Re-apply once DOM is ready to catch dynamically-added buttons
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            applyTheme(localStorage.getItem('jb_theme') || 'dark');
        });
    }
})();