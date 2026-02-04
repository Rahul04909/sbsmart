<?php
// fix_db_web.php - Run this in browser
date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<h2>Database Fix & Diagnostic Tool</h2>";

try {
    $conn = get_db();
    
    // 1. Show DB Name
    $config = require __DIR__ . '/includes/config.php';
    echo "<li><strong>Connected to DB:</strong> " . $config['db']['dsn'] . "</li>";
    
    // 2. Check Orders Table Columns
    $stmt = $conn->query("SHOW COLUMNS FROM orders");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $colNames = array_column($columns, 'Field');
    
    echo "<li><strong>Current Columns:</strong> " . implode(', ', $colNames) . "</li>";
    
    // 3. Apply Fixes
    $updates = [];
    
    if (!in_array('updated_at', $colNames)) {
        $conn->exec("ALTER TABLE orders ADD COLUMN updated_at DATETIME DEFAULT NULL");
        $updates[] = "Added 'updated_at' column";
    }
    
    if (!in_array('razorpay_payment_id', $colNames)) {
        $conn->exec("ALTER TABLE orders ADD COLUMN razorpay_payment_id VARCHAR(255) DEFAULT NULL");
        $updates[] = "Added 'razorpay_payment_id' column";
    }
    
    if (!in_array('razorpay_order_id', $colNames)) {
        $conn->exec("ALTER TABLE orders ADD COLUMN razorpay_order_id VARCHAR(255) DEFAULT NULL");
        $updates[] = "Added 'razorpay_order_id' column";
    }
    
    // Force status to VARCHAR
    $conn->exec("ALTER TABLE orders MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending'");
    $updates[] = "Ensured 'status' is VARCHAR(50)";
    
    if (empty($updates)) {
        echo "<li style='color:green'>Database Schema looks correct. No changes needed.</li>";
    } else {
        foreach($updates as $u) echo "<li style='color:blue'>$u</li>";
    }
    
    // 4. Force Update Order #28 (or latest)
    // Find latest pending order
    $stmtLast = $conn->query("SELECT * FROM orders WHERE status='pending' ORDER BY id DESC LIMIT 1");
    $lastOrder = $stmtLast->fetch(PDO::FETCH_ASSOC);
    
    if ($lastOrder) {
        $oid = $lastOrder['id'];
        echo "<li>Found Pending Order #$oid. Attempting to mark as PAID...</li>";
        
        $stmtUp = $conn->prepare("UPDATE orders SET status='paid', razorpay_payment_id='MANUAL_FIX', updated_at=NOW() WHERE id = ?");
        $stmtUp->execute([$oid]);
        
        if ($stmtUp->rowCount() > 0) {
            echo "<h3 style='color:green'>Success! Order #$oid marked as PAID.</h3>";
        } else {
            echo "<h3 style='color:red'>Update ran but no rows changed?</h3>";
        }
    } else {
        echo "<li>No pending orders found to fix.</li>";
    }

} catch (Exception $e) {
    echo "<h3 style='color:red'>Error: " . $e->getMessage() . "</h3>";
}
echo "<br><br><a href='account-orders.php'>Go to My Orders</a>";
?>
