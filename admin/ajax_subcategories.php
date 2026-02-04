<?php
// ajax_subcategories.php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');

$bid = (int)($_GET['brand_id'] ?? 0);
if (!$bid) { echo json_encode([]); exit; }

$stmt = $pdo->prepare("SELECT id,name FROM subcategories WHERE brand_id = :bid ORDER BY name");
$stmt->execute(['bid'=>$bid]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
