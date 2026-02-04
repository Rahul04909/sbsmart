<?php
// product_export.php - CSV Export
// Clean output buffer to prevent any output before headers
ob_start();

require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

// Clear any previous output
ob_end_clean();

// Fetch all products with category/subcategory names
$stmt = $pdo->query("
    SELECT p.*, c.name AS category_name, s.name AS subcategory_name 
    FROM products p 
    LEFT JOIN categories c ON c.id = p.category_id 
    LEFT JOIN subcategories s ON s.id = p.subcategory_id 
    ORDER BY p.id ASC
");

// Generate filename with timestamp
$filename = 'products_export_' . date('Ymd_His') . '.csv';

// Set proper CSV headers - IMPORTANT: No output before this!
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$out = fopen('php://output', 'w');

// Add BOM for Excel UTF-8 compatibility
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

// Write header row
fputcsv($out, [
    'SKU',
    'Title',
    'HSN Code',
    'Slug',
    'Short Description',
    'Short Description 2',
    'Description',
    'Price',
    'MRP',
    'Stock',
    'Category',
    'Subcategory',
    'Tags',
    'Images',
    'Main Image',
    'Status',
    'Created At'
]);

// Write data rows
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Handle images array
    $images_csv = '';
    if (!empty($r['images'])) {
        $arr = json_decode($r['images'], true);
        if (is_array($arr)) {
            $images_csv = implode('|', $arr); // Use pipe separator for multiple images
        }
    }
    
    fputcsv($out, [
        $r['sku'] ?? '',
        $r['title'] ?? '',
        $r['hsn_code'] ?? '',
        $r['slug'] ?? '',
        $r['short_desc'] ?? '',
        $r['short_description'] ?? '',
        $r['description'] ?? '',
        isset($r['price']) ? $r['price'] : '',
        isset($r['mrp']) ? $r['mrp'] : '',
        isset($r['stock']) ? $r['stock'] : '',
        $r['category_name'] ?? '',
        $r['subcategory_name'] ?? '',
        $r['tags'] ?? '',
        $images_csv,
        $r['image'] ?? '',
        $r['status'] ?? '',
        $r['created_at'] ?? ''
    ]);
}

fclose($out);
exit;
