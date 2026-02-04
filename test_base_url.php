<?php
function get_base_url_mock($scriptName): string {
    $basePath = dirname($scriptName);
    // Clean up common subdirs if we are inside them
    $basePath = preg_replace('#/(auth|admin|includes|account).*$#', '', $basePath);
    if ($basePath === '' || $basePath === '.') {
        $basePath = '/';
    } else {
        $basePath = rtrim($basePath, '/') . '/';
    }
    return $basePath;
}

echo "Request: /account/index.php -> Base URL: " . get_base_url_mock('/account/index.php') . "\n";
echo "Request: /account/admin/index.php -> Base URL: " . get_base_url_mock('/account/admin/index.php') . "\n";
echo "Request: /index.php -> Base URL: " . get_base_url_mock('/index.php') . "\n";
?>
