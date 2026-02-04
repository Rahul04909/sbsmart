<?php
require 'includes/db.php';
$pdo = get_db();
$stmt = $pdo->query("SELECT sku, title, image FROM products WHERE image != '' LIMIT 10");
foreach ($stmt->fetchAll() as $row) {
    echo "SKU: {$row['sku']} | Image: {$row['image']}\n";
}
?>
