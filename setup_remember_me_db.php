<?php
require_once __DIR__ . '/includes/db.php';
$conn = get_db();

try {
    $conn->exec("CREATE TABLE IF NOT EXISTS user_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        selector VARCHAR(255) NOT NULL,
        hashed_validator VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_tokens_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Table 'user_tokens' created successfully.";
} catch (Exception $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
