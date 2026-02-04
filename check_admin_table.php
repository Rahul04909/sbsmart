<?php
require 'includes/db.php';
$pdo = get_db();

try {
    $stmt = $pdo->query('SHOW COLUMNS FROM admin_users');
    echo "Admin Users Table Structure:\n\n";
    while($row = $stmt->fetch()) {
        echo "Column: " . $row['Field'] . "\n";
        echo "Type: " . $row['Type'] . "\n";
        echo "Null: " . $row['Null'] . "\n";
        echo "Key: " . $row['Key'] . "\n";
        echo "Default: " . ($row['Default'] ?? 'NULL') . "\n";
        echo "---\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
