<?php
// account.php
// Main user dashboard and account hub.

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

// Ensure user is logged in
require_login();

$page_title = "My Account Dashboard - SBSmart";
require_once __DIR__ . '/../includes/header.php';

$user_session = current_user();
$userName = html($user_session['name'] ?? 'Customer');
$userId = (int)($user_session['id'] ?? 0);

// --- Fetch Recent Order Summary ---
$recent_orders = [];
try {
    $conn = db();
    $stmt = $conn->prepare("SELECT id, total, status, created_at FROM orders WHERE email = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$user_session['email']]);
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log('Account Dashboard: Error fetching orders: ' . $e->getMessage());
    // Fail gracefully: orders remain empty.
}

$flash = flash_get();
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="h3 mb-4">Welcome, <?= $userName ?></h1>
            
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
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="card shadow-sm p-3">
                <h5 class="mb-3">Account Navigation</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><a href="account.php" class="btn btn-primary w-100 text-start"><i class="bi bi-house-door me-2"></i> Dashboard</a></li>
                    <li class="mb-2"><a href="account-orders.php" class="btn btn-outline-primary w-100 text-start"><i class="bi bi-list-check me-2"></i> My Orders</a></li>
                    <li class="mb-2"><a href="../account-profile.php" class="btn btn-outline-primary w-100 text-start"><i class="bi bi-person me-2"></i> Profile & Password</a></li>
                    <li class="mb-2"><a href="../auth/logout.php" class="btn btn-outline-danger w-100 text-start"><i class="bi bi-box-arrow-right me-2"></i> Log Out</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-lg-9">
            <!-- Recent Orders Card -->
            <div class="card shadow-sm p-4">
                <h4 class="card-title mb-3">Recent Orders</h4>
                
                <?php if (empty($recent_orders)): ?>
                    <div class="alert alert-info mb-0">You have no recent orders.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
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
                                        <td><?= $order_date->format('M j, Y') ?></td>
                                        <td>₹<?= html(number_format((float)$order['total'], 2)) ?></td>
                                        <td><span class="<?= $status_class ?>"><?= (strtolower($order['status']) === 'paid' ? 'Payment Done' : html(ucfirst($order['status']))) ?></span></td>
                                        <td>
                                            <a href="account-order-detail.php?id=<?= (int)$order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="account-orders.php" class="btn btn-outline-secondary">View All Orders</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>