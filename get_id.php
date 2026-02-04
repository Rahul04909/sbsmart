<?php
require 'includes/db.php';
$pdo = get_db();
$id = $pdo->query("SELECT id FROM products LIMIT 1")->fetchColumn();
echo $id;
?>
