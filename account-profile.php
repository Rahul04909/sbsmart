<?php
// account-profile.php
// Save as UTF-8 WITHOUT BOM. No output before session_start().

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';

require_login('account-profile.php'); // ensures user logged in and redirects to login if not

$flash = flash_get(); // retrieves and clears flash messages
$user = current_user(); // minimal user array stored in session
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="container py-5" role="main" aria-labelledby="profile-heading">
  <h1 id="profile-heading" class="visually-hidden">My Profile</h1>

  <div class="row justify-content-center">
    <div class="col-md-8">

      <!-- Flash messages -->
      <?php if (!empty($flash)): ?>
        <?php foreach ($flash as $type => $messages): ?>
          <?php
            $bs = ($type === 'error' || $type === 'danger') ? 'danger' : (($type === 'success') ? 'success' : 'info');
          ?>
          <?php foreach ((array)$messages as $m): ?>
            <div class="alert alert-<?= $bs ?> alert-dismissible fade show" role="alert">
              <?= htmlspecialchars((string)$m, ENT_QUOTES|ENT_SUBSTITUTE) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endforeach; ?>
        <?php endforeach; ?>
      <?php endif; ?>

      <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-start gap-3">
          <div>
            <h4 class="mb-1">Account</h4>
            <div class="small text-muted">Manage your account details</div>
          </div>

          <div class="d-flex gap-2">
            <!-- Logout (POST) -->
            <form action="auth/logout.php" method="post" class="m-0">
              <?= csrf_input(); ?>
              <button type="submit" class="btn btn-outline-secondary btn-sm">Logout</button>
            </form>
            <a href="account-orders.php" class="btn btn-outline-primary btn-sm">My Orders</a>
            <a href="account-profile.php#profile-form" class="btn btn-outline-primary btn-sm">Edit</a>
          </div>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">

          <h5 class="mb-3">Profile Details</h5>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <div class="d-flex align-items-center justify-content-between gap-3">
              <div>
                <div class="fw-semibold"><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE) ?></div>
                <?php if (!empty($user['is_verified'])): ?>
                  <!-- <div class="small text-success">Email verified</div> -->
                <?php else: ?>
                  <!-- <div class="small text-warning">Email not verified</div> -->
                <?php endif; ?>
              </div>

              <?php if (empty($user['is_verified'])): ?>
                <!-- Resend verification (POST) -->
                <form action="auth/resend-verification.php" method="post" class="m-0">
                  <?= csrf_input(); ?>
                  <input type="hidden" name="email" value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE) ?>">
                  <!-- <button type="submit" class="btn btn-sm btn-outline-warning">Resend verification</button> -->
                </form>
              <?php endif; ?>
            </div>
          </div>

          <form id="profile-form" action="auth/profile-update.php" method="post" class="mt-3">
            <?= csrf_input(); ?>

            <div class="mb-3">
              <label for="profile-name" class="form-label">Full Name</label>
              <input id="profile-name" type="text" name="name" class="form-control"
                     value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE) ?>" required>
            </div>

            <div class="mb-3">
              <label for="profile-phone" class="form-label">Phone</label>
              <input id="profile-phone" type="tel" name="phone" class="form-control"
                     value="<?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE) ?>" pattern="\d{10}">
              <div class="form-text">Enter 10-digit mobile number (optional).</div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary">Save Changes</button>

              <!-- Password reset link (uses existing reset flow) -->
              <a href="forgot-password.php" class="btn btn-outline-secondary">Forgot Password</a>
            </div>
          </form>

        </div>
      </div>

    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
