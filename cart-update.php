<?php
// cart-update.php
// Handles quantity updates for multiple items submitted from cart.php.

// Session + helpers + DB
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';

// Make sure session is started
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    safe_redirect('cart.php');
}

// 1. SECURITY CHECK
if (!function_exists('csrf_check') || !csrf_check()) {
    flash_set('error', 'Security token mismatch. Please try again.');
    safe_redirect('cart.php');
}

// Ensure input is present and valid
if (!isset($_POST['qty']) || !is_array($_POST['qty'])) {
    flash_set('error', 'Invalid input received for quantities.');
    safe_redirect('cart.php');
}

// Ensure cart exists
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Sanitize input
$updates = [];
foreach ($_POST['qty'] as $k => $v) {
    if (!is_scalar($k)) continue;
    $pid = (int)$k;
    if ($pid <= 0) continue;

    $qty = max(0, (int)$v); // cannot be negative
    $updates[$pid] = $qty;
}

if (empty($updates)) {
    flash_set('info', 'No changes submitted.');
    safe_redirect('cart.php');
}

// DB connection (for logged-in user)
$pdo = null;
try {
    if (function_exists('get_db')) {
        $pdo = get_db();
    }
} catch (Exception $e) {
    error_log("DB Connection failed in cart-update.php: " . $e->getMessage());
}

$cart = &$_SESSION['cart'];
$userId = !empty($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

$removed = 0;
$updated = 0;

foreach ($updates as $pid => $qty) {
    if (!isset($cart[$pid])) {
        // Item not in session cart, skip
        continue;
    }

    // A) Remove item
    if ($qty <= 0) {
        unset($cart[$pid]);

        // Also delete from DB cart if logged in
        if ($pdo && $userId) {
            try {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$userId, $pid]);
            } catch (Exception $e) {
                error_log("Cart delete failed for product $pid: " . $e->getMessage());
            }
        }

        $removed++;
        continue;
    }

    // B) Update session quantity
    if (is_array($cart[$pid])) {
        $cart[$pid]['qty'] = $qty;
    } else {
        $cart[$pid] = $qty;
    }

    // C) Update DB cart if logged in
    if ($pdo && $userId) {
        try {
            // Simple table assumed: id, user_id, product_id, quantity
            $uStmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $uStmt->execute([$qty, $userId, $pid]);

            // If no row updated, maybe row not exist -> insert
            if ($uStmt->rowCount() === 0) {
                $iStmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $iStmt->execute([$userId, $pid, $qty]);
            }
        } catch (Exception $e) {
            error_log("Cart update/insert failed for product $pid: " . $e->getMessage());
        }
    }

    $updated++;
}

// Flash messages
if ($removed > 0) {
    flash_set('success', $removed . ' item(s) removed from cart.');
}
if ($updated > 0) {
    flash_set('success', $updated . ' item(s) updated.');
}

safe_redirect('cart.php');
