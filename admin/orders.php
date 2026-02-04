<?php
// orders.php - Admin Order Management
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

// fetch orders
$stmt = $pdo->query("SELECT id, name, email, phone, total, status, created_at FROM orders ORDER BY id DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Orders | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
.badge { font-weight: 500; }
</style>
</head>
<body class="bg-light">
<?php include 'partials/topnav.php'; ?>
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800">Orders</h2>
    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print List</button>
  </div>

  <div class="card shadow border-0">
    <div class="card-header bg-white py-3">
        <h6 class="m-0 fw-bold text-primary">All Orders</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-3">ID</th>
                  <th>Customer</th>
                  <th>Contact</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th class="text-end pe-3">Action</th>
                </tr>
              </thead>
              <tbody>
              <?php if (empty($orders)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No orders received yet.</td></tr>
              <?php else: ?>
              <?php foreach($orders as $o): 
                  $st = strtolower($o['status']);
                  $badge = match($st) {
                      'pending' => 'bg-warning text-dark',
                      'cod' => 'bg-info text-dark',
                      'paid','captured','confirmed' => 'bg-primary',
                      'shipped' => 'bg-info',
                      'delivered' => 'bg-success',
                      'cancelled','failed' => 'bg-danger',
                      default => 'bg-secondary'
                  };
              ?>
                <tr>
                  <td class="ps-3 fw-bold">#<?php echo $o['id']; ?></td>
                  <td>
                      <div class="fw-semibold text-dark"><?= htmlspecialchars($o['name']); ?></div>
                      <div class="small text-muted"><?= htmlspecialchars($o['email']); ?></div>
                  </td>
                  <td><?= htmlspecialchars($o['phone']); ?></td>
                  <td class="fw-bold">₹<?= number_format($o['total'], 2); ?></td>
                  <td>
                      <span class="badge <?= $badge ?> text-uppercase"><?= htmlspecialchars($o['status']); ?></span>
                  </td>
                  <td class="small text-muted"><?= date('d M Y, h:i A', strtotime($o['created_at'])); ?></td>
                  <td class="text-end pe-3">
                    <a href="order_view.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-primary"><i class="bi bi-eye-fill me-1"></i> View</a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php endif; ?>
              </tbody>
            </table>
        </div>
    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
