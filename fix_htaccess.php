<?php
// fix_htaccess.php — backup current .htaccess and write a minimal safe one
// Upload to public_html and open in browser once. Delete after use.

set_time_limit(30);
$doc = __DIR__; // public_html
$ht = $doc . '/.htaccess';
$now = date('Ymd_His');

if (!is_writable($doc)) {
    echo "Directory not writable: $doc";
    exit;
}

// backup existing .htaccess if exists
if (file_exists($ht)) {
    $bak = $doc . '/.htaccess.bak.' . $now;
    if (!copy($ht, $bak)) {
        echo "Failed to create backup .htaccess.bak. Aborting.";
        exit;
    } else {
        echo "Backup created: " . basename($bak) . "<br>";
    }
} else {
    echo ".htaccess not found — creating new file.<br>";
}

// minimal safe .htaccess content that preserves cPanel handler
$content = <<<'HT'
# php -- BEGIN cPanel-generated handler, do not edit
# Set the “ea-php83” package as the default “PHP” programming language.
<IfModule mime_module>
  AddHandler application/x-httpd-ea-php83___lsphp .php .php8 .phtml
</IfModule>
# php -- END cPanel-generated handler, do not edit

# Minimal security
Options -Indexes
HT;

if (false === file_put_contents($ht, $content)) {
    echo "Failed to write new .htaccess";
    exit;
}
echo "Wrote minimal .htaccess successfully.<br>";

// Quick check to show index.php output head (non-destructive)
if (file_exists($doc . '/index.php')) {
    echo "index.php exists in public_html.<br>";
} else {
    echo "<strong>index.php not found in public_html.</strong><br>";
}

echo "<hr>Now try opening your site home page. If it works, remove fix_htaccess.php.<br>";
echo "To restore original .htaccess, rename the .bak file back: .htaccess.bak.YYYYMMDD_HHMMSS -> .htaccess (via File Manager).<br>";
?>
