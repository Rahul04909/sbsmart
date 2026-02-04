<?php
// admin/fix_live.php
// Helper to fix the live database schema (Add is_bestseller column)
// require_once __DIR__ . '/includes/auth.php'; 
require_once __DIR__ . '/includes/db.php';

// Auth check disabled for easier execution. 
// IMPORTANT: DELETE THIS FILE AFTER RUNNING IT.
// if (session_status() === PHP_SESSION_NONE) session_start();
// if (empty($_SESSION['admin_logged_in'])) {
//    die("Please log in as admin first.");
// }

$message = "";
try {
    // 1 check if column exists
    $check = $pdo->query("SHOW COLUMNS FROM products LIKE 'is_bestseller'");
    $exists = $check->fetch();

    if ($exists) {
        $message = "<div style='color:green; font-weight:bold;'>Column 'is_bestseller' ALREADY EXISTS. You are good to go!</div>";
    } else {
        // 2 Add column
        $sql = "ALTER TABLE products ADD COLUMN is_bestseller TINYINT(1) DEFAULT 0 AFTER status";
        $pdo->exec($sql);
        $message = "<div style='color:green; font-weight:bold;'>SUCCESS: Column 'is_bestseller' has been ADDED to the products table.</div>";
    }

} catch (PDOException $e) {
    $message = "<div style='color:red; font-weight:bold;'>ERROR: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Database</title>
    <style>body { font-family: sans-serif; padding: 50px; text-align:center; }</style>
</head>
<body>
    <h1>Database Fix Tool</h1>
    <?php echo $message; ?>
    <br><br>
    <p>Now verify that you have also uploaded the latest <code>admin/product_form.php</code> file.</p>
    <a href="products.php">Back to Products</a>
</body>
</html>
