<?php
// Test login by directly posting to the login endpoint
echo "<h2>Testing Login Functionality</h2>";

// Simulate a POST request to the login endpoint
$_POST = [
    'email' => 'ritesh.singh@venetsmedia.com',
    'password' => 'test123',
    'redirect' => ''
];

$_SERVER['REQUEST_METHOD'] = 'POST';

// Include the login script
ob_start();
include 'auth/login.php';
$content = ob_get_clean();

echo "<h3>Login Response:</h3>";
echo "<pre>" . htmlspecialchars($content) . "</pre>";

// Check if there was a redirect
if (headers_sent()) {
    echo "<p>Headers were sent - login processing completed</p>";
} else {
    echo "<p>No redirect occurred</p>";
}

// Check session
session_start();
if (isset($_SESSION['user'])) {
    echo "<p>✅ Login successful! User: " . htmlspecialchars($_SESSION['user']['name']) . "</p>";
} else {
    echo "<p>❌ Login failed - no session created</p>";
}
?>
