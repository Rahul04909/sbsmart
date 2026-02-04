<?php
$_GET['id'] = 1;
$_SERVER['REQUEST_URI'] = '/account/product.php?id=1';
$_SERVER['SCRIPT_NAME'] = '/account/product.php';
$_SERVER['SCRIPT_FILENAME'] = 'C:/xampp/htdocs/account/product.php';
$_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';

ob_start();
include 'product.php';
$html = ob_get_clean();

// Find the main product image tag
if (preg_match('/<img[^>]+class="[^"]*main-product-image[^"]*"[^>]+src="([^"]+)"/', $html, $m)) {
    echo "Main Image Src: " . $m[1] . "\n";
} else {
    // Try any product image
    if (preg_match('/<img[^>]+src="([^"]*\/uploads\/products\/[^"]+)"/', $html, $m)) {
        echo "Found Uploads Image Src: " . $m[1] . "\n";
    } else {
        echo "No product image found in HTML.\n";
        // Print first 500 chars of HTML to debug
        // echo substr($html, 0, 500);
    }
}
?>
