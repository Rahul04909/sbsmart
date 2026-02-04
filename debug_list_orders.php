<?php
require_once __DIR__ . '/includes/db.php';
$conn = get_db();
$stmt = $conn->query("SELECT id, status, created_at FROM orders ORDER BY id DESC LIMIT 5");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
?>
