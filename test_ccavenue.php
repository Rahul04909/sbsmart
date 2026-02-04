<?php
require_once __DIR__ . '/includes/ccavenue-crypto.php';
$wk = '20F8642681BB4F3BA1BD8D6B38F727AE0C'; // or load from config.php
$data = 'order_id=SB1001|amount=10.00';
$enc = ccavenue_encrypt_hex($data, $wk);
echo "Enc (hex): $enc\n";
$dec = ccavenue_decrypt_hex($enc, $wk);
echo "Dec: $dec\n";
