<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
if ($token === '') {
    flash_set('error','Invalid token.');
    header('Location: /auth/login.php');
    exit;
}
$pdo = get_db();
$stmt = $pdo->prepare('SELECT pr.id AS reset_id, pr.user_id, pr.expires_at, u.email FROM password_resets pr JOIN users u ON u.id = pr.user_id WHERE pr.token = :t LIMIT 1');
$stmt->execute([':t'=>$token]);
$row = $stmt->fetch();
if (!$row) { flash_set('error','Invalid or expired token.'); header('Location: /auth/login.php'); exit; }
if (new DateTimeImmutable($row['expires_at']) < new DateTimeImmutable()) {
    flash_set('error','Token expired.');
    header('Location: /auth/forgot-password.php');
    exit;
}

$page_title = 'Reset password';
require __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <h3>Reset password</h3>
  <?php if ($m = flash_get('error')): ?><div class="alert alert-danger"><?= esc($m) ?></div><?php endif; ?>
  <form method="post" action="/auth/reset-password-action.php" novalidate>
    <input type="hidden" name="token" value="<?= esc($token) ?>">
    <div class="mb-3"><label class="form-label">New password</label><input name="password" type="password" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Confirm</label><input name="password_confirm" type="password" class="form-control" required></div>
    <button class="btn btn-primary" type="submit">Set password</button>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
