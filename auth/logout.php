<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'] ?? '/', $p['domain'] ?? '', $p['secure'] ?? false, $p['httponly'] ?? true);
}
// Clear Remember Me Cookie
if (!empty($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
    
    // Optional: Delete from DB if we can connect
    // Since logout.php doesn't include db.php by default here, we might skip it or require it.
    // For completeness, let's try.
    if (file_exists(__DIR__ . '/../includes/db.php')) {
        require_once __DIR__ . '/../includes/db.php';
        try {
            if (function_exists('get_db')) {
                $parts = explode(':', $_COOKIE['remember_me']);
                if (count($parts) === 2) {
                    $selector = $parts[0];
                    $pdo = get_db();
                    $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE selector = ?");
                    $stmt->execute([$selector]);
                }
            }
        } catch (Exception $e) { }
    }
}
@session_destroy();
header('Location: login.php');
exit;
