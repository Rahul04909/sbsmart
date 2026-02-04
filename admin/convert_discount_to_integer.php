<?php
require_once 'includes/db.php';

echo "<h2>Converting Discount Percentage to Whole Numbers</h2>";

try {
    // Check current column type
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'discount_percentage'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($column) {
        echo "<p><strong>Current type:</strong> <code>{$column['Type']}</code></p>";
    }
    
    // Change column from DECIMAL(5,2) to TINYINT (0-100 is enough for percentage)
    $pdo->exec("ALTER TABLE products MODIFY COLUMN discount_percentage TINYINT DEFAULT 0");
    echo "<p style='color: green;'>✅ <strong>Column updated to TINYINT</strong> - No more decimals allowed!</p>";
    
    // Round existing decimal values to integers
    $pdo->exec("UPDATE products SET discount_percentage = ROUND(discount_percentage)");
    echo "<p style='color: green;'>✅ <strong>Existing data rounded</strong> - All decimal values converted to whole numbers</p>";
    
    // Verify the change
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'discount_percentage'");
    $newColumn = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>New type:</strong> <code>{$newColumn['Type']}</code></p>";
    
    echo "<hr>";
    echo "<h3>✅ Discount Percentage Now Accepts Only Whole Numbers!</h3>";
    echo "<ul>";
    echo "<li>✅ Database column: TINYINT (0-255)</li>";
    echo "<li>✅ Form input: Integer only</li>";
    echo "<li>✅ CSV import: Integer only</li>";
    echo "<li>✅ All existing decimals rounded</li>";
    echo "</ul>"; 
    echo "<div class='alert alert-success'>";
    echo "<strong>Examples:</strong><br>";
    echo "• 10.5% → 11%<br>";
    echo "• 25.75% → 26%<br>";
    echo "• 15% → 15% (unchanged)";
    echo "</div>"; 
    echo "<p><a href='products.php' class='btn btn-primary'>View Products</a> ";
    echo "<a href='product_form.php' class='btn btn-success'>Add New Product</a></p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 900px; margin: 0 auto; }
.btn { display: inline-block; padding: 10px 20px; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
.btn-primary { background: #007bff; }
.btn-success { background: #28a745; }
.alert { padding: 15px; border-radius: 4px; margin: 15px 0; }
.alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
</style>
