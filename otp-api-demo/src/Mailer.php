<?php
namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    public function sendOTP(string $toEmail, string $otp): bool {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION']; // tls or ssl
            $mail->Port       = $_ENV['MAIL_PORT'];

            // Recipients
            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($toEmail);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Reset OTP';
            $mail->Body    = "
                <h3>Password Reset Request</h3>
                <p>Your One-Time Password (OTP) is: <h1>$otp</h1></p>
                <p>This code expires in 10 minutes.</p>
                <p>If you did not request this, please ignore this email.</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log error: $mail->ErrorInfo
            return false;
        }
    }
}
