<?php
declare(strict_types=1);

/**
 * includes/db.php
 * Database connection wrapper
 */

// Load configuration
$config = require __DIR__ . '/config.php';
if (!is_array($config) || !isset($config['db'])) {
    // Fallback if config.php returns null or invalid structure
    $dbConfig = [
        'dsn' => 'mysql:host=localhost;dbname=invest13_sbsmart;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    ];
} else {
    $dbConfig = $config['db'];
}

/**
 * Get the PDO database connection
 * @return PDO
 */
function get_db(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        global $dbConfig;

        // Ensure dbConfig is set if global failed (e.g. inside function scope issues)
        if (empty($dbConfig)) {
             $config = require __DIR__ . '/config.php';
             $dbConfig = $config['db'] ?? null;
        }
        
        // Final fallback
        if (empty($dbConfig)) {
            $dbConfig = [
                'dsn' => 'mysql:host=localhost;dbname=invest13_sbsmart;charset=utf8mb4',
                'user' => 'root',
                'pass' => '',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            ];
        }
        
        try {
            $pdo = new PDO(
                $dbConfig['dsn'],
                $dbConfig['user'],
                $dbConfig['pass'],
                $dbConfig['options']
            );

            // Auto-create cart table if not exists (Dev/Setup convenience)
            $pdo->exec("CREATE TABLE IF NOT EXISTS cart (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_product (user_id, product_id),
                CONSTRAINT fk_cart_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_cart_products FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Auto-create password_resets table if not exists (Dev/Setup convenience)
            $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                token VARCHAR(255) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_token (user_id, token),
                CONSTRAINT fk_resets_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Auto-create password_otps table (New Pattern)
            $pdo->exec("CREATE TABLE IF NOT EXISTS password_otps (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL,
                otp_hash VARCHAR(255) NOT NULL,
                expires_at DATETIME NOT NULL,
                used TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_used (user_id, used),
                CONSTRAINT fk_otps_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        } catch (PDOException $e) {
            // Log detailed error information
            $errorDetails = sprintf(
                "Database connection failed:\n" .
                "Error Code: %s\n" .
                "Error Message: %s\n" .
                "DSN: %s\n" .
                "User: %s\n" .
                "File: %s\n" .
                "Line: %s",
                $e->getCode(),
                $e->getMessage(),
                $dbConfig['dsn'] ?? 'N/A',
                $dbConfig['user'] ?? 'N/A',
                $e->getFile(),
                $e->getLine()
            );
            
            error_log($errorDetails);
            
            // User-friendly error message
            $userMessage = 'Database connection error. ';
            
            // Provide specific guidance based on error
            if (strpos($e->getMessage(), 'Access denied') !== false) {
                $userMessage .= 'Invalid database credentials. Please contact support.';
            } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
                $userMessage .= 'Database not found. Please contact support.';
            } elseif (strpos($e->getMessage(), "Can't connect") !== false) {
                $userMessage .= 'Cannot reach database server. Please contact support.';
            } else {
                $userMessage .= 'Please check server logs or contact support.';
            }
            
            // In development, show detailed error
            if (getenv('SITE_ENV') === 'development' || getenv('SITE_DEBUG') === 'true') {
                die("<pre style='background:#fee; padding:20px; border:2px solid #c00;'>" . 
                    htmlspecialchars($errorDetails) . "</pre>");
            }
            
            die($userMessage);
        }
    }
    
    return $pdo;
}

/**
 * Alias for get_db()
 * @return PDO
 */
function db(): PDO {
    return get_db();
}
