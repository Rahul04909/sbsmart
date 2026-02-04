<?php
require_once __DIR__ . '/includes/db.php';
try {
    $pdo = get_db();
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables:\n" . implode("\n", $tables);
    
    // Check columns of 'cart' or 'cart_items' if they exist
    foreach (['cart', 'cart_items'] as $t) {
        if (in_array($t, $tables)) {
            echo "\n\nColumns in $t:\n";
            $stmt = $pdo->query("DESCRIBE $t");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
                echo $col['Field'] . " (" . $col['Type'] . ")\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
