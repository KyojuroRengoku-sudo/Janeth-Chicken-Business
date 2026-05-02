<?php
/**
 * helpers.php – Shared utility functions.
 */

/**
 * Send a JSON response and exit.
 */
function send(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Require an authenticated session; redirect or send 401 if missing.
 *
 * @param string|null $requiredRole  'admin' | 'staff' | null (any role)
 * @param bool        $isApi         true → send JSON 401, false → redirect
 */
function requireAuth(?string $requiredRole = null, bool $isApi = false): void
{
    if (!isset($_SESSION['user_id'])) {
        if ($isApi) {
            send(['error' => 'Unauthorized'], 401);
        }
        header('Location: /janeth-chicken-business/public/login.php');
        exit;
    }

    if ($requiredRole !== null && $_SESSION['role'] !== $requiredRole) {
        if ($isApi) {
            send(['error' => 'Forbidden'], 403);
        }
        header('Location: /janeth-chicken-business/public/login.php');
        exit;
    }
}

/**
 * Validate a date string in Y-m-d format.
 */
function validDate(string $date): bool
{
    return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
}
