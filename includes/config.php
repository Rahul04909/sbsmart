<?php
declare(strict_types=1);

/**
 * config.php
 * Configuration loader that reads from .env file
 *
 * Environment variables are loaded from .env file in project root.
 * Edit values in .env file, not here.
 */

// Simple .env loader (PHP 7.x compatible)
if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void {
        if (!file_exists($path)) {
            error_log("WARNING: .env file not found at: $path");
            return;
        }
        
        if (!is_readable($path)) {
            error_log("WARNING: .env file exists but is not readable at: $path");
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            error_log("WARNING: Failed to read .env file at: $path");
            return;
        }
        
        $loadedCount = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments (PHP 7.x compatible)
            if (strlen($line) > 0 && $line[0] === '#') {
                continue;
            }

            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes if present (PHP 7.x compatible)
                $firstChar = strlen($value) > 0 ? $value[0] : '';
                $lastChar = strlen($value) > 0 ? $value[strlen($value) - 1] : '';
                
                if (($firstChar === '"' && $lastChar === '"') ||
                    ($firstChar === "'" && $lastChar === "'")) {
                    $value = substr($value, 1, -1);
                }

                putenv("$key=$value");
                $_ENV[$key] = $value;
                $loadedCount++;
            }
        }
        
        error_log("INFO: Loaded $loadedCount environment variables from .env");
    }
}

// Load .env file from project root
loadEnv(__DIR__ . '/../.env');

return (function(): array {

    /* ---------------------------------------------------------
       BASIC SITE SETTINGS
       Values loaded from .env file
    --------------------------------------------------------- */
    $site = [
        'name'       => getenv('SITE_NAME') ?: 'SBSmart',
        'base_url'   => getenv('SITE_BASE_URL') ?: 'https://sbsmart.in',
        'assets_path'=> getenv('SITE_ASSETS_PATH') ?: '/assets',
        'env'        => getenv('SITE_ENV') ?: 'production',
        'debug'      => filter_var(getenv('SITE_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    ];

    /* ---------------------------------------------------------
       DATABASE
       Values loaded from .env file
    --------------------------------------------------------- */
    $db = [
        'dsn'  => sprintf('mysql:host=%s;dbname=%s;charset=%s',
                         getenv('DB_HOST') ?: 'localhost',
                         getenv('DB_NAME') ?: 'invest13_sbsmart',
                         getenv('DB_CHARSET') ?: 'utf8mb4'),
        'user' => getenv('DB_USER') ?: 'invest13_pram',
        'pass' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'aA1qwerty@@@',
        'options' => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ],
    ];

    /* ---------------------------------------------------------
       MAILER SETTINGS
       Values loaded from .env file
    --------------------------------------------------------- */
    $mail = [
        'smtp_host'  => getenv('MAIL_SMTP_HOST') ?: 'mail.sbsmart.in',
        'smtp_port'  => (int)(getenv('MAIL_SMTP_PORT') ?: 587),
        'smtp_user'  => getenv('MAIL_SMTP_USER') ?: 'noreply@sbsmart.in',
        'smtp_pass'  => getenv('MAIL_SMTP_PASS') ?: 'aA1qwerty@@@',
        'from_email' => getenv('MAIL_FROM_EMAIL') ?: 'noreply@sbsmart.in',
        'from_name'  => getenv('MAIL_FROM_NAME') ?: 'SBSmart',
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
        'use_smtp'   => filter_var(getenv('MAIL_USE_SMTP') ?: 'true', FILTER_VALIDATE_BOOLEAN),
    ];

    /* ---------------------------------------------------------
       PAYMENT — CCAvenue
       Values loaded from .env file
    --------------------------------------------------------- */
    $payments = [
        'ccavenue' => [
            'merchant_id'  => getenv('CCAVENUE_MERCHANT_ID') ?: '254361',
            'access_code'  => getenv('CCAVENUE_ACCESS_CODE') ?: 'AVY9JHI33CI9S6VYIC',
            'working_key'  => getenv('CCAVENUE_WORKING_KEY') ?: '20F8642681BB4F3BA1BD8D6B38F727AE0C',
            'redirect_url' => getenv('CCAVENUE_REDIRECT_URL') ?: 'https://sbsmart.in/ccavResponseHandler.php',
            'cancel_url'   => getenv('CCAVENUE_CANCEL_URL') ?: 'https://sbsmart.in/ccavCancel.php',
        ]
    ];

    /* ---------------------------------------------------------
       RETURN FINAL CONFIG
    --------------------------------------------------------- */
    return compact('site', 'db', 'mail', 'payments');
})();
