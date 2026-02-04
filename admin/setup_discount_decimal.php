<?php
require_once 'includes/db.php';

echo "<h2>Setting Up Discount as Decimal (0.00 to 1.00)</h2>";

try {
    // Change column back to DECIMAL(3,2) for storing values like 0.25
    $pdo->exec("ALTER TABLE products MODIFY COLUMN discount_percentage DECIMAL(3,2) DEFAULT 0.00");
    echo "<p style='color: green;'>✅ Column changed to DECIMAL(3,2) - Stores values from 0.00 to 0.99</p>";
    
    // Convert existing integer values to decimal (25 -> 0.25, 10 -> 0.10, etc)
    $pdo->exec("UPDATE products SET discount_percentage = discount_percentage / 100 WHERE discount_percentage > 1");
    echo "<p style='color: green;'>✅ Existing values converted (25 → 0.25, 10 → 0.10, etc)</p>";
    
    echo "<hr>";
    echo "<h3>✅ Setup Complete!</h3>";
    
    echo "<div class='alert alert-info'>";
    echo "<strong>How it works:</strong><br>";
    echo "• <strong>User enters:</strong> 25 (in form/CSV)<br>";
    echo "• <strong>System converts:</strong> 25 ÷ 100 = 0.25<br>";
    echo "• <strong>Database stores:</strong> 0.25<br>";
    echo "• <strong>Formula calculates:</strong> price = mrp - (mrp × 0.25)<br>";
    echo "• <strong>Result:</strong> 2000 - 500 = 1500";
    echo "</div>";
    
    echo "<div class='alert alert-success'>";
    echo "<strong>New Formula (NO /100):</strong><br>";
    echo "<code>price = mrp - (mrp × discount_percentage)</code>";
    echo "</div>";
    
    echo "<p><a href='products.php' class='btn btn-primary'>View Products</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 900px; margin: 0 auto; }
.btn { display: inline-block; padding: 10px 20px; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
.btn-primary { background: #007bff; }
.alert { padding: 15px; border-radius: 4px; margin: 15px 0; }
.alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
.alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
</style>
