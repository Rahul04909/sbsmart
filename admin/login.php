<?php
// login.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Enter email & password';
    } else {
        $user = admin_user_by_email($email);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid credentials';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container">
  <div class="row justify-content-center" style="margin-top:10vh">
    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h4 class="card-title mb-3">Admin Login</h4>
          <?php if($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>
          <form method="post">
            <div class="mb-2">
              <label>Email</label>
              <input class="form-control" name="email" type="email" required>
            </div>
            <div class="mb-3">
              <label>Password</label>
              <input class="form-control" name="password" type="password" required>
            </div>
            <button class="btn btn-primary w-100">Login</button>
          </form>
        </div>
      </div>
      <p class="text-muted mt-2">Use your admin credentials.</p>
    </div>
  </div>
</div>
</body>
</html>
