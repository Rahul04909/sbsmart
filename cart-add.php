<?php
// cart-add.php
// Robust add-to-cart handler — Save as UTF-8 WITHOUT BOM. No output before session_start().


// 1) DEPENDENCY LOADING
// Ensure session config runs first (so session cookie params and name are set before session_start()).
if (file_exists(__DIR__ . '/includes/session.php')) {
    require_once __DIR__ . '/includes/session.php';
}

// Load helpers/db (helpers may call session functions, but session.php above ensures config)
if (file_exists(__DIR__ . '/includes/helpers.php')) {
    require_once __DIR__ . '/includes/helpers.php';
}
if (file_exists(__DIR__ . '/includes/db.php')) {
    require_once __DIR__ . '/includes/db.php';
}

// ---------- FALLBACKS for helper functions (if missing) ----------
if (!function_exists('post_required')) {
    function post_required(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // If accessed via GET (e.g. direct URL), redirect to home
            header('Location: index.php');
            exit;
        }
    }
}

if (!function_exists('csrf_check')) {
    // Very simple fallback (always pass). Prefer your app's implementation.
    function csrf_check(): bool {
        return true;
    }
}

if (!function_exists('flash_set')) {
    function flash_set(string $key, $value): void {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION['_flash'][$key] = $value;
    }
}

if (!function_exists('safe_redirect')) {
    function safe_redirect(string $url): void {
        // basic local redirect sanitizer: allow absolute path or full URL on same host
        if (strpos($url, '/') === 0 || preg_match('#^https?://#i', $url)) {
            header('Location: ' . $url);
        } else {
            header('Location: index.php');
        }
        exit;
    }
}

// Helper to read integer from POST safely
function _post_int(string $name, int $default = 0): int {
    $v = filter_input(INPUT_POST, $name, FILTER_VALIDATE_INT);
    return $v === false || $v === null ? $default : (int)$v;
}

// Utility to unify flash + redirect
function _flash_and_redirect(string $type, string $message, string $redirect = 'cart.php'): void {
    flash_set($type, $message);
    safe_redirect($redirect);
    // safe_redirect exits, but keep exit to be explicit
    exit;
}

// 2) REFERER / REDIRECTION SETUP (determine early so used on any early exit)
$referer = 'cart.php';
if (!empty($_SERVER['HTTP_REFERER'])) {
    $ref = @parse_url($_SERVER['HTTP_REFERER']);
    $host = ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
    if ($ref !== false && isset($ref['host']) && ($ref['host'] === $host) && isset($ref['path'])) {
        $path = $ref['path'];
        $query = isset($ref['query']) ? ('?' . $ref['query']) : '';
        $referer = $path . $query;
    }
}

// 3) ENSURE DB CONNECTION AVAILABLE
$conn = null;
if (!isset($conn) || !($conn instanceof PDO)) {
    if (function_exists('db')) {
        try {
            $conn = db();
        } catch (Throwable $ex) {
            $conn = null;
            error_log('cart-add: db() threw: ' . $ex->getMessage());
        }
    }
}

// 4) SECURITY & REQUEST VALIDATION
post_required();

// Check if user is logged in
if (empty($_SESSION['user']['id'])) {
    // Get parameters to pass to login
    $pendingPid = _post_int('id', 0) ?: _post_int('product_id', 0);
    $pendingQty = _post_int('qty', 1);
    
    if ($pendingPid > 0) {
        flash_set('info', 'Please login to add this item to your cart.');
        $redirectUrl = 'account.php?tab=login&pending_product_id=' . $pendingPid . '&pending_qty=' . $pendingQty;
        header('Location: ' . $redirectUrl);
        exit;
    }
}

if (!csrf_check()) {
    _flash_and_redirect('error', 'Security token mismatch. Please try again.', $referer);
}

if (!($conn instanceof PDO)) {
    error_log('cart-add.php: No database connection available.');
    _flash_and_redirect('error', 'Service temporarily unavailable. Please try again later.', $referer);
}

// 5) INPUT VALIDATION & CART INIT
// Accept both 'id' and 'product_id'
$product_id = _post_int('id', 0) ?: _post_int('product_id', 0);
$product_id = (int)$product_id;
if ($product_id <= 0) {
    _flash_and_redirect('error', 'Invalid product selection.', $referer);
}

$qty = _post_int('qty', 1);
if ($qty < 1) $qty = 1;

// Ensure the cart session container
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 6) FETCH PRODUCT & STOCK CHECK
try {
    $stmt = $conn->prepare("SELECT id, title, price, mrp, image, stock FROM products WHERE id = :id AND status = 1 LIMIT 1");
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        _flash_and_redirect('error', 'Product not found or unavailable.', $referer);
    }

    $pid = (int)$product['id'];

    // Check if price is 0 (Price on Request) - Block add to cart
    $price = isset($product['price']) ? (float)$product['price'] : 0.0;
    if ($price <= 0) {
        _flash_and_redirect('info', 'This product is available on request. Please use the "Request Quote" button to contact us.', $referer);
    }

    $stock = null;
    if (isset($product['stock']) && $product['stock'] !== '') {
        $stock = (int)$product['stock'];
    }

    // existing quantity in cart
    $existingQty = 0;
    if (isset($_SESSION['cart'][$pid])) {
        $item = $_SESSION['cart'][$pid];
        if (is_array($item)) {
            $existingQty = (int)($item['qty'] ?? $item['quantity'] ?? 0);
        } else {
            $existingQty = (int)$item;
        }
    }

    $newQty = $existingQty + $qty;

    // Enforce stock if defined and positive
    if (is_int($stock) && $stock > 0) {
        if ($existingQty >= $stock) {
            // already at or above stock
            _flash_and_redirect('warning', "The product is already in your cart at maximum stock capacity ({$stock}).", $referer);
        }
        if ($newQty > $stock) {
            // add only up to available
            $added = $stock - $existingQty;
            if ($added <= 0) {
                _flash_and_redirect('warning', "Only {$stock} unit(s) are available and already reserved in your cart.", $referer);
            }
            $newQty = $stock;
            flash_set('warning', "Only {$added} unit(s) could be added due to stock limitations.");
        }
    }

    // 7) WRITE CART
    
    // If user is logged in, save to DB
    if (!empty($_SESSION['user']['id'])) {
        $userId = (int)$_SESSION['user']['id'];

        
        try {
            // Check if exists in DB
            $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = :uid AND product_id = :pid LIMIT 1");
            $stmt->execute([':uid' => $userId, ':pid' => $pid]);
            $dbItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dbItem) {
                // Update
                $newDbQty = $dbItem['quantity'] + $qty;
                // Check stock again for DB total? (Already checked against session, which should be in sync, but let's be safe)
                if (is_int($stock) && $stock > 0 && $newDbQty > $stock) {
                     $newDbQty = $stock;
                }
                $upd = $conn->prepare("UPDATE cart SET quantity = :qty, updated_at = NOW() WHERE user_id = :uid AND product_id = :pid");
                $upd->execute([':qty' => $newDbQty, ':uid' => $userId, ':pid' => $pid]);

            } else {
                // Insert
                $ins = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) VALUES (:uid, :pid, :qty, NOW(), NOW())");
                $ins->execute([':uid' => $userId, ':pid' => $pid, ':qty' => $qty]);

            }
        } catch (Exception $e) {

        }
    } else {

    }

    // Update Session (Keep in sync)
    $_SESSION['cart'][$pid] = [
        'id'    => $pid,
        'title' => (string)($product['title'] ?? ''),
        'price' => isset($product['price']) ? (float)$product['price'] : 0.0,
        'mrp'   => isset($product['mrp']) ? (float)$product['mrp'] : 0.0,
        'image' => (string)($product['image'] ?? ''),
        'qty'   => (int)$newQty,
    ];

    $safeTitle = htmlspecialchars((string)($product['title'] ?? 'product'), ENT_QUOTES | ENT_SUBSTITUTE);
    _flash_and_redirect('success', "Added {$qty} × {$safeTitle} to cart.", $referer);

} catch (Throwable $e) {
    error_log('cart-add.php error: ' . $e->getMessage());
    _flash_and_redirect('error', 'An unexpected error occurred. Please try again.', $referer);
}
