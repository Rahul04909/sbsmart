<?php
namespace App;

use PDO;
use Exception;

class AuthController {
    private PDO $db;
    private Mailer $mailer;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->mailer = new Mailer();
    }

    public function forgotPassword(string $email): array {
        // 1. Check if email exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Email does not exist'];
        }

        // 2. Generate OTP
        $otp = (string) random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // 3. Save OTP (Delete old ones first)
        $this->db->prepare("DELETE FROM password_resets WHERE email = :email")->execute(['email' => $email]);
        
        $sql = "INSERT INTO password_resets (email, otp, expires_at) VALUES (:email, :otp, :expires_at)";
        $stmt = $this->db->prepare($sql);
        $saved = $stmt->execute([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => $expiresAt
        ]);

        if ($saved) {
            // 4. Send Email
            if ($this->mailer->sendOTP($email, $otp)) {
                return ['success' => true, 'message' => 'OTP sent to your email'];
            }
            return ['success' => false, 'message' => 'Failed to send email'];
        }

        return ['success' => false, 'message' => 'System error'];
    }

    public function resetPassword(string $email, string $otp, string $newPass, string $confirmPass): array {
        // Validation
        if ($newPass !== $confirmPass) {
            return ['success' => false, 'message' => 'Passwords do not match'];
        }
        if (strlen($newPass) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters'];
        }

        // Verify OTP
        $stmt = $this->db->prepare("SELECT * FROM password_resets WHERE email = :email AND otp = :otp");
        $stmt->execute(['email' => $email, 'otp' => $otp]);
        $resetRequest = $stmt->fetch();

        if (!$resetRequest) {
            return ['success' => false, 'message' => 'Invalid OTP'];
        }

        if (strtotime($resetRequest['expires_at']) < time()) {
            return ['success' => false, 'message' => 'OTP expired'];
        }

        // Update Password
        $hashedPassword = password_hash($newPass, PASSWORD_DEFAULT);
        $updateStmt = $this->db->prepare("UPDATE users SET password = :pass WHERE email = :email");
        $updated = $updateStmt->execute(['pass' => $hashedPassword, 'email' => $email]);

        if ($updated) {
            // Invalidate OTP
            $this->db->prepare("DELETE FROM password_resets WHERE email = :email")->execute(['email' => $email]);
            return ['success' => true, 'message' => 'Password updated successfully'];
        }

        return ['success' => false, 'message' => 'Failed to update password'];
    }
}
