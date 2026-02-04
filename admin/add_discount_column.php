<?php
require_once 'includes/db.php';

try {
    // Add discount_percentage column to products table
    $pdo->exec('ALTER TABLE products ADD COLUMN discount_percentage DECIMAL(5,2) DEFAULT 0 AFTER mrp');
    echo "✅ Successfully added discount_percentage column to products table!";
} catch(PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "⚠️ Column discount_percentage already exists.";
    } else {
        echo "❌ Error: " . $e->getMessage();
    }
}
