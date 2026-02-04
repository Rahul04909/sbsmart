<?php
require_once 'includes/db.php';

echo "<h2>Adding Missing Columns to Products Table</h2>";

$columnsToAdd = [
    'discount_percentage' => "ALTER TABLE products ADD COLUMN discount_percentage DECIMAL(5,2) DEFAULT 0 AFTER mrp",
    'is_bestseller' => "ALTER TABLE products ADD COLUMN is_bestseller TINYINT(1) DEFAULT 0 AFTER status"
];

foreach ($columnsToAdd as $column => $sql) {
    try {
        // Check if column exists
        $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE '$column'");
        if ($stmt->rowCount() > 0) {
            echo "<p>ℹ️ Column <strong>$column</strong> already exists.</p>";
        } else {
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Successfully added <strong>$column</strong> column!</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Error adding $column: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

echo "<hr>";
echo "<h3>✅ Migration Complete!</h3>";
echo "<p><a href='products.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>Go to Products →</a></p>";
echo "<p><a href='product_form.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;'>Add New Product →</a></p>";
?>
<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
</style>
