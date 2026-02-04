<?php
// ajax_subcategories.php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
header('Content-Type: application/json');

$cat = (int)($_GET['category_id'] ?? 0);
if (!$cat) { echo json_encode([]); exit; }

$stmt = $pdo->prepare("SELECT id,name FROM subcategories WHERE category_id = :cid ORDER BY name");
$stmt->execute(['cid'=>$cat]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
