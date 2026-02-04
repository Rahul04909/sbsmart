<?php
declare(strict_types=1);

// Backend processor for contact form
// Logic moved here to be included by the public endpoint

// Adjust paths relative to 'includes/' directory
require_once __DIR__ . '/helpers.php';
$config = require __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.php');
    exit;
}

// Minimal sanitization
$n = htmlspecialchars(trim($_POST['user_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$e = filter_var(trim($_POST['user_email'] ?? ''), FILTER_SANITIZE_EMAIL);
$p = htmlspecialchars(trim($_POST['user_phone'] ?? ''), ENT_QUOTES, 'UTF-8');
$s = htmlspecialchars(trim($_POST['user_subject'] ?? ''), ENT_QUOTES, 'UTF-8');
$m = htmlspecialchars(trim($_POST['user_msg'] ?? ''), ENT_QUOTES, 'UTF-8');
$c = htmlspecialchars(trim($_POST['user_company'] ?? ''), ENT_QUOTES, 'UTF-8');

$err = [];
if (!$n) $err[] = 'Name is required';
if (!$e || !filter_var($e, FILTER_VALIDATE_EMAIL)) $err[] = 'Invalid Email';
if (!$s) $err[] = 'Subject required';
if (!$m) $err[] = 'Message required';

// Safe Link Check (No Regex)
$bad = ['http:', 'https:', 'www.', '.com', '.net', '.org'];
foreach($bad as $b) {
    if (stripos($s, $b) !== false || stripos($m, $b) !== false) {
        $err[] = 'No links allowed';
        break;
    }
}

if (!empty($err)) {
    $_SESSION['contact_error'] = implode(', ', $err);
    header('Location: contact.php');
    exit;
}

if ($c) $m = "Company: $c\n\n$m";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Adjust path to vendor (up one level from includes)
require __DIR__ . '/../vendor/autoload.php';

try {
    $mc = $config['mail'];
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $mc['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $mc['smtp_user'];
    $mail->Password = $mc['smtp_pass'];
    $mail->SMTPSecure = ($mc['encryption'] === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $mc['smtp_port'];

    $mail->setFrom($mc['smtp_user'], 'SBSmart Notification'); 
    $mail->addAddress('marcom.sbsyscon@gmail.com');
    $mail->addReplyTo($e, $n);

    $mail->isHTML(false);
    $mail->Subject = 'Contact Inquiry';
    $mail->Body = "Name: $n\nEmail: $e\nPhone: $p\nSubject: $s\n\nMessage:\n$m\n";

    $mail->send();
} catch (Exception $ex) {
    $errorMsg = $mail->ErrorInfo;
    // Log to specific file
    $logDir = isset($_SERVER['HOME']) ? $_SERVER['HOME'] . '/logs' : __DIR__;
    if (!is_dir($logDir) && isset($_SERVER['HOME'])) {
       $logDir = __DIR__; 
    }
    file_put_contents($logDir . '/mail_error.log', date('Y-m-d H:i:s') . " - Error: " . $errorMsg . "\n", FILE_APPEND);
    error_log("Mail Error: " . $errorMsg);
    $_SESSION['contact_error'] = 'Sending Failed.';
}

// DB Log (Silent)
try {
    if (function_exists('db') || function_exists('get_db')) {
        $pdo = function_exists('db') ? db() : get_db();
        if ($pdo) {
            $stm = $pdo->prepare("INSERT INTO contact_submissions (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stm->execute([$n, $e, $p, $s, $m]);
        }
    }
} catch (Exception $e) {}

if (!isset($_SESSION['contact_error'])) {
    $_SESSION['contact_success'] = 'Thank you!';
}
header('Location: contact.php');
exit;
