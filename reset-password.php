<?php
// reset-password.php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';

$userId = $_SESSION['reset_user_id'] ?? null;
// We also keep email for display
$email = $_SESSION['reset_email'] ?? 'User';

if (!$userId) {
    flash_set('error', 'Unauthorized or session expired.');
    safe_redirect('forgot-password.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($pass === '' || $confirm === '') {
        flash_set('error', 'Please fill all fields.');
    } elseif ($pass !== $confirm) {
        flash_set('error', 'Passwords do not match.');
    } elseif (strlen($pass) < 6) {
        flash_set('error', 'Password must be at least 6 characters.');
    } else {
        try {
            $pdo = get_db();
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
            $stmt->execute([
                ':hash' => $hash,
                ':id'   => $userId,
            ]);
            
            // Clear session
            unset($_SESSION['reset_user_id'], $_SESSION['reset_email']);
            
            flash_set('success', 'Password updated successfully. You can now login.');
            safe_redirect('account.php?tab=login');
            
        } catch (Throwable $e) {
            error_log('Reset Pass Error: ' . $e->getMessage());
            flash_set('error', 'Database error occurred.');
        }
    }
}
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <?php $flash = flash_get(); ?>
            <?php if (!empty($flash)): ?>
                <?php foreach ($flash as $type => $messages): ?>
                    <?php foreach ((array)$messages as $msg): ?>
                        <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($msg) ?></div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-3">Reset Password</h4>
                    <p class="text-muted">Set a new password for <strong><?= htmlspecialchars($email) ?></strong></p>

                    <form method="post">
                        <?= csrf_input(); ?>
                        
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>

                        <button class="btn btn-primary w-100">Update Password</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
