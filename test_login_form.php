<?php
// Test login form submission
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $message = 'Email and password are required.';
    } else {
        try {
            $pdo = get_db();
            $stmt = $pdo->prepare('SELECT id, name, email, password_hash, is_active FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                if (empty($user['is_active'])) {
                    $message = 'Account not activated. Please verify your email.';
                } else {
                    // Login success
                    session_regenerate();
                    $_SESSION['user'] = [
                        'id' => (int)$user['id'],
                        'name' => $user['name'],
                        'email' => $user['email']
                    ];
                    $message = 'Login successful! User: ' . $user['name'];
                }
            } else {
                $message = 'Invalid email or password.';
            }
        } catch (Throwable $e) {
            $message = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2>Login Test</h2>
                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>

                <div class="mt-4">
                    <h5>Test Users:</h5>
                    <p><strong>Email:</strong> ritesh.singh@venetsmedia.com</p>
                    <p><strong>Password:</strong> (Contact admin for password)</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
