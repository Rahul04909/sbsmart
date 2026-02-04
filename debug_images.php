<?php
require 'includes/helpers.php';
require 'includes/db.php';

$uploads_dir = 'uploads/products';
$projectRoot = realpath(__DIR__);

$pdo = get_db();
$stmt = $pdo->query("SELECT id, sku, image FROM products WHERE image != '' AND image IS NOT NULL LIMIT 5");
$products = $stmt->fetchAll();

foreach ($products as $p) {
    $filename = basename($p['image']);
    $fullPath = $projectRoot . '/' . $uploads_dir . '/' . $filename;
    $exists = file_exists($fullPath) ? 'YES' : 'NO';
    echo "SKU: {$p['sku']} | DB: {$p['image']} | Path: $fullPath | Exists: $exists\n";
}
