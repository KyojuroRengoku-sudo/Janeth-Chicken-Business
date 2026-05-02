<?php
namespace App\Models;

use PDO;
use PDOException;

/**
 * Database – PDO singleton.
 * Usage: $pdo = Database::getInstance();
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $cfg = require dirname(__DIR__, 2) . '/config/database.php';

            try {
                self::$instance = new PDO(
                    "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset=utf8mb4",
                    $cfg['username'],
                    $cfg['password'],
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
            }
        }

        return self::$instance;
    }
}
