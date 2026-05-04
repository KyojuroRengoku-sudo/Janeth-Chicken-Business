/**
 * theme.js  –  Shared light/dark mode for Janeth's Business
 */
(function () {
    const DARK = {
        '--bg': '#080c14', '--surface': '#0f1724', '--surface-2': '#161f30', '--surface-3': '#1c2840',
        '--border': 'rgba(255,255,255,0.07)', '--border-hi': 'rgba(255,255,255,0.13)',
        '--text': '#e2e8f4', '--text-muted': '#8a9bbf', '--text-faint': '#4a5a72',
        '--shadow': 'rgba(0,0,0,0.5)', '--input-bg': '#1c2840',
    };
    const LIGHT = {
        '--bg': '#f0f4f9', '--surface': '#ffffff', '--surface-2': '#e8eef5', '--surface-3': '#d8e3ef',
        '--border': 'rgba(0,0,0,0.09)', '--border-hi': 'rgba(0,0,0,0.16)',
        '--text': '#0d1b2a', '--text-muted': '#4a6080', '--text-faint': '#7090b0',
        '--shadow': 'rgba(0,0,0,0.13)', '--input-bg': '#f0f4f9',
    };

    function applyTheme(mode) {
        const vars = mode === 'light' ? LIGHT : DARK;
        for (const [k, v] of Object.entries(vars)) {
            document.documentElement.style.setProperty(k, v);
        }
        document.documentElement.setAttribute('data-theme', mode);
        localStorage.setItem('jb_theme', mode);

        // Fix all select options for light mode
        if (mode === 'light') {
            injectSelectFix();
        } else {
            removeSelectFix();
        }

        // Update all toggle buttons on the page
        document.querySelectorAll('#themeToggle').forEach(btn => {
            btn.innerHTML = mode === 'light' ? '🌙 Dark' : '☀️ Light';
        });
    }

    // Inject a style tag that forces select option colours in light mode
    function injectSelectFix() {
        let el = document.getElementById('_jb_select_fix');
        if (!el) {
            el = document.createElement('style');
            el.id = '_jb_select_fix';
            document.head.appendChild(el);
        }
        el.textContent = `
            select option { background: #e8eef5 !important; color: #0d1b2a !important; }
            input[type="number"], input[type="text"], input[type="date"], input[type="password"], select {
                background: #e8eef5 !important; color: #0d1b2a !important; border-color: rgba(0,0,0,0.09) !important;
            }
            input::placeholder { color: #7090b0 !important; }
        `;
    }
    function removeSelectFix() {
        const el = document.getElementById('_jb_select_fix');
        if (el) el.remove();
    }

    window.toggleTheme = function () {
        const current = localStorage.getItem('jb_theme') || 'dark';
        applyTheme(current === 'dark' ? 'light' : 'dark');
    };

    // Apply on load — use saved preference, default dark
    applyTheme(localStorage.getItem('jb_theme') || 'dark');

    // Re-apply after DOM is fully ready (handles buttons injected after script)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            applyTheme(localStorage.getItem('jb_theme') || 'dark');
        });
    }
})();
