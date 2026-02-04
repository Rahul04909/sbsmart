<?php
require_once 'includes/db.php';

echo "<h2>Setting up Price Auto-Calculation in Database</h2>";

try {
    // Drop existing triggers if any
    try {
        $pdo->exec("DROP TRIGGER IF EXISTS products_before_insert");
        echo "<p>✓ Removed old INSERT trigger (if existed)</p>";
    } catch (Exception $e) {}
    
    try {
        $pdo->exec("DROP TRIGGER IF EXISTS products_before_update");
        echo "<p>✓ Removed old UPDATE trigger (if existed)</p>";
    } catch (Exception $e) {}
    
    // Create BEFORE INSERT trigger
    $pdo->exec("
        CREATE TRIGGER products_before_insert 
        BEFORE INSERT ON products
        FOR EACH ROW
        BEGIN
            -- Auto-calculate price
            IF (NEW.price IS NULL OR NEW.price = 0) AND NEW.mrp > 0 AND NEW.discount_percentage > 0 THEN
                SET NEW.price = NEW.mrp - (NEW.mrp * NEW.discount_percentage);
            END IF;
            
            -- If price still 0/NULL and MRP exists, use MRP as price
            IF (NEW.price IS NULL OR NEW.price = 0) AND NEW.mrp > 0 THEN
                SET NEW.price = NEW.mrp;
            END IF;
        END
    ");
    echo "<p style='color: green;'>✅ <strong>INSERT Trigger Created!</strong> Price will auto-calculate when adding new products.</p>";
    
    // Create BEFORE UPDATE trigger
    $pdo->exec("
        CREATE TRIGGER products_before_update 
        BEFORE UPDATE ON products
        FOR EACH ROW
        BEGIN
            -- Auto-calculate price
            IF (NEW.price IS NULL OR NEW.price = 0) AND NEW.mrp > 0 AND NEW.discount_percentage > 0 THEN
                SET NEW.price = NEW.mrp - (NEW.mrp * NEW.discount_percentage);
            END IF;
            
            -- If price still 0/NULL and MRP exists, use MRP as price
            IF (NEW.price IS NULL OR NEW.price = 0) AND NEW.mrp > 0 THEN
                SET NEW.price = NEW.mrp;
            END IF;
        END
    ");
    echo "<p style='color: green;'>✅ <strong>UPDATE Trigger Created!</strong> Price will auto-calculate when updating products.</p>";
    
    echo "<hr>";
    echo "<h3>✅ Database Triggers Successfully Created!</h3>";
    echo "<div class='alert alert-success'>";
    echo "<strong>Formula Active:</strong> <code>price = mrp - (mrp × discount_percentage)</code>";
    echo "</div>";
    
    echo "<h4>Now the formula will automatically work:</h4>";
    echo "<ul>";
    echo "<li>✅ When inserting new products</li>";
    echo "<li>✅ When updating existing products</li>";
    echo "<li>✅ In CSV imports</li>";
    echo "<li>✅ In product forms</li>";
    echo "</ul>";
    
    echo "<p><a href='products.php' class='btn btn-primary'>Go to Products</a> ";
    echo "<a href='product_form.php' class='btn btn-success'>Add New Product</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 900px; margin: 0 auto; }
.btn { display: inline-block; padding: 10px 20px; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
.btn-primary { background: #007bff; }
.btn-primary:hover { background: #0056b3; }
.btn-success { background: #28a745; }
.btn-success:hover { background: #218838; }
.alert { padding: 15px; border-radius: 4px; margin: 15px 0; }
.alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
</style>