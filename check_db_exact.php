<?php
require 'includes/db.php';
$pdo = get_db();
$stmt = $pdo->query("SELECT sku, image FROM products WHERE image != '' LIMIT 20");
foreach ($stmt->fetchAll() as $row) {
    echo "{$row['sku']} => {$row['image']}\n";
}
?>
