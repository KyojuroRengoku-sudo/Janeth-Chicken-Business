<?php session_start(); if (isset($_SESSION["user_id"])) { header("Location: janeth-input.php"); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Register · Janeth's Business</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#080c14; --surface:#0f1724; --surface-2:#161f30; --surface-3:#1c2840;
            --border:rgba(255,255,255,0.06); --border-focus:rgba(41,182,200,0.5);
            --accent:#f5a623; --accent-dim:rgba(245,166,35,0.15);
            --teal:#29b6c8; --teal-dim:rgba(41,182,200,0.1); --teal-glow:rgba(41,182,200,0.28); --teal-dark:#1a9aab;
            --text:#e2e8f4; --text-muted:#5d6e87; --text-faint:#2e3d52;
            --danger:#f87171; --danger-dim:rgba(248,113,113,0.08);
            --success:#34d399; --success-dim:rgba(52,211,153,0.1);
        }
        [data-theme="light"] { --bg:#f0f4f9;--surface:#ffffff;--surface-2:#e8eef5;--surface-3:#d8e3ef;--border:rgba(0,0,0,0.09);--border-focus:rgba(41,182,200,0.6);--text:#0d1b2a;--text-muted:#4a6080;--text-faint:#7090b0;--danger-dim:rgba(248,113,113,0.1);--success-dim:rgba(52,211,153,0.1); }
        [data-theme="light"] .card { box-shadow: 0 20px 60px rgba(0,0,0,0.1); }
        [data-theme="light"] .field input,[data-theme="light"] .field select { background:var(--surface-2); }
        [data-theme="light"] .field select option { background:#e8eef5; }
        [data-theme="light"] .info-box { background:var(--surface-2); }
        .brand-name { font-size:1.65rem !important; }
        .field label { font-size:.74rem !important; }
        .field input,.field select { font-size:.95rem !important; padding:.9rem 1rem !important; }
        .btn-register { font-size:.95rem !important; }
        .alert { font-size:.85rem !important; }
        .info-box { font-size:.82rem !important; }
        .login-link { font-size:.84rem !important; }
        #themeToggle { position:fixed;top:1rem;right:1rem;z-index:10;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);color:#ccc;border-radius:50px;padding:.38rem .85rem;font-size:.78rem;font-weight:600;cursor:pointer;font-family:'Sora',sans-serif;backdrop-filter:blur(8px);transition:.18s; }
        [data-theme="light"] #themeToggle { background:rgba(0,0,0,0.06);border-color:rgba(0,0,0,0.12);color:#4a6080; }
        #themeToggle:hover { border-color:var(--teal);color:var(--teal); }
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Sora',sans-serif; background:var(--bg); min-height:100vh;
            display:flex; align-items:center; justify-content:center; padding:1.5rem;
            overflow:hidden; position:relative;
        }
        .bg-mesh {
            position:fixed; inset:0; z-index:0; pointer-events:none;
            background:
                radial-gradient(ellipse 600px 500px at 10% 20%, rgba(41,182,200,0.06) 0%, transparent 70%),
                radial-gradient(ellipse 500px 400px at 90% 80%, rgba(245,166,35,0.04) 0%, transparent 70%);
            animation:meshDrift 18s ease-in-out infinite alternate;
        }
        @keyframes meshDrift { from{transform:scale(1)} to{transform:scale(1.05) translateX(-15px)} }
        body::before {
            content:''; position:fixed; inset:0; z-index:0; pointer-events:none;
            background-image:linear-gradient(rgba(255,255,255,0.014) 1px,transparent 1px),
                             linear-gradient(90deg,rgba(255,255,255,0.014) 1px,transparent 1px);
            background-size:48px 48px;
        }
        .card {
            position:relative; z-index:1; background:var(--surface);
            border:1px solid var(--border); border-radius:28px;
            width:100%; max-width:460px;
            padding:2.75rem 2.75rem 2.25rem;
            box-shadow:0 0 0 1px rgba(255,255,255,0.025), 0 32px 80px -16px rgba(0,0,0,.75), 0 0 120px -40px var(--teal-glow);
            animation:cardIn .5s cubic-bezier(.34,1.2,.64,1) both;
        }
        @keyframes cardIn { from{opacity:0;transform:translateY(32px) scale(.96)} to{opacity:1;transform:none} }
        .card::before {
            content:''; position:absolute; top:0; left:50%; transform:translateX(-50%);
            width:60%; height:1px;
            background:linear-gradient(90deg,transparent,var(--teal),var(--accent),var(--teal),transparent);
            animation:shimmer 4s ease-in-out infinite alternate;
        }
        @keyframes shimmer { from{opacity:.6;width:40%} to{opacity:1;width:70%} }
        .brand { text-align:center; margin-bottom:2rem; }
        .brand-logo {
            width:62px; height:62px; margin:0 auto .9rem;
            background:linear-gradient(135deg,var(--teal),var(--teal-dark));
            border-radius:18px; display:flex; align-items:center; justify-content:center;
            font-size:1.6rem; box-shadow:0 8px 40px var(--teal-glow), 0 0 0 1px rgba(41,182,200,.2);
        }
        .brand-name { font-size:1.45rem; font-weight:700; letter-spacing:-.04em; color:var(--text); }
        .brand-sub  { font-size:.67rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.12em; margin-top:.3rem; }
        .field { margin-bottom:1.1rem; }
        .field label {
            display:flex; align-items:center; gap:.4rem;
            font-size:.65rem; font-weight:700; text-transform:uppercase;
            letter-spacing:.1em; color:var(--text-muted); margin-bottom:.48rem;
        }
        .field input, .field select {
            width:100%; background:var(--surface-2); border:1px solid var(--border);
            border-radius:12px; color:var(--text); font-family:'Sora',sans-serif;
            font-size:.875rem; padding:.85rem 1rem; outline:none;
            transition:border-color .18s, background .18s, box-shadow .18s;
        }
        .field input::placeholder { color:var(--text-faint); }
        .field input:focus, .field select:focus {
            border-color:var(--border-focus); background:rgba(41,182,200,.04);
            box-shadow:0 0 0 3px var(--teal-dim), 0 2px 8px rgba(0,0,0,.3);
        }
        .field select option { background:#161f30; }
        .input-wrap { position:relative; }
        .pw-toggle {
            position:absolute; right:.85rem; top:50%; transform:translateY(-50%);
            background:none; border:none; color:var(--text-muted); cursor:pointer;
            padding:.2rem; font-size:.85rem; transition:color .15s;
        }
        .pw-toggle:hover { color:var(--teal); }
        .strength-bar {
            height:4px; border-radius:2px; margin-top:.5rem;
            background:var(--surface-3); overflow:hidden;
        }
        .strength-fill { height:100%; width:0; border-radius:2px; transition:width .3s, background .3s; }
        .strength-label { font-size:.62rem; color:var(--text-muted); margin-top:.3rem; }
        .btn-register {
            width:100%; margin-top:.65rem;
            background:linear-gradient(135deg,var(--teal),var(--teal-dark));
            color:#05111e; border:none; padding:.95rem; border-radius:50px;
            font-family:'Sora',sans-serif; font-size:.9rem; font-weight:700;
            cursor:pointer; letter-spacing:.03em;
            transition:transform .18s, box-shadow .18s, opacity .18s;
            box-shadow:0 4px 24px var(--teal-glow); position:relative; overflow:hidden;
        }
        .btn-register::before {
            content:''; position:absolute; inset:0;
            background:linear-gradient(135deg,rgba(255,255,255,.15),transparent); border-radius:inherit;
        }
        .btn-register:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 8px 36px rgba(41,182,200,.5); }
        .btn-register:disabled { opacity:.5; cursor:not-allowed; }
        .spinner {
            display:inline-block; width:14px; height:14px;
            border:2px solid rgba(5,17,30,.3); border-top-color:#05111e;
            border-radius:50%; animation:spin .7s linear infinite;
            vertical-align:middle; margin-right:.4rem;
        }
        @keyframes spin { to{transform:rotate(360deg)} }
        .alert {
            display:none; align-items:flex-start; gap:.55rem;
            border-radius:12px; padding:.75rem 1rem;
            margin-top:1rem; font-size:.78rem; font-weight:500; line-height:1.5;
        }
        .alert.show { display:flex; }
        .alert-error   { background:var(--danger-dim); border:1px solid rgba(248,113,113,.18); color:var(--danger); }
        .alert-success  { background:var(--success-dim); border:1px solid rgba(52,211,153,.2); color:var(--success); }
        .alert-pending  { background:rgba(245,166,35,.08); border:1px solid rgba(245,166,35,.2); color:var(--accent); }
        .login-link {
            text-align:center; margin-top:1.5rem; font-size:.76rem; color:var(--text-muted);
        }
        .login-link a { color:var(--teal); text-decoration:none; font-weight:600; }
        .login-link a:hover { text-decoration:underline; }
        .info-box {
            background:var(--surface-2); border:1px solid var(--border);
            border-radius:12px; padding:.9rem 1rem; margin-bottom:1.5rem;
            font-size:.75rem; color:var(--text-muted); line-height:1.6;
        }
        .info-box strong { color:var(--teal); }
    </style>
<script src="theme.js"></script>
</head>
<body>
<div class="bg-mesh"></div>

<div class="card">
    <div class="brand">
        <div class="brand-logo">📦</div>
        <div class="brand-name">Janeth's Business</div>
        <div class="brand-sub">Request an Account</div>
    </div>

    <div class="info-box">
        <strong>How it works:</strong> Your request will be sent to the admin for approval.
        You'll be able to log in once the admin approves your account.
    </div>

    <div class="field">
        <label><span>👤</span> Username</label>
        <input type="text" id="username" placeholder="Choose a username" autocomplete="username" required>
    </div>
    <div class="field">
        <label><span>🔒</span> Password</label>
        <div class="input-wrap">
            <input type="password" id="password" placeholder="••••••••" autocomplete="new-password" required>
            <button class="pw-toggle" id="pwToggle1" type="button">👁</button>
        </div>
        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
        <div class="strength-label" id="strengthLabel">Enter a password</div>
    </div>
    <div class="field">
        <label><span>🔒</span> Confirm Password</label>
        <div class="input-wrap">
            <input type="password" id="confirmPassword" placeholder="••••••••" autocomplete="new-password" required>
            <button class="pw-toggle" id="pwToggle2" type="button">👁</button>
        </div>
    </div>
    <div class="field">
        <label><span>🎭</span> Requested Role</label>
        <select id="role">
            <option value="staff">👤 Staff</option>
            <option value="admin">👑 Admin</option>
        </select>
    </div>

    <button class="btn-register" id="registerBtn" type="button">Request Account →</button>

    <div id="errorBox"   class="alert alert-error">  <span>⚠️</span><span id="errorText"></span></div>
    <div id="successBox" class="alert alert-success"> <span>✅</span><span id="successText"></span></div>

    <div class="login-link">Already have an account? <a href="login.php">Sign in</a></div>
</div>

<script>
const API = 'register.php';

// Password toggles
function setupToggle(toggleId, inputId) {
    document.getElementById(toggleId).addEventListener('click', function() {
        const inp = document.getElementById(inputId);
        const show = inp.type === 'password';
        inp.type = show ? 'text' : 'password';
        this.textContent = show ? '🙈' : '👁';
    });
}
setupToggle('pwToggle1','password');
setupToggle('pwToggle2','confirmPassword');

// Strength meter
document.getElementById('password').addEventListener('input', function() {
    const val = this.value;
    let score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const fill   = document.getElementById('strengthFill');
    const label  = document.getElementById('strengthLabel');
    const levels = [
        {w:'0%',   bg:'transparent', txt:'Enter a password'},
        {w:'25%',  bg:'#f87171',     txt:'Weak'},
        {w:'50%',  bg:'#fb923c',     txt:'Fair'},
        {w:'75%',  bg:'#fbbf24',     txt:'Good'},
        {w:'100%', bg:'#34d399',     txt:'Strong'},
    ];
    const lvl = val.length === 0 ? levels[0] : levels[Math.min(score, 4)];
    fill.style.width      = lvl.w;
    fill.style.background = lvl.bg;
    label.textContent     = val.length ? `Strength: ${lvl.txt}` : lvl.txt;
});

function hideAlerts() {
    document.getElementById('errorBox').classList.remove('show');
    document.getElementById('successBox').classList.remove('show');
}
function showError(msg) {
    hideAlerts();
    document.getElementById('errorText').textContent = msg;
    document.getElementById('errorBox').classList.add('show');
}
function showSuccess(msg) {
    hideAlerts();
    document.getElementById('successText').textContent = msg;
    document.getElementById('successBox').classList.add('show');
}

document.getElementById('registerBtn').addEventListener('click', async () => {
    const btn      = document.getElementById('registerBtn');
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const confirm  = document.getElementById('confirmPassword').value;
    const role     = document.getElementById('role').value;

    hideAlerts();
    if (!username)               return showError('Please enter a username.');
    if (username.length < 3)     return showError('Username must be at least 3 characters.');
    if (!/^[a-zA-Z0-9_]+$/.test(username)) return showError('Username can only contain letters, numbers, and underscores.');
    if (!password)               return showError('Please enter a password.');
    if (password.length < 6)     return showError('Password must be at least 6 characters.');
    if (password !== confirm)    return showError('Passwords do not match.');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>Submitting…';

    try {
        const res  = await fetch(API, {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({username, password, role})
        });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch {
            showError('Server error. Make sure XAMPP is running and you\'re using http://localhost/…');
            btn.disabled = false; btn.innerHTML = 'Request Account →'; return;
        }
        if (data.success) {
            showSuccess(data.message || 'Registration request submitted! Awaiting admin approval.');
            btn.innerHTML = '✓ Request Sent!';
            setTimeout(() => { window.location.href = 'login.html'; }, 2500);
        } else {
            showError(data.message || 'Registration failed.');
            btn.disabled = false; btn.innerHTML = 'Request Account →';
        }
    } catch {
        showError('Cannot reach server. Is XAMPP running?');
        btn.disabled = false; btn.innerHTML = 'Request Account →';
    }
});

['username','password','confirmPassword'].forEach(id =>
    document.getElementById(id).addEventListener('keydown', e => { if(e.key==='Enter') document.getElementById('registerBtn').click(); })
);
['username','password','confirmPassword'].forEach(id =>
    document.getElementById(id).addEventListener('input', hideAlerts)
);
</script>
<button id="themeToggle" onclick="toggleTheme()">☀️ Light</button>
</body>
</html>