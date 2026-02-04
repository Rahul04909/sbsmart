<?php
// subcategory_delete.php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete_all') {
        $pdo->exec("DELETE FROM subcategories");
        header('Location: subcategories.php');
        exit;
    }
    $ids = $_POST['ids'] ?? [];
    if (!empty($ids) && is_array($ids)) {
        $in = implode(',', array_map('intval', $ids));
        $pdo->exec("DELETE FROM subcategories WHERE id IN ($in)");
    }
    header('Location: subcategories.php');
    exit;
}

// GET single delete
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM subcategories WHERE id = :id");
    $stmt->execute(['id'=>$id]);
}
header('Location: subcategories.php');
exit;
