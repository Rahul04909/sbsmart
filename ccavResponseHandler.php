<?php
// TEMP: enable errors while debugging (later you can comment these two lines)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/ccavenue-crypto.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';

// ---- LOAD CONFIG (ADJUST ACCORDING TO YOUR CONFIG.PHP) ----
// If your config.php returns an array:
$cfg = is_array($config ?? null) ? $config : (is_array($cfg ?? null) ? $cfg : []);
if (empty($cfg)) {
    // if config.php actually returns array, you can instead do:
    // $cfg = require __DIR__ . '/includes/config.php';
    // and remove the is_array() checks above.
}

$workingKey = $cfg['payments']['ccavenue']['working_key'] ?? '';
if ($workingKey === '') {
    error_log('CCAv Error: working_key missing in config.');
    echo "Payment configuration error (working key missing).";
    exit;
}

// If called directly without POST (e.g. you open URL in browser)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "CCAvenue handler is reachable but expects POST data.";
    exit;
}

// 1. Capture and Validate Response
$encResp = $_POST['encResp'] ?? '';
if ($encResp === '') {
    error_log('CCAv Error: response missing encResp');
    flash_set('error', 'Payment response missing.');
    safe_redirect('checkout.php');
    exit;
}

// Ensure decrypt function exists
if (!function_exists('ccavenue_decrypt_hex')) {
    error_log('CCAv Error: ccavenue_decrypt_hex() not defined. Check ccavenue-crypto.php include.');
    flash_set('error', 'Payment security module missing.');
    safe_redirect('checkout.php');
    exit;
}

// 2. Decrypt
$plain = ccavenue_decrypt_hex($encResp, $workingKey);
if ($plain === false) {
    error_log('CCAv Decrypt Failed: OpenSSL returned false');
    flash_set('error', 'Payment security verification failed.');
    safe_redirect('checkout.php');
    exit;
}

parse_str($plain, $data);

if (empty($data)) {
    error_log('CCAv Error: Failed to parse plain response: ' . $plain);
    flash_set('error', 'Invalid payment response.');
    safe_redirect('checkout.php');
    exit;
}

// 3. Restore Session
if (!empty($data['merchant_param1'])) {
    $sid = $data['merchant_param1'];
    if (session_id() !== $sid && preg_match('/^[a-zA-Z0-9,-]+$/', $sid)) {
        session_write_close();
        session_id($sid);
        session_start();
    }
}

// 4. Extract Key Fields
$order_status = $data['order_status'] ?? '';  // Success, Failure, Aborted, Invalid
$order_id     = (int)($data['order_id'] ?? 0);
$amount_paid  = (float)($data['amount'] ?? 0.0);
$tracking_id  = $data['tracking_id'] ?? '';
$bank_ref     = $data['bank_ref_no'] ?? '';
$failure_msg  = $data['failure_message'] ?? '';

error_log("CCAv Callback: Order #$order_id | Status: $order_status | Amount: $amount_paid | Tracking: $tracking_id");

$conn = get_db();

// 5. Fetch Order from DB to Validate
$stmt = $conn->prepare("SELECT id, status, total, email, name FROM orders WHERE id = ? LIMIT 1");
$stmt->execute([$order_id]);
$dbOrder = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dbOrder) {
    error_log("CCAv Error: Order #$order_id not found in DB.");
    flash_set('error', 'Order record not found.');
    safe_redirect('index.php');
    exit;
}

// 6. Determine Transaction Outcome
$isSuccess = false;
$msg = '';

if (strtolower($order_status) === 'success' || strtolower($order_status) === 'captured') {

    if (abs((float)$dbOrder['total'] - $amount_paid) > 1.0) {
        $isSuccess = false;
        $order_status = 'AmountMismatch';
        error_log("CCAv Fraud Alert: Order #$order_id amount mismatch. DB: {$dbOrder['total']}, Paid: $amount_paid");
        $msg = 'Payment amount mismatch. Order flagged.';
    } else {
        $isSuccess = true;
    }

} else {
    $isSuccess = false;
    $msg = 'Payment ' . $order_status;
}

// 7. Update Database
if ($isSuccess) {
    if ($dbOrder['status'] !== 'paid') {
        // NOTE: change column names if you like (transaction_id, bank_ref_no)
        $upd = $conn->prepare("
            UPDATE orders 
            SET status = 'paid', 
                razorpay_payment_id = ?, 
                razorpay_order_id   = ?
            WHERE id = ?
        ");
        $upd->execute([$tracking_id, $bank_ref, $order_id]);

        $_SESSION['cart'] = [];
        unset($_SESSION['checkout_order_id'], $_SESSION['checkout_order_total']);

        if (!empty($_SESSION['user']['id'])) {
            try {
                $conn->prepare("DELETE FROM cart WHERE user_id = ?")
                     ->execute([(int)$_SESSION['user']['id']]);
            } catch (Exception $e) {
                error_log('Cart clear error: ' . $e->getMessage());
            }
        }
        // TODO: send mail here
    }

    flash_set('success', 'Payment Successful! Order #' . $order_id);
    safe_redirect('payment-success.php?id=' . $order_id);
    exit;

} else {
    if ($dbOrder['status'] !== 'paid') {
        $failStatus = ($order_status === 'AmountMismatch') ? 'fraud_check' : 'failed';
        $upd = $conn->prepare("
            UPDATE orders 
            SET status = ?, 
                razorpay_payment_id = ? 
            WHERE id = ?
        ");
        $upd->execute([$failStatus, $tracking_id, $order_id]);
    }

    flash_set('error', 'Transaction Failed: ' . ($failure_msg ?: $order_status));
    safe_redirect('order-status.php?id=' . $order_id);
    exit;
}
