<?php
require_once 'includes/db.php';

echo "<h2>Checking Database Tables</h2>";

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Tables in database:</h3><ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    if (in_array('products', $tables)) {
        echo "<h3 style='color: green;'>✅ Products table exists!</h3>";
        
        // Check columns
        $stmt = $pdo->query("DESCRIBE products");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h4>Products table columns:</h4><ul>";
        foreach ($columns as $col) {
            echo "<li><strong>{$col['Field']}</strong> ({$col['Type']})</li>";
        }
        echo "</ul>";
        
        //  Count products
        $count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        echo "<p><strong>Total products:</strong> $count</p>";
        
        echo "<p><a href='products.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>View Products →</a></p>";
    } else {
        echo "<h3 style='color: red;'>❌ Products table NOT found!</h3>";
        echo "<p>Please import the database manually via phpMyAdmin</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
</style>