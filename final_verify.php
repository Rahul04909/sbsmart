<?php
// Mock browser environment as it would be for the user
$_SERVER['SCRIPT_NAME'] = '/account/products.php';
$_SERVER['SCRIPT_FILENAME'] = 'C:/xampp/htdocs/account/products.php';
$_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';

require 'includes/helpers.php';
require 'includes/db.php';

$pdo = get_db();
$stmt = $pdo->prepare("SELECT image FROM products WHERE sku = ?");
$stmt->execute(['BIL-50000']);
$imgField = $stmt->fetchColumn();

echo "SKU: BIL-50000\n";
echo "DB Field: $imgField\n";
$resolved = resolve_image($imgField);
echo "Resolved URL: $resolved\n";

if (strpos($resolved, 'noimage.webp') !== false) {
    echo "ERROR: resolve_image returned placeholder!\n";
    echo "Check path: " . realpath(__DIR__ . '/..') . "/uploads/products/" . basename($imgField) . "\n";
    echo "File exists: " . (file_exists(realpath(__DIR__ . '/..') . "/uploads/products/" . basename($imgField)) ? 'YES' : 'NO') . "\n";
}
?>
