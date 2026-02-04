<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    flash_set('error', 'Invalid token.');
    header('Location: /auth/login.php');
    exit;
}
try {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id,is_active FROM users WHERE verify_token = :t LIMIT 1');
    $stmt->execute([':t'=>$token]);
    $u = $stmt->fetch();
    if (!$u) {
        flash_set('error', 'Token invalid or expired.');
        header('Location: /auth/login.php');
        exit;
    }
    if (!empty($u['is_active'])) {
        flash_set('success', 'Account already active. Please login.');
        header('Location: /auth/login.php');
        exit;
    }
    $upd = $pdo->prepare('UPDATE users SET is_active = 1, verify_token = NULL WHERE id = :id');
    $upd->execute([':id'=>$u['id']]);
    flash_set('success', 'Account verified. You may login now.');
    header('Location: /auth/login.php');
    exit;
} catch (Throwable $e) {
    app_log('Verify error: '.$e->getMessage(),'error');
    flash_set('error','Verification failed.');
    header('Location: /auth/login.php');
    exit;
}
