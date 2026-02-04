<?php
declare(strict_types=1);

// products.php - All Products Page

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';

// Page meta
$page_title = "All Products — SBSmart";
$meta_description = "Browse our complete collection of industrial and electrical supplies.";

require_once __DIR__ . '/includes/header.php';

// Fetch products
$products = [];
try {
    $pdo = db();
    // Fetch all active products with subcategory name
    $sql = "SELECT p.id, p.sku, p.title, p.slug, p.price, p.mrp, p.image, s.name as subcategory_name 
            FROM products p
            LEFT JOIN subcategories s ON p.subcategory_id = s.id
            WHERE p.status = 1 ORDER BY p.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($rows) {
        foreach ($rows as $r) {
            $products[] = [
                'id'    => (int)($r['id'] ?? 0),
                'sku'   => (string)($r['sku'] ?? ''),
                'name'  => (string)($r['title'] ?? ($r['name'] ?? 'Product')),
                'subcategory_name' => (string)($r['subcategory_name'] ?? ''),
                'slug'  => isset($r['slug']) ? (string)$r['slug'] : '',
                'price' => isset($r['price']) ? (float)$r['price'] : 0.0,
                'mrp'   => isset($r['mrp']) ? (float)$r['mrp'] : null,
                'image' => (string)($r['image'] ?? ''),
            ];
        }
    }
} catch (Throwable $e) {
    error_log('products.php fetch failed: ' . $e->getMessage());
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Our Products</h1>
        <span class="text-muted"><?= count($products) ?> items</span>
    </div>

    <?php if (empty($products)): ?>
        <div class="text-center py-5 px-3">
            <div class="mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 100px; height: 100px; background: rgba(13, 110, 253, 0.1);">
                     <i class="bi bi-hourglass-split text-primary fs-1"></i>
                </div>
            </div>
            <h3 class="h4 fw-bold mb-2">Coming Soon</h3>
            <p class="text-muted mb-4" style="max-width: 450px; margin: 0 auto;">
                 We are currently updating our catalog with new industrial and electrical supplies. Please check back shortly or contact us for specific requirements.
            </p>
            <div class="d-flex justify-content-center gap-3">
                 <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">Back to Home</a>
                 <a href="contact-us.php" class="btn btn-primary rounded-pill px-4">Contact Us</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $p): 
                $pid = $p['id'];
                $pname = esc($p['name']);
                $psku  = esc($p['sku']);
                $psub  = esc($p['subcategory_name']);
                
                // Logic: If SKU exists, use SKU as main title, else Name.
                // Secondary: Subcategory (if exists)
                $displayTitle = $psku ?: $pname;
                $displaySub   = $psub;
                
                $img = esc(resolve_image($p['image']));
                $price = $p['price'];
                $mrp = $p['mrp'];
                $link = "/product.php?id=$pid";
            ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 product-card shadow-sm border-0">
                        <a href="<?= $link ?>" class="d-block position-relative overflow-hidden skeleton-box">
                            <img src="<?= $img ?>" class="card-img-top" alt="<?= $pname ?>" loading="lazy"
                                 style="height:200px; object-fit:contain; background:#fff; transition: transform 0.3s ease;"
                                 onerror="this.onerror=null;this.src='<?= esc(resolve_image('')) ?>'">
                        </a>
                        
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title" title="<?= $pname ?>">
                                <a href="<?= $link ?>" class="stretched-link">
                                    <?= $displayTitle ?>
                                    <?php if(!empty($displaySub)): ?>
                                        <br><span class="text-muted small fw-normal" style="font-size:0.75rem;">(<?= $displaySub ?>)</span>
                                    <?php endif; ?>
                                </a>
                            </h6>
                            
                            <div class="mt-auto">
                                <div class="mb-2">
                                    <?php if ($price > 0): ?>
                                        <span class="product-price"><?= format_price($price) ?></span>
                                        <?php if ($mrp && $mrp > $price): ?>
                                            <span class="product-mrp"><?= format_price($mrp) ?></span>
                                            <small class="text-success fw-bold d-block mt-1" style="font-size:0.75rem">
                                                <?= round((($mrp - $price) / $mrp) * 100) ?>% OFF
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="product-price text-primary" style="font-size: 1rem;">Price on Request</span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($price > 0): ?>
                                    <form action="cart-add.php" method="post">
                                        <?= csrf_input(); ?>
                                        <input type="hidden" name="id" value="<?= (int)$pid ?>">
                                        <input type="hidden" name="qty" value="1">
                                        <button class="btn btn-view w-100 position-relative z-2">Add to Cart</button>
                                    </form>
                                <?php else: ?>
                                    <a href="contact-us.php?subject=Quote Request for <?= urlencode($psku) ?>" class="btn btn-outline-primary w-100 position-relative z-2 fw-bold small">Request Quote</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


<?php require __DIR__ . '/includes/footer.php'; ?>
