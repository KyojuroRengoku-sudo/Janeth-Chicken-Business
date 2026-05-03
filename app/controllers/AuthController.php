<?php
namespace App\Controllers;

use App\Models\User;

/**
 * AuthController – handles login, logout, registration.
 */
class AuthController
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    // ── POST /public/login.php ────────────────────────────────────────────

    public function login(): void
    {
        $input    = json_decode(file_get_contents('php://input'), true);
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            send(['success' => false, 'message' => 'Username and password required.']);
        }

        $user = $this->user->findByUsername($username);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            send(['success' => true]);
        }

        send(['success' => false, 'message' => 'Invalid username or password.']);
    }

    // ── GET /public/logout.php ────────────────────────────────────────────

    public function logout(): void
    {
        session_unset();
        session_destroy();

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        header('Location:/Janeth_Business/Janeth-Chicken-Business/public/login.php');
        exit;
    }

    // ── POST /public/register.php ─────────────────────────────────────────

    public function register(): void
    {
        $input    = json_decode(file_get_contents('php://input'), true);
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $role     = in_array($input['role'] ?? '', ['admin', 'staff']) ? $input['role'] : 'staff';

        if (empty($username) || empty($password)) {
            send(['success' => false, 'message' => 'Username and password are required.']);
        }
        if (strlen($username) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            send(['success' => false, 'message' => 'Username must be 3+ chars (letters, numbers, underscores).']);
        }
        if (strlen($password) < 6) {
            send(['success' => false, 'message' => 'Password must be at least 6 characters.']);
        }
        if ($this->user->usernameExists($username)) {
            send(['success' => false, 'message' => 'That username is already taken.']);
        }
        if ($this->user->pendingRequestExists($username)) {
            send(['success' => false, 'message' => 'A pending request for that username already exists.']);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->user->createRequest($username, $hash, $role);

        send(['success' => true, 'message' => 'Registration request submitted! An admin will review your account shortly.']);
    }
}
