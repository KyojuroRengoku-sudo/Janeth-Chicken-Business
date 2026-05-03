<?php
require_once __DIR__ . '/index.php';
// index.php already calls session_start() — do not call it again
header('Content-Type: application/json');
use App\Controllers\AuthController;
(new AuthController())->register();