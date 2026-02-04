<?php
// enter-otp.php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/session.php';

// Ensure we have an email in session to verify against
if (empty($_SESSION['reset_email'])) {
    flash_set('warning', 'Session expired. Please start over.');
    safe_redirect('forgot-password.php');
}
$email = $_SESSION['reset_email'];
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
                    <p class="text-muted small">Enter the OTP sent to <strong><?= htmlspecialchars($email) ?></strong> and your new password.</p>

                    <form action="auth/reset-password-otp-action.php" method="post">
                        <?= csrf_input(); ?>
                        
                        <div class="mb-3">
                            <label class="form-label">OTP Code</label>
                            <input type="text" name="otp" class="form-control" placeholder="6-digit code" required pattern="\d{6}" maxlength="6">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirm" class="form-control" required minlength="6">
                        </div>

                        <button class="btn btn-primary w-100">Reset Password</button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
