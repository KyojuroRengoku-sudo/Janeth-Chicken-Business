<?php
/**
 * routes/web.php
 * ──────────────────────────────────────────────────────
 * Central route table for the application.
 * Called by public/index.php.
 *
 * Pattern: simple string-match on REQUEST_URI path segment.
 * For a larger app, swap with a proper router library.
 */

use App\Controllers\AuthController;
use App\Controllers\ProductController;
use App\Controllers\UserController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Normalize: strip /janeth-chicken-business/public prefix
$uri = preg_replace('#^/janeth-chicken-business/public#', '', $uri);
$uri = rtrim($uri, '/') ?: '/';

$method = $_SERVER['REQUEST_METHOD'];

// ── Public routes (no auth required) ─────────────────────────────────────

if ($uri === '/login' || $uri === '/login.php') {
    if ($method === 'POST') {
        (new AuthController())->login();
    }
    // GET → show login page (handled by public/login.php directly)
    return;
}

if ($uri === '/register' || $uri === '/register.php') {
    if ($method === 'POST') {
        (new AuthController())->register();
    }
    return;
}

if ($uri === '/logout' || $uri === '/logout.php') {
    (new AuthController())->logout();
}

// ── Authenticated API routes ──────────────────────────────────────────────

if ($uri === '/api' || $uri === '/api/inventory') {
    requireAuth(null, true);   // FIXED: was requireAuth(isApi: true)
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    (new ProductController())->handle();
}

// ── Admin-only API routes ─────────────────────────────────────────────────

if ($uri === '/api/users') {
    requireAuth('admin', true);   // FIXED: was requireAuth('admin', isApi: true)
    header('Content-Type: application/json');
    (new UserController())->handle();
}