<?php
// product_delete.php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

function deleteImageFile($f) {
    $p = __DIR__ . '/uploads/products/' . $f;
    if ($f && file_exists($p)) @unlink($p);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete_all') {
        $rows = $pdo->query("SELECT image, images FROM products")->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as $r){
            if (!empty($r['image'])) deleteImageFile($r['image']);
            if (!empty($r['images'])) {
                $arr = json_decode($r['images'], true);
                if (is_array($arr)) foreach($arr as $f) deleteImageFile($f);
            }
        }
        $pdo->exec("DELETE FROM products");
        header('Location: products.php?msg=All+deleted'); exit;
    }
    $ids = $_POST['ids'] ?? [];
    if (!empty($ids) && is_array($ids)) {
        $in = implode(',', array_map('intval', $ids));
        $rows = $pdo->query("SELECT image, images FROM products WHERE id IN ($in)")->fetchAll(PDO::FETCH_ASSOC);
        foreach($rows as $r){
            if (!empty($r['image'])) deleteImageFile($r['image']);
            if (!empty($r['images'])) {
                $arr = json_decode($r['images'], true);
                if (is_array($arr)) foreach($arr as $f) deleteImageFile($f);
            }
        }
        $pdo->exec("DELETE FROM products WHERE id IN ($in)");
    }
    header('Location: products.php?msg=Deleted'); exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT image, images FROM products WHERE id = :id");
    $stmt->execute(['id'=>$id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        if (!empty($r['image'])) deleteImageFile($r['image']);
        if (!empty($r['images'])) {
            $arr = json_decode($r['images'], true);
            if (is_array($arr)) foreach($arr as $f) deleteImageFile($f);
        }
    }
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id'=>$id]);
}
header('Location: products.php?msg=Deleted');
exit;
