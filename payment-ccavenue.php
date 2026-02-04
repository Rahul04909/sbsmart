<?php
// payment-ccavenue.php
// Lightweight forwarding placeholder - posts to ccavenue-request.php
require_once __DIR__ . '/includes/helpers.php';
post_required();
if (!csrf_check()) {
    flash_set('error','Security token mismatch.');
    safe_redirect('/cart.php');
}
require_once __DIR__ . '/ccavenue-request.php';
