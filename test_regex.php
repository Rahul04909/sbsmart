<?php
$paths = ['/account', '/account/admin', '/account/auth', '/', '/shop/admin'];
foreach ($paths as $p) {
    $res = preg_replace('#/(auth|admin|includes|account).*$#', '', $p);
    echo "Path: $p -> Result: '$res'\n";
}
?>
