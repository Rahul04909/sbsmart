<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('error','Invalid email format');
        header('Location: ../forgot-password.php');
        exit;
    }
    
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT id, name FROM users WHERE email = :e LIMIT 1');
        $stmt->execute([':e'=>$email]);
        $u = $stmt->fetch();
        
        if (!$u) { 
            flash_set('error', 'Email address not found.');
            header('Location: ../forgot-password.php'); 
            exit; 
        }
        
        // Generate 6-digit OTP
        $otp = (string)random_int(100000, 999999);
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        
        // Expiry (e.g. 15 minutes)
        $expires = (new DateTimeImmutable('+15 minutes'))->format('Y-m-d H:i:s');
        
        // Insert into password_otps
        $stmt = $pdo->prepare("
            INSERT INTO password_otps (user_id, otp_hash, expires_at, used) 
            VALUES (:uid, :hash, :exp, 0)
        ");
        $stmt->execute([
            ':uid'  => $u['id'],
            ':hash' => $otpHash,
            ':exp'  => $expires
        ]);
            
        // Send Email (unhashed OTP)
        $mail = new Mailer();
        $sent = $mail->send($email, 'Password Reset OTP', "Your OTP is: <b>$otp</b>. It expires in 15 minutes.", "Your OTP is: $otp");
        
        if (!$sent) {
            error_log("Mail failed for $email");
             // Optional: fail logic, or let user proceed (maybe they saw it in logs?)
        }

        // Store email in session for next step
        $_SESSION['reset_email'] = $email;
        
        flash_set('success','OTP sent to your email.');
        header('Location: ../verify-otp.php');
        exit;
        
    } catch (Throwable $e) {
        error_log('Forgot error: '.$e->getMessage());
        flash_set('error','Failed to process request: ' . $e->getMessage());
        header('Location: ../forgot-password.php');
        exit;
    }
}

header('Location: ../forgot-password.php');
exit;
