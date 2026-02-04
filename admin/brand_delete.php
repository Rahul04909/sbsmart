<?php
// brand_delete.php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $ids = $_POST['ids'] ?? [];
        if (!empty($ids)) {
            // Bulk delete
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM brands WHERE id IN ($placeholders)");
            $stmt->execute($ids);
        }
    }
} elseif ($id) {
    // Single delete
    $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: brands.php');
exit;
