<?php
// includes/header.php
// Save as UTF-8 WITHOUT BOM. This file must not output anything before session start.
// This header expects /includes/session.php to handle session_start() and safe cookie params.
// It also expects /includes/db.php to provide get_db() function returning a PDO instance.

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/helpers.php';

/**
 * Ensure db helper is available.
 * If your site provides get_db(), expose a small db() wrapper for compatibility.
 */
if (!function_exists('db')) {
    if (file_exists(__DIR__ . '/db.php')) {
        require_once __DIR__ . '/db.php'; // defines get_db()
    }
    if (function_exists('get_db') && !function_exists('db')) {
        function db() {
            return get_db();
        }
    }
}

// lightweight per-request cache for nav categories
static $cachedNav = null;
$navCats = [];

try {
    if (function_exists('db')) {
        // obtain PDO safely
        try {
            $pdo = db();
        } catch (Throwable $ex) {
            $pdo = null;
            error_log("header.php: db() threw: " . $ex->getMessage());
        }

        if ($cachedNav === null && $pdo instanceof \PDO) {
            $sql = "SELECT c.id AS cat_id, c.name AS cat_name, s.id AS sub_id, s.name AS sub_name
                    FROM categories c
                    LEFT JOIN subcategories s ON s.category_id = c.id
                    ORDER BY c.name, s.name";
            $stmt = $pdo->query($sql);
            if ($stmt !== false) {
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                $rows = [];
            }

            $tmp = [];
            foreach ($rows as $r) {
                // protect against unexpected missing columns
                $cid = isset($r['cat_id']) ? (int)$r['cat_id'] : 0;
                $cname = isset($r['cat_name']) ? (string)$r['cat_name'] : '';
                $sid = isset($r['sub_id']) ? (int)$r['sub_id'] : 0;
                $sname = isset($r['sub_name']) ? (string)$r['sub_name'] : '';

                if ($cid === 0) continue;
                if (!isset($tmp[$cid])) $tmp[$cid] = ['id' => $cid, 'name' => $cname, 'subs' => []];
                if ($sid !== 0) $tmp[$cid]['subs'][] = ['id' => $sid, 'name' => $sname];
            }

            // Convert associative to sequential preserving order
            $navCats = array_values($tmp);

            // Custom Sort Order requested by User
            $desiredOrder = [
                'Siemens', 
                'Innomotics', 
                'Lapp', 
                'Asco Schneider', 
                'Secure', 
                'Flender', 
                'BCH', 
                'Connectwell',  
                'Others'
            ];
            
            $orderMap = [];
            foreach ($desiredOrder as $k => $v) {
                $orderMap[strtolower($v)] = $k;
            }

            usort($navCats, function($a, $b) use ($orderMap) {
                $defaultRank = 999; 
                
                $nameA = strtolower(trim((string)$a['name']));
                $nameB = strtolower(trim((string)$b['name']));
                
                // Check exact match or partial match if needed, but exact is safer
                // If "Asco Schneider" is in DB as "Asco", we might miss it. 
                // Using simple exact match for now based on user input.
                $rankA = isset($orderMap[$nameA]) ? $orderMap[$nameA] : $defaultRank;
                $rankB = isset($orderMap[$nameB]) ? $orderMap[$nameB] : $defaultRank;
                
                if ($rankA === $rankB) {
                    return strcasecmp($nameA, $nameB); // Fallback to name sort
                }
                return $rankA <=> $rankB;
            });

            $cachedNav = $navCats;
        } elseif ($cachedNav !== null) {
            $navCats = $cachedNav;
        }
    }
} catch (Throwable $e) {
    // fail gracefully: log and keep nav empty
    error_log('header.php: nav load failed: ' . $e->getMessage());
    $navCats = [];
}

/* helper: cart quantity */
/* Accepts several cart shapes:
   - $_SESSION['cart'] = [ product_id => qty, ... ]
   - $_SESSION['cart'] = [ product_id => ['qty'=>n], ... ]
   - $_SESSION['cart'] = [ ['id'=>pid,'qty'=>n], ... ] */
if (!function_exists('cart_qty')) {
    function cart_qty() {
        // If logged in, count from DB
        if (!empty($_SESSION['user']['id'])) {
            try {
                if (function_exists('get_db')) {
                    $pdo = get_db();
                    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
                    $stmt->execute([(int)$_SESSION['user']['id']]);
                    return (int)$stmt->fetchColumn();
                }
            } catch (Exception $e) {
                // Fallback to session
            }
        }

        if (empty($_SESSION['cart'])) return 0;
        $cart = $_SESSION['cart'];

        // shape: sequential array of items with ['id'=>..., 'qty'=>...]
        if (array_values($cart) === $cart) {
            $total = 0;
            foreach ($cart as $it) {
                if (is_array($it)) {
                    $q = isset($it['qty']) ? (int)$it['qty'] : (isset($it['quantity']) ? (int)$it['quantity'] : 0);
                    $total += max(0, $q);
                }
            }
            if ($total > 0) return $total;
        }

        // shape: associative [ pid => qty ] or [ pid => ['qty'=>n] ]
        $total = 0;
        foreach ($cart as $k => $v) {
            if (is_array($v)) {
                $q = isset($v['qty']) ? (int)$v['qty'] : (isset($v['quantity']) ? (int)$v['quantity'] : 0);
                $total += max(0, $q);
            } else {
                $total += max(0, (int)$v);
            }
        }
    }
}

/* helper: fetch mini cart items */
$mini_cart_items = [];
try {
    if (function_exists('get_db')) {
        $pdo = get_db();
        if (!empty($_SESSION['user']['id'])) {
            // DB Cart
            $stmt = $pdo->prepare("
                SELECT p.id, p.title, p.price, p.image, c.quantity 
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ? 
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([(int)$_SESSION['user']['id']]);
            $mini_cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif (!empty($_SESSION['cart'])) {
            // Session Cart
            // IDs
            $s_ids = [];
            foreach ($_SESSION['cart'] as $pid => $val) {
                // $val could be int or array
                $qty = is_array($val) ? ($val['qty'] ?? 0) : $val;
                if ($qty > 0) $s_ids[] = (int)$pid;
            }
            if (!empty($s_ids)) {
                $in = str_repeat('?,', count($s_ids) - 1) . '?';
                $stmt = $pdo->prepare("SELECT id, title, price, image FROM products WHERE id IN ($in)");
                $stmt->execute($s_ids);
                $s_prods = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Merge quantities
                foreach ($s_prods as $p) {
                    $pid = $p['id'];
                    $qty = 0;
                    if (isset($_SESSION['cart'][$pid])) {
                        $val = $_SESSION['cart'][$pid];
                        $qty = is_array($val) ? ($val['qty'] ?? 0) : $val;
                    }
                    $p['quantity'] = $qty;
                    $mini_cart_items[] = $p;
                }
            }
        }
    }
} catch (Exception $e) {
    // minimal failure resilience
}

/* meta defaults */
$page_title = $page_title ?? "SBSmart";
$meta_description = $meta_description ?? "SBSmart - Industrial & Electrical Supplies. Top brands, fast delivery.";

/* canonical */
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
$host = preg_replace('/:\d+$/', '', trim($host));
$host = preg_replace('/[^A-Za-z0-9\.\-]/', '', $host);
$path = '/';
if (!empty($_SERVER['REQUEST_URI'])) {
    $path = strtok($_SERVER['REQUEST_URI'], '?') ?: '/';
}
$canonical = $canonical ?? ($scheme . '://' . $host . $path);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <title><?= htmlspecialchars($page_title, ENT_QUOTES|ENT_SUBSTITUTE) ?></title>
  <meta name="description" content="<?= htmlspecialchars($meta_description, ENT_QUOTES|ENT_SUBSTITUTE) ?>">
  <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES|ENT_SUBSTITUTE) ?>">
  <meta name="robots" content="index,follow">
  <meta name="theme-color" content="#0056d6">
  <link rel="icon" type="image/png" href="assets/images/SBS-Logo[1].png">

  <!-- Google font + Bootstrap + icons -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" crossorigin="anonymous">

  <script>
document.addEventListener('click', function(e){

    const btn = e.target.closest('[data-add-to-cart]');
    if (!btn) return;

    // If user NOT logged in → open login popup
    if (!window.APP?.loggedIn) {
        e.preventDefault();

        const modal = document.getElementById('authModal');
        if (modal) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
        return;
    }

    // otherwise allow normal add-to-cart behaviour
});
</script>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <style>
    :root{
      --accent:#ffb100;
      --menu-bg:#0b63d6;
      --menu-item-bg:#ffffff;
      --muted:#6c757d;
      --logo-h:44px;
      --header-h-mobile:64px;
      --header-h-desktop:76px;
      --menu-item-radius:10px;
    }
    *{box-sizing:border-box;}
    html,body{height:100%;}
    body{font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto, Arial; margin:0; background:#f6f7f9; color:#222; -webkit-font-smoothing:antialiased;}
    .top-strip{background:#fff;border-bottom:1px solid rgba(0,0,0,.04);font-size:13px;color:var(--muted);}
    .top-strip a{color:var(--muted);text-decoration:none;}
    .top-strip a:hover{color:var(--accent);}
    .site-header { position: sticky; top: 0; z-index: 1040; background:#fff; box-shadow: 0 1px 10px rgba(0,0,0,0.05); }
    .header-row{display:flex;align-items:center;gap:.6rem;padding:.4rem 0;min-height:var(--header-h-mobile);}
    @media(min-width:992px){ 
        .site-header { position: relative; box-shadow: none; top: auto; z-index: 1010; }
        .header-row{height:var(--header-h-desktop);} 
    }
    .logo-block img{height:var(--logo-h);display:block;}
    .search-box{flex:1;}
    .search-input{height:44px;border-radius:10px;padding:.45rem .75rem;border:1px solid rgba(0,0,0,.08);}
    .icon-btn{width:46px;height:46px;border-radius:12px;border:1px solid rgba(0,0,0,.06);display:inline-flex;align-items:center;justify-content:center;background:#fff; transition: background 0.2s;}
    .icon-btn:active { transform: scale(0.95); background: #f0f0f0; }
    .badge-cart{background:#ff3b30;color:#fff;padding:.28rem .45rem;border-radius:999px;font-weight:700;font-size:.75rem;}
    .menu-bar { background: var(--menu-bg); color: #fff; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.06); position: sticky; top: 0; z-index: 1020; }
    .menu-inner { display:flex; justify-content:center; align-items:center; gap:14px; flex-wrap:wrap; }
    .menu-item { background: var(--menu-item-bg); color: #111; padding:10px 18px; border-radius: var(--menu-item-radius); box-shadow: 0 8px 18px rgba(2,6,23,0.06); font-weight:700; text-decoration:none; display:inline-block; transition: transform .08s ease, box-shadow .12s ease; white-space:nowrap; font-size:0.95rem; }
    .menu-item:hover, .menu-item:focus { transform: translateY(-3px); box-shadow: 0 14px 34px rgba(2,6,23,0.10); text-decoration:none; }
    .dropdown-mega { width:720px; max-width: calc(100vw - 32px); left:50%; transform:translateX(-50%); padding:14px; border-radius:10px; background:#fff; color:#111; box-shadow: 0 20px 40px rgba(2,6,23,0.08); }
    .dropdown-mega .dropdown-item { color:#222; padding:.35rem .6rem; }
    .dropdown-mega .dropdown-item:hover { background: rgba(0,0,0,.03); }
    @media(max-width:991.98px){ .menu-bar { display:none; } }
    .menu-item:focus { outline: 3px solid rgba(255,181,0,0.18); outline-offset:3px; }
    .dropdown-submenu{position:relative;}
    .dropdown-submenu>.dropdown-menu{position:absolute;top:0;left:100%;margin-left:.08rem;display:none;min-width:12rem;border-radius:.35rem;z-index:1055;}
    .dropdown-submenu.show>.dropdown-menu{display:block;}
    @media (max-width:991.98px){ .dropdown-submenu>.dropdown-menu{position:static;left:auto;top:auto;display:none;} .dropdown-submenu.show>.dropdown-menu{display:block;} }
    
    /* Enhanced Product Card (Premium Minimalist) */
    .product-card {
      border: 1px solid #f0f0f0;
      border-radius: 16px;
      overflow: hidden;
      transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
      background: #fff;
      position: relative;
    }
    .product-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 30px rgba(0,0,0,0.06);
      border-color: transparent;
    }
    .product-card .card-img-top {
      padding: 20px;
      transition: transform 0.5s ease;
      mix-blend-mode: multiply; /* Helps if images have white bg */
    }
    .product-card:hover .card-img-top {
      transform: scale(1.08);
    }
    .product-card .card-body {
      padding: 1.25rem;
      text-align: center; /* Center align for a cleaner look */
    }
    .product-card .card-title {
      margin-bottom: 0.5rem;
      min-height: 2.8em; /* Reserve space for 2 lines */
    }
    .product-card .card-title a {
      color: #1a1a1a;
      font-weight: 600;
      font-size: 0.95rem;
      text-decoration: none;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      transition: color 0.2s;
    }
    .product-card .card-title a:hover {
      color: var(--menu-bg);
    }
    .product-price {
      font-size: 1.1rem;
      font-weight: 700;
      color: #111;
      margin-bottom: 0.5rem;
    }
    .product-mrp {
      font-size: 0.85rem;
      color: #999;
      text-decoration: line-through;
      margin-left: 6px;
      font-weight: 400;
    }
    .btn-view {
      border-radius: 50px;
      padding: 6px 20px;
      font-size: 0.85rem;
      font-weight: 600;
      transition: all 0.2s;
      background: #f8f9fa;
      color: #333;
      border: 1px solid #eee;
    }
    .product-card:hover .btn-view {
      background: var(--menu-bg);
      color: #fff;
      border-color: var(--menu-bg);
    }
    
    /* Skeleton Loader */
    .skeleton-box {
      position: relative;
      background: #eee;
      background: linear-gradient(110deg, #ececec 8%, #f5f5f5 18%, #ececec 33%);
      background-size: 200% 100%;
      animation: 1s shimmer linear infinite;
      border-radius: 4px; /* optional */
    }
    .skeleton-box img {
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }
    .skeleton-box.loaded {
      animation: none;
      background: none;
    }
    .skeleton-box.loaded img {
      opacity: 1;
    }

    @keyframes shimmer {
      to { background-position-x: -200%; }
    }
    
    /* Desktop Hover Menu (exclude .no-hover) */
    @media (min-width: 992px) {
      .dropdown:not(.no-hover):hover > .dropdown-menu {
        display: block;
        margin-top: 12px !important; /* Reduced gap */
        animation: fadeInUp 0.2s ease-out forwards;
      }
      /* Invisible bridge to keep hover active while crossing the gap */
      .dropdown-menu::before {
        content: "";
        position: absolute;
        top: -12px;
        left: 0;
        width: 100%;
        height: 12px;
        background: transparent;
      }
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translate(-50%, 10px); }
      to { opacity: 1; transform: translate(-50%, 0); }
    }
    /* Fix for non-centered dropdowns (like 'More') */
    @media (min-width: 992px) {
      .dropdown:not(.no-hover):hover > .dropdown-menu:not(.dropdown-mega) {
        animation: fadeInUpSmall 0.2s ease-out forwards;
      }
    }
    @keyframes fadeInUpSmall {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Page Loader */
    #page-loader {
      position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: #ffffff;
      z-index: 99999;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: opacity 0.4s ease-out, visibility 0.4s ease-out;
    }
    #page-loader.loader-hidden {
      opacity: 0;
      visibility: hidden;
    }
    .loader-pulse {
      width: 150px; /* Adjust size as needed */
      height: auto;
      animation: loaderPulse 1.5s infinite ease-in-out;
    }
    @keyframes loaderPulse {
      0% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.1); opacity: 0.85; }
      100% { transform: scale(1); opacity: 1; }
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(function() {
        document.querySelectorAll('.skeleton-box').forEach(function(el) {
          el.classList.add('loaded');
        });
      }, 1000);
    });

    window.addEventListener('load', function() {
        const loader = document.getElementById('page-loader');
        if (loader) {
            // Optional: minimal delay to ensure user sees the branding
            setTimeout(() => {
                loader.classList.add('loader-hidden');
            }, 600); 
        }
    });
  </script>
</head>
<body>
  <!-- PAGE LOADER -->
  <div id="page-loader">
    <img src="assets/images/SBS-Logo[1].png" alt="SBSmart" class="loader-pulse">
  </div>

  <!-- top strip -->
  <div class="top-strip">
    <div class="container d-flex justify-content-between align-items-center py-1">
      <div class="small d-flex align-items-center gap-3">
        <span class="d-flex align-items-center"><i class="bi bi-envelope me-1" style="color:var(--accent)"></i><a href="mailto:marcom.sbsyscon@gmail.com">marcom.sbsyscon@gmail.com</a></span>
        <span class="d-flex align-items-center"><i class="bi bi-telephone me-1" style="color:var(--accent)"></i><a href="tel:+911294150555">(+91) 129 4150 555</a></span>
      </div>
      <div class="small d-none d-md-flex">
        <a href="about.php" class="me-3">About</a>
        <a href="contact.php" class="me-3">Contact</a>
        <a href="blog.php" class="me-3">Blog</a>
        <a href="faqs.php" class="me-3">FAQ</a>
        <a href="assisted-orders.php">Assisted Orders</a>
      </div>
    </div>
  </div>

  <!-- header (static) -->
  <header class="site-header" role="banner" aria-label="Top header">
    <div class="container">
      <div class="header-row">
        <div class="d-flex align-items-center">
          <!-- mobile menu toggle -->
          <button class="btn icon-btn d-lg-none border-0 bg-transparent ps-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-label="Open menu">
            <i class="bi bi-list fs-3 text-dark" aria-hidden="true"></i>
          </button>
        </div>

        <div class="logo-block me-auto ms-2">
          <a href="index.php" aria-label="SBSmart Home">
            <img src="assets/images/logo.png" alt="SBSmart" loading="lazy" onerror="this.onerror=null;this.src='assets/images/logo-text.png'">
          </a>
        </div>

        <!-- Desktop Search (Hidden on Mobile) -->
        <div class="search-box mx-3 d-none d-md-block" role="search" aria-label="Search products">
          <form id="siteSearchForm" action="<?= get_base_url() ?>search.php" method="get" class="d-flex w-100" novalidate>
            <input id="siteSearchInput" type="search" name="q" class="form-control search-input bg-light border-0" placeholder="Search products, brands or model numbers..." aria-label="Search products">
            <button class="btn btn-primary ms-2 px-3 rounded-3" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
          </form>
        </div>

        <div class="d-flex align-items-center gap-2 ms-0 ms-md-2">
          <?php $cq = cart_qty(); ?>
          <div class="dropdown d-inline-block position-relative no-hover">
            <?php $cq = cart_qty(); ?>
            <a href="cart.php" class="position-relative text-decoration-none" id="cartDropdownLink" aria-label="Cart">
              <button class="btn icon-btn border-0 bg-transparent" type="button"><i class="bi bi-bag fs-4 text-dark" aria-hidden="true"></i></button>
              <?php if ($cq > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light" style="font-size: 0.7rem; padding: 0.35em 0.5em;">
                  <?= (int)$cq ?>
                </span>
              <?php endif; ?>
            </a>
            
            <!-- Mini Cart Dropdown (Hover/Click) -->
             <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0 rounded-4 mt-2" aria-labelledby="cartDropdownLink" style="width:340px; overflow:hidden;">
                <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">My Cart (<?= $cq ?>)</h6>
                    <a href="cart.php" class="text-decoration-none small fw-bold">View Cart</a>
                </div>
                
                <div class="mini-cart-body" style="max-height: 350px; overflow-y: auto;">
                    <?php if (empty($mini_cart_items)): ?>
                        <div class="p-4 text-center">
                            <i class="bi bi-cart-x text-muted fs-1 mb-2"></i>
                            <p class="text-muted small mb-0">Your cart is empty</p>
                            <a href="products.php" class="btn btn-sm btn-outline-primary mt-3 rounded-pill">Start Shopping</a>
                        </div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($mini_cart_items as $mc_item): 
                                $mc_img = !empty($mc_item['image']) ? $mc_item['image'] : 'noimage.webp';
                                // Simple resolve
                                if (strpos($mc_img, 'http') !== 0 && strpos($mc_img, '/') !== 0) {
                                    $mc_img = 'uploads/products/' . basename($mc_img);
                                }
                                $mc_price = isset($mc_item['price']) ? (float)$mc_item['price'] : 0.0;
                            ?>
                            <li class="list-group-item d-flex gap-3 align-items-center p-3">
                                <img src="<?= htmlspecialchars($mc_img) ?>" alt="Product" class="rounded border" style="width:60px; height:60px; object-fit:contain;">
                                <div class="flex-grow-1" style="min-width:0;">
                                    <h6 class="mb-0 text-truncate small fw-bold text-dark w-100"><?= htmlspecialchars($mc_item['title']) ?></h6>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="text-muted small"><?= (int)$mc_item['quantity'] ?> x</span>
                                        <?php if ($mc_price > 0): ?>
                                            <span class="text-primary fw-bold small">₹<?= number_format($mc_price, 2) ?></span>
                                        <?php else: ?>
                                            <span class="text-primary fw-bold small">Price on Request</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($mini_cart_items)): ?>
                <div class="p-3 border-top bg-white">
                    <?php 
                        $mc_total = 0;
                        foreach($mini_cart_items as $mi) $mc_total += ($mi['price'] * $mi['quantity']);
                    ?>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Subtotal:</span>
                        <span class="fw-bold text-dark">₹<?= number_format($mc_total, 2) ?></span>
                    </div>
                    <a href="cart-checkout.php" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Checkout</a>
                </div>
                <?php endif; ?>
             </div>
          </div>

          <?php if (!empty($_SESSION['user']['id'])): ?>
            <a href="account-profile.php" class="d-md-inline" aria-label="Account">
              <button class="btn icon-btn border-0 bg-transparent" type="button"><i class="bi bi-person-circle fs-4 text-dark" aria-hidden="true"></i></button>
            </a>
          <?php else: ?>
            <a href="#" class="d-md-inline" aria-label="Login" data-bs-toggle="modal" data-bs-target="#authModal">
              <button class="btn icon-btn border-0 bg-transparent" type="button"><i class="bi bi-person fs-4 text-dark" aria-hidden="true"></i></button>
            </a>
          <?php endif; ?>
        </div>
      </div>
      
      <!-- Mobile Search Row (Visible only on Mobile) -->
      <div class="d-md-none pb-3">
          <form id="mobileHeaderSearchForm" action="search.php" method="get" class="d-flex w-100" novalidate>
            <div class="input-group">
                <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                <input type="search" name="q" class="form-control bg-light border-0" placeholder="Search for products..." aria-label="Search products">
            </div>
          </form>
      </div>
    </div>
  </header>

  <!-- MENU BAR (sticky) -->
  <div class="menu-bar" role="navigation" aria-label="Primary categories">
    <div class="container">
      <div class="menu-inner" id="mainMenuInner">
        <?php
          $maxVisible = 9; 
          $count = 0; 
          $extra = []; 
          foreach ($navCats as $cat) {
              if ($count < $maxVisible) {
                  $hasSubs = !empty($cat['subs']);
                  $id = (int)$cat['id'];
                  $name = htmlspecialchars($cat['name'], ENT_QUOTES|ENT_SUBSTITUTE);
                  
                  if ($hasSubs) {
                      $toggleId = 'catToggle' . $id;
                      ?>
                      <div class="dropdown">
                        <a class="menu-item dropdown-toggle" href="category.php?id=<?= $id ?>&type=cat" id="<?= $toggleId ?>" aria-expanded="false">
                          <?= $name ?>
                        </a>
                        <ul class="dropdown-menu shadow border-0" aria-labelledby="<?= $toggleId ?>">
                          <?php 
                          // Simple list for subcategories
                          foreach ($cat['subs'] as $sub): ?>
                            <li>
                              <a class="dropdown-item" href="category.php?id=<?= (int)$sub['id'] ?>&type=sub">
                                <?= htmlspecialchars($sub['name'], ENT_QUOTES|ENT_SUBSTITUTE) ?>
                              </a>
                            </li>
                          <?php endforeach; ?>
                          <li><hr class="dropdown-divider"></li>
                          <li>
                            <a class="dropdown-item fw-bold text-primary" href="category.php?id=<?= $id ?>&type=cat">
                              View all <?= $name ?>
                            </a>
                          </li>
                        </ul>
                      </div>
                      <?php
                  } else {
                      echo '<a class="menu-item" href="category.php?id=' . $id . '&type=cat">' . $name . '</a>';
                  }
              } else {
                  $extra[] = $cat;
              }
              $count++;
          }

          // "More" Dropdown
          if (!empty($extra)) {
              ?>
              <div class="dropdown">
                <a class="menu-item dropdown-toggle" href="#" id="moreMenu" aria-expanded="false">More</a>
                <ul class="dropdown-menu shadow border-0" aria-labelledby="moreMenu">
                  <?php foreach ($extra as $cat): ?>
                    <li>
                      <a class="dropdown-item" href="category.php?id=<?= (int)$cat['id'] ?>&type=cat">
                        <?= htmlspecialchars($cat['name'], ENT_QUOTES|ENT_SUBSTITUTE) ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <?php
          }
        ?>
      </div>
    </div>
  </div>

  <!-- Mobile offcanvas menu -->
  <style>
  /* Improve mobile offcanvas menu appearance and touch targets */
  #mobileMenu .offcanvas-body { padding: 0.75rem 0.75rem 1.25rem; }
  #mobileMenu .input-group .form-control { height: calc(2.25rem + 8px); }
  #mobileMenu .list-group-item { padding: 0; }
  #mobileMenu .list-group-item > a { padding: .9rem 1rem; display: flex; align-items: center; justify-content: space-between; }
  #mobileMenu .list-group-item .collapse { padding-left: 0; }
  #mobileMenu .collapse a { padding: .6rem 1.25rem; display:block; }
  #mobileMenu .bi-chevron-down { transition: transform .18s ease; }
  #mobileMenu a[aria-expanded="true"] .bi-chevron-down { transform: rotate(180deg); }
  @media (max-width: 576px) {
    /* Make the offcanvas nearly full-screen on small devices for better usability */
    #mobileMenu.offcanvas-start { width: 100% !important; }
  }
  </style>
  <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header bg-primary text-white">
      <h5 id="mobileMenuLabel" class="fw-bold"><i class="bi bi-grid-fill me-2"></i> Menu</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <!-- mobile search: removed required and added ids -->
      <form id="mobileSearchForm" action="<?= get_base_url() ?>search.php" method="get" class="mb-3" role="search" novalidate>
        <div class="input-group">
          <input id="mobileSearchInput" type="search" name="q" class="form-control" placeholder="Search products...">
          <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
        </div>
      </form>

      <div class="list-group list-group-flush mb-3">
        <a href="index.php" class="list-group-item list-group-item-action border-0 fw-bold"><i class="bi bi-house-door me-2 text-primary"></i> Home</a>
        <?php foreach ($navCats as $cat): ?>
          <div class="list-group-item border-0 p-0">
            <a class="d-flex justify-content-between align-items-center text-decoration-none list-group-item-action p-3 border-bottom-0" data-bs-toggle="collapse" href="#catCollapse<?= (int)$cat['id'] ?>" role="button" aria-expanded="false" aria-controls="catCollapse<?= (int)$cat['id'] ?>">
              <span class="fw-semibold"><?= htmlspecialchars($cat['name'], ENT_QUOTES|ENT_SUBSTITUTE) ?></span>
              <i class="bi bi-chevron-down small text-muted"></i>
            </a>
            <?php if (!empty($cat['subs'])): ?>
              <div class="collapse bg-light" id="catCollapse<?= (int)$cat['id'] ?>">
                <?php foreach ($cat['subs'] as $sub): ?>
                  <a class="d-block py-2 ps-4 text-decoration-none text-secondary border-bottom border-light" href="category.php?id=<?= (int)$sub['id'] ?>&type=sub">
                      <i class="bi bi-caret-right-fill small me-1 opacity-50"></i> <?= htmlspecialchars($sub['name'], ENT_QUOTES|ENT_SUBSTITUTE) ?>
                  </a>
                <?php endforeach; ?>
                <a class="d-block py-2 ps-4 text-decoration-none fw-bold text-primary" href="category.php?id=<?= (int)$cat['id'] ?>&type=cat">View all <?= htmlspecialchars($cat['name'], ENT_QUOTES|ENT_SUBSTITUTE) ?></a>
              </div>
            <?php else: ?>
              <div class="mt-0"><a class="d-block py-2 ps-4 text-decoration-none" href="category.php?id=<?= (int)$cat['id'] ?>&type=cat">View <?= htmlspecialchars($cat['name'], ENT_QUOTES|ENT_SUBSTITUTE) ?></a></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <hr>
      <div class="small">
        <?php if (!empty($_SESSION['user']['id'])): ?>
          <a class="d-block mb-1 fw-bold text-primary" href="account-profile.php">My Account</a>
          <a class="d-block mb-1 text-danger" href="auth/logout.php">Logout</a>
          <hr>
        <?php else: ?>
          <a class="d-block mb-1 fw-bold text-primary" href="#" data-bs-toggle="modal" data-bs-target="#authModal" onclick="const m=bootstrap.Offcanvas.getInstance('#mobileMenu');if(m)m.hide();">Login / Sign Up</a>
          <hr>
        <?php endif; ?>
        <a class="d-block mb-1" href="about.php">About</a>
        <a class="d-block mb-1" href="contact.php">Contact</a>
        <a class="d-block mb-1" href="blog.php">Blog</a>
        <a class="d-block mb-1" href="faqs.php">FAQ</a>
        <a class="d-block mb-1" href="assisted-orders.php">Assisted Orders</a>
      </div>
    </div>
  </div>

  <!-- Bootstrap bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

  <script src="assets/js/dropdown-submenu.js"></script>

  <!--
    Small script: validate header/mobile search only when those forms are submitted.
    This prevents the header search input from blocking unrelated form submissions (checkout, etc.)
  -->
  <script>
  (function(){
    function bindSearch(formId, inputId){
      var form = document.getElementById(formId);
      var input = document.getElementById(inputId);
      if (!form || !input) return;
      form.addEventListener('submit', function(e){
        var v = (input.value || '').trim();
        if (v.length === 0) {
          e.preventDefault();
          input.focus();
          input.style.outline = '3px solid rgba(255,181,0,0.25)';
          setTimeout(function(){ input.style.outline = ''; }, 1400);
        }
      }, {passive:false});
    }

    // main header search + mobile offcanvas search
    bindSearch('siteSearchForm','siteSearchInput');
    bindSearch('mobileSearchForm','mobileSearchInput');
  })();
  </script>
