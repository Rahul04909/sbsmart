<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';
if (file_exists(__DIR__ . '/../includes/mailer.php')) {
    require_once __DIR__ . '/../includes/mailer.php';
} else {
    die("Error: includes/mailer.php is missing. Please upload the 'includes' folder to your server.");
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check honeypot
    if (!empty($_POST['fax'])) {
        header('Location: ../account.php?tab=register');
        exit;
    }

    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate input
    if ($name === '' || $email === '' || $password === '') {
        flash_set('error', 'Name, email, and password are required.');
        header('Location: ../account.php?tab=register');
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('error', 'Invalid email address.');
        header('Location: ../account.php?tab=register');
        exit;
    }
    if (strlen($password) < 6) {
        flash_set('error', 'Password must be at least 6 characters.');
        header('Location: ../account.php?tab=register');
        exit;
    }

    try {
        $pdo = get_db();
        // Check if email exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            flash_set('error', 'Email is already registered. Please login.');
            header('Location: ../account.php?tab=login');
            exit;
        }

        // Generate OTP
        $otp = (string)random_int(100000, 999999);
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        
        // Store partial completion in SESSION
        // We'll store: user data, otp hash, expiry (15 mins)
        $_SESSION['reg_pending'] = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT), // Hash pass now
            'otp_hash' => $otpHash,
            'expires_at' => time() + 900 // 15 mins
        ];

        // Send OTP
        $mail = new Mailer();
        $ok = $mail->send($email, 'Registration Verification', "Your verification code is: <b>$otp</b>. This usage expires in 15 minutes.", "Your OTP is: $otp");
        
        if (!$ok) {
            flash_set('error', 'Failed to send verification email. Please try again.');
            header('Location: ../account.php?tab=register');
            exit;
        }

        flash_set('success', 'Verification code sent to your email.');
        header('Location: ../verify-register.php');
        exit;

    } catch (Throwable $e) {
        error_log('Register error: ' . $e->getMessage());
        flash_set('error', 'An error occurred. Please try again.');
        header('Location: ../account.php?tab=register');
        exit;
    }
}

header('Location: ../account.php?tab=register');
exit;
