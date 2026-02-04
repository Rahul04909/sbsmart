<?php
// =========================================================
// DEBUGGER FOR CHECKOUT.PHP - PLACE IN THE SAME DIRECTORY
// =========================================================

// 1. Force all PHP errors to be displayed
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Temporary variables to capture status for later output ---
$debug_output = "<h1>Checkout Debugger</h1>";
$debug_output .= "<p>Attempting to load critical files and initialize session...</p>";
$db_status_output = "";


// 3. Attempt to load dependencies (MUST BE DONE BEFORE ANY OUTPUT)
try {
    $debug_output .= "<h2>Dependency Check:</h2>";
    
    // Core Dependencies
    $debug_output .= "<p>Attempting to load <code>session.php</code>...</p>";
    require_once __DIR__ . '/includes/session.php';
    $debug_output .= "<p>&#x2714; <code>session.php</code> loaded successfully.</p>";

    // Now that session.php is loaded, the session status should be active
    if (session_status() === PHP_SESSION_ACTIVE) {
        $debug_output .= "<p>&#x2714; Session started successfully.</p>";
    } else {
        $debug_output .= "<p style='color:red;'>&#x274C; Error: Session failed to start after loading <code>session.php</code>.</p>";
    }

    $debug_output .= "<p>Attempting to load <code>helpers.php</code>...</p>";
    require_once __DIR__ . '/includes/helpers.php';
    $debug_output .= "<p>&#x2714; <code>helpers.php</code> loaded successfully.</p>";

    $debug_output .= "<p>Attempting to load <code>db.php</code>...</p>";
    require_once __DIR__ . '/includes/db.php';
    $debug_output .= "<p>&#x2714; <code>db.php</code> loaded successfully.</p>";

    $debug_output .= "<p>Attempting to load <code>config.php</code>...</p>";
    require_once __DIR__ . '/includes/config.php';
    $debug_output .= "<p>&#x2714; <code>config.php</code> loaded successfully.</p>";

    // 4. Test database connection
    if (function_exists('db')) {
        $db_status_output .= "<p>Attempting to connect to the database via <code>db()</code>...</p>";
        $conn = db();
        if ($conn instanceof PDO) {
            $db_status_output .= "<p>&#x2714; Database connection established successfully!</p>";
        } else {
            $db_status_output .= "<p style='color:orange;'>&#9888; Warning: Database function 'db()' did not return a valid connection object.</p>";
        }
    } else {
        $db_status_output .= "<p style='color:orange;'>&#9888; Warning: Database connection function 'db()' not found. Check <code>db.php</code>.</p>";
    }
    $debug_output .= $db_status_output;


    // 5. Load the Header (which performs a DB query and outputs HTML)
    $debug_output .= "<h2>Header Load Test:</h2>";
    $debug_output .= "<p>Attempting to load <code>header.php</code>...</p>";

    // ECHO CAPTURED STATUS BEFORE LOADING HEADER WHICH STARTS HTML OUTPUT
    echo $debug_output;
    
    require_once __DIR__ . '/includes/header.php';

    // The rest of the success message will be displayed after header.php's HTML
    echo "<p>&#x2714; <code>header.php</code> loaded successfully.</p>";

    echo "<h2>Final Status:</h2>";
    echo "<div style='background-color:#d4edda; color:#155724; border: 1px solid #c3e6cb; padding:15px; border-radius:5px;'>";
    echo "If you see this message and no other errors, the core dependencies are likely fine. The 500 error may be a syntax issue in the main <code>checkout.php</code> file *itself*.";
    echo "</div>";

} catch (Throwable $e) {
    // Output error status immediately if a fatal error occurred during dependency loading
    echo $debug_output;
    echo "<h2>&#x274C; CRITICAL ERROR DURING LOAD!</h2>";
    echo "<div style='background-color:#f8d7da; color:#721c24; border: 1px solid #f5c6cb; padding:15px; border-radius:5px;'>";
    echo "<strong>This means one of the required files failed to load or caused a fatal error.</strong><br>";
    echo "<strong>File:</strong> " . htmlspecialchars($e->getFile()) . "<br>";
    echo "<strong>Line:</strong> " . htmlspecialchars($e->getLine()) . "<br>";
    echo "<strong>Message:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}
?>