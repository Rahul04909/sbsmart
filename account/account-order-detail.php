<?php
// account/account-order-detail.php
// Displays the detailed summary and status of a single order.

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

require_login();

// --- Setup ---
$user_session = current_user();
$userEmail = $user_session['email'] ?? '';
$orderId = (int)($_GET['id'] ?? 0);
$order = null;
$items = [];

if ($orderId <= 0) {
    flash_set('danger', 'Invalid order ID provided.');
    safe_redirect('/account/account-orders.php');
}

// --- Fetch Data ---
try {
    $conn = db();
    
    // 1. Fetch Order Details (must match logged-in user's email for security)
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND email = ? LIMIT 1");
    $stmt->execute([$orderId, $userEmail]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // 2. Fetch Order Items
        $item_stmt = $conn->prepare("SELECT product_id, title, price, qty FROM order_items WHERE order_id = ?");
        $item_stmt->execute([$orderId]);
        $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    error_log('Order Detail: Error fetching data: ' . $e->getMessage());
    flash_set('danger', 'Could not load order details due to a system error.');
    safe_redirect('/account/account-orders.php');
}

if (!$order) {
    flash_set('danger', 'Order not found or access denied.');
    safe_redirect('/account/account-orders.php');
}

// --- Status Classification ---
$order_date = new DateTime($order['created_at']);
$status_class = match (strtolower($order['status'])) {
    'paid' => 'bg-success',
    'pending' => 'bg-warning text-dark',
    'cancelled' => 'bg-danger',
    'failed' => 'bg-danger',
    default => 'bg-secondary',
};

$page_title = "Order #{$orderId} Detail - SBSmart";
require_once __DIR__ . '/../includes/header.php';
$flash = flash_get();
?>

<div class="container my-5">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm p-3">
                <h5 class="mb-3">Account Navigation</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><a href="account.php" class="btn btn-outline-primary w-100 text-start"><i class="bi bi-house-door me-2"></i> Dashboard</a></li>
                    <li class="mb-2"><a href="account-orders.php" class="btn btn-primary w-100 text-start"><i class="bi bi-list-check me-2"></i> My Orders</a></li>
                    <li class="mb-2"><a href="../account-profile.php" class="btn btn-outline-primary w-100 text-start"><i class="bi bi-person me-2"></i> Profile & Password</a></li>
                    <li class="mb-2"><a href="../auth/logout.php" class="btn btn-outline-danger w-100 text-start"><i class="bi bi-box-arrow-right me-2"></i> Log Out</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Order Detail -->
        <div class="col-lg-9">
            <div class="card shadow-sm p-4">
                <h1 class="h4 card-title mb-4">Order Details #<?= html($order['id']) ?></h1>
                
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <div>
                        <span class="badge <?= $status_class ?> fs-6"><?= (strtolower($order['status']) === 'paid' ? 'Payment Done' : html(ucfirst($order['status']))) ?></span>
                        <p class="text-muted small mb-0 mt-1">Placed on <?= $order_date->format('F j, Y, h:i A') ?></p>
                    </div>
                    <div>
                        <a href="../invoice.php?id=<?= $order['id'] ?>" target="_blank" class="btn btn-outline-dark btn-sm me-2"><i class="bi bi-file-earmark-text"></i> Download Invoice</a>
                        <a href="account-orders.php" class="btn btn-outline-secondary btn-sm">← Back to Orders</a>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <!-- Billing/Shipping Details -->
                    <div class="col-md-6">
                        <h5>Shipping Details</h5>
                        <ul class="list-unstyled small">
                            <li><strong>Recipient:</strong> <?= html($order['name']) ?></li>
                            <li><strong>Email:</strong> <?= html($order['email']) ?></li>
                            <li><strong>Phone:</strong> <?= html($order['phone']) ?></li>
                            <li><strong>Address:</strong> <?= nl2br(html($order['address'])) ?></li>
                        </ul>
                    </div>
                    
                    <!-- Payment Details -->
                    <div class="col-md-6">
                        <h5>Payment Information</h5>
                        <ul class="list-unstyled small">
                            <li><strong>Method:</strong> <?= html($order['razorpay_payment_id'] === 'COD' ? 'Cash On Delivery' : 'Online Payment (CCAvenue)') ?></li>
                            <li><strong>Total Paid:</strong> ₹<?= html(number_format((float)$order['total'], 2)) ?></li>
                            <li><strong>Ref ID:</strong> <?= html($order['razorpay_payment_id'] ?: 'N/A') ?></li>
                            <li><strong>CCA Order ID:</strong> <?= html($order['razorpay_order_id'] ?: 'N/A') ?></li>
                        </ul>
                    </div>
                </div>

                <!-- Items Table -->
                <h5 class="mt-2 mb-3">Items Purchased</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle small">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-center" width="80">Qty</th>
                                <th class="text-end" width="100">Price</th>
                                <th class="text-end" width="120">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $grand_total = 0; foreach ($items as $item): ?>
                                <?php $item_subtotal = $item['price'] * $item['qty']; $grand_total += $item_subtotal; ?>
                                <tr>
                                    <td><?= html($item['title']) ?></td>
                                    <td class="text-center"><?= (int)$item['qty'] ?></td>
                                    <td class="text-end">₹<?= html(number_format($item['price'], 2)) ?></td>
                                    <td class="text-end">₹<?= html(number_format($item_subtotal, 2)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Grand Total</th>
                                <th class="text-end">₹<?= html(number_format($grand_total, 2)) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>