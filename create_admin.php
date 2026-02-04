<?php
/**
 * Create Admin Account Script
 * Run this once to create your admin account, then delete this file!
 */

require_once __DIR__ . '/includes/db.php';

// Configuration - CHANGE THESE VALUES
$adminEmail = 'admin@sbsmart.in';
$adminPassword = 'Admin@123';  // Change this to a strong password
$adminName = 'Administrator';

echo "<pre style='background:#f5f5f5; padding:20px; border-radius:8px; font-family:monospace;'>";
echo "=== ADMIN ACCOUNT CREATION ===\n\n";

try {
    $pdo = get_db();
    
    // Check if admin_users table exists
    echo "Checking if admin_users table exists...\n";
    $tableExists = false;
    try {
        $pdo->query("SELECT 1 FROM admin_users LIMIT 1");
        $tableExists = true;
        echo "✅ Table exists\n\n";
    } catch (PDOException $e) {
        echo "❌ Table doesn't exist. Creating it...\n";
    }
    
    // Create table if it doesn't exist
    if (!$tableExists) {
        $createTableSQL = "CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'admin',
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createTableSQL);
        echo "✅ admin_users table created successfully\n\n";
    }
    
    // Check if admin already exists
    echo "Checking if admin account already exists...\n";
    $stmt = $pdo->prepare("SELECT id, email, name FROM admin_users WHERE email = :email");
    $stmt->execute(['email' => $adminEmail]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        echo "⚠️  Admin account already exists!\n";
        echo "Email: " . htmlspecialchars($existing['email']) . "\n";
        echo "Name: " . htmlspecialchars($existing['name']) . "\n";
        echo "ID: " . $existing['id'] . "\n\n";
        
        echo "Do you want to update the password? (y/n)\n";
        echo "To update, modify this script and set \$updateExisting = true;\n\n";
        
        $updateExisting = false; // Change to true to update password
        
        if ($updateExisting) {
            $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admin_users SET password = :password, updated_at = NOW() WHERE email = :email");
            $stmt->execute([
                'password' => $hashedPassword,
                'email' => $adminEmail
            ]);
            echo "✅ Password updated successfully!\n";
        }
    } else {
        // Create new admin account
        echo "Creating new admin account...\n";
        
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("
            INSERT INTO admin_users (email, password, name, role, status, created_at) 
            VALUES (:email, :password, :name, :role, :status, NOW())
        ");
        
        $stmt->execute([
            'email' => $adminEmail,
            'password' => $hashedPassword,
            'name' => $adminName,
            'role' => 'admin',
            'status' => 'active'
        ]);
        
        $adminId = $pdo->lastInsertId();
        
        echo "✅ Admin account created successfully!\n\n";
        echo "--- Account Details ---\n";
        echo "ID: $adminId\n";
        echo "Email: $adminEmail\n";
        echo "Name: $adminName\n";
        echo "Password: [hidden for security]\n";
        echo "Role: admin\n";
        echo "Status: active\n\n";
    }
    
    // Display all admin accounts
    echo "--- All Admin Accounts ---\n";
    $stmt = $pdo->query("SELECT id, email, name, role, status, created_at FROM admin_users ORDER BY id");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins)) {
        echo "No admin accounts found.\n";
    } else {
        foreach ($admins as $admin) {
            echo "\nID: " . $admin['id'] . "\n";
            echo "Email: " . htmlspecialchars($admin['email']) . "\n";
            echo "Name: " . htmlspecialchars($admin['name']) . "\n";
            echo "Role: " . $admin['role'] . "\n";
            echo "Status: " . $admin['status'] . "\n";
            echo "Created: " . $admin['created_at'] . "\n";
            echo "---\n";
        }
    }
    
    echo "\n=== SUCCESS ===\n";
    echo "You can now login to the admin panel at:\n";
    echo "http://localhost/account/admin/login.php\n\n";
    echo "Email: $adminEmail\n";
    echo "Password: [the password you set above]\n\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nFull error details:\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";

echo "<div style='background:#fff3cd; border:1px solid #ffc107; padding:15px; margin-top:20px; border-radius:5px;'>";
echo "<strong>⚠️ SECURITY WARNING:</strong><br>";
echo "1. Delete this file (create_admin.php) immediately after creating your account!<br>";
echo "2. Change the default password after first login<br>";
echo "3. Never commit this file to version control";
echo "</div>";
?>
