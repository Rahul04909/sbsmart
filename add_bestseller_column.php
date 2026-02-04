<?php
require_once __DIR__ . '/includes/db.php';

try {
    $pdo = get_db();
    
    // Check if column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM products LIKE 'is_bestseller'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        echo "Adding is_bestseller column...\n";
        $pdo->exec("ALTER TABLE products ADD COLUMN is_bestseller TINYINT(1) DEFAULT 0 AFTER status");
        echo "Column added successfully.\n";
    } else {
        echo "Column is_bestseller alread exists.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
