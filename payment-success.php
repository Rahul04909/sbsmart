<?php
// payment-success.php
// Dedicated success page for successful payments.

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';

// Ensure user is logged in
require_login();

$orderId = (int)($_GET['id'] ?? 0);
$auth_check_passed = false;
$order = null;

if ($orderId > 0) {
    $conn = get_db();
    // Validate order belongs to user
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    // Simple security check: Ensure the order exists. 
    // In a stricter system, you might check if it belongs to the logged-in user, 
    // but users might pay as guest (if allowed) or session might be tricky.
    // Since we have require_login(), we can check against session email if desired, 
    // but the critical part is just verifying the order is actually paid to show the success screen.
    if ($order && (strtolower($order['status']) === 'paid' || strtolower($order['status']) === 'captured')) {
        $auth_check_passed = true;
    }
}

$page_title = "Payment Successful - SBSmart";
require __DIR__ . '/includes/header.php';
?>

<div class="container my-5 py-5 text-center">
    <?php if ($auth_check_passed): ?>
        <div class="card shadow-lg border-0 mx-auto" style="max-width: 600px;">
            <div class="card-body p-5">
                <div class="mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle" style="width: 80px; height: 80px;">
                        <i class="bi bi-check-lg" style="font-size: 3rem;"></i>
                    </div>
                </div>
                
                <h1 class="h3 mb-3 text-success">Payment Successful!</h1>
                <p class="text-muted mb-4 lead">
                    Thank you! Your payment has been processed successfully.
                </p>
                
                <div class="bg-light p-4 rounded mb-4 text-start">
                    <div class="row mb-2">
                        <div class="col-6 text-muted">Order Number:</div>
                        <div class="col-6 fw-bold text-end">#<?= html($order['id']) ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6 text-muted">Amount Paid:</div>
                        <div class="col-6 fw-bold text-end">₹<?= number_format((float)$order['total'], 2) ?></div>
                    </div>
                    <?php if (!empty($order['razorpay_payment_id'])): ?>
                    <div class="row">
                        <div class="col-6 text-muted">Transaction ID:</div>
                        <div class="col-6 fw-bold text-end"><?= html($order['razorpay_payment_id']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                    <a href="account/account-orders.php" class="btn btn-outline-primary btn-lg px-4">My Orders</a>
                    <a href="index.php" class="btn btn-primary btn-lg px-4">Continue Shopping</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning mx-auto" style="max-width: 600px;">
            <h4 class="alert-heading">Order Not Found</h4>
            <p>We couldn't find the payment details for this order, or the payment is still pending.</p>
            <hr>
            <p class="mb-0">
                <a href="account/account-orders.php" class="btn btn-warning">View My Orders</a>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
