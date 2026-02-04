<?php
$file = __DIR__ . '/.env';
if (!file_exists($file)) {
    die(".env not found");
}

$content = file_get_contents($file);

// Remove quotes and spaces from the password
$replacements = [
    '/^MAIL_SMTP_PASS=.*$/m' => 'MAIL_SMTP_PASS=vsyjtbwhkumurmxk'
];

$newContent = preg_replace(array_keys($replacements), array_values($replacements), $content);

if (file_put_contents($file, $newContent) !== false) {
    echo "Updated .env (stripped quotes/spaces)";
} else {
    echo "Failed to write .env";
}
