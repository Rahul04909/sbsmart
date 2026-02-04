<?php
// db-test.php
// Standalone script to test database connectivity using hardcoded credentials.

// Force display of all errors immediately
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html><html><head><title>DB Connection Test</title>";
echo "<style>body{font-family: Arial, sans-serif; padding: 20px;} .success{color: green; font-weight: bold;} .error{color: red; font-weight: bold;}</style>";
echo "</head><body><h1>Database Connection Tester</h1>";

// --- Credentials from includes/db.php ---
$host = 'localhost';
$db   = 'invest13_sbsmart';
$user = 'root';
$pass = ' ';
$user = 'invest13_pram';
$pass = 'aA1qwerty@@@';
$charset = 'utf8mb4';

echo "<p>Attempting connection with the following details:</p>";
echo "<ul>";
echo "<li><strong>Host:</strong> $host</li>";
echo "<li><strong>Database:</strong> $db</li>";
echo "<li><strong>User:</strong> $user</li>";
echo "<li><strong>Password:</strong> ********</li>";
echo "</ul>";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];

try {
    // Attempt the connection
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Attempt a simple query to confirm the database is responsive
    $test_query = $pdo->query("SELECT 1");
    $test_query->fetch();

    echo "<h2 class='success'>SUCCESS! Connection Established.</h2>";
    echo "<p>The credentials in <code>db-test.php</code> are correct and the database is accessible.</p>";
    echo "<p>The 'Access Denied' error you see in the application logs must be caused by:</p>";
    echo "<ul>";
    echo "<li><strong>Error 1:</strong> The <code>db.php</code> file is not successfully reading the credentials before the <code>db()</code> function executes. (This is unlikely given our last fix, but possible).</li>";
    echo "<li><strong>Error 2:</strong> Another included file (e.g., in <code>config.php</code> or <code>helpers.php</code>) is overwriting the <code>\$user</code> or <code>\$pass</code> variables before <code>db()</code> is called.</li>";
    echo "</ul>";

} catch (\PDOException $e) {
    // If connection fails, output the exact PDO error message
    echo "<h2 class='error'>FAILURE! Connection Failed.</h2>";
    echo "<p>The database server has officially rejected these credentials.</p>";
    echo "<p><strong>Exact Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Action Required:</strong> The error message <code>Access denied for user 'invest13_pram'@'localhost' (using password: YES)</code> means that the password <code>aA1qwerty@@@</code> is definitely incorrect for that user/host combination. You must update the password in your hosting panel and then update <code>includes/db.php</code> to match the new, working password.</p>";
}

echo "</body></html>";
?>