<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';

/**
 * ccavCancel.php
 * Handles payment cancellation from CCAvenue
 */

// Get order ID from query string if available
$orderId = (int)($_GET['order_id'] ?? 0);

// Set flash message
flash_set('warning', 'Payment was cancelled. You can try again or choose Cash On Delivery.');

// Redirect back to checkout
if ($orderId > 0) {
    safe_redirect('checkout.php');
} else {
    safe_redirect('cart.php');
}
