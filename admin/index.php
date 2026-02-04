<?php
// index.php - Admin dashboard (Redesigned)
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

// Safe counts
function safe_count($pdo, $table) {
    try {
        return (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

$conn = $pdo;
$products = safe_count($pdo, 'products');
$categories = safe_count($pdo, 'categories');
$subcategories = safe_count($pdo, 'subcategories');
$ordersCount = safe_count($pdo, 'orders');
$users = safe_count($pdo, 'users');

// Recent Orders
try {
    $stmt = $pdo->query("SELECT id, name, total, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recentOrders = [];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard | SBSmart</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
      .card-stat { transition: transform 0.2s; border: none; border-radius: 10px; }
      .card-stat:hover { transform: translateY(-5px); }
      .icon-box { font-size: 2rem; opacity: 0.8; }
      .bg-gradient-primary { background: linear-gradient(45deg, #4e73df, #224abe); color: white; }
      .bg-gradient-success { background: linear-gradient(45deg, #1cc88a, #13855c); color: white; }
      .bg-gradient-info { background: linear-gradient(45deg, #36b9cc, #258391); color: white; }
      .bg-gradient-warning { background: linear-gradient(45deg, #f6c23e, #dda20a); color: white; }
      .text-white-50 { color: rgba(255,255,255,0.7) !important; }
  </style>
</head>
<body class="bg-light">

<?php include __DIR__ . '/partials/topnav.php'; ?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 mb-0 text-gray-800">Dashboard</h2>
    <div>
        <a href="product_import.php" class="btn btn-sm btn-success me-2"><i class="bi bi-file-earmark-spreadsheet"></i> Import Data</a>
        <a href="product_export.php" class="btn btn-sm btn-primary me-2"><i class="bi bi-download"></i> Export Data</a>
        <a href="index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i> Refresh</a>
    </div>
  </div>

  <!-- Stats Row -->
  <div class="row g-4 mb-5">
    
    <!-- Products -->
    <div class="col-md-3">
      <div class="card card-stat bg-gradient-primary shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs fw-bold text-uppercase mb-1 text-white-50">Products</div>
              <div class="h3 mb-0 fw-bold text-white"><?= $products ?></div>
            </div>
            <div class="col-auto">
              <i class="bi bi-box-seam icon-box text-white"></i>
            </div>
          </div>
          <a href="products.php" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- Orders -->
    <div class="col-md-3">
      <div class="card card-stat bg-gradient-success shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs fw-bold text-uppercase mb-1 text-white-50">Total Orders</div>
              <div class="h3 mb-0 fw-bold text-white"><?= $ordersCount ?></div>
            </div>
            <div class="col-auto">
              <i class="bi bi-cart-check icon-box text-white"></i>
            </div>
          </div>
          <a href="orders.php" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- Users -->
    <div class="col-md-3">
      <div class="card card-stat bg-gradient-info shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs fw-bold text-uppercase mb-1 text-white-50">Registered Users</div>
              <div class="h3 mb-0 fw-bold text-white"><?= $users ?></div>
            </div>
            <div class="col-auto">
              <i class="bi bi-people-fill icon-box text-white"></i>
            </div>
          </div>
          <a href="users.php" class="stretched-link"></a>
        </div>
      </div>
    </div>

    <!-- Categories -->
    <div class="col-md-3">
      <div class="card card-stat bg-gradient-warning shadow h-100 py-2">
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs fw-bold text-uppercase mb-1 text-white-50">Categories</div>
              <div class="h3 mb-0 fw-bold text-white"><?= $categories ?></div>
            </div>
            <div class="col-auto">
              <i class="bi bi-tags-fill icon-box text-white"></i>
            </div>
          </div>
          <a href="categories.php" class="stretched-link"></a>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Orders Table -->
  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
      <h6 class="m-0 fw-bold text-primary">Recent Orders</h6>
      <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <?php if (empty($recentOrders)): ?>
            <p class="text-muted text-center py-3">No orders yet.</p>
        <?php else: ?>
        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentOrders as $ro): 
                $st = strtolower($ro['status']);
                $badge = match($st) {
                    'pending' => 'bg-warning text-dark',
                    'success','paid','captured','delivered' => 'bg-success',
                    'failed','cancelled' => 'bg-danger',
                    default => 'bg-secondary'
                };
            ?>
            <tr>
              <td><a href="order_view.php?id=<?= $ro['id'] ?>" class="text-decoration-none fw-bold">#<?= $ro['id'] ?></a></td>
              <td><?= htmlspecialchars($ro['name']) ?></td>
              <td>₹<?= number_format((float)$ro['total'], 2) ?></td>
              <td><span class="badge <?= $badge ?> text-uppercase" style="font-size:0.7rem"><?= htmlspecialchars($ro['status']) ?></span></td>
              <td class="small text-muted"><?= date('d M Y', strtotime($ro['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>