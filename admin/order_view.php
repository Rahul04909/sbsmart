<?php
// order_view.php - Admin Order Details
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id");
$stmt->execute(['id'=>$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "<div class='alert alert-danger'>Order not found</div>";
    exit;
}

$stmt = $pdo->prepare("SELECT oi.*, p.title, p.sku FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = :order_id");
$stmt->execute(['order_id'=>$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Order #<?php echo $order['id']; ?> | Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<?php include 'partials/topnav.php'; ?>
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-4 keep-print">
    <div>
        <a href="orders.php" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left"></i> Back to Orders</a>
        <h2 class="h3 mb-0 text-gray-800">Order #<?= $order['id'] ?></h2>
    </div>
    <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer-fill me-2"></i> Print Invoice</button>
  </div>

  <div class="row">
      <div class="col-lg-8">
          <!-- Items Card -->
          <div class="card shadow-sm mb-4">
              <div class="card-header bg-white py-3">
                  <h6 class="m-0 fw-bold text-primary">Order Items</h6>
              </div>
              <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $it): 
                                $subtotal = $it['price'] * $it['qty'];
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($it['title']); ?></div>
                                    <?php if(!empty($it['sku'])): ?>
                                        <div class="small text-muted">SKU: <?= htmlspecialchars($it['sku']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">₹<?= number_format($it['price'], 2); ?></td>
                                <td class="text-center"><?= $it['qty']; ?></td>
                                <td class="text-end fw-bold">₹<?= number_format($subtotal, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                                <td class="text-end fw-bold text-success fs-5">₹<?= number_format($order['total'], 2); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                  </div>
              </div>
          </div>
      </div>

      <div class="col-lg-4">
          <!-- Customer Info -->
          <div class="card shadow-sm mb-4">
              <div class="card-header bg-white py-3">
                  <h6 class="m-0 fw-bold text-primary">Customer Details</h6>
              </div>
              <div class="card-body">
                  <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold">Customer Name</label>
                    <div class="fw-bold"><?= htmlspecialchars($order['name']); ?></div>
                  </div>
                  <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold">Email Address</label>
                    <div><a href="mailto:<?= htmlspecialchars($order['email']); ?>" class="text-decoration-none"><?= htmlspecialchars($order['email']); ?></a></div>
                  </div>
                  <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold">Phone Number</label>
                    <div><?= htmlspecialchars($order['phone']); ?></div>
                  </div>
                  <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold">Shipping Address</label>
                    <div><?= nl2br(htmlspecialchars($order['address'])); ?></div>
                  </div>
              </div>
          </div>

          <!-- Order Info -->
          <div class="card shadow-sm mb-4">
              <div class="card-header bg-white py-3">
                  <h6 class="m-0 fw-bold text-primary">Order Info</h6>
              </div>
              <div class="card-body">
                  <div class="mb-3">
                      <label class="small text-muted text-uppercase fw-bold">Date Placed</label>
                      <div><?= date('d M Y, h:i A', strtotime($order['created_at'])); ?></div>
                  </div>
                  <div class="mb-3">
                      <label class="small text-muted text-uppercase fw-bold">Payment Method</label>
                      <div class="text-uppercase"><?= htmlspecialchars($order['payment_method'] ?? 'Online / COD'); ?></div>
                  </div>
                  <div class="mb-3">
                      <label class="small text-muted text-uppercase fw-bold">Status</label>
                      <div>
                          <span class="badge bg-primary text-uppercase fs-6"><?= htmlspecialchars($order['status']); ?></span>
                      </div>
                  </div>
                  <?php if(!empty($order['razorpay_payment_id'])): ?>
                   <div class="mb-3">
                      <label class="small text-muted text-uppercase fw-bold">Transaction ID</label>
                      <div class="font-monospace small"><?= htmlspecialchars($order['razorpay_payment_id']); ?></div>
                   </div>
                  <?php endif; ?>
              </div>
          </div>
      </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
