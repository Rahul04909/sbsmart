<?php
/**
 * Database Connection Diagnostic Script
 * Upload this to your live server and access it via browser to see detailed error info
 */

// Load environment variables
function loadEnv(string $path): void {
    if (!file_exists($path)) {
        echo "❌ .env file not found at: $path\n";
        return;
    }
    
    echo "✅ .env file found\n";
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (str_starts_with($line, '#')) continue;
        
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

echo "<pre style='background:#f5f5f5; padding:20px; border-radius:8px; font-family:monospace;'>";
echo "=== DATABASE CONNECTION DIAGNOSTIC ===\n\n";

// Load .env
loadEnv(__DIR__ . '/.env');

echo "\n--- Environment Variables ---\n";
echo "DB_HOST: " . (getenv('DB_HOST') ?: 'NOT SET (will use: localhost)') . "\n";
echo "DB_NAME: " . (getenv('DB_NAME') ?: 'NOT SET (will use: invest13_sbsmart)') . "\n";
echo "DB_USER: " . (getenv('DB_USER') ?: 'NOT SET (will use: invest13_pram)') . "\n";
echo "DB_PASS: " . (getenv('DB_PASS') !== false ? '***SET***' : 'NOT SET (will use default)') . "\n";
echo "DB_CHARSET: " . (getenv('DB_CHARSET') ?: 'NOT SET (will use: utf8mb4)') . "\n";

// Build DSN
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'invest13_sbsmart';
$charset = getenv('DB_CHARSET') ?: 'utf8mb4';
$user = getenv('DB_USER') ?: 'invest13_pram';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'aA1qwerty@@@';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

echo "\n--- Connection Details ---\n";
echo "DSN: $dsn\n";
echo "User: $user\n";
echo "Password Length: " . strlen($pass) . " characters\n";

echo "\n--- Testing Connection ---\n";

try {
    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ CONNECTION SUCCESSFUL!\n\n";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $result = $stmt->fetch();
    echo "✅ Query test passed. Products count: " . $result['count'] . "\n";
    
    echo "\n--- Database Info ---\n";
    $version = $pdo->query("SELECT VERSION()")->fetchColumn();
    echo "MySQL Version: $version\n";
    
    $currentDb = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "Current Database: $currentDb\n";
    
} catch (PDOException $e) {
    echo "❌ CONNECTION FAILED!\n\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Error Message: " . $e->getMessage() . "\n\n";
    
    echo "--- Common Issues & Solutions ---\n";
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "• Wrong username or password\n";
        echo "  → Check DB_USER and DB_PASS in your .env file\n";
        echo "  → Verify credentials with your hosting provider\n";
    }
    
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "• Database doesn't exist\n";
        echo "  → Check DB_NAME in your .env file\n";
        echo "  → Create the database via cPanel/phpMyAdmin\n";
    }
    
    if (strpos($e->getMessage(), "Can't connect") !== false || strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "• Can't reach database server\n";
        echo "  → Check DB_HOST in your .env file\n";
        echo "  → Usually 'localhost' for shared hosting\n";
        echo "  → Contact hosting support if issue persists\n";
    }
}

echo "\n--- File Permissions Check ---\n";
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "✅ .env file exists\n";
    echo "Readable: " . (is_readable($envPath) ? '✅ Yes' : '❌ No') . "\n";
} else {
    echo "❌ .env file NOT FOUND at: $envPath\n";
    echo "   → Make sure .env file is uploaded to your live server\n";
}

echo "\n=== END DIAGNOSTIC ===\n";
echo "</pre>";

echo "\n<p style='background:#fff3cd; padding:15px; border-left:4px solid #ffc107; margin-top:20px;'>";
echo "<strong>⚠️ SECURITY WARNING:</strong> Delete this file (db_test.php) after diagnosing the issue!";
echo "</p>";
?>
