<?php
/**
 * fix_passwords.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Run this ONCE via browser: http://localhost/yourfolder/fix_passwords.php
 *
 * WHY THIS EXISTS:
 * The original schema.sql had a bug — the password hashes were the bcrypt hash
 * of the word "password" (a common Laravel/demo placeholder), NOT of "admin123"
 * or "staff123". This caused all login attempts to fail.
 *
 * This script regenerates correct hashes and updates the users table.
 * DELETE this file after running it (for security).
 * ─────────────────────────────────────────────────────────────────────────────
 */

// ── Safety lock: delete this file after first run ──
$lockFile = __DIR__ . '/.pw_fixed';

require_once 'db.php';

$accounts = [
    ['username' => 'admin',  'password' => 'admin123', 'role' => 'admin'],
    ['username' => 'staff1', 'password' => 'staff123', 'role' => 'staff'],
];

$results = [];

foreach ($accounts as $acc) {
    $hash = password_hash($acc['password'], PASSWORD_DEFAULT);

    // Check if user already exists
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$acc['username']]);
    $existing = $check->fetch();

    if ($existing) {
        // Update hash
        $pdo->prepare("UPDATE users SET password_hash = ?, role = ? WHERE username = ?")
            ->execute([$hash, $acc['role'], $acc['username']]);
        $results[] = "✅ Updated password for <strong>{$acc['username']}</strong> → <code>{$acc['password']}</code>";
    } else {
        // Insert fresh
        $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)")
            ->execute([$acc['username'], $hash, $acc['role']]);
        $results[] = "✅ Created user <strong>{$acc['username']}</strong> with password <code>{$acc['password']}</code>";
    }
}

// Also add registration_requests table if missing
$pdo->exec("
    CREATE TABLE IF NOT EXISTS registration_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        requested_role ENUM('admin', 'staff') DEFAULT 'staff',
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        reviewed_at DATETIME DEFAULT NULL
    ) ENGINE=InnoDB
");
$results[] = "✅ Ensured <strong>registration_requests</strong> table exists.";

// Create lock file
file_put_contents($lockFile, date('Y-m-d H:i:s'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fix Passwords · Janeth's Business</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=DM+Mono&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#0a0e17;--surface:#111827;--border:rgba(255,255,255,.07);--teal:#29b6c8;--success:#34d399;--danger:#f87171;--text:#e8edf5;--muted:#6b7a93;}
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Sora',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem;}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:2.5rem;max-width:520px;width:100%;}
        h1{font-size:1.3rem;font-weight:700;margin-bottom:.4rem;}
        .sub{font-size:.78rem;color:var(--muted);margin-bottom:1.75rem;}
        .result{padding:.65rem 1rem;border-radius:10px;font-size:.82rem;margin-bottom:.6rem;
                background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.2);color:var(--success);}
        .warn{background:rgba(245,166,35,.08);border:1px solid rgba(245,166,35,.2);color:#f5a623;
              padding:.75rem 1rem;border-radius:10px;font-size:.8rem;margin-top:1.25rem;line-height:1.6;}
        .warn strong{display:block;font-size:.85rem;margin-bottom:.25rem;}
        a{display:inline-block;margin-top:1.5rem;padding:.55rem 1.4rem;background:var(--teal);color:#0a0e17;
          font-weight:700;border-radius:50px;text-decoration:none;font-size:.82rem;}
        a:hover{opacity:.9;}
        code{font-family:'DM Mono',monospace;background:rgba(41,182,200,.12);padding:.1rem .4rem;border-radius:4px;color:var(--teal);}
    </style>
</head>
<body>
<div class="card">
    <h1>🔧 Password Fix Complete</h1>
    <p class="sub">The wrong bcrypt hashes in schema.sql have been corrected.</p>

    <?php foreach ($results as $r): ?>
    <div class="result"><?= $r ?></div>
    <?php endforeach; ?>

    <div class="warn">
        <strong>⚠️ Security — Delete this file!</strong>
        This file resets passwords. Now that it has run, delete <code>fix_passwords.php</code>
        from your server to prevent anyone from resetting passwords again.
        A lock file <code>.pw_fixed</code> has also been created.
    </div>

    <a href="../frontend/login.html">→ Go to Login</a>
</div>
</body>
</html>