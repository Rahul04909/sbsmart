<?php
$config = require __DIR__ . '/includes/config.php';
$db = $config['db'];
echo "DB Host: " . $db['user'] . "@" . explode(';', $db['dsn'])[0] . "\n";
// Extract dbname from dsn
preg_match('/dbname=([^;]+)/', $db['dsn'], $matches);
echo "DB Name: " . ($matches[1] ?? 'unknown') . "\n";
?>
