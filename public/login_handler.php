<?php
/**
 * public/login_handler.php
 * Called via fetch() POST from login.php.
 */
require_once __DIR__ . '/index.php';

session_start();
header('Content-Type: application/json');

use App\Controllers\AuthController;

(new AuthController())->login();
