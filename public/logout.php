<?php
require_once __DIR__ . '/index.php';
session_start();
use App\Controllers\AuthController;
(new AuthController())->logout();
