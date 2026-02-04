<?php
require_once __DIR__ . '/includes/db.php';
$conn = get_db();

function add_col_if_missing($conn, $table, $col, $def) {
    try {
        $stmt = $conn->query("SHOW COLUMNS FROM $table LIKE '$col'");
        if ($stmt->fetch()) {
            echo "Column '$col' already exists in '$table'.\n";
        } else {
            $conn->exec("ALTER TABLE $table ADD COLUMN $col $def");
            echo "Added column '$col' to '$table'.\n";
        }
    } catch (Exception $e) {
        echo "Error checking/adding $col: " . $e->getMessage() . "\n";
    }
}

add_col_if_missing($conn, 'orders', 'updated_at', 'DATETIME DEFAULT NULL');
add_col_if_missing($conn, 'orders', 'razorpay_payment_id', 'VARCHAR(255) DEFAULT NULL');
add_col_if_missing($conn, 'orders', 'razorpay_order_id', 'VARCHAR(255) DEFAULT NULL');

// Also check status column enum/varchar size if needed, but usually it's varchar(50) or enum
try {
    // Just modify status to be varchar(50) to allow 'paid', 'cod', etc without enum restriction issues if it was enum
    $conn->exec("ALTER TABLE orders MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending'");
    echo "Ensured 'status' column is VARCHAR(50).\n";
} catch (Exception $e) {
    echo "Error modifying status: " . $e->getMessage() . "\n";
}

echo "Database fix complete.\n";
?>
