<?php
/**
 * public/janeth.php – backward-compatibility alias.
 * All existing fetch() calls use 'janeth.php'; this simply delegates to api.php.
 */
require __DIR__ . '/api.php';
