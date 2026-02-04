<?php
// Test login functionality
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';

echo "Testing database connection...\n";

try {
    $pdo = get_db();
    echo "Database connection successful!\n";

    // Test if users table exists and has data
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $tableExists = $stmt->fetch();

    if ($tableExists) {
        echo "Users table exists.\n";

        // Check if there are any users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "Total users: " . $result['count'] . "\n";

        if ($result['count'] > 0) {
            // Show first user (for testing)
            $stmt = $pdo->query("SELECT id, name, email, is_active FROM users LIMIT 1");
            $user = $stmt->fetch();
            echo "Sample user: ID=" . $user['id'] . ", Name=" . $user['name'] . ", Email=" . $user['email'] . ", Active=" . $user['is_active'] . "\n";
        }
    } else {
        echo "Users table does not exist!\n";
    }

} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\nTesting login functions...\n";

// Test flash functions
try {
    flash_set('test', 'Login test successful');
    $flash = flash_get('test');
    echo "Flash functions work: " . $flash . "\n";
} catch (Exception $e) {
    echo "Flash function error: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
?>
