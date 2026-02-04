<?php
// manual_update_order.php
// Utility to manually mark an order as PAID via user browser

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php'; // for safe_redirect if needed

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    echo "<h1>Error</h1><p>Please provide an order ID in the URL. Example: manual_update_order.php?id=28</p>";
    exit;
}

try {
    $conn = get_db();
    
    // Check current status
    $stmt = $conn->prepare("SELECT id, status FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        die("<h1>Order #$orderId not found in the database connected to this web server.</h1>");
    }
    
    echo "Current Status for #$orderId: <strong>" . htmlspecialchars($order['status']) . "</strong><br><br>";
    
    // Update to PAID
    $sql = "UPDATE orders SET 
            status = 'paid', 
            razorpay_payment_id = 'MANUAL_FIX_CCAV', 
            razorpay_order_id = 'MANUAL_FIX_REF',
            updated_at = NOW() 
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$orderId]);
    
    echo "<h2 style='color:green'>Success!</h2>";
    echo "Updated order #$orderId to 'paid'.<br>";
    echo "<a href='account/account-order-detail.php?id=$orderId'>Click here to view Order Details</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
