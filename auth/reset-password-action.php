<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../account.php?tab=login');
    exit;
}
$token = trim($_POST['token'] ?? '');
$pw = $_POST['password'] ?? '';
$pw2 = $_POST['password_confirm'] ?? '';
if ($token === '' || $pw === '' || $pw !== $pw2) {
    flash_set('error','Invalid input.');
    header('Location: ../forgot-password.php');
    exit;
}
try {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT pr.user_id, pr.expires_at FROM password_resets pr WHERE pr.token = :t LIMIT 1');
    $stmt->execute([':t'=>$token]);
    $row = $stmt->fetch();
    if (!$row) { flash_set('error','Token invalid.'); header('Location: ../forgot-password.php'); exit; }
    if (new DateTimeImmutable($row['expires_at']) < new DateTimeImmutable()) { flash_set('error','Token expired.'); header('Location: ../forgot-password.php'); exit; }
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :id')->execute([':h'=>$hash,':id'=>$row['user_id']]);
    // remove resets for user
    $pdo->prepare('DELETE FROM password_resets WHERE user_id = :id')->execute([':id'=>$row['user_id']]);
    flash_set('success','Password updated. Please login.');
    header('Location: ../account.php?tab=login');
    exit;
} catch (Throwable $e) {
    error_log('Reset action error: '.$e->getMessage());
    flash_set('error','Failed to reset password.');
    header('Location: ../forgot-password.php');
    exit;
}
