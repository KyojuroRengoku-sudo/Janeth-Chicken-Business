<?php
/**
 * config/database.php
 * Reads DB credentials from .env (one level up from config/).
 */

$envPath = dirname(__DIR__) . '/.env';

if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val);
    }
}

return [
    'host'    => $_ENV['DB_HOST'] ?? 'localhost',
    'dbname'  => $_ENV['DB_NAME'] ?? 'inventory_system',
    'username'=> $_ENV['DB_USER'] ?? 'root',
    'password'=> $_ENV['DB_PASS'] ?? '',
];
