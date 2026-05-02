<?php
// register.php – Handles new account registration requests
session_start();
header('Content-Type: application/json');

require_once 'db.php';

$input    = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';
$role     = in_array($input['role'] ?? '', ['admin','staff']) ? $input['role'] : 'staff';

// ── Validation ──
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit;
}
if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    echo json_encode(['success' => false, 'message' => 'Username must be 3+ characters and contain only letters, numbers, or underscores.']);
    exit;
}
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}

// ── Check if username already exists in users table ──
$check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$check->execute([$username]);
if ($check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'That username is already taken.']);
    exit;
}

// ── Check if there is already a pending request for this username ──
$pendingCheck = $pdo->prepare("SELECT id FROM registration_requests WHERE username = ? AND status = 'pending'");
$pendingCheck->execute([$username]);
if ($pendingCheck->fetch()) {
    echo json_encode(['success' => false, 'message' => 'A pending request for that username already exists.']);
    exit;
}

// ── Hash password and insert request ──
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO registration_requests (username, password_hash, requested_role)
    VALUES (?, ?, ?)
");
$stmt->execute([$username, $hash, $role]);

echo json_encode([
    'success' => true,
    'message' => 'Registration request submitted! An admin will review your account shortly.'
]);
exit;