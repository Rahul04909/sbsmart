<?php
require_once 'includes/db.php';

echo "<h2>Database Setup</h2>";
echo "<p>Importing database structure and sample data...</p>";

// Read the SQL file
$sqlFile = __DIR__ . '/invest13_sbsmart (3).sql';
$sql = file_get_contents($sqlFile);

if (!$sql) {
    die("Error: Could not read SQL file!");
}

try {
    // Execute the SQL (split by semicolons)
    $statements = explode(';', $sql);
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Ignore "table already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "<div style='color: orange;'>Warning: " . htmlspecialchars($e->getMessage()) . "</div>";
                $errors++;
            }
        }
    }
    
    echo "<p>✅ Executed $executed SQL statements ($errors warnings)</p>";
    
    // Add missing columns
    echo "<h3>Adding missing columns...</h3>";
    
    // Add discount_percentage column
    try {
        $pdo->exec('ALTER TABLE products ADD COLUMN discount_percentage DECIMAL(5,2) DEFAULT 0 AFTER mrp');
        echo "<p>✅ Added discount_percentage column</p>";
    } catch(PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ discount_percentage column already exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding discount_percentage: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // Add is_bestseller column
    try {
        $pdo->exec('ALTER TABLE products ADD COLUMN is_bestseller TINYINT(1) DEFAULT 0 AFTER status');
        echo "<p>✅ Added is_bestseller column</p>";
    } catch(PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "<p>ℹ️ is_bestseller column already exists</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding is_bestseller: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // Add subcategory_id to products table (line 14 addition)
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'subcategory_id'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec('ALTER TABLE products ADD COLUMN subcategory_id INT(11) DEFAULT NULL AFTER category_id');
            echo "<p>✅ Added subcategory_id column</p>";
        } else {
            echo "<p>ℹ️ subcategory_id column already exists</p>";
        }
    } catch(PDOException $e) {
        echo "<p style='color: orange;'>⚠️ subcategory_id: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "<hr>";
    echo "<h3>✅ Database setup completed!</h3>";
    echo "<p><a href='index.php' class='btn'>Go to Admin Dashboard</a> | <a href='products.php'>View Products</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
.btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin-top: 10px; }
.btn:hover { background: #0056b3; }
</style>
