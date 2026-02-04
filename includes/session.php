<?php
declare(strict_types=1);

/**
 * session.php
 * Robust session bootstrap. Must be included before any output is sent.
 *
 * - Avoids fatal warnings when headers already sent.
 * - Sets safe defaults for cookies when possible.
 * - Provides small helpers for flash messages and timeout enforcement.
 */

// Nothing should echo here. Silence is important.

if (php_sapi_name() === 'cli') {
    // No sessions for CLI scripts
    return;
}

// Only attempt to change ini/session settings if headers not sent
$can_modify_headers = !headers_sent();

if ($can_modify_headers && session_status() === PHP_SESSION_NONE) {
    // Strong session config (only set if allowed)
    @ini_set('session.use_strict_mode', '1');
    @ini_set('session.cookie_httponly', '1');

    // Prefer 'Lax' for widest compatibility; adjust to 'Strict' if you know there is no cross-site use
    if (PHP_VERSION_ID >= 70300) {
        // use session_set_cookie_params with options array (PHP 7.3+)
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '', // default to current host; set explicitly if needed
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } else {
        // older PHP: best-effort
        session_set_cookie_params(
            0,
            '/',
            '',
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            true
        );
    }

    // If using HTTPS, ensure secure flag for cookies
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        @ini_set('session.cookie_secure', '1');
    }
}

// Ensure consistent session name (only before session_start and when safe)
if ($can_modify_headers && session_status() === PHP_SESSION_NONE) {
    // Use a short, unique name but avoid sensitive info in name
    session_name('sbsmart_sess');
}

// Start the session if not started and headers not sent in a way preventing it
if (session_status() === PHP_SESSION_NONE) {
    // If headers already sent, attempt to start anyway — PHP may still allow it, but warnings
    // are suppressed here to avoid visible warnings in production. Errors still go to logs.
    try {
        @session_start();
    } catch (Throwable $e) {
        // Log and continue; pages expecting a session may behave differently.
        error_log('session.php: session_start() failed: ' . $e->getMessage());
    }
}

/* -------------------------
   Helpers (flash + timeout)
   ------------------------- */

/**
 * Regenerate session id (should be called after login)
 */
if (!function_exists('session_regenerate')) {
    function session_regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}

/**
 * Flash messages
 */
if (!function_exists('flash_set')) {
    function flash_set(string $key, $value): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) return;
        $_SESSION['_flash'][$key] = $value;
    }
}
if (!function_exists('flash_get')) {
    function flash_get(?string $key = null, $default = null)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) return $default;
        
        // If no key provided, return all flash messages and clear them
        if ($key === null) {
            $all = $_SESSION['_flash'] ?? [];
            $_SESSION['_flash'] = [];
            return $all;
        }

        if (!isset($_SESSION['_flash'][$key])) return $default;
        $val = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
}

/**
 * Session timeout enforcement
 */
if (!function_exists('enforce_session_timeout')) {
    function enforce_session_timeout(int $seconds = 1800): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) return;
        $now = time();
        if (isset($_SESSION['last_activity']) && ($now - (int)$_SESSION['last_activity']) > $seconds) {
            session_unset();
            session_destroy();
            @session_start();
        }
        $_SESSION['last_activity'] = $now;
    }
}
