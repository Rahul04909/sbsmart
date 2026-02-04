<?php
declare(strict_types=1);

/**
 * test_env.php
 * Test script to verify .env file loading and configuration
 */

require_once __DIR__ . '/includes/config.php';

$config = require __DIR__ . '/includes/config.php';

echo "<h1>Environment Configuration Test</h1>";
echo "<pre>";

// Test database config
echo "Database Configuration:\n";
echo "Host: " . ($config['db']['user'] ?? 'Not set') . "\n";
echo "Database: " . (preg_match('/dbname=([^;]+)/', $config['db']['dsn'], $matches) ? $matches[1] : 'Not found') . "\n";
echo "User: " . $config['db']['user'] . "\n";
echo "Password: " . (strlen($config['db']['pass']) > 0 ? 'Set (hidden)' : 'Not set') . "\n\n";

// Test mail config
echo "Mail Configuration:\n";
echo "SMTP Host: " . $config['mail']['smtp_host'] . "\n";
echo "SMTP User: " . $config['mail']['smtp_user'] . "\n";
echo "From Email: " . $config['mail']['from_email'] . "\n\n";

// Test CCAvenue config
echo "CCAvenue Configuration:\n";
echo "Merchant ID: " . $config['payments']['ccavenue']['merchant_id'] . "\n";
echo "Access Code: " . (strlen($config['payments']['ccavenue']['access_code']) > 0 ? 'Set (hidden)' : 'Not set') . "\n";
echo "Working Key: " . (strlen($config['payments']['ccavenue']['working_key']) > 0 ? 'Set (hidden)' : 'Not set') . "\n\n";

// Test site config
echo "Site Configuration:\n";
echo "Site Name: " . $config['site']['name'] . "\n";
echo "Base URL: " . $config['site']['base_url'] . "\n";
echo "Environment: " . $config['site']['env'] . "\n";
echo "Debug: " . ($config['site']['debug'] ? 'true' : 'false') . "\n";

echo "</pre>";
echo "<p><strong>Test completed!</strong> Check the values above to ensure they match your .env file.</p>";
