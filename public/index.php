<?php
/**
 * public/index.php
 * ──────────────────────────────────────────────────────
 * Front controller – bootstraps the app, then dispatches via routes/web.php.
 *
 * PSR-4-style manual autoloader (no Composer required for this simple app).
 */

session_start();

// ── Autoloader ────────────────────────────────────────────────────────────

spl_autoload_register(function (string $class): void {
    $base = dirname(__DIR__) . '/';

    $map = [
        'App\\Controllers\\' => 'app/controllers/',
        'App\\Models\\'      => 'app/models/',
    ];

    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $base . $dir . substr($class, strlen($prefix)) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});

// ── Helpers ───────────────────────────────────────────────────────────────

require_once dirname(__DIR__) . '/app/helpers/helpers.php';

// ── Router ────────────────────────────────────────────────────────────────

require_once dirname(__DIR__) . '/routes/web.php';
