<?php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h2>index_debug — start</h2>";
// Try to include header safely
if (file_exists(__DIR__ . '/includes/header.php')) {
    try {
        require_once __DIR__ . '/includes/header.php';
        echo "<p>Header included OK</p>";
    } catch (Throwable $e) {
        echo "<p>Header error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p>includes/header.php NOT FOUND</p>";
}

echo "<p>index_debug — end</p>";
