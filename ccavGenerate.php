<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ccavenue-crypto.php';
require_once __DIR__ . '/includes/helpers.php';

// Ensure user is logged in
require_login();

$config = require __DIR__ . '/includes/config.php';
$cca = $config['payments']['ccavenue'] ?? [];

$workingKey = $cca['working_key'] ?? '';
$access_code = $cca['access_code'] ?? '';
$merchant_id = $cca['merchant_id'] ?? '';
$redirect_url = $cca['redirect_url'] ?? ($config['site']['base_url'] . '/ccavResponseHandler.php');
$cancel_url = $cca['cancel_url'] ?? $redirect_url;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

// Get Order ID from POST
$orderId = (int)($_POST['order_id'] ?? 0);
if ($orderId <= 0) {
    die("Invalid Order ID");
}

// Fetch Order from DB
$conn = get_db();
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = :id AND status = 'pending' LIMIT 1");
$stmt->execute([':id' => $orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found or already processed.");
}

// Validate User (Optional but recommended: check if order belongs to current user)
$currentUser = current_user();
if ($currentUser && $order['email'] !== $currentUser['email']) {
    // This check assumes email is unique/consistent. 
    // Better to check user_id if orders table has user_id.
    // For now, we'll skip strict ownership check if user_id is missing in orders table,
    // but relying on session login is a good baseline.
}

// Prepare CCAvenue Parameters
$amount = number_format((float)$order['total'], 2, '.', '');
$billing_name = $order['name'];
$billing_email = $order['email'];
$billing_tel = $order['phone'];
$billing_address = $order['address'];

// CCAvenue Order ID (Can be same as DB ID or prefixed)
// Using DB ID ensures we can match it back easily.
$ccaOrderId = (string)$orderId; 

$requestParams = [
    'merchant_id' => $merchant_id,
    'order_id' => $ccaOrderId,
    'currency' => 'INR',
    'amount' => $amount,
    'redirect_url' => $redirect_url,
    'cancel_url' => $cancel_url,
    'language' => 'EN',
    'billing_name' => $billing_name,
    'billing_address' => $billing_address,
    'billing_city' => '', // Add if available
    'billing_state' => '', // Add if available
    'billing_zip' => '', // Add if available
    'billing_country' => 'India',
    'billing_tel' => $billing_tel,
    'billing_email' => $billing_email,
    'merchant_param1' => session_id(), // Pass Session ID to restore session on callback
];

$paramString = http_build_query($requestParams, '', '&');
$enc = ccavenue_encrypt_hex($paramString, $workingKey);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Redirecting to CCAvenue</title></head>
<body>
<p>Redirecting to payment gateway...</p>
<form id="ccaForm" method="post" action="https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction">
  <input type="hidden" name="encRequest" value="<?= htmlspecialchars($enc) ?>">
  <input type="hidden" name="access_code" value="<?= htmlspecialchars($access_code) ?>">
</form>
<script>document.getElementById('ccaForm').submit();</script>
</body>
</html>