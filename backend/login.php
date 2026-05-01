<?php
// ─── FIX: login.html must be served through XAMPP (http://localhost/yourfolder/login.html)
// NOT opened directly as a file:/// URL. That causes the fetch() to fail with
// "Cannot reach server" because there is no HTTP server handling the request.
//
// This file also had a missing exit; after the failed-login JSON response.

session_start();
header('Content-Type: application/json');

require_once 'db.php';

$input    = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password required.']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    session_regenerate_id(true);          // prevent session fixation
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];
    echo json_encode(['success' => true]);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    exit; // ← BUG FIX: was missing, response fell through
}
?>