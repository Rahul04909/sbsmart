<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

if (empty($_SESSION['user']['id'])) {
    flash_set('error','Please login.');
    header('Location: ../account.php?tab=login');
    exit;
}
$uid = (int)$_SESSION['user']['id'];
$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pw = $_POST['password'] ?? '';
    $pw2 = $_POST['password_confirm'] ?? '';
    if ($pw !== '' && $pw !== $pw2) {
        flash_set('error','Passwords do not match.');
        header('Location: ../account-profile.php');
        exit;
    }
    try {
        if ($pw !== '') {
            $hash = password_hash($pw, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET name = :n, phone = :p, password_hash = :h WHERE id = :id')->execute([':n'=>$name,':p'=>$phone,':h'=>$hash,':id'=>$uid]);
        } else {
            $pdo->prepare('UPDATE users SET name = :n, phone = :p WHERE id = :id')->execute([':n'=>$name,':p'=>$phone,':id'=>$uid]);
        }
        
        // Update session data
        $_SESSION['user']['name'] = $name;
        if (isset($_SESSION['user']['phone'])) {
            $_SESSION['user']['phone'] = $phone;
        }
        
        flash_set('success','Profile updated.');
        header('Location: ../account-profile.php');
        exit;
    } catch (Throwable $e) {
        error_log('Profile update error: '.$e->getMessage());
        flash_set('error','Failed to update profile.');
        header('Location: ../account-profile.php');
        exit;
    }
}

// GET: fetch current values
$stmt = $pdo->prepare('SELECT name,email,phone FROM users WHERE id = :id LIMIT 1');
$stmt->execute([':id'=>$uid]);
$profile = $stmt->fetch() ?: [];

$page_title = 'Profile';
require __DIR__ . '/../includes/header.php';
?>
<div class="container py-5">
  <h3>Profile</h3>
  <?php if ($m = flash_get('error')): ?><div class="alert alert-danger"><?= esc($m) ?></div><?php endif; ?>
  <?php if ($m = flash_get('success')): ?><div class="alert alert-success"><?= esc($m) ?></div><?php endif; ?>
  <form method="post" action="" novalidate>
    <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= esc($profile['name'] ?? '') ?>"></div>
    <div class="mb-3"><label class="form-label">Email (read-only)</label><input class="form-control" value="<?= esc($profile['email'] ?? '') ?>" readonly></div>
    <div class="mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= esc($profile['phone'] ?? '') ?>"></div>
    <hr>
    <div class="mb-3">
        <label class="form-label d-block">Password</label>
        <a href="../forgot-password.php" class="btn btn-outline-secondary">Forgot Password?</a>
    </div>
    <button class="btn btn-primary" type="submit">Save</button>
  </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
