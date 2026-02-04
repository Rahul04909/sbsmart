<?php
require_once __DIR__ . '/includes/db.php';
$conn = get_db();
try {
    $stmt = $conn->query("DESCRIBE orders");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h1>Orders Table Schema</h1><pre>";
    print_r($columns);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
