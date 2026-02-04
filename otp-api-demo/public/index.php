<?php
// public/index.php

header('Content-Type: application/json');

// 1. Load Composer Autoloader
// Assuming this is run from the 'public' folder, verify path.
// If composer.json is in parent, vendor is in parent.
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Load Environment Variables
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// 3. Simple Router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Helper to get JSON input
function getJsonInput() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

$controller = new \App\AuthController();

// Route: POST /forgot-password
// Note: Depending on your server config, URI might include project folder name.
// e.g. /sbsnewbackup/otp-api-demo/public/forgot-password
// We'll match loosely for this demo.

if ($method === 'POST' && strpos($uri, 'forgot-password') !== false) {
    $input = getJsonInput();
    $email = $input['email'] ?? '';

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }

    $response = $controller->forgotPassword($email);
    echo json_encode($response);
    exit;
}

// Route: POST /reset-password
if ($method === 'POST' && strpos($uri, 'reset-password') !== false) {
    $input = getJsonInput();
    $email = $input['email'] ?? '';
    $otp = $input['otp'] ?? '';
    $newPass = $input['new_password'] ?? '';
    $confirmPass = $input['confirm_password'] ?? '';

    if (empty($email) || empty($otp) || empty($newPass)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $response = $controller->resetPassword($email, $otp, $newPass, $confirmPass);
    echo json_encode($response);
    exit;
}

// 404
http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
