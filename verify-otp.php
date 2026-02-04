<?php
// verify-otp.php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';

$email = $_SESSION['reset_email'] ?? null;
if (!$email) {
    flash_set('warning', 'Session expired. Please start over.');
    safe_redirect('forgot-password.php');
}

// Logic: Handle Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otpInput = trim($_POST['otp'] ?? '');

    if ($otpInput === '') {
        flash_set('error', 'Please enter OTP.');
    } else {
        try {
            $pdo = get_db();
            
            // Get User ID
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :e LIMIT 1");
            $stmt->execute([':e' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                // Should exist if session email is valid, but safety check
                flash_set('error', 'User not found.');
                safe_redirect('forgot-password.php');
            }
            $userId = $user['id'];

            // Find latest unused OTP
            $stmt = $pdo->prepare("
                SELECT * FROM password_otps
                WHERE user_id = :uid AND used = 0
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $stmt->execute([':uid' => $userId]);
            $row = $stmt->fetch();

            if (!$row) {
                 flash_set('error', 'No valid OTP found or already used.');
            } else {
                $now = new DateTimeImmutable();
                $expiresAt = new DateTimeImmutable($row['expires_at']);

                if ($now > $expiresAt) {
                    flash_set('error', 'OTP has expired.');
                } elseif (!password_verify($otpInput, $row['otp_hash'])) {
                    flash_set('error', 'Invalid OTP code.');
                } else {
                    // Valid! Mark used.
                    $stmt = $pdo->prepare("UPDATE password_otps SET used = 1 WHERE id = :id");
                    $stmt->execute([':id' => $row['id']]);

                    // Set session for next step (reset password)
                    $_SESSION['reset_user_id'] = $userId;
                    
                    flash_set('success', 'OTP Verified.');
                    safe_redirect('reset-password.php');
                }
            }
        } catch (Throwable $e) {
            error_log('Verify OTP error: ' . $e->getMessage());
            flash_set('error', 'System error during verification.');
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
                    <h4 class="mb-3">Verify OTP</h4>
                    <p class="text-muted">OTP has been sent to: <strong><?= htmlspecialchars($email) ?></strong></p>

                    <form method="post">
                        <?= csrf_input(); // Optional but good practice ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Enter OTP</label>
                            <input type="text" name="otp" class="form-control" maxlength="6" required placeholder="6-digit code">
                        </div>

                        <button class="btn btn-primary w-100">Verify</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
