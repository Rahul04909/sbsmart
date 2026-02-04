<?php
// Mock browser environment
$_SERVER['SCRIPT_NAME'] = '/account/index.php';
require 'includes/helpers.php';

echo "Request: /account/index.php\n";
echo "get_base_url() returns: '" . get_base_url() . "'\n";
echo "resolve_image('test.png') returns: '" . resolve_image('test.png') . "'\n";

$_SERVER['SCRIPT_NAME'] = '/account/admin/index.php';
echo "\nRequest: /account/admin/index.php\n";
echo "get_base_url() returns: '" . get_base_url() . "'\n";
echo "resolve_image('test.png') returns: '" . resolve_image('test.png') . "'\n";
?>
