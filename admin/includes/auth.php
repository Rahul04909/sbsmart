<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

function is_logged_in(){
    return !empty($_SESSION['admin_id']);
}

function require_login(){
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function admin_user_by_email($email){
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = :email LIMIT 1");
    $stmt->execute(['email'=>$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
