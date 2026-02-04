<?php
require_once 'includes/db.php';

echo "<h2>Removing Price Auto-Calculation Triggers</h2>";

try {
    // Drop existing triggers
    $pdo->exec("DROP TRIGGER IF EXISTS products_before_insert");
    echo "<p style='color: green;'>✅ Removed INSERT trigger</p>";
    
    $pdo->exec("DROP TRIGGER IF EXISTS products_before_update");
    echo "<p style='color: green;'>✅ Removed UPDATE trigger</p>";
    
    echo "<hr>";
    echo "<h3>✅ All Triggers Removed!</h3>";
    echo "<p>Now products will be saved exactly as entered - no automatic calculations.</p>";
    
    echo "<p><a href='products.php' class='btn btn-primary'>Go to Products</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 900px; margin: 0 auto; }
.btn { display: inline-block; padding: 10px 20px; color: white; text-decoration: none; border-radius: 4px; margin: 5px; background: #007bff; }
</style>
