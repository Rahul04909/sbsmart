<?php
// verify-register.php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';

$pending = $_SESSION['reg_pending'] ?? null;
if (!$pending || empty($pending['email'])) {
    flash_set('warning', 'Registration session expired. Please register again.');
    safe_redirect('account.php?tab=register');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');
    
    if (empty($otp)) {
        flash_set('error', 'Please enter the verification code.');
    } else {
        // Validation
        if (time() > $pending['expires_at']) {
            flash_set('error', 'OTP has expired. Please register again.');
            unset($_SESSION['reg_pending']);
            safe_redirect('account.php?tab=register'); // force restart
        } elseif (!password_verify($otp, $pending['otp_hash'])) {
            flash_set('error', 'Invalid verification code.');
        } else {
            // Valid! Commit to DB.
            try {
                $pdo = get_db();
                // Check email uniqueness again (race condition check)
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
                $stmt->execute([':email' => $pending['email']]);
                if ($stmt->fetch()) {
                     flash_set('error', 'Email already registered.');
                     unset($_SESSION['reg_pending']);
                     safe_redirect('account.php?tab=login');
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, phone, password_hash, is_active, created_at)
                    VALUES (:name, :email, :phone, :hash, 1, NOW())
                ");
                $stmt->execute([
                    ':name' => $pending['name'],
                    ':email' => $pending['email'],
                    ':phone' => $pending['phone'],
                    ':hash' => $pending['password_hash']
                ]);
                
                // Cleanup
                unset($_SESSION['reg_pending']);
                
                flash_set('success', 'Account created successfully! Please login.');
                safe_redirect('account.php?tab=login');
                
            } catch (Throwable $e) {
                error_log('Final Reg Error: ' . $e->getMessage());
                flash_set('error', 'Database error.');
            }
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
                    <h4 class="mb-3">Verify Registration</h4>
                    <p class="text-muted">A code has been sent to <strong><?= htmlspecialchars($pending['email']) ?></strong></p>

                    <form method="post">
                        <?= csrf_input(); ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Verification Code</label>
                            <input type="text" name="otp" class="form-control" placeholder="6-digit code" required maxlength="6">
                        </div>

                        <button class="btn btn-primary w-100">Verify & Create Account</button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <a href="account.php?tab=register" class="small">Change Email / Register Again</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
