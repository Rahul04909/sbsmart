<?php
// order-status.php
// Displays the final status of an order after payment processing (CCAvenue or COD).

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';

// --- Initialization ---

$orderId = (int)($_GET['id'] ?? 0);
$order = null;
$items = [];
$status_class = 'alert-warning';
$status_icon = 'bi-info-circle';

// --- Database Lookup ---

try {
    $conn = get_db();
    if (!$conn) {
        throw new Exception("Database connection not available.");
    }

    if ($orderId > 0) {
        // 1. Fetch main order details
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Fetch order items
        if ($order) {
            $item_stmt = $conn->prepare("SELECT product_id, title, price, qty FROM order_items WHERE order_id = ?");
            $item_stmt->execute([$orderId]);
            $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (Throwable $e) {
    error_log("order-status.php DB error: " . $e->getMessage());
    $status_class = 'alert-danger';
    $status_icon = 'bi-x-octagon';
    flash_set('danger', 'System error retrieving order details.');
}

// --- Status Classification ---

if ($order) {
    switch (strtolower($order['status'])) {
        case 'paid':
            $status_class = 'alert-success';
            $status_icon = 'bi-check-circle';
            break;
        case 'pending':
            // Can happen after COD confirmation (before internal processing) or initial order creation
            $status_class = 'alert-warning';
            $status_icon = 'bi-clock';
            break;
        case 'cancelled':
        case 'failed':
            $status_class = 'alert-danger';
            $status_icon = 'bi-x-octagon';
            break;
    }
}

// --- Render Page ---

$page_title = "Order Status - SBSmart";
require __DIR__ . '/includes/header.php';
$flash = flash_get();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <?php if (!empty($flash)): ?>
                <?php foreach ($flash as $type => $msgs): ?>
                    <?php foreach ((array)$msgs as $m): ?>
                        <?php $bsClass = in_array($type, ['error','danger']) ? 'danger' : ($type === 'success' ? 'success' : 'info'); ?>
                        <div class="alert alert-<?= $bsClass ?> alert-dismissible fade show">
                            <?= html($m) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!$order): ?>
                <div class="alert alert-danger text-center">
                    <i class="bi bi-exclamation-triangle fs-4 me-2"></i>
                    <h4>Order Not Found</h4>
                    <p>The provided order ID is invalid or the order does not exist.</p>
                </div>
            <?php else: ?>
                <div class="card shadow-lg border-0">
                    <div class="card-header text-white <?= str_replace('alert-', 'bg-', $status_class) ?>">
                        <div class="d-flex align-items-center">
                            <i class="bi <?= $status_icon ?> fs-4 me-3"></i>
                            <h4 class="mb-0">Order #<?= html($order['id']) ?> Status</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Status:</strong> <span class="badge bg-<?= str_replace('alert-', '', $status_class) ?> fs-6"><?= html(ucwords($order['status'])) ?></span></p>
                        <p class="mb-1"><strong>Total Amount:</strong> ₹<?= html(number_format((float)$order['total'], 2)) ?></p>
                        <p class="mb-4"><strong>Payment Reference:</strong> <?= html($order['razorpay_payment_id'] ?: 'N/A') ?></p>
                        
                        <h5>Customer Details</h5>
                        <ul class="list-unstyled small text-muted">
                            <li><strong>Name:</strong> <?= html($order['name']) ?></li>
                            <li><strong>Email:</strong> <?= html($order['email']) ?></li>
                            <li><strong>Phone:</strong> <?= html($order['phone']) ?></li>
                            <li><strong>Shipping Address:</strong> <?= nl2br(html($order['address'])) ?></li>
                        </ul>
                        
                        <h5 class="mt-4">Items Ordered</h5>
                        <table class="table table-sm">
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= html($item['title']) ?></td>
                                    <td class="text-end">₹<?= html(number_format($item['price'], 2)) ?></td>
                                    <td class="text-center">x<?= (int)$item['qty'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="d-grid mt-4">
                            <a href="/index.php" class="btn btn-primary">Continue Shopping</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>