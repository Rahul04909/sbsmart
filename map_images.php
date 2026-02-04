<?php
require 'includes/db.php';
$pdo = get_db();

$imgFolder = 'assets/bch sbsmart website products images';
$uploadFolder = 'uploads/products';

if (!is_dir($uploadFolder)) {
    mkdir($uploadFolder, 0777, true);
}

// 1. Get all images from source folder
$sourceImages = [];
$imageMap = [];
if (is_dir($imgFolder)) {
    $files = scandir($imgFolder);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['png', 'jpg', 'jpeg', 'webp'])) {
            $base = pathinfo($file, PATHINFO_FILENAME);
            $cleaned = preg_replace('/[^a-z0-9]/', '', strtolower($base));
            if ($cleaned === '') continue;
            if (!isset($imageMap[$cleaned])) {
                $imageMap[$cleaned] = [];
            }
            $imageMap[$cleaned][] = $file;
        }
    }
}

// 2. Fetch all products
$stmt = $pdo->query("SELECT id, sku, title, image FROM products");
$products = $stmt->fetchAll();

$updates = 0;
$matches = [];
$noMatches = [];

foreach ($products as $p) {
    $sku = trim((string)$p['sku']);
    if ($sku === '') {
        $noMatches[] = "ID: {$p['id']} - No SKU";
        continue;
    }

    // Clean SKU properly
    $cleanedSku = preg_replace('/[^a-z0-9]/', '', strtolower($sku));
    if ($cleanedSku === '') {
        $noMatches[] = "SKU: $sku - Cleaned SKU is empty";
        continue;
    }

    $foundMatch = null;

    // Try exact cleaned match
    if (isset($imageMap[$cleanedSku])) {
        // Prefer exact filename match if possible, but already cleaned so its same
        $foundMatch = $imageMap[$cleanedSku][0];
    } else {
        // Try fuzzy match: see if cleaned SKU is part of any image name or vice-versa
        foreach ($imageMap as $cleanedImgName => $imgs) {
            // Only match if at least 3 chars match to avoid false positives with very short SKUs
            if (strlen($cleanedSku) >= 3 && strlen($cleanedImgName) >= 3) {
                if (strpos($cleanedImgName, $cleanedSku) !== false || strpos($cleanedSku, $cleanedImgName) !== false) {
                    $foundMatch = $imgs[0];
                    break;
                }
            }
        }
    }

    if ($foundMatch) {
        $ext = pathinfo($foundMatch, PATHINFO_EXTENSION);
        // Sanitize for storage
        $newFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($foundMatch, PATHINFO_FILENAME)) . '.' . $ext;
        
        $srcPath = $imgFolder . '/' . $foundMatch;
        $destPath = $uploadFolder . '/' . $newFilename;
        
        if (file_exists($srcPath)) {
            if (!file_exists($destPath)) {
                copy($srcPath, $destPath);
            }
            $updateStmt = $pdo->prepare("UPDATE products SET image = ? WHERE id = ?");
            $updateStmt->execute([$newFilename, $p['id']]);
            $matches[] = "SKU: $sku -> $foundMatch (Stored as $newFilename)";
            $updates++;
        }
    } else {
        $noMatches[] = "SKU: $sku - No match found (Cleaned: $cleanedSku)";
    }
}

$output = "Total Products: " . count($products) . "\n";
$output .= "Successfully Matched & Updated: $updates\n";
$output .= "No matches found for: " . count($noMatches) . "\n\n";

$output .= "--- Matches Sample ---\n";
$output .= implode("\n", array_slice($matches, 0, 100)) . "\n\n";

$output .= "--- No Matches Sample ---\n";
$output .= implode("\n", array_slice($noMatches, 0, 100)) . "\n";

file_put_contents('mapping_result.txt', $output);
echo "Mapping completed. Check mapping_result.txt";
