<?php
// debug_search.php - Debug search query
require_once __DIR__ . '/includes/db.php';

$pdo = get_db();

echo "<h1>Search Debug</h1>";

// Test 1: Check all products
$stmt = $pdo->query("SELECT id, title, status FROM products LIMIT 10");
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>All Products (first 10):</h2>";
echo "<pre>";
print_r($all);
echo "</pre>";

// Test 2: Check products with status = 1
$stmt = $pdo->query("SELECT id, title, status FROM products WHERE status = 1 LIMIT 10");
$active = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Active Products (status=1):</h2>";
echo "<pre>";
print_r($active);
echo "</pre>";

// Test 3: Search for 'enclosure'
$search_term = '%enclosure%';
$stmt = $pdo->prepare("SELECT id, title, status FROM products WHERE title LIKE :search");
$stmt->execute(['search' => $search_term]);
$search_all = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Products matching 'enclosure' (any status):</h2>";
echo "<pre>";
print_r($search_all);
echo "</pre>";

// Test 4: Search for 'enclosure' with status = 1
$stmt = $pdo->prepare("SELECT id, title, status FROM products WHERE status = 1 AND title LIKE :search");
$stmt->execute(['search' => $search_term]);
$search_active = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Products matching 'enclosure' with status=1:</h2>";
echo "<pre>";
print_r($search_active);
echo "</pre>";
?>
