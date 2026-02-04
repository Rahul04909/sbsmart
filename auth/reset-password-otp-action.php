<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    safe_redirect('../enter-otp.php');
}

// 1. Validate Session & Input
if (empty($_SESSION['reset_email'])) {
    flash_set('error', 'Session expired. Please try again.');
    safe_redirect('../forgot-password.php');
}
$email = $_SESSION['reset_email'];

$otp = trim($_POST['otp'] ?? '');
$pw = $_POST['password'] ?? '';
$pw2 = $_POST['password_confirm'] ?? '';

if ($otp === '' || $pw === '' || $pw !== $pw2) {
    flash_set('error','Invalid input or passwords do not match.');
    safe_redirect('../enter-otp.php');
}

try {
    $pdo = get_db();
    
    // 2. Validate OTP against User
    // We need to match user ID from email, then match token
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e LIMIT 1');
    $stmt->execute([':e'=>$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Should not happen if flow followed, but handle it
        flash_set('error','User not found.');
        safe_redirect('../forgot-password.php');
    }
    
    $uid = $user['id'];
    
    // Check Password Resets table for this UID and Token
    $stmt = $pdo->prepare('SELECT expires_at FROM password_resets WHERE user_id = :uid AND token = :t LIMIT 1');
    $stmt->execute([':uid'=>$uid, ':t'=>$otp]);
    $resetData = $stmt->fetch();
    
    if (!$resetData) {
        flash_set('error', 'Invalid OTP.');
        safe_redirect('../enter-otp.php');
    }
    
    if (new DateTimeImmutable($resetData['expires_at']) < new DateTimeImmutable()) {
        flash_set('error', 'OTP has expired. Please request a new one.');
        safe_redirect('../forgot-password.php');
    }
    
    // 3. Reset Password
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :id')->execute([':h'=>$hash, ':id'=>$uid]);
    
    // 4. Cleanup
    $pdo->prepare('DELETE FROM password_resets WHERE user_id = :id')->execute([':id'=>$uid]);
    unset($_SESSION['reset_email']); // Clear reset session
    
    flash_set('success', 'Password updated successfully. Please login.');
    safe_redirect('../account.php?tab=login');

} catch (Throwable $e) {
    error_log('Reset OTP Error: ' . $e->getMessage());
    flash_set('error', 'An error occurred. Please try again.');
    safe_redirect('../enter-otp.php');
}
