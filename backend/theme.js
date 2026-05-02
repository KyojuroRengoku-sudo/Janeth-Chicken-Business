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
        for (const [k, v] of Object.entries(vars)) document.documentElement.style.setProperty(k, v);
        document.documentElement.setAttribute('data-theme', mode);
        localStorage.setItem('jb_theme', mode);
        const btn = document.getElementById('themeToggle');
        if (btn) btn.innerHTML = mode === 'light' ? '🌙 Dark' : '☀️ Light';
    }
    window.toggleTheme = function () {
        applyTheme((localStorage.getItem('jb_theme') || 'dark') === 'dark' ? 'light' : 'dark');
    };
    applyTheme(localStorage.getItem('jb_theme') || 'dark');
})();