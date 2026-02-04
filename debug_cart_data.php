<?php
// debug_cart_data.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/includes/db.php';

echo "<h1>Cart Debugger</h1>";

// 1. Check Session
echo "<h2>1. Session Data</h2>";
if (empty($_SESSION)) {
    echo "Session is empty.<br>";
} else {
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
}

$userId = $_SESSION['user']['id'] ?? null;
echo "User ID from session: " . ($userId ? $userId : "<strong>NOT SET</strong>") . "<br>";

// 2. Check DB Connection
echo "<h2>2. DB Connection</h2>";
try {
    $pdo = get_db();
    echo "PDO Connection: Success<br>";
} catch (Exception $e) {
    die("PDO Connection Failed: " . $e->getMessage());
}

// 3. Check Cart Table
echo "<h2>3. Cart Table Contents (All)</h2>";
try {
    $stmt = $pdo->query("SELECT * FROM cart");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo "Cart table is empty.<br>";
    } else {
        echo "<table border='1'><tr><th>ID</th><th>User ID</th><th>Product ID</th><th>Qty</th></tr>";
        foreach ($rows as $r) {
            echo "<tr><td>{$r['id']}</td><td>{$r['user_id']}</td><td>{$r['product_id']}</td><td>{$r['quantity']}</td></tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "Error reading cart: " . $e->getMessage() . "<br>";
}

// 4. Check Specific User Cart
if ($userId) {
    echo "<h2>4. Cart for User ID $userId</h2>";
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($userRows)) {
        echo "No items found for this user in 'cart' table.<br>";
    } else {
        echo "Found " . count($userRows) . " items.<br>";
        
        // 5. Check Join
        echo "<h2>5. Join Check</h2>";
        foreach ($userRows as $row) {
            $pid = $row['product_id'];
            echo "Checking Product ID $pid... ";
            $pStmt = $pdo->prepare("SELECT id, title FROM products WHERE id = ?");
            $pStmt->execute([$pid]);
            $prod = $pStmt->fetch(PDO::FETCH_ASSOC);
            if ($prod) {
                echo "Found: " . htmlspecialchars($prod['title']) . "<br>";
            } else {
                echo "<strong style='color:red'>NOT FOUND in products table!</strong><br>";
            }
        }
    }
}
