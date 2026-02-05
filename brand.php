<?php
declare(strict_types=1);

// brand.php — Displays a paginated list of products filtered by brand or subcategory.

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';

// Fallback helper shims (non-destructive)
if (!function_exists('esc')) {
    function esc($v) { return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE); }
}
if (!function_exists('format_price')) {
    function format_price(float $amount, string $currency = 'INR'): string {
        if ($currency === 'INR') return '₹' . number_format($amount, 2);
        return number_format($amount, 2) . ' ' . esc($currency);
    }
}
// resolve_image is now globally handled by includes/helpers.php

if (!function_exists('csrf_input')) {
    function csrf_input(): string { return ''; } // implement real CSRF in includes/session.php if needed
}

// Ensure required helpers present (best-effort)
if (!function_exists('esc') || !function_exists('format_price') || !function_exists('resolve_image')) {
    error_log("BRAND.PHP FATAL: Required helpers are missing.");
    http_response_code(500);
    die("Application configuration error. Please check includes/helpers.php.");
}

// --- Input Validation ---
$filterId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// We now support 'brand' or 'sub' (legacy 'cat' redirected to 'brand' logic internally)
$filterType = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : 'brand';
if ($filterType === 'cat') $filterType = 'brand'; // redirect cat -> brand logic
$filterType = in_array($filterType, ['brand', 'sub'], true) ? $filterType : 'brand';

if ($filterId <= 0) {
    http_response_code(400);
    die("Invalid brand or subcategory ID.");
}

// --- Configuration ---
$limit = 12;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$filter_column = ($filterType === 'sub') ? 'p.subcategory_id' : 'p.brand_id';
$where_clause = "WHERE p.status = 1 AND {$filter_column} = :id";
$search_params = [':id' => $filterId];

$filter_name = 'Products';
$total_products = 0;
$total_pages = 1;

try {
    $pdo = get_db();

    // Fetch filter name (brand/subcategory)
    $table = ($filterType === 'sub') ? 'subcategories' : 'brands';
    $safeTable = preg_replace('/[^a-z_]/i', '', $table); // basic safeguard
    $name_stmt = $pdo->prepare("SELECT name FROM {$safeTable} WHERE id = :id LIMIT 1");
    $name_stmt->execute([':id' => $filterId]);
    $result = $name_stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && !empty($result['name'])) {
        $filter_name = esc($result['name']);
    }

    // Count total products
    $count_stmt = $pdo->prepare("SELECT COUNT(p.id) AS cnt FROM products p {$where_clause}");
    $count_stmt->execute($search_params);
    $total_products = (int)$count_stmt->fetchColumn();
    $total_pages = max(1, (int)ceil($total_products / $limit));
} catch (Throwable $e) {
    error_log("brand.php count error: " . $e->getMessage());
    $total_products = 0;
    $total_pages = 1;
}

// --- Fetch Products for Current Page ---
$products = [];
if ($total_products > 0) {
    try {
        $sql = "
            SELECT p.id, p.title, p.sku, p.price, p.mrp, p.stock, p.image, p.short_description, s.name as subcategory_name
            FROM products p
            LEFT JOIN subcategories s ON p.subcategory_id = s.id
            {$where_clause}
            ORDER BY p.title ASC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $filterId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log("brand.php products fetch error: " . $e->getMessage());
        $products = [];
    }
}

$page_title = "{$filter_name} - SBSmart";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <?php 
    // Example specific content logic (previously for ID 3, assuming Flender brand ID was 3)
    // You might want to remove this or adapt it if ID 3 is still Flender in Brands table
    if ($filterId === 3 && $filterType === 'brand'): 
    ?>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                    <h1 class="display-5 fw-bold text-dark mb-3">Flender – Engineered Drive Technology</h1>
                    <div style="width: 80px; height: 4px; background: var(--primary-color, #0d6efd); margin: 0 auto;"></div>
                </div>

                <div class="prose lead text-secondary mb-5">
                    <p class="mb-4">
                        Flender is a global leader in mechanical and electrical drive technology, known worldwide for its highly reliable, efficient, and application-engineered gearbox solutions. With decades of engineering excellence, Flender products are designed to perform in the most demanding industrial environments across sectors such as cement, steel, power, mining, oil & gas, material handling, and infrastructure.
                    </p>
                    <p class="mb-4">
                        <strong>Flender gearboxes are not standard off-the-shelf products.</strong>
                        They are application-specific solutions, engineered based on load conditions, duty cycles, mounting positions, environmental factors, and lifecycle expectations — ensuring maximum uptime, efficiency, and long service life.
                    </p>
                </div>

                <div class="card border-0 shadow-sm bg-light mb-5">
                    <div class="card-body p-4 p-md-5">
                        <h3 class="h4 fw-bold mb-3 text-dark"><i class="bi bi-patch-check-fill text-primary me-2"></i>Authorized Partner</h3>
                        <p class="mb-0">
                            <strong>S.B. Syscon Pvt. Ltd.</strong> is an Authorized Partner for Flender Gearboxes.
                            All Flender solutions offered through SBS are designed and validated with direct support from the Flender backend technical team, ensuring correct selection, compliance with application requirements, and long-term operational reliability.
                        </p>
                    </div>
                </div>

                <div class="alert alert-warning d-flex align-items-start gap-3 p-4 mb-5 shadow-sm" role="alert" style="background-color: #fff3cd; border-color: #ffecb5;">
                    <i class="bi bi-info-circle-fill fs-4 mt-1 text-warning"></i>
                    <div>
                        <h4 class="alert-heading h5 fw-bold text-dark">Assisted Support Required</h4>
                        <p class="mb-0 text-dark">
                            Due to the engineering-driven nature of Flender products, we require detailed technical inputs before recommending or quoting a solution. Hence, Flender enquiries are handled through Assisted Support rather than direct online checkout.
                        </p>
                    </div>
                </div>

                <div class="text-center bg-white border rounded-3 shadow-lg p-5">
                    <h3 class="h4 fw-bold mb-4">Connect with our Flender Experts</h3>
                    <div class="d-flex justify-content-center flex-wrap gap-4 mb-4">
                        <a href="mailto:marcom.sbsyscon@gmail.com" class="btn btn-outline-dark btn-lg rounded-pill px-4">
                            <i class="bi bi-envelope-fill me-2"></i> marcom.sbsyscon@gmail.com
                        </a>
                        <a href="tel:+917506943307" class="btn btn-primary btn-lg rounded-pill px-4">
                            <i class="bi bi-telephone-fill me-2"></i> +91-75069-43307
                        </a>
                    </div>
                    <p class="text-muted small mb-0">Our team will work closely with Flender’s technical experts to study your application and deliver the most optimized, reliable, and future-ready drive solution.</p>
                </div>
            </div>
        </div>
    <?php else: ?>
    <h1 class="h4 mb-4"><?= esc($filter_name) ?> Catalog</h1>
    <p class="text-muted">Showing <?= (int)$total_products ?> available industrial and electrical supplies.</p>

    <div class="row g-4">
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="text-center py-5 px-3">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 100px; height: 100px; background: rgba(13, 110, 253, 0.1);">
                             <i class="bi bi-hourglass-split text-primary fs-1"></i>
                        </div>
                    </div>
                    <h3 class="h4 fw-bold mb-2">Coming Soon</h3>
                    <p class="text-muted mb-4" style="max-width: 450px; margin: 0 auto;">
                         Products for <?= esc($filter_name) ?> are currently being updated. We are working to bring you the best selection.
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                         <a href="products.php" class="btn btn-outline-secondary rounded-pill px-4">Browse All Products</a>
                         <a href="contact-us.php" class="btn btn-primary rounded-pill px-4">Enquire Now</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $p):
                $pid   = (int)($p['id'] ?? 0);
                if ($pid === 0) continue;

                $raw_image_path = resolve_image($p['image'] ?? '');
                $img   = esc($raw_image_path);
                $title = esc($p['title'] ?? 'N/A');
                $short = esc(mb_substr((string)($p['short_description'] ?? ''), 0, 120));
                
                $dName = !empty($p['sku']) ? esc($p['sku']) : $title;
                $dSub = esc($p['subcategory_name'] ?? '');

                $price = isset($p['price']) ? (float)$p['price'] : 0.0;
                $mrp   = isset($p['mrp']) && $p['mrp'] !== null ? (float)$p['mrp'] : null;
                $stock = isset($p['stock']) ? (int)$p['stock'] : 0;
                $is_in_stock = $stock > 0;
                ?>
                <div class="col-6 col-sm-6 col-md-4 col-lg-3">
                    <div class="card h-100 product-card shadow-sm">

                        <div class="position-relative">
                            <a href="product.php?id=<?= $pid ?>" class="d-block skeleton-box">
                                <img src="<?= $img ?>" class="card-img-top" alt="<?= $title ?>" loading="lazy"
                                     style="height:220px; object-fit:contain; background:#fff;"
                                     onerror="this.onerror=null;this.src='<?= esc(resolve_image('')) ?>';">

                            </a>

                            <?php if ($is_in_stock && $mrp !== null && $mrp > $price):
                                $discount_pct = round((($mrp - $price) / $mrp) * 100);
                                ?>
                                <span class="badge bg-success position-absolute" style="top:10px; left:10px;"><?= (int)$discount_pct ?>% OFF</span>
                            <?php elseif (!$is_in_stock): ?>
                                <span class="badge bg-danger position-absolute" style="top:10px; left:10px;">Out of stock</span>
                            <?php endif; ?>
                        </div> 
                        <div class="card-body d-flex flex-column">
                            <h3 class="h6 card-title mb-1" title="<?= $title ?>">
                                <a class="stretched-link" href="product.php?id=<?= $pid ?>">
                                    <?= $dName ?>
                                    <?php if(!empty($dSub)): ?>
                                        <br><span class="text-muted small fw-normal" style="font-size:0.75rem;">(<?= $dSub ?>)</span>
                                    <?php endif; ?>
                                </a>
                            </h3>

                            <div class="mt-auto">
                                <div class="mb-2">
                                    <?php if ($price > 0): ?>
                                        <?php if ($mrp !== null && $mrp > $price): ?>
                                            <span class="product-price"><?= format_price($price) ?></span>
                                            <span class="product-mrp"><?= format_price($mrp) ?></span>
                                        <?php else: ?>
                                            <span class="product-price"><?= format_price($price) ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="product-price text-primary" style="font-size: 1rem;">Price on Request</span>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <?php if ($price > 0): ?>
                                        <?php if ($is_in_stock): ?>
                                            <form method="post" action="cart-add.php" class="d-inline-block w-100">
                                                <?= csrf_input(); ?>
                                                <input type="hidden" name="product_id" value="<?= $pid ?>">
                                                <input type="hidden" name="qty" value="1">
                                                <button type="submit" class="btn btn-view w-100 position-relative z-2">Add to Cart</button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary w-100" disabled>Out of Stock</button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                         <a href="contact-us.php?subject=Quote Request for <?= urlencode($dName) ?>" class="btn btn-outline-primary w-100 position-relative z-2 fw-bold small">Request Quote</a>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Product pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php
                // Build base URL preserving GET params other than page
                $query = $_GET;
                $query['id'] = $filterId;
                $query['type'] = $filterType;
                $base_query = http_build_query(array_diff_key($query, array_flip(['page'])));
                $base_url = 'brand.php' . ($base_query ? ('?' . $base_query) : '');
                ?>

                <!-- Previous Button -->
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= esc($base_url . (strpos($base_url, '?') === false ? '?' : '&') . 'page=' . max(1, $page - 1)) ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);

                if ($start > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . esc($base_url . (strpos($base_url, '?') === false ? '?' : '&') . 'page=1') . '">1</a></li>';
                }
                if ($start > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }

                for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= esc($base_url . (strpos($base_url, '?') === false ? '?' : '&') . 'page=' . $i) ?>"><?= $i ?></a>
                    </li>
                <?php endfor;

                if ($end < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                if ($end < $total_pages) {
                    echo '<li class="page-item"><a class="page-link" href="' . esc($base_url . (strpos($base_url, '?') === false ? '?' : '&') . 'page=' . $total_pages) . '">' . $total_pages . '</a></li>';
                }
                ?>

                <!-- Next Button -->
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= esc($base_url . (strpos($base_url, '?') === false ? '?' : '&') . 'page=' . min($total_pages, $page + 1)) ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
