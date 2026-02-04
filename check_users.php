<?php
require_once 'includes/db.php';
try {
    $pdo = get_db();
    $stmt = $pdo->query('SELECT id, email, name, is_active FROM users LIMIT 5');
    echo "Available users:\n";
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['id']}, Email: {$row['email']}, Name: {$row['name']}, Active: {$row['is_active']}\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
?>
