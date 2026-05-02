<?php
/**
 * admin/bootstrap.php
 * Minimal bootstrap for admin pages that use $pdo directly.
 */
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit;
}

require_once dirname(__DIR__) . '/app/helpers/helpers.php';

// Manual autoloader (no Composer)
spl_autoload_register(function (string $class): void {
    $base = dirname(__DIR__) . '/';
    $map  = [
        'App\\Controllers\\' => 'app/controllers/',
        'App\\Models\\'      => 'app/models/',
    ];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $base . $dir . substr($class, strlen($prefix)) . '.php';
            if (file_exists($file)) { require $file; return; }
        }
    }
});

use App\Models\Database;
$pdo = Database::getInstance();
