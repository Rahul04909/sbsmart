<?php 
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';
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
                    <h4 class="mb-3">Forgot Password</h4>

                    <form action="auth/forgot-password.php" method="post">
                        <?= csrf_input(); ?>

                        <div class="mb-3">
                            <label class="form-label">Enter your email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <button class="btn btn-primary w-100">Send Reset Otp</button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
