<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';

if (!empty($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT email,name,is_active FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id'=>$uid]);
    $u = $stmt->fetch();
    if ($u && empty($u['is_active'])) {
        $token = bin2hex(random_bytes(24));
        $pdo->prepare('UPDATE users SET verify_token = :t WHERE id = :id')->execute([':t'=>$token,':id'=>$uid]);
        $cfg = require __DIR__ . '/../includes/config.php';
        $mail = new Mailer();
        $url = rtrim($cfg['site']['base_url'],'/') . '/auth/verify.php?token=' . urlencode($token);
        $mail->send($u['email'],'Verify your account','Click: <a href="'.$url.'">Verify</a>','Verify: '.$url);
        flash_set('success','Verification email sent.');
        header('Location: /account.php');
        exit;
    }
    flash_set('info','Account already active or user not found.');
    header('Location: /account.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('error','Invalid email.');
        header('Location: /auth/resend-verification.php');
        exit;
    }
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT id,is_active,name FROM users WHERE email = :e LIMIT 1');
    $stmt->execute([':e'=>$email]);
    $u = $stmt->fetch();
    if (!$u) { flash_set('error','User not found'); header('Location: /auth/resend-verification.php'); exit; }
    if (!empty($u['is_active'])) { flash_set('info','Already active.'); header('Location: /auth/login.php'); exit; }
    $token = bin2hex(random_bytes(24));
    $pdo->prepare('UPDATE users SET verify_token = :t WHERE id = :id')->execute([':t'=>$token,':id'=>$u['id']]);
    $cfg = require __DIR__ . '/../includes/config.php';
    $mail = new Mailer();
    $url = rtrim($cfg['site']['base_url'],'/') . '/auth/verify.php?token=' . urlencode($token);
    $mail->send($email,'Verify account','Click: <a href="'.$url.'">Verify</a>','Verify: '.$url);
    flash_set('success','Verification email sent.');
    header('Location: /auth/login.php');
    exit;
}

$page_title = 'Resend verification';
require __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <h3>Resend verification</h3>
  <?php if ($m = flash_get('error')): ?><div class="alert alert-danger"><?= esc($m) ?></div><?php endif; ?>
  <form method="post" action="/auth/resend-verification.php">
    <div class="mb-3"><label class="form-label">Email</label><input name="email" type="email" class="form-control" required></div>
    <button class="btn btn-primary" type="submit">Send</button>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
