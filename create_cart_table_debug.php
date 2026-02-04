<?php
require_once __DIR__ . '/includes/db.php';

try {
    $pdo = get_db();
    // Drop if exists to be clean
    $pdo->exec("DROP TABLE IF EXISTS cart");

    $sql = "CREATE TABLE cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_product (user_id, product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "Table 'cart' created successfully (no FKs yet).<br>";

    // Try adding FK for users
    try {
        $pdo->exec("ALTER TABLE cart ADD CONSTRAINT fk_cart_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
        echo "FK users added.<br>";
    } catch (Exception $e) {
        echo "FK users failed: " . $e->getMessage() . "<br>";
    }

    // Try adding FK for products
    try {
        $pdo->exec("ALTER TABLE cart ADD CONSTRAINT fk_cart_products FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE");
        echo "FK products added.<br>";
    } catch (Exception $e) {
        echo "FK products failed: " . $e->getMessage() . "<br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
