<?php
// product.php — Product Detail Page (with Add-to-Cart + Social Share)
// Save as UTF-8 (no BOM)

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';

// Safe PDO getter
function db_conn(): ?PDO {
    if (function_exists('db')) {
        try { return db(); } catch (Throwable $e) { error_log($e->getMessage()); }
    }
    if (function_exists('get_db')) {
        try { return get_db(); } catch (Throwable $e) { error_log($e->getMessage()); }
    }
    return null;
}

$pdo = db_conn();
if (!$pdo) {
    http_response_code(500);
    die("Database connection error.");
}

// Input
$productId = (int)($_GET['id'] ?? 0);
if ($productId <= 0) {
    http_response_code(404);
    die("Invalid product ID.");
}

// Fetch product
try {
    $sql = "SELECT p.*, c.name AS category_name, s.name AS subcategory_name
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN subcategories s ON s.id = p.subcategory_id
            WHERE p.id = :id AND p.status = 1
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log("product.php main fetch: " . $e->getMessage());
    http_response_code(500);
    die("Server error.");
}

if (!$product) {
    $page_title = "Product Not Found - SBSmart";
    require __DIR__ . '/includes/header.php';
    echo '<div class="container py-5"><div class="alert alert-warning">Product not found.</div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Related products
try {
    $rel = $pdo->prepare("SELECT id,sku,title,price,mrp,image FROM products WHERE status = 1 AND subcategory_id = ? AND id <> ? ORDER BY id DESC LIMIT 8");
    $rel->execute([(int)$product['subcategory_id'], $productId]);
    $related = $rel->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    error_log("product.php related fetch: ". $e->getMessage());
    $related = [];
}

// Helpers
// resolve_image handled by includes/helpers.php
if (!function_exists('money_inr')) {
    function money_inr($v) { return '₹' . number_format((float)$v, 2); }
}

$h = function_exists('html') ? 'html' : 'htmlspecialchars';
$product_img = resolve_image($product['image']);
$product_url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
$meta_description = $product['short_description'] ?: mb_substr(strip_tags($product['description'] ?? ''),0,150);

$page_title = $h($product['title']) . " | SBSmart";

// Render header and inject SEO OG tags into head
ob_start();
require __DIR__ . '/includes/header.php';
$header = ob_get_clean();

$seo = <<<HTML
    <meta property="og:title" content="{$h($product['title'])}">
    <meta property="og:description" content="{$h($meta_description)}">
    <meta property="og:image" content="{$h($product_img)}">
    <meta property="og:url" content="{$h($product_url)}">
    <meta property="og:type" content="product">
HTML;

$header = str_replace("</head>", "$seo\n</head>", $header);
echo $header;
?>

<div class="container my-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <?php if (!empty($product['category_id'])): ?>
        <li class="breadcrumb-item">
          <a href="category.php?id=<?= (int)$product['category_id'] ?>&type=cat"><?= $h($product['category_name']) ?></a>
        </li>
      <?php endif; ?>
      <?php if (!empty($product['subcategory_id'])): ?>
        <li class="breadcrumb-item">
          <a href="category.php?id=<?= (int)$product['subcategory_id'] ?>&type=sub"><?= $h($product['subcategory_name']) ?></a>
        </li>
      <?php endif; ?>
      <li class="breadcrumb-item active" aria-current="page"><?= $h($product['title']) ?></li>
    </ol>
  </nav>

  <div class="row g-4">
    <div class="col-lg-5">
      <div class="card shadow-sm skeleton-box">
        <img src="<?= $h($product_img) ?>" alt="<?= $h($product['title']) ?>" class="card-img-top" style="object-fit:contain; max-height:420px; background:#fff;" onerror="this.onerror=null;this.src='<?= esc(resolve_image('')) ?>'">
      </div>
    </div>

    <div class="col-lg-7">
      
      <!-- Category Label -->
      <?php if (!empty($product['category_name'])): ?>
        <div class="text-uppercase text-primary fw-bold small mb-1 tracking-wide">
          <?= $h($product['category_name']) ?>
        </div>
      <?php endif; ?>

      <!-- Title -->
      <h1 class="fw-bold mb-2 text-dark" style="font-size: 1.75rem; line-height: 1.2;">
        <?= $h($product['sku'] ?? $product['sku']) ?>
          <?php if (!empty($product['subcategory_name'])): ?>
            <span class="text-muted fw-normal fs-5">(<?= $h($product['subcategory_name']) ?>)</span>
          <?php endif; ?>
      </h1>

      <!-- Price Block -->
      <div class="d-flex align-items-center flex-wrap gap-3 mb-3 pb-2 border-bottom">
        <div class="d-flex align-items-baseline">
          <?php if ($product['price'] > 0): ?>
            <span class="fw-bold text-dark" style="font-size: 1.75rem;"><?= money_inr($product['price']) ?></span>
            <?php if (!empty($product['mrp']) && $product['mrp'] > $product['price']): ?>
              <span class="text-muted text-decoration-line-through fs-6 ms-2"><?= money_inr($product['mrp']) ?></span>
              <?php $off = round((($product['mrp'] - $product['price']) / $product['mrp']) * 100); ?>
              <span class="badge bg-danger ms-2 px-2 py-1 rounded-pill small"><?= $off ?>% OFF</span>
            <?php endif; ?>
          <?php else: ?>
             <span class="fw-bold text-primary" style="font-size: 1.5rem;">Price on Request</span>
          <?php endif; ?>
        </div>
        
        <?php if ((int)$product['stock'] > 0): ?>
          <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
            <i class="bi bi-check-lg"></i> In Stock
          </span>
        <?php else: ?>
          <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
            <i class="bi bi-x-lg"></i> Out of Stock
          </span>
        <?php endif; ?>
      </div>

      <!-- Short Description -->
      <?php if (!empty($product['short_description'])): ?>
        <div class="mb-3 text-secondary" style="font-size: 1rem; line-height: 1.5;">
          <?= nl2br($h($product['short_description'])) ?>
        </div>
      <?php endif; ?>

      <!-- Add to Cart Section -->
      <div class="mb-4">
       <?php if ($product['price'] > 0): ?>
        <form action="cart-add.php" method="post" class="d-flex flex-wrap align-items-end gap-2">
          <?= csrf_input(); ?>
          <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
          
          <div style="width: 80px;">
            <label class="form-label fw-bold small text-uppercase text-muted mb-1" style="font-size:0.75rem">Quantity</label>
            <input type="number" name="qty" min="1" max="<?= max(1,(int)$product['stock']) ?>" value="1" class="form-control text-center fw-bold" style="border-radius: 6px;">
          </div>

          <div class="flex-grow-1" style="min-width: 180px;">
            <button class="btn btn-primary w-100 py-2 fw-bold" style="border-radius: 6px;" <?= ((int)$product['stock'] <= 0) ? 'disabled' : '' ?>>
              <i class="bi bi-bag-plus-fill me-2"></i> Add to Cart
            </button>
          </div>
        </form>
       <?php else: ?>
         <div class="d-grid">
            <a href="contact-us.php?subject=Quote Request for <?= urlencode($product['sku']) ?>" class="btn btn-outline-primary py-2 fw-bold" style="border-radius: 6px;">
                <i class="bi bi-envelope-fill me-2"></i> Request Quote
            </a>
         </div>
       <?php endif; ?>
      </div>

      <!-- Meta Details -->
      <div class="bg-light rounded-3 p-3 mb-3">
        <h6 class="fw-bold mb-2 text-uppercase small text-muted">Product Details</h6>
        <div class="row g-2 small">
          <?php if (!empty($product['sku'])): ?>
            <div class="col-6">
              <span class="text-muted d-block" style="font-size:0.8rem">SKU</span>
              <span class="fw-semibold text-dark"><?= $h($product['sku']) ?></span>
            </div>
          <?php endif; ?>
          <?php if (!empty($product['subcategory_name'])): ?>
            <div class="col-6">
              <span class="text-muted d-block" style="font-size:0.8rem">Type</span>
              <span class="fw-semibold text-dark"><?= $h($product['subcategory_name']) ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Share -->
      <div class="d-flex align-items-center gap-2 mb-4">
        <span class="fw-bold small text-uppercase text-muted me-2">Share:</span>
        <a href="https://wa.me/?text=<?= urlencode($product['title'] . ' - ' . $product_url) ?>" class="btn btn-outline-success btn-sm rounded-circle" style="width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;" target="_blank"><i class="bi bi-whatsapp"></i></a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($product_url) ?>" class="btn btn-outline-primary btn-sm rounded-circle" style="width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;" target="_blank"><i class="bi bi-facebook"></i></a>
        <a href="https://twitter.com/intent/tweet?text=<?= urlencode($product['title']) ?>&url=<?= urlencode($product_url) ?>" class="btn btn-outline-dark btn-sm rounded-circle" style="width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;" target="_blank"><i class="bi bi-twitter-x"></i></a>
        <button id="copyLinkBtn" class="btn btn-outline-secondary btn-sm rounded-circle" style="width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;" title="Copy Link"><i class="bi bi-link-45deg"></i></button>
      </div>

      <!-- Full Description -->
      <?php if (!empty($product['description'])): ?>
        <div class="pt-3 border-top">
          <h5 class="fw-bold mb-2 h6 text-uppercase">Description</h5>
          <div class="text-secondary small" style="line-height: 1.6;">
            <?= nl2br($h($product['description'])) ?>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- Related products -->
  <div class="mt-5 pt-4 border-top">
    <h4 class="mb-4">Related Products</h4>
    <div class="row g-4">
      <?php if (!empty($related)): ?>
        <?php foreach ($related as $r): ?>
          <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100 product-card shadow-sm">
              <a href="product.php?id=<?= (int)$r['id'] ?>" class="skeleton-box d-block">
                <img src="<?= $h(resolve_image($r['image'])) ?>" class="card-img-top" alt="<?= $h($r['title']) ?>" style="height:180px; object-fit:contain; padding:10px;" onerror="this.onerror=null;this.src='<?= esc(resolve_image('')) ?>'">
              </a>
              <div class="card-body d-flex flex-column">
                <h6 class="card-title" title="<?= $h($r['title']) ?>">
                  <a href="product.php?id=<?= (int)$r['id'] ?>" class="stretched-link">
                    <?php
                        // Related products are from same subcategory, so use main product's sub_name
                        $rSub = $product['subcategory_name'] ?? '';
                        $rTitle = !empty($r['sku']) ? $r['sku'] : $r['title'];
                    ?>
                    <?= $h($rTitle) ?>
                    <?php if(!empty($rSub)): ?>
                        <br><span class="text-muted small fw-normal" style="font-size:0.75rem;">(<?= $h($rSub) ?>)</span>
                    <?php endif; ?>
                  </a>
                </h6>
                <div class="mt-auto">
                  <div class="mb-2">
                    <?php if ($r['price'] > 0): ?>
                        <?php if (!empty($r['mrp']) && $r['mrp'] > $r['price']): ?>
                          <span class="product-price"><?= money_inr($r['price']) ?></span>
                          <span class="product-mrp"><?= money_inr($r['mrp']) ?></span>
                        <?php else: ?>
                          <span class="product-price"><?= money_inr($r['price']) ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="small fw-bold text-primary">Price on Request</span>
                    <?php endif; ?>
                  </div>
                  <?php if ($r['price'] > 0): ?>
                  <form action="cart-add.php" method="post">
                    <?= csrf_input(); ?>
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <input type="hidden" name="qty" value="1">
                    <button class="btn btn-view w-100 position-relative z-2">Add to Cart</button>
                  </form>
                  <?php else: ?>
                    <a href="product.php?id=<?= (int)$r['id'] ?>" class="btn btn-outline-secondary w-100 position-relative z-2">View Details</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12"><div class="alert alert-info">No related products found.</div></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

<script>
(function(){
  // Copy link button
  var copyBtn = document.getElementById('copyLinkBtn');
  if (copyBtn) {
    copyBtn.addEventListener('click', function(e){
      e.preventDefault();
      var link = <?= json_encode($product_url) ?>;
      
      // Try modern clipboard API first (requires secure context like HTTPS or localhost)
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(link).then(function(){
          showSuccess();
        }, function(){
          fallbackCopy(link);
        });
      } else {
        // Fallback for non-secure contexts (http)
        fallbackCopy(link);
      }
    });
  }

  function showSuccess() {
    var btn = document.getElementById('copyLinkBtn');
    if(!btn) return;
    var originalHtml = '<i class="bi bi-link-45deg"></i>';
    btn.innerHTML = '<i class="bi bi-check-lg"></i>';
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-success');
    
    setTimeout(function(){ 
      btn.innerHTML = originalHtml; 
      btn.classList.remove('btn-success');
      btn.classList.add('btn-outline-secondary');
    }, 2000);
  }

  function fallbackCopy(text) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    
    // Ensure it's not visible but part of the DOM
    textArea.style.position = "fixed";
    textArea.style.left = "0";
    textArea.style.top = "0";
    textArea.style.opacity = "0";
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
      var successful = document.execCommand('copy');
      if (successful) {
        showSuccess();
      } else {
        prompt("Press Ctrl+C to copy link:", text);
      }
    } catch (err) {
      prompt("Press Ctrl+C to copy link:", text);
    }

    document.body.removeChild(textArea);
  }

  // Open share popups
  document.querySelectorAll('a[target="_blank"]').forEach(function(a){
    var href = a.getAttribute('href')||'';
    if (href.includes('facebook.com') || href.includes('twitter.com') || href.includes('linkedin.com')) {
      a.addEventListener('click', function(e){
        e.preventDefault();
        var w = 600, h = 400;
        var left = (screen.width/2) - (w/2);
        var top = (screen.height/2) - (h/2);
        window.open(href, 'sharewin', 'toolbar=0,status=0,width='+w+',height='+h+',top='+top+',left='+left);
      });
    }
  });
})();
</script>
