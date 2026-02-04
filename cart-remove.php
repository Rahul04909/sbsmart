<?php
// cart-remove.php
// Handles removal of a single item from the session cart.

require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php'; // Provides flash_set, safe_redirect, csrf_check

// Ensure cart exists before attempting operations
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/**
 * Removes a product ID from the session cart, regardless of whether the cart is
 * indexed associatively (by ID) or sequentially (as an array of item arrays).
 *
 * @param int $pid Product ID to remove.
 * @return bool True if removal was successful, false otherwise.
 */
function remove_from_cart_by_id(int $pid): bool {
    if ($pid <= 0) return false;
    
    // Remove from DB if logged in
    if (!empty($_SESSION['user']['id'])) {
        require_once __DIR__ . '/includes/db.php';
        $userId = (int)$_SESSION['user']['id'];
        try {
            $pdo = get_db();
            $del = $pdo->prepare("DELETE FROM cart WHERE user_id = :uid AND product_id = :pid");
            $del->execute([':uid' => $userId, ':pid' => $pid]);
        } catch (Exception $e) {
            error_log("DB Cart Remove Error: " . $e->getMessage());
        }
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) return false;

    // Check if cart is associative (keys are product IDs - current standard)
    if (array_values($_SESSION['cart']) !== $_SESSION['cart']) {
        if (isset($_SESSION['cart'][$pid])) { 
            unset($_SESSION['cart'][$pid]); 
            return true; 
        }
        return false;
    }

    // Check if cart is sequential (legacy/simple array of items)
    foreach ($_SESSION['cart'] as $idx => $item) {
        $itemId = is_array($item) ? (int)($item['id'] ?? 0) : (int)$item;
        if ($itemId === $pid) {
            // Use array_splice to remove the item and re-index
            array_splice($_SESSION['cart'], $idx, 1);
            return true;
        }
    }
    return false;
}


// --- Handling GET Request (Quick Link Removal) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) { 
        flash_set('error','Invalid product selection.'); 
        safe_redirect('cart.php'); 
    }

    // Security Check 1: Referer validation to prevent external hotlinking/attacks
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer !== '') {
        $refHost = parse_url($referer, PHP_URL_HOST);
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? '');
        if (strtolower($refHost) !== strtolower($host)) { // Case-insensitive host match
            flash_set('error','Invalid request source (referer check failed).'); 
            safe_redirect('cart.php'); 
        }
    } else {
        // Optionally flash a warning if referer is missing, as a missing referer is suspicious
        error_log('cart-remove: Missing HTTP_REFERER on GET request for PID: ' . $id);
    }

    if (remove_from_cart_by_id($id)) flash_set('success','Item removed from cart.');
    else flash_set('info','Item not found in cart.');
    
    safe_redirect('cart.php');
    exit;
}


// --- Handling POST Request (Form Submission Removal) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Security Check 2: CSRF protection (stronger defense)
    if (!csrf_check()) { 
        flash_set('error','Security token mismatch. Please try again.'); 
        safe_redirect('cart.php'); 
    }
    
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id <= 0) { 
        flash_set('error','Invalid product selection.'); 
        safe_redirect('cart.php'); 
    }
    
    if (remove_from_cart_by_id($id)) flash_set('success','Item removed from cart.');
    else flash_set('info','Item not found in cart.');
    
    safe_redirect('cart.php');
    exit;
}

// Fallback: If neither GET nor POST, simply redirect to cart.
safe_redirect('cart.php');
exit;