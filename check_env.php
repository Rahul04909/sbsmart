<?php
/**
 * .env File Checker
 * Upload this to verify your .env file is correctly uploaded and readable
 */

echo "<pre style='background:#f5f5f5; padding:20px; border-radius:8px; font-family:monospace;'>";
echo "=== .ENV FILE VERIFICATION ===\n\n";

$envPath = __DIR__ . '/.env';

echo "Looking for .env at: $envPath\n\n";

// Check if file exists
if (!file_exists($envPath)) {
    echo "❌ ERROR: .env file NOT FOUND!\n\n";
    echo "Solutions:\n";
    echo "1. Make sure you uploaded the .env file to your server\n";
    echo "2. Upload it to the same directory as index.php\n";
    echo "3. Make sure it's named exactly '.env' (not .env.txt)\n";
    echo "4. Check if your FTP client shows hidden files (files starting with .)\n\n";
    
    echo "Current directory contents:\n";
    $files = scandir(__DIR__);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "  - $file\n";
        }
    }
    exit;
}

echo "✅ .env file exists\n";

// Check if readable
if (!is_readable($envPath)) {
    echo "❌ ERROR: .env file exists but is NOT READABLE!\n\n";
    echo "Solutions:\n";
    echo "1. Change file permissions to 644\n";
    echo "2. Via FTP: Right-click → File Permissions → Set to 644\n";
    echo "3. Via SSH: chmod 644 .env\n";
    exit;
}

echo "✅ .env file is readable\n\n";

// Read and display contents (with passwords masked)
echo "--- .env File Contents ---\n";
$contents = file_get_contents($envPath);
$lines = explode("\n", $contents);

$foundDbVars = false;
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || $line[0] === '#') {
        continue;
    }
    
    if (strpos($line, '=') !== false) {
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Mask sensitive values
        if (strpos($key, 'PASS') !== false || strpos($key, 'SECRET') !== false || strpos($key, 'KEY') !== false) {
            $maskedValue = str_repeat('*', min(strlen($value), 8));
            echo "$key = $maskedValue (length: " . strlen($value) . ")\n";
        } else {
            echo "$key = $value\n";
        }
        
        if (strpos($key, 'DB_') === 0) {
            $foundDbVars = true;
        }
    }
}

echo "\n";

if (!$foundDbVars) {
    echo "⚠️ WARNING: No DB_* variables found in .env file!\n";
    echo "Make sure your .env file contains:\n";
    echo "  DB_HOST=localhost\n";
    echo "  DB_NAME=your_database_name\n";
    echo "  DB_USER=your_database_user\n";
    echo "  DB_PASS=your_database_password\n\n";
}

// Test loading with config.php
echo "--- Testing Config Load ---\n";
try {
    $config = require __DIR__ . '/includes/config.php';
    
    if (isset($config['db'])) {
        echo "✅ Config loaded successfully\n";
        echo "Database DSN: " . $config['db']['dsn'] . "\n";
        echo "Database User: " . $config['db']['user'] . "\n";
        echo "Password Length: " . strlen($config['db']['pass']) . " characters\n";
    } else {
        echo "❌ Config loaded but 'db' key not found\n";
    }
} catch (Exception $e) {
    echo "❌ Error loading config: " . $e->getMessage() . "\n";
}

echo "\n=== END VERIFICATION ===\n";
echo "</pre>";

echo "\n<div style='background:#d4edda; border:1px solid #c3e6cb; padding:15px; margin-top:20px; border-radius:5px;'>";
echo "<strong>✅ Next Steps:</strong><br>";
echo "1. If .env file is missing, upload it from your local computer<br>";
echo "2. Make sure database credentials match your hosting provider's settings<br>";
echo "3. After fixing, test with db_test.php<br>";
echo "4. Delete both check_env.php and db_test.php after fixing!";
echo "</div>";
?>
