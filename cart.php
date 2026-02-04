<?php
// cart.php
// Main cart display page.

declare(strict_types=1);

// Load helpers (includes session start, flash messages, csrf_input, and resolve_image)
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';

// Start session if not already started (helpers usually do this, but be defensive)
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Page title and header
$page_title = "Your Cart - SBSmart";
require_once __DIR__ . '/includes/header.php';

// ---------------------------
// Fallbacks for helper funcs
// ---------------------------
// resolve_image is now globally handled by includes/helpers.php


if (!function_exists('flash_get')) {
    function flash_get($key = null, $default = null) {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $f = $_SESSION['_flash'] ?? [];
        if ($key === null) {
            // consume all
            $_SESSION['_flash'] = [];
            return $f ?: [];
        }
        $val = $f[$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
}

// Generates a remove URL (currently GET). Using POST is recommended for production.
if (!function_exists('get_remove_url')) {
    function get_remove_url(int $id): string {
        // Keep this simple to match cart-remove.php which checks referer and auth
        return 'cart-remove.php?id=' . (int)$id;
    }
}

// ---------------------------
// Get cart + flash messages
// ---------------------------
// If logged in, sync session cart from DB
if (!empty($_SESSION['user']['id'])) {
    $userId = (int)$_SESSION['user']['id'];
    
    // Ensure PDO is available
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        require_once __DIR__ . '/includes/db.php';
        $pdo = get_db();
    }

    if ($pdo instanceof PDO) {
        // DEBUG: Enable to see what's happening
        // echo "<div style='background:#ffc; padding:10px; border:1px solid #cc0;'>";
        // echo "<strong>Debug Info:</strong><br>";
        // echo "User ID: " . htmlspecialchars((string)$userId) . "<br>";
        
        $stmt = $pdo->prepare("
            SELECT c.product_id, c.quantity, p.sku, p.title, p.price, p.mrp, p.image 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = :uid
        ");
        $stmt->execute([':uid' => $userId]);
        $dbItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // echo "Items found in DB: " . count($dbItems) . "<br>";
        // if (empty($dbItems)) {
        //    echo "Query executed: SELECT ... WHERE user_id = $userId<br>";
        //    // Check if user has any items at all (maybe product join failed?)
        //    $chk = $pdo->query("SELECT * FROM cart WHERE user_id = $userId")->fetchAll();
        //    echo "Raw cart rows (without join): " . count($chk) . "<br>";
        // }
        // echo "</div>";

        // Rebuild session cart from DB to ensure consistency across devices
        $_SESSION['cart'] = [];
        foreach ($dbItems as $item) {
            $_SESSION['cart'][$item['product_id']] = [
                'id'    => $item['product_id'],
                'sku'   => $item['sku'] ?? '',
                'title' => $item['title'],
                'price' => (float)$item['price'],
                'mrp'   => (float)$item['mrp'],
                'image' => $item['image'],
                'qty'   => (int)$item['quantity'],
            ];
        }
    } else {
        // echo "PDO Failed to initialize.<br>";
    }
} else {
    // echo "User ID not set in session.<br>";
}

$cart = $_SESSION['cart'] ?? [];
$allFlash = flash_get(); // returns array of flashes (may be [])

// Helper to render flash messages (handles different flash shapes)
function render_flashes(array $flashes): void {
    foreach ($flashes as $type => $messages) {
        // messages might be a string or an array
        $msgs = is_array($messages) ? $messages : [$messages];
        foreach ($msgs as $m) {
            $bsClass = in_array($type, ['error','danger']) ? 'danger' : ($type === 'success' ? 'success' : 'info');
            $safe = htmlspecialchars((string)$m, ENT_QUOTES | ENT_SUBSTITUTE);
            echo '<div class="alert alert-' . $bsClass . ' alert-dismissible fade show" role="alert">'
               .  $safe
               .  '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
               .  '</div>';
        }
    }
}

?>
<style>
/* Responsive cart table for small screens */
@media (max-width: 576px) {
  .cart-responsive .table thead { display: none; }
  .cart-responsive .table tbody tr { display: block; margin-bottom: 0.75rem; border-bottom: 1px solid #eef; padding-bottom: .5rem; }
  .cart-responsive .table tbody td { display: flex; justify-content: space-between; align-items: center; padding: .4rem 0; }
  .cart-responsive .table tbody td:first-child { align-items: flex-start; }
  .cart-responsive .table tbody td img { width: 56px; height: 56px; margin-right: .75rem; object-fit: contain; }
  .cart-responsive .table tbody td .fw-semibold { font-size: .95rem; }
  .cart-responsive .table tbody td:nth-child(2),
  .cart-responsive .table tbody td:nth-child(3),
  .cart-responsive .table tbody td:nth-child(4) { width: 90px; flex: 0 0 90px; text-align: right; }
  .cart-responsive .table tfoot tr { display: block; }
  .cart-actions { flex-direction: column !important; align-items: stretch; }
  .cart-actions .btn { width: 100%; }
  .cart-responsive .table { font-size: .95rem; }
  input.form-control-sm { width: 70px !important; }
}
</style>

<div class="container my-4">
  <h1 class="h4 mb-4">Your Cart</h1>

  <?php
  // Debug output removed
  ?>

  <?php if (!empty($allFlash)): ?>
    <?php render_flashes($allFlash); ?>
  <?php endif; ?>

  <?php if (empty($cart)): ?>
    <div class="alert alert-info">Your cart is empty.</div>
    <a href="index.php" class="btn btn-primary">Continue shopping</a>
  <?php else: ?>
    <form action="checkout.php" method="post" class="mb-3" novalidate>
      <?= function_exists('csrf_input') ? csrf_input() : ''; ?>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th scope="col">Product</th>
              <th scope="col" width="120">Price</th>
              <th scope="col" width="100">Qty</th>
              <th scope="col" width="120">Subtotal</th>
              <th scope="col" width="50" aria-hidden="true"></th>
            </tr>
          </thead>
          <tbody>
            <?php
              $total = 0.0;
              foreach ($cart as $cartKey => $cartItem):
                  // Normalize shapes
                  if (is_array($cartItem)) {
                      $id = (int)($cartItem['id'] ?? $cartKey ?? 0);
                      $sku = (string)($cartItem['sku'] ?? '');
                      $title = (string)($cartItem['title'] ?? $cartItem['name'] ?? 'Product');
                      $price = isset($cartItem['price']) ? (float)$cartItem['price'] : 0.0;
                      $qty = max(0, (int)($cartItem['qty'] ?? $cartItem['quantity'] ?? 0));
                      $raw_img = $cartItem['image'] ?? ($cartItem['img'] ?? '');
                      $mrp = isset($cartItem['mrp']) && $cartItem['mrp'] !== '' ? (float)$cartItem['mrp'] : null;
                  } else {
                      // key as id, value as qty
                      $id = (int)$cartKey;
                      $sku = '';
                      $title = 'Product';
                      $price = 0.0;
                      $qty = (int)$cartItem;
                      $raw_img = '';
                      $mrp = null;
                  }

                  if ($qty <= 0) continue;

                  $subtotal = $price * $qty;
                  $total += $subtotal;
                  $imgSrc = htmlspecialchars(resolve_image($raw_img), ENT_QUOTES | ENT_SUBSTITUTE);
                  $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE);
            ?>
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <img src="<?= $imgSrc ?>" alt="<?= $safeTitle ?>" loading="lazy"
                       class="me-3" style="width:72px;height:72px;object-fit:contain;border-radius:6px;background:#fff;padding:6px;"
                       onerror="this.onerror=null;this.src='<?= htmlspecialchars(resolve_image(''), ENT_QUOTES | ENT_SUBSTITUTE) ?>'">

                  <div>
                    <div class="fw-semibold">
                      <a href="product.php?id=<?= (int)$id ?>" class="text-dark text-decoration-none">
                        <?= $safeTitle ?>
                        <?php if(!empty($sku)): ?>
                            <span class="text-muted small fw-normal">(<?= htmlspecialchars($sku) ?>)</span>
                        <?php endif; ?>
                      </a>
                    </div>
                    <?php if ($mrp !== null && $mrp > $price): ?>
                      <small class="text-muted text-decoration-line-through">₹<?= number_format((float)$mrp, 2) ?></small>
                    <?php endif; ?>
                  </div>
                </div>
              </td>

              <td>
                  <?php if ($price > 0): ?>
                    ₹<?= number_format((float)$price, 2) ?>
                  <?php else: ?>
                    <span class="text-primary small fw-bold">Price on Request</span>
                  <?php endif; ?>
              </td>

              <td>
                <input type="number" name="qty[<?= (int)$id ?>]" value="<?= (int)$qty ?>" min="0" class="form-control form-control-sm" style="width:90px;">
              </td>

              <td>
                <?php if ($price > 0): ?>
                    ₹<?= number_format((float)$subtotal, 2) ?>
                <?php else: ?>
                    <span class="text-primary small fw-bold">Price on Request</span>
                <?php endif; ?>
              </td>

              <td class="text-center">
                <a href="<?= htmlspecialchars(get_remove_url((int)$id), ENT_QUOTES | ENT_SUBSTITUTE) ?>"
                   class="btn btn-sm btn-outline-danger"
                   title="Remove item"
                   onclick="return confirm('Are you sure you want to remove this item?');"
                   aria-label="Remove <?= $safeTitle ?>">&times;</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="3" class="text-end">Total</th>
              <th>₹<?= number_format((float)$total, 2) ?></th>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="d-flex justify-content-between mt-3 cart-actions">
        <a href="index.php" class="btn btn-outline-secondary">Continue shopping</a>

        <div class="d-flex gap-2">
          <!-- Update Cart button posts to cart-update.php; csrf_input already included above -->
          <button formaction="cart-update.php" formmethod="post" class="btn btn-primary">Update Cart</button>
          <button type="submit" class="btn btn-success">Checkout</button>
        </div>
      </div>
    </form>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php';
