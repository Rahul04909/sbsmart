<?php
// account/account-orders.php
// Lists all orders placed by the logged-in user.

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

require_login();

$page_title = "My Orders - SBSmart";
require_once __DIR__ . '/../includes/header.php';

$user_session = current_user();
$userEmail = $user_session['email'] ?? '';
$all_orders = [];

try {
    $conn = db();
    $stmt = $conn->prepare("SELECT id, total, status, created_at FROM orders WHERE email = ? ORDER BY created_at DESC");
    $stmt->execute([$userEmail]);
    $all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Account Orders: Error fetching orders: ' . $e->getMessage());
    flash_set('danger', 'Could not load your order history.');
}

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

        <!-- Main Orders List -->
        <div class="col-lg-9">
            <div class="card shadow-sm p-4">
                <h1 class="h4 card-title mb-4">All Orders (<?= count($all_orders) ?>)</h1>
                
                <?php if (!empty($flash)): ?>
                    <?php foreach ($flash as $type => $msgs): ?>
                        <?php foreach ((array)$msgs as $m): ?>
                            <?php $bsClass = in_array($type, ['error','danger']) ? 'danger' : ($type === 'success' ? 'success' : 'info'); ?>
                            <div class="alert alert-<?= $bsClass ?> alert-dismissible fade show" role="alert">
                                <?= html($m) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (empty($all_orders)): ?>
                    <div class="alert alert-info">You have not placed any orders yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_orders as $order): ?>
                                    <?php 
                                        $order_date = new DateTime($order['created_at']);
                                        $status_class = match (strtolower($order['status'])) {
                                            'paid' => 'badge bg-success',
                                            'pending' => 'badge bg-warning text-dark',
                                            'confirmed' => 'badge bg-primary',
                                            'shipped' => 'badge bg-primary',
                                            'delivered' => 'badge bg-success',
                                            'cancelled' => 'badge bg-danger',
                                            'failed' => 'badge bg-danger',
                                            'cod' => 'badge bg-info text-dark',
                                            default => 'badge bg-secondary',
                                        };
                                    ?>
                                    <tr>
                                        <td><?= html($order['id']) ?></td>
                                        <td><?= $order_date->format('F j, Y') ?></td>
                                        <td>₹<?= html(number_format((float)$order['total'], 2)) ?></td>
                                        <td><span class="<?= $status_class ?>"><?= (strtolower($order['status']) === 'paid' ? 'Payment Done' : html(ucfirst($order['status']))) ?></span></td>
                                        <td>
                                            <a href="account-order-detail.php?id=<?= (int)$order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                            <a href="../invoice.php?id=<?= (int)$order['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark"><i class="bi bi-file-text"></i> Invoice</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>