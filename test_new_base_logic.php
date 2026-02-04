<?php
function get_base_url_new($scriptName, $scriptFilename, $projectRoot): string {
    $basePath = dirname($scriptName);
    $basePath = str_replace('\\', '/', $basePath);
    
    // Normalize roots to avoid trailing slash issues
    $currentDir = rtrim(str_replace('\\', '/', dirname($scriptFilename)), '/');
    $projectRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');

    if ($projectRoot !== '' && strpos($currentDir, $projectRoot) === 0) {
        $relative = trim(substr($currentDir, strlen($projectRoot)), '/');
        
        if ($relative === '') {
            return ($basePath === '/' ? '/' : rtrim($basePath, '/') . '/');
        } else {
            $depth = count(explode('/', $relative));
            $parts = explode('/', trim($basePath, '/'));
            for ($i = 0; $i < $depth; $i++) {
                array_pop($parts);
            }
            $base = implode('/', $parts);
            return '/' . ($base ? $base . '/' : '');
        }
    }
    return '/';
}

$pRoot = 'C:/xampp/htdocs/account';

echo "1. Root: " . get_base_url_new('/account/index.php', "$pRoot/index.php", $pRoot) . "\n";
echo "2. Admin: " . get_base_url_new('/account/admin/index.php', "$pRoot/admin/index.php", $pRoot) . "\n";
echo "3. Sub: " . get_base_url_new('/account/account/orders.php', "$pRoot/account/orders.php", $pRoot) . "\n";
echo "4. Prod: " . get_base_url_new('/index.php', "/var/www/html/index.php", "/var/www/html") . "\n";
?>
