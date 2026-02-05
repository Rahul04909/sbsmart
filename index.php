<?php
declare(strict_types=1);

/**
 * index.php - Modern, Premium Home Page
 */

require_once __DIR__ . '/includes/helpers.php';
// Database connection logic
if (file_exists(__DIR__ . '/includes/db.php')) {
    require_once __DIR__ . '/includes/db.php';
}

function resolve_pdo(): ?PDO {
    if (function_exists('db')) return db();
    if (function_exists('get_db')) return get_db();
    return null;
}

// Ensure helpers
if (!function_exists('format_price')) {
    function format_price($amount) { return '₹' . number_format((float)$amount, 2); }
}

// Helper for resolving image paths
if (!function_exists('resolve_image')) {
    function resolve_image(?string $img): string {
        if (empty($img)) return 'noimage.webp';
        
        // If external or absolute
        if (strpos($img, 'http') === 0) return $img;
        if (strpos($img, '/') === 0) return $img;
        
        // Check relative path
        $path = 'uploads/products/' . basename($img);
        if (file_exists(__DIR__ . '/' . $path)) {
            return $path;
        }
        return 'noimage.webp';
    }
}

// 1. Fetch Slider Images
function get_slider_images(string $dir = __DIR__ . '/assets/images/slider'): array {
    $imgs = [];
    $banner5 = null;
    
    if (is_dir($dir)) {
        foreach (glob($dir . '/*.{jpg,jpeg,png,webp,avif}', GLOB_BRACE) as $file) {
            $basename = basename($file);
            // Check if this is banner5.png
            if ($basename === 'banner5.png') {
                $banner5 = 'assets/images/slider/' . $basename;
            } else {
                $imgs[] = 'assets/images/slider/' . $basename;
            }
        }
    }
    
    // Put banner5 first if found
    if ($banner5) {
        array_unshift($imgs, $banner5);
    }
    
    return $imgs;
}
$slider_images = get_slider_images();

// 2. Fetch Featured Products
$products = [];
try {
    $pdo = resolve_pdo();
    if ($pdo) {
        // Fetch 4 random featured products
        $stmt = $pdo->query("SELECT p.id, p.sku, p.title, p.slug, p.price, p.mrp, p.image, s.name as subcategory_name FROM products p LEFT JOIN subcategories s ON p.subcategory_id = s.id ORDER BY RAND() LIMIT 4");
        if ($stmt) {
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $products[] = [
                    'id' => (int)$r['id'],
                    'sku' => (string)($r['sku'] ?? ''),
                    'name' => (string)($r['title'] ?? 'Product'),
                    'price' => (float)($r['price'] ?? 0),
                    'mrp' => (float)($r['mrp'] ?? 0),
                    'image' => (string)($r['image'] ?? ''),
                    'subcategory_name' => (string)($r['subcategory_name'] ?? '')
                ];
            }
        }

        // Fallback: If random fetch failed or returned nothing, get latest products
        if (empty($products)) {
             $stmt = $pdo->query("SELECT p.id, p.sku, p.title, p.slug, p.price, p.mrp, p.image, s.name as subcategory_name FROM products p LEFT JOIN subcategories s ON p.subcategory_id = s.id ORDER BY p.id DESC LIMIT 4");
             if ($stmt) {
                 while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $products[] = [
                        'id' => (int)$r['id'],
                        'sku' => (string)($r['sku'] ?? ''),
                        'name' => (string)($r['title'] ?? 'Product'),
                        'price' => (float)($r['price'] ?? 0),
                        'mrp' => (float)($r['mrp'] ?? 0),
                        'image' => (string)($r['image'] ?? ''),
                        'subcategory_name' => (string)($r['subcategory_name'] ?? '')
                    ];
                 }
             }
        }
    }
} catch (Exception $e) {}

// 2.5 Fetch Best Seller Products
$best_sellers = [];
try {
    if ($pdo) {
        $stmt = $pdo->query("SELECT p.id, p.sku, p.title, p.slug, p.price, p.mrp, p.image, s.name as subcategory_name FROM products p LEFT JOIN subcategories s ON p.subcategory_id = s.id WHERE p.status = 1 AND p.is_bestseller = 1 ORDER BY p.created_at DESC LIMIT 8");
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $best_sellers[] = [
                'id' => (int)$r['id'],
                'sku' => (string)($r['sku'] ?? ''),
                'name' => (string)($r['title'] ?? 'Product'),
                'price' => (float)($r['price'] ?? 0),
                'mrp' => (float)($r['mrp'] ?? 0),
                'image' => (string)($r['image'] ?? ''),
                'subcategory_name' => (string)($r['subcategory_name'] ?? '')
            ];
        }
    }
} catch (Exception $e) {}

// 3. Fetch Categories
$categories = [];
try {
    if ($pdo) {
        $stmt = $pdo->query("SELECT name, slug, image FROM categories WHERE status = 1 ORDER BY sort_order ASC LIMIT 12");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {}

// Static Fallback Categories
if (empty($categories)) {
     $categories = [
        ['name'=>'Enclosures', 'slug'=>'enclosures', 'image'=>'assets/images/categories/enclosure.svg'],
        ['name'=>'Switches', 'slug'=>'switches', 'image'=>'assets/images/categories/switch.svg'],
        ['name'=>'Sensors', 'slug'=>'sensors', 'image'=>'assets/images/categories/sensor.svg'],
        ['name'=>'Cables', 'slug'=>'cables', 'image'=>'assets/images/categories/cable.svg'],
    ];
}

$page_title = "SBSmart — Industrial & Electrical Supplies";
require_once __DIR__ . '/includes/header.php';

// 4. Fetch Cart Snapshot
$home_cart_items = [];
try {
    $pdo = resolve_pdo(); // Ensure $pdo is set
    if ($pdo) {
         if (!empty($_SESSION['user']['id'])) {
             // DB Cart logic for logged-in users
             $h_uid = (int)$_SESSION['user']['id'];
             $stmt = $pdo->prepare("
                SELECT p.id, p.sku, p.title as name, p.price, p.mrp, p.image, c.quantity as qty, s.name as subcategory_name 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                LEFT JOIN subcategories s ON p.subcategory_id = s.id
                WHERE c.user_id = ? 
                ORDER BY c.created_at DESC LIMIT 4
             ");
             $stmt->execute([$h_uid]);
             $home_cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
         } elseif (!empty($_SESSION['cart'])) {
             // Session Cart logic for guests
             $h_ids = [];
             foreach ($_SESSION['cart'] as $pid => $item) {
                  $qty = is_array($item) ? ($item['qty'] ?? 0) : $item;
                  if ($qty > 0) $h_ids[] = (int)$pid;
             }
             if (!empty($h_ids)) {
                 // Fetch product details for items in session cart
                 $h_ids = array_slice($h_ids, 0, 4); 
                 $in = str_repeat('?,', count($h_ids) - 1) . '?';
                 $stmt = $pdo->prepare("SELECT p.id, p.sku, p.title as name, p.price, p.mrp, p.image, s.name as subcategory_name FROM products p LEFT JOIN subcategories s ON p.subcategory_id = s.id WHERE p.id IN ($in)");
                 $stmt->execute($h_ids);
                 $h_prods = $stmt->fetchAll(PDO::FETCH_ASSOC);
                 
                 // Merge quantity from session
                 foreach ($h_prods as $p) {
                     $pid = $p['id'];
                     $qty = 0;
                     if (isset($_SESSION['cart'][$pid])) {
                         $val = $_SESSION['cart'][$pid];
                         $qty = is_array($val) ? ($val['qty'] ?? 0) : $val;
                     }
                     $p['qty'] = $qty;
                     $home_cart_items[] = $p;
                 }
             }
         }
    }
} catch (Exception $e) {
    // Silent fail
}
?>

<!-- Custom CSS for Homepage Polish -->
<style>
    :root {
        --primary-color: #0d6efd; /* Bootstrap default, adjust if you have custom theme */
        --secondary-color: #6c757d;
        --hover-shadow: 0 10px 20px rgba(0,0,0,0.08);
        --transition-speed: 0.3s;
    }
    
    body { background-color: #f8f9fa; }

    /* Gateway Cards */
    .gateway-link-hover { transition: color 0.2s; }
    .gateway-link-hover:hover { color: #c7511f !important; text-decoration: underline !important; }
    
    /* Gateway Inner Item Box (Square) */
    .gateway-card .col-6 img {
        aspect-ratio: 1 / 1;
        width: 100%;
        object-fit: contain;
        background-color: #f8f9fa; /* Light background to define the box */
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 5px;
    }

    /* Hero Section */
    .hero-section {
        background: radial-gradient(circle at center, #ffffff 0%, #f1f3f5 100%);
    }

    /* Category Cards */
    .cat-card {
        transition: transform var(--transition-speed), box-shadow var(--transition-speed);
        background: #fff;
        border: 1px solid #e9ecef;
    }
    .cat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
        border-color: var(--primary-color);
    }
    .cat-img-wrapper {
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cat-img-wrapper img {
        max-height: 60px;
        width: auto;
        transition: transform var(--transition-speed);
    }
    .cat-card:hover .cat-img-wrapper img {
        transform: scale(1.1);
    }

    /* Product Cards */
    .product-card {
        border: none;
        transition: all var(--transition-speed);
        background: #fff;
        overflow: hidden;
    }
    .product-card:hover {
        box-shadow: var(--hover-shadow);
        transform: translateY(-5px);
    }
    .product-card .card-img-top {
        height: 220px;
        object-fit: contain;
        padding: 20px;
        transition: transform 0.5s ease;
    }
    .product-card:hover .card-img-top {
        transform: scale(1.05);
    }
    .product-price { font-size: 1.1rem; font-weight: 700; color: #212529; }
    .product-mrp { font-size: 0.9rem; text-decoration: line-through; color: #999; }
    
    /* Feature Icons */
    .feature-box {
        background: #fff;
        padding: 2rem;
        border-radius: 12px;
        height: 100%;
        transition: transform 0.3s;
        border: 1px solid #eee;
    }
    .feature-box:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .feature-icon {
        width: 60px; height: 60px;
        background: rgba(13, 110, 253, 0.1);
        color: var(--primary-color);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    /* Trust Section */
    .trust-section { background: #fff; border-top: 1px solid #eee; }

    /* Search Bar Hero */
    .search-hero-input {
        border-radius: 50px 0 0 50px;
        border: 2px solid transparent;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        height: 55px;
        padding-left: 1.5rem;
    }
    .search-hero-btn {
        border-radius: 0 50px 50px 0;
        font-weight: 600;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
    .search-hero-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.15);
    }
    
    /* FAQ Section Styles */
    .faq-section { background-color: #f8f9fa; }
    .accordion-item { border: none; margin-bottom: 1rem; border-radius: 8px !important; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
    .accordion-button { font-weight: 600; padding: 1.2rem; background: #fff; }
    .accordion-button:not(.collapsed) { background-color: #e7f1ff; color: var(--primary-color); box-shadow: none; }
    .accordion-button:focus { box-shadow: none; border-color: rgba(0,0,0,.125); }
    .accordion-body { background: #fff; color: #555; line-height: 1.6; }

    /* Responsive Banner with Zoom Effect */
    .carousel-item {
        overflow: hidden;
    }
    .banner-img {
        width: 100%;
        height: auto;
        object-fit: cover;
        transform-origin: center center;
    }
    .carousel-item.active .banner-img {
        animation: zoomEffect 8s linear infinite alternate;
    }
    @keyframes zoomEffect {
        0% { transform: scale(1); }
        100% { transform: scale(1.15); }
    }

    @media (min-width: 768px) {
        .banner-img {
            width: 100%;
            height: auto; 
        }
    }
    
    /* Responsive Gateway Cards */
    .gateway-card {
        background: #fff;
        padding: 1rem;
        border: 1px solid #dee2e6;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    @media (min-width: 1200px) {
        .gateway-card { min-height: 350px; }
    }
    
    /* Mobile Typography & Spacing Fixes */
    @media (max-width: 767.98px) {
        .display-4 { font-size: 2.5rem; }
        .feature-box { margin-bottom: 1rem; }
    }
    
    .aspect-square {
        aspect-ratio: 1 / 1;
        object-fit: cover;
    }
    .cat-card-square {
        aspect-ratio: 1/1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .cat-card-square:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-color: var(--primary-color);
    }
    .cat-card-square img {
        max-width: 60%;
        max-height: 60%;
        object-fit: contain;
        margin-bottom: 1rem;
    }

    /* GLOBAL PRESENCE / EXPORTER SECTION */
    .exporter-section {
        background-color: #1aaeb8; /* Teal color from reference */
        background-image: linear-gradient(135deg, #1aaeb8 0%, #15939c 100%);
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .exporter-bg-text {
        position: absolute;
        bottom: -20px;
        left: 0;
        width: 100%;
        font-size: 8rem;
        font-weight: 900;
        color: rgba(255, 255, 255, 0.05);
        text-transform: uppercase;
        line-height: 1;
        pointer-events: none;
        white-space: nowrap;
        text-align: center;
        z-index: 0;
    }
    .exporter-content { position: relative; z-index: 1; }
    .exporter-map-col {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    /* Premium CSS Stamp */
    .stamp-box {
        width: 140px;
        height: 140px;
        border: 3px solid #b71c1c; /* Darker red */
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #b71c1c;
        font-weight: 900;
        text-transform: uppercase;
        font-size: 0.9rem;
        transform: rotate(-10deg); /* Slight rotation */
        background: #fffafa; /* Very light red/white tint */
        box-shadow: 0 0 0 4px #fff, 0 0 0 6px #b71c1c, 0 10px 20px rgba(0,0,0,0.2); /* Double border effect + shadow */
        text-align: center;
        line-height: 1.2;
        z-index: 2;
        letter-spacing: 1px;
    }
    .stamp-box::before {
        content: '★ ★ ★';
        font-size: 10px;
        color: #d32f2f;
        margin-bottom: 2px;
        display: block;
    }
    .stamp-box span {
        font-size: 1.1em;
        display: block;
    }
    
    /* Logo Placeholders / Badge */
    .partner-logo-box {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255,255,255,0.4);
        border-radius: 4px; /* More rectangular like image */
        padding: 8px 16px;
        display: inline-block;
        font-weight: 700;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    
    /* Map Dot Pulse Animation */
    .map-dot {
        transform-origin: center;
        animation: pulseDot 2s infinite;
    }
    @keyframes pulseDot {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.5); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }

    /* Animated Background Blobs */
    .blob-cont {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        z-index: -1;
        overflow: hidden;
        pointer-events: none;
    }
    .blob {
        position: absolute;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.5;
        animation: blob-float 10s infinite ease-in-out alternate;
    }
    .blob-1 {
        top: -10%;
        left: -10%;
        width: 50vw;
        height: 50vw;
        max-width: 600px;
        max-height: 600px;
        background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);
        animation-delay: 0s;
    }
    .blob-2 {
        bottom: -10%;
        right: -10%;
        width: 40vw;
        height: 40vw;
        max-width: 500px;
        max-height: 500px;
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        animation-delay: -5s;
    }
    .blob-3 {
        top: 40%;
        left: 40%;
        width: 30vw;
        height: 30vw;
        max-width: 400px;
        max-height: 400px;
        background: rgba(13, 110, 253, 0.2);
        animation-duration: 15s;
    }
    @keyframes blob-float {
        0% { transform: translate(0, 0) scale(1); }
        100% { transform: translate(30px, 40px) scale(1.1); }
    }
    /* Stats Section - Minimalist Redesign */
    .stats-section {
        background: #fff;
        color: #212529;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
    }
    .stat-item {
        position: relative;
        padding: 1rem;
        /* Removed card styling for cleaner look */
        background: transparent;
        border: none;
        transition: transform 0.3s ease;
    }
    .stat-item:hover {
        transform: translateY(-5px);
    }
    .stat-number {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 0.5rem;
        letter-spacing: -2px;
        color: var(--primary-color); /* Blue numbers */
    }
    .stat-label {
        font-size: 0.95rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #6c757d; /* Muted gray label */
        line-height: 1.4;
    }
    
    /* Decoration: Vertical divider for desktop */
    @media (min-width: 768px) {
        .stat-col:not(:last-child) .stat-item::after {
            content: '';
            position: absolute;
            right: 0;
            top: 20%;
            height: 60%;
            width: 1px;
            background-color: #eee;
        }
    }

    /* Mobile Optimizations for Stats */
    @media (max-width: 767.98px) {
        .stats-section .display-5 {
            font-size: 2rem;
        }
        .stats-section .lead {
            font-size: 1rem;
        }
        .stat-item {
            padding: 0.5rem;
        }
        .stat-number {
            font-size: 2.5rem;
            margin-bottom: 0.25rem;
        }
        .stat-label {
            font-size: 0.75rem;
        }
    }
</style>
<!-- GSAP CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.4/gsap.min.js"></script>

<!-- Background Blobs -->
<div class="blob-cont">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
</div>

<!-- HERO SECTION SLIDER -->
<?php if (!empty($slider_images)): ?>
<div class="container-fluid p-0 mb-5">
    <div id="heroCarousel" class="carousel slide carousel-fade shadow-sm" data-bs-ride="carousel" data-bs-interval="3000" data-bs-pause="false">
        <div class="carousel-indicators">
            <?php foreach ($slider_images as $i => $_): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i===0?'active':'' ?>"></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($slider_images as $i => $img): ?>
            <div class="carousel-item <?= $i===0?'active':'' ?>">
                <a href="products.php"><img src="<?= htmlspecialchars($img) ?>" class="d-block banner-img" alt="Banner"></a>
                <!-- <div class="carousel-caption d-none d-md-block text-start p-5" style="background: linear-gradient(90deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0) 100%); bottom:0; left:0; width:100%; text-shadow: 1px 1px 3px rgba(0,0,0,0.5);">
                    <h2 class="display-4 fw-bold">Premium Industrial Supplies</h2>
                    <p class="lead">Reliable components for your critical infrastructure.</p>
                    <a href="products.php" class="btn btn-primary btn-lg rounded-pill px-4 mt-2">Shop Now</a>
                </div> -->
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php else: ?>
<!-- NO SLIDER HERO FALLBACK -->
<section class="hero-section py-5 mb-5 text-center border-bottom">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Welcome to SBSmart</h1>
        <p class="lead text-muted mb-4">Your trusted partner for Industrial & Electrical Supplies.</p>
        
        <div class="row justify-content-center mb-4">
            <div class="col-md-8 col-lg-6">
                <form action="search.php" method="get" class="d-flex">
                    <input type="search" name="q" class="form-control search-hero-input fs-5" placeholder="Search for products, brands..." required>
                    <button type="submit" class="btn btn-primary search-hero-btn fs-5 text-uppercase">Search</button>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-2 flex-wrap">
             <span class="badge bg-white text-secondary border rounded-pill px-3 py-2 fw-normal"><i class="bi bi-tag-fill text-primary me-1"></i> Fast Shipping</span>
             <span class="badge bg-white text-secondary border rounded-pill px-3 py-2 fw-normal"><i class="bi bi-shield-fill-check text-success me-1"></i> Genuine Parts</span>
             <span class="badge bg-white text-secondary border rounded-pill px-3 py-2 fw-normal"><i class="bi bi-headset text-info me-1"></i> Expert Support</span>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CART SNAPSHOT -->
<?php if (!empty($home_cart_items)): ?>
<div class="container mb-5">
    <div class="p-4 rounded-3 border bg-white shadow-sm position-relative overflow-hidden" style="border-left: 5px solid var(--primary-color) !important;">
        <div class="d-flex justify-content-between align-items-center mb-3 position-relative z-1">
             <h4 class="h5 fw-bold mb-0 text-dark"><i class="bi bi-bag-check-fill me-2 text-primary"></i>Continue Your Order</h4>
             <a href="cart.php" class="btn btn-sm btn-primary px-4 rounded-pill fw-bold">View Cart <i class="bi bi-arrow-right list-inline-item ms-1"></i></a>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 position-relative z-1">
            <?php foreach ($home_cart_items as $item): 
                $img = resolve_image($item['image']);
            ?>
            <div class="col">
                <a href="product.php?id=<?= $item['id'] ?>" class="text-decoration-none">
                    <div class="d-flex align-items-center gap-3 border p-2 rounded bg-light h-100 hover-shadow-sm transition-all">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="rounded bg-white border" style="width: 50px; height: 50px; object-fit: contain;" onerror="this.onerror=null;this.src='<?= esc(resolve_image('')) ?>'">
                        <div class="flex-grow-1" style="min-width:0;">
                            <h6 class="mb-0 small fw-bold text-truncate text-dark">
                                <?php 
                                    $dName = !empty($item['sku']) ? $item['sku'] : $item['name'];
                                    $dSub = $item['subcategory_name'] ?? '';
                                ?>
                                <?= htmlspecialchars($dName) ?> 
                                <?php if(!empty($dSub)): ?>
                                    <span class="text-muted fw-normal" style="font-size:0.85em;">(<?= htmlspecialchars($dSub) ?>)</span>
                                <?php endif; ?>
                            </h6>
                            <div class="small text-muted"><?= $item['qty'] ?> x <span class="text-primary fw-bold"><?= format_price((float)$item['price']) ?></span></div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <!-- Decorative bg -->
        <div class="position-absolute top-0 end-0 h-100 w-100" style="background: radial-gradient(circle at top right, rgba(13, 110, 253, 0.05), transparent 40%); pointer-events: none;"></div>
    </div>
</div>
<?php endif; ?>

<!-- CATEGORIES GATEWAY SLIDER -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
    .swiper-gateways {
        padding-bottom: 40px; /* Pagination space */
    }
    .swiper-gateways .swiper-slide {
        height: auto;
    }
    .swiper-button-next, .swiper-button-prev {
        color: var(--primary-color);
        background: rgba(255,255,255,0.8);
        width: 40px; height: 40px;
        border-radius: 50%;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .swiper-button-next:after, .swiper-button-prev:after {
        font-size: 18px;
        font-weight: bold;
    }
</style>

<?php
$browse_categories = [
    [
        'title' => 'Siemens',
        'link' => 'products.php?category=siemens',
        'items' => [
            ['img' => 'assets/images/cards/Siemens-3wa-6300A-4p-acb[1].png', 'label' => 'WA 6300A 4P ACB', 'url' => 'products.php?category=siemens&search=relay'],
            ['img' => 'assets/images/cards/3wj-1000A-4P-ACB[1].png', 'label' => '3WJ 1000A 4P ACB', 'url' => 'products.php?category=siemens&search=mccb'],
            ['img' => 'assets/images/cards/3SE3100-1C[1].png', 'label' => '3SE3100 1C', 'url' => 'products.php?category=siemens&search=mccb'],
            ['img' => 'assets/images/cards/3RT075[1].png', 'label' => '3RT075', 'url' => 'products.php?category=siemens&search=iot']
            ]
        ],
    [
        'title' => 'Innomotics',
        'link' => 'products.php?category=innomotics',
        'items' => [
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=Motor', 'label' => 'Motors', 'url' => 'products.php?category=innomotics&search=motor'],
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=Drive', 'label' => 'Drives', 'url' => 'products.php?category=innomotics&search=drive'],
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=Gen', 'label' => 'Generators', 'url' => 'products.php?category=innomotics&search=gen'],
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=Service', 'label' => 'Services', 'url' => 'products.php?category=innomotics&search=service']
        ]
    ],
    [
        'title' => 'Lapp',
        'link' => 'products.php?category=lapp',
        'items' => [
            ['img' => 'assets/images/cards/LappCables[1].png', 'label' => 'OLFLEX', 'url' => 'products.php?category=lapp&search=cable'],
            ['img' => 'assets/images/cards/LappCONNECTORS.png', 'label' => 'Connectors', 'url' => 'products.php?category=lapp&search=connector'],
            ['img' => 'assets/images/cards/LappSKINTOPCableGlands.png', 'label' => 'Glands', 'url' => 'products.php?category=lapp&search=gland'],
            ['img' => 'assets/images/cards/LappCAT6.png', 'label' => 'Data', 'url' => 'products.php?category=lapp&search=data']
        ]
    ],
    [
        'title' => 'Asco Schneider',
        'link' => 'products.php?category=asco',
        'items' => [
            ['img' => 'assets/images/cards/Asco7000series[1].png', 'label' => '7000 Series', 'url' => 'products.php?category=asco&search=7000'],
            ['img' => 'assets/images/cards/AscoTransferpact[1].png', 'label' => 'Transferpact', 'url' => 'products.php?category=asco&search=transfer'],
            ['img' => 'assets/images/cards/Asco230series.png', 'label' => 'Series 230', 'url' => 'products.php?category=asco&search=230'],
            ['img' => 'assets/images/cards/AscoWATSN.png', 'label' => 'Series 300', 'url' => 'products.php?category=asco&search=300']
        ]
    ],
    [
        'title' => 'Secure',
        'link' => 'products.php?category=secure',
        'items' => [
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=Meter', 'label' => 'Smart Meters', 'url' => 'products.php?category=secure&search=meter'],
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=EMS', 'label' => 'EMS', 'url' => 'products.php?category=secure&search=ems'],
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=Control', 'label' => 'Controls', 'url' => 'products.php?category=secure&search=control'],
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=Switch', 'label' => 'Switches', 'url' => 'products.php?category=secure&search=switch']
        ]
    ], 
    [
        'title' => 'BCH',
        'link' => 'products.php?category=bch',
        'items' => [
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=Switch', 'label' => 'Switchgear', 'url' => 'products.php?category=bch&search=switchgear'],
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=Enc', 'label' => 'Enclosures', 'url' => 'products.php?category=bch&search=enclosure'],
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=PDB', 'label' => 'PDB', 'url' => 'products.php?category=bch&search=pdb'],
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=Drive', 'label' => 'Drives', 'url' => 'products.php?category=bch&search=drive']
        ]
    ],
    [
        'title' => 'Connectwell',
        'link' => 'products.php?category=connectwell',
        'items' => [
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=TB', 'label' => 'Terminals', 'url' => 'products.php?category=connectwell&search=terminal'],
            ['img' => 'https://placehold.co/150x150?text=Module', 'label' => 'Modules', 'url' => 'products.php?category=connectwell&search=module'],
            ['img' => 'https://placehold.co/150x150?text=PSU', 'label' => 'Power Supply', 'url' => 'products.php?category=connectwell&search=psu'],
            ['img' => 'https://placehold.co/150x150?text=Marking', 'label' => 'Marking', 'url' => 'products.php?category=connectwell&search=marking']
        ]
    ],
    [
        'title' => 'Other',
        'link' => 'products.php?category=other',
        'items' => [
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=Tool', 'label' => 'Tools', 'url' => 'products.php?category=other&search=tools'],
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=PPE', 'label' => 'Safety', 'url' => 'products.php?category=other&search=safety'],
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=Etc', 'label' => 'Accessories', 'url' => 'products.php?category=other&search=acc'],
            ['img' => 'https://placehold.co/150x150/f0f0f0/333?text=All', 'label' => 'View All', 'url' => 'products.php?category=other']
        ]
    ]
];
?>

<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 fw-bold mb-0">Browse Categories</h2>
        <a href="products.php" class="text-decoration-none fw-bold">View All <i class="bi bi-arrow-right"></i></a>
    </div>

    <!-- Swiper Container -->
    <div class="swiper swiper-gateways">
        <div class="swiper-wrapper">
            <?php foreach ($browse_categories as $cat): ?>
            <div class="swiper-slide">
                <div class="gateway-card rounded-3 h-100">
                    <h2 class="fs-5 fw-bold text-dark mb-3"><?= htmlspecialchars($cat['title']) ?></h2>
                    <div class="row g-3 mb-3">
                        <?php foreach ($cat['items'] as $item): ?>
                        <div class="col-6">
                            <a href="<?= htmlspecialchars($item['url']) ?>" class="d-block text-decoration-none">
                                <!-- Using existing container img styling references -->
                                <img src="<?= htmlspecialchars($item['img']) ?>" class="img-fluid mb-2" alt="<?= htmlspecialchars($item['label']) ?>" style="background:#f8f9fa;">
                                <span class="d-block small text-muted lh-sm text-truncate"><?= htmlspecialchars($item['label']) ?></span>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-auto">
                        <a href="<?= htmlspecialchars($cat['link']) ?>" class="text-decoration-none small fw-bold gateway-link-hover">See all <?= htmlspecialchars($cat['title']) ?></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev d-none d-md-flex"></div>
        <div class="swiper-button-next d-none d-md-flex"></div>
    </div>
</div>

<?php if (!empty($best_sellers)): ?>
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 fw-bold mb-0 text-dark">Best Sellers</h2>
        <a href="products.php?sort=bestseller" class="text-decoration-none fw-bold user-select-none">View All <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 best-sellers-row">
        <?php foreach ($best_sellers as $p): 
            $img = resolve_image($p['image']);
        ?>
        <div class="col">
            <div class="card product-card h-100 rounded-3 shadow-sm border-0" style="background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                <div class="position-absolute top-0 start-0 m-3 z-1">
                     <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-star-fill small me-1"></i>Best Seller</span>
                </div>
                
                <a href="product.php?id=<?= $p['id'] ?>">
                    <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>">
                </a>
                
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title h6">
                        <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark text-truncate-2" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:3.6em;">
                            <?php 
                                $dName = !empty($p['sku']) ? $p['sku'] : $p['name'];
                                $dSub = $p['subcategory_name'] ?? '';
                            ?>
                            <?= htmlspecialchars($dName) ?>
                            <?php if(!empty($dSub)): ?>
                                <br><span class="text-muted small fw-normal" style="font-size:0.75rem;">(<?= htmlspecialchars($dSub) ?>)</span>
                            <?php endif; ?>
                        </a>
                    </h5>
                    
                    <div class="mt-auto pt-3 d-flex justify-content-between align-items-end">
                        <div class="price-block">
                            <?php if ($p['price'] > 0): ?>
                                <?php if ($p['mrp'] > $p['price']): ?>
                                    <small class="product-mrp d-block"><?= format_price($p['mrp']) ?></small>
                                <?php endif; ?>
                                <span class="product-price text-primary"><?= format_price($p['price']) ?></span>
                            <?php else: ?>
                                <span class="product-price text-primary small fw-bold">Price on Request</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($p['price'] > 0): ?>
                        <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            Buy
                        </a>
                        <?php else: ?>
                        <a href="contact-us.php?subject=Quote Request for <?= urlencode($dName) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            Quote
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- FEATURED PRODUCTS -->
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 fw-bold mb-0">Featured Products</h2>
        <a href="products.php" class="text-decoration-none fw-bold user-select-none">See all offers <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 featured-products-row">
        <?php foreach ($products as $p): 
            $img = resolve_image($p['image']);
        ?>
        <div class="col">
            <div class="card product-card h-100 rounded-3 shadow-sm">
                <a href="product.php?id=<?= $p['id'] ?>">
                    <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>">
                </a> 
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title h6">
                        <a href="product.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark text-truncate-2" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:3.6em;">
                            <?php 
                                $dName = !empty($p['sku']) ? $p['sku'] : $p['name'];
                                $dSub = $p['subcategory_name'] ?? '';
                            ?>
                            <?= htmlspecialchars($dName) ?>
                            <?php if(!empty($dSub)): ?>
                                <br><span class="text-muted small fw-normal" style="font-size:0.75rem;">(<?= htmlspecialchars($dSub) ?>)</span>
                            <?php endif; ?>
                        </a>
                    </h5>
                    
                    <div class="mt-auto pt-3 d-flex justify-content-between align-items-end">
                        <div class="price-block">
                            <?php if ($p['price'] > 0): ?>
                                <?php if ($p['mrp'] > $p['price']): ?>
                                    <small class="product-mrp d-block"><?= format_price($p['mrp']) ?></small>
                                <?php endif; ?>
                                <span class="product-price text-primary"><?= format_price($p['price']) ?></span>
                            <?php else: ?>
                                <span class="product-price text-primary small fw-bold">Price on Request</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($p['price'] > 0): ?>
                        <a href="product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="bi bi-cart-plus"></i> Buy
                        </a>
                        <?php else: ?>
                        <a href="contact-us.php?subject=Quote Request for <?= urlencode($dName) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            Quote
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div> 
    <?php if (empty($products)): ?>
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-hourglass-split text-muted fs-1 opacity-50"></i>
            </div>
            <h4 class="h5 fw-bold text-muted">More Products Coming Soon</h4>
            <p class="text-muted small">We are updating our featured collection.</p>
        </div>
    <?php else: ?>
        <div class="text-center mt-5">
            <a href="products.php" class="btn btn-outline-dark btn-lg rounded-pill px-5">Load More Products</a>
        </div>
    <?php endif; ?>
</div>

<!-- NUMBERS AT A GLANCE (STATS) -->
<section class="stats-section py-5 my-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-2">Numbers that Matter</h2>
            <p class="lead text-muted">Empowering Industry Since 1992</p>
        </div>
        <div class="row g-4 justify-content-center text-center">
            <div class="col-6 col-md-3 stat-col">
                <div class="stat-item h-100">
                    <div class="stat-number"><span class="counter" data-target="10000">0</span>+</div>
                    <div class="stat-label">SKUs Available</div>
                </div>
            </div>
            <div class="col-6 col-md-3 stat-col">
                <div class="stat-item h-100">
                    <div class="stat-number"><span class="counter" data-target="7">0</span>+</div>
                    <div class="stat-label">Global Brands</div>
                </div>
            </div>
            <div class="col-6 col-md-3 stat-col">
                <div class="stat-item h-100">
                    <div class="stat-number"><span class="counter" data-target="34">0</span>+</div>
                    <div class="stat-label">Years of Trust</div>
                </div>
            </div>
            <div class="col-6 col-md-3 stat-col">
                <div class="stat-item h-100">
                    <div class="stat-number"><span class="counter" data-target="160000">0</span></div>
                    <div class="stat-label">PIN Codes Covered</div>
                </div>
            </div>
        </div>
    </div>
</section><!-- WHY CHOOSE US -->
<div class="trust-section py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="feature-box text-center">
                    <div class="feature-icon mx-auto"><i class="bi bi-truck"></i></div>
                    <h5 class="fw-bold">Fast Delivery</h5>
                    <p class="text-muted small mb-0">Reliable logistics partner ensuring timely delivery across India.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-box text-center">
                    <div class="feature-icon mx-auto"><i class="bi bi-shield-check"></i></div>
                    <h5 class="fw-bold">Quality Guarantee</h5>
                    <p class="text-muted small mb-0">All products sourced directly from manufacturers or authorized dealers.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-box text-center">
                    <div class="feature-icon mx-auto"><i class="bi bi-box-seam-fill"></i></div>
                    <h5 class="fw-bold">Ex Stock Availability</h5>
                    <p class="text-muted small mb-0">Ready Stock availability to reduce turnaround times.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-box text-center">
                    <div class="feature-icon mx-auto"><i class="bi bi-headset"></i></div>
                    <h5 class="fw-bold">24/7 Support</h5>
                    <p class="text-muted small mb-0">Dedicated support team available to assist with technical queries.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ SECTION -->
<div class="faq-section py-0">
    <div class="container">
        <div class="text-center mb-5 animate-on-scroll">
            <h2 class="h3 fw-bold">Frequently Asked Questions</h2>
            <p class="text-muted">Common questions about our products and services.</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-12 animate-on-scroll">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                What is SB Smart?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                SB Smart is the official e-commerce portal of S.B. Syscon Pvt. Ltd., created to offer a seamless, transparent, and efficient digital buying experience for industrial electrical products.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Do you supply products for bulk or project requirements?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes. SB Smart supports bulk orders and project-based requirements, including planned quantities and phased deliveries aligned with project timelines.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                What payment methods are accepted?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept Credit Cards, Debit Cards, UPI, Net Banking, and other secure online payment options. All transactions on our website are handled securely through CC Avenue Platform - Our Payments Partner.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                Do you provide GST billing?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes. GST-compliant invoices are issued mandatorily for all orders.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                How can I place an order?
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                You can place orders directly through the SB Smart website or contact our sales team for customized or bulk requirements.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSix">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                Does SB Smart have an option to place an offline order?
                            </button>
                        </h2>
                        <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes. SB Smart allows users to place offline orders via the “Offline Order” tab. This option is ideal for bulk, project-based, or customized orders. Users submit their requirements, and the S.B. Syscon sales team coordinates directly to finalize pricing and delivery timelines.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Read More Button -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="faqs.php" class="btn btn-outline-primary rounded-pill px-5 fw-bold">Read More FAQs</a>
            </div>
        </div>
    </div>
</div>






<!-- NEWSLETTER -->
<div class="container my-5">
    <div class="bg-primary text-white p-5 rounded-3 shadow-lg text-center" style="background-image: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
        <h2 class="fw-bold mb-3">Stay Updated</h2>
        <p class="mb-4 text-white-50">Subscribe to our newsletter for exclusive offers, new product announcements, and technical articles.</p>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form class="d-flex gap-2">
                    <input type="email" class="form-control form-control-lg border-0" placeholder="Enter your email address" required>
                    <button class="btn btn-light btn-lg px-4 fw-bold text-primary" type="submit">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", (event) => {
        gsap.registerPlugin(ScrollTrigger);
        

        // Initialize Categories Slider
        if (document.querySelector('.swiper-gateways')) {
            new Swiper('.swiper-gateways', {
                slidesPerView: 1,
                spaceBetween: 16,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                breakpoints: {
                    576: { slidesPerView: 2 },
                    992: { slidesPerView: 4 },
                    1200: { slidesPerView: 5 }
                }
            });
        }
        
        // Animate Hero/Banner

        gsap.from(".carousel, .hero-section", {
            duration: 1, 
            y: 30, 
            opacity: 0, 
            ease: "power3.out", 
            delay: 0.2
        });

        // Gateway Cards Animation (Modified for Swiper if needed, likely handled by swiper itself)
        gsap.from(".swiper-gateways", {
            scrollTrigger: {
                trigger: ".container.mb-5",
                start: "top 80%"
            },
            duration: 1,
            y: 50,
            opacity: 0,
            stagger: 0.15,
            ease: "power2.out"
        });

        // Category Grid (Commented out as the HTML section is hidden)
        /*
        gsap.from(".cat-card", {
            scrollTrigger: {
                trigger: ".cat-card",
                start: "top 85%"
            },
            duration: 1,
            scale: 0.9,
            opacity: 0,
            stagger: 0.05,
            ease: "back.out(1.7)"
        });
        */

        // Best Sellers
        gsap.fromTo(".best-sellers-row .product-card", 
            { y: 50, opacity: 0 },
            {
                scrollTrigger: {
                    trigger: ".best-sellers-row",
                    start: "top 85%"
                },
                duration: 0.8,
                y: 0,
                opacity: 1,
                stagger: 0.1,
                ease: "power2.out"
            }
        );

        // Products
        // Products - Safer fromTo animation
        gsap.fromTo(".featured-products-row .product-card", 
            { y: 50, opacity: 0 },
            {
                scrollTrigger: {
                    trigger: ".featured-products-row",
                    start: "top 85%"
                },
                duration: 0.8,
                y: 0,
                opacity: 1,
                stagger: 0.1,
                ease: "power2.out"
            }
        );

        // Features/Trust Section
        gsap.from(".feature-box", {
            scrollTrigger: {
                trigger: ".trust-section",
                start: "top 80%"
            },
            duration: 1,
            y: 30,
            opacity: 0,
            stagger: 0.2,
            ease: "power2.out"
        });

        // FAQ Section
        gsap.from(".faq-section .animate-on-scroll", {
            scrollTrigger: {
                trigger: ".faq-section",
                start: "top 75%"
            },
            duration: 1,
            y: 30,
            opacity: 0,
            stagger: 0.2,
            ease: "power2.out"
        });

        // Global Presence / Exporter Section Animations
        // 1. Parallax for Background Text
        gsap.fromTo(".exporter-bg-text", 
            { x: -400 }, 
            {
                scrollTrigger: {
                    trigger: ".exporter-section",
                    start: "top bottom",
                    end: "bottom top",
                    scrub: 1
                },
                x: 400, 
                ease: "none"
            }
        );

        // 2. Content Slide-in
        gsap.from(".exporter-map-col", {
            scrollTrigger: {
                trigger: ".exporter-section",
                start: "top 75%"
            },
            duration: 1,
            x: -50,
            opacity: 0,
            ease: "power3.out"
        });

        gsap.from(".exporter-content .col-md-6.order-md-2", {
            scrollTrigger: {
                trigger: ".exporter-section",
                start: "top 75%"
            },
            duration: 1,
            x: 50,
            opacity: 0,
            delay: 0.2,
            ease: "power3.out"
        });

        // Stats Counters Animation
        gsap.utils.toArray(".counter").forEach(counter => {
            let target = parseInt(counter.getAttribute("data-target"));
            let obj = { val: 0 };
            
            gsap.to(obj, {
                val: target,
                duration: 2.5,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: counter,
                    start: "top 85%",
                    toggleActions: "play none none reverse"
                },
                onUpdate: () => {
                    // Format with commas if > 999
                    // But maybe user wants exact formatting. 
                    // 34 shouldn't have commas. 50000 should be 50,000 potentially? 
                    // The user input was 50000. I'll just show the number. 
                    // Let's add commas for thousands for better readability.
                    counter.innerText = Math.ceil(obj.val).toLocaleString(); 
                }
            });
        });
    });
</script>
<!-- Note: We need ScrollTrigger for scroll-based animations. Adding it now. -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.4/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- Auto Login Popup for Guests -->
<?php if (empty($_SESSION['user']['id'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if we have already shown the popup specifically for this session if you wanted to limit it
    // For now, per user request, "if user comes to index page... popup appears"
    
    setTimeout(function() {
        var authModalEl = document.getElementById('authModal');
        if (authModalEl) {
            var modal = new bootstrap.Modal(authModalEl);
            modal.show();
        }
    }, 1500); // 1.5 seconds delay for better UX
});
</script>
<?php endif; ?>