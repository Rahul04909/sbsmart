<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/session.php'; // Defines defaults
require_once __DIR__ . '/includes/helpers.php';
$config = require __DIR__ . '/includes/config.php';

echo "<h2>SMTP Debugger</h2>";

// Check Config
$mailCfg = $config['mail'] ?? [];
echo "SMTP Config Loaded:<br>";
echo "Host: " . htmlspecialchars($mailCfg['smtp_host'] ?? 'Not Set') . "<br>";
echo "Port: " . htmlspecialchars($mailCfg['smtp_port'] ?? 'Not Set') . "<br>";
echo "User: " . htmlspecialchars($mailCfg['smtp_user'] ?? 'Not Set') . "<br>";
echo "Pass: " . substr($mailCfg['smtp_pass'] ?? '', 0, 4) . "***<br>";

// Manual PHPMailer Test
require_once __DIR__ . '/vendor/autoload.php'; // Try composer autoload
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "PHPMailer class not found via vendor/autoload.php. Trying manual include if needed...<br>";
    // Fallback if vendor is missing? User said they wanted Composer previously.
    // Assuming vendor exists.
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;   // Enable verbose debug output
    $mail->isSMTP();
    $mail->Host       = $mailCfg['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailCfg['smtp_user'];
    $mail->Password   = $mailCfg['smtp_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Force TLS for 587
    $mail->Port       = $mailCfg['smtp_port'];

    // Recipients
    $mail->setFrom($mailCfg['from_email'], 'Debug Test');
    $mail->addAddress($mailCfg['smtp_user']); // Send to self

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test Email';
    $mail->Body    = 'This is a test email <b>in bold!</b>';

    echo "<pre>";
    $mail->send();
    echo "</pre>";
    echo "<h3 style='color:green'>Message has been sent</h3>";
} catch (Exception $e) {
    echo "<pre>";
    // Output is already captured by SMTPDebug usually, but print error too
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    echo "</pre>";
}
