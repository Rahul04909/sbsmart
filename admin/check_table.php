<?php
require_once 'includes/db.php';
$stmt = $pdo->query('DESCRIBE products');
echo "<pre>";
while($col = $stmt->fetch()) {
    echo $col['Field'] . ' (' . $col['Type'] . ")\n";
}
echo "</pre>";
