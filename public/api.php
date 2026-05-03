<?php
/**
 * public/api.php  (was: janeth.php)
 * ──────────────────────────────────────────────────────
 * Thin entry point — boots the app, checks auth, delegates to ProductController.
 * The frontend JS uses const API = 'api.php' (or still 'janeth.php' via alias).
 */

require_once __DIR__ . '/index.php';

use App\Controllers\ProductController;

session_start();
requireAuth(null, true);   // FIXED: was requireAuth(isApi: true)

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

(new ProductController())->handle();