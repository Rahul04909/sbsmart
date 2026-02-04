<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? '';

    // Validate input
    if ($email === '' || $password === '') {
        flash_set('error', 'Email and password are required.');
        header('Location: ../account.php?tab=login&redirect=' . urlencode($redirect));
        exit;
    }

    try {
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT id, name, email, password_hash, is_active FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify password
            $passwordValid = password_verify($password, $user['password_hash']);

            if (!$passwordValid) {
                flash_set('error', 'Invalid email or password.');
                header('Location: ../account.php?tab=login&redirect=' . urlencode($redirect));
                exit;
            }

            if (empty($user['is_active'])) {
                flash_set('error', 'Account not activated. Please verify your email.');
                header('Location: ../account.php?tab=login&redirect=' . urlencode($redirect));
                exit;
            }
            
            // Login success
            if (function_exists('session_regenerate')) {
                session_regenerate();
            } else {
                // Fallback if function not available
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_regenerate_id(true);
                }
            }
            
            // Set session data in the format expected by the app
            $_SESSION['user'] = [
                'id' => (int)$user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ];

            // REMEMBER ME FUNCTIONALITY
            if (!empty($_POST['remember'])) {
                try {
                    $selector = bin2hex(random_bytes(12));
                    $validator = bin2hex(random_bytes(32));
                    $hashedValidator = hash('sha256', $validator);
                    $expiry = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 days

                    $stmt = $pdo->prepare("INSERT INTO user_tokens (user_id, selector, hashed_validator, expires_at) VALUES (?, ?, ?, ?)");
                    $stmt->execute([(int)$user['id'], $selector, $hashedValidator, $expiry]);

                    // Set cookie: selector:validator
                    setcookie('remember_me', "$selector:$validator", [
                        'expires' => time() + (86400 * 30),
                        'path' => '/',
                        'httponly' => true,
                        'samesite' => 'Lax' // or Strict
                    ]);
                } catch (Exception $e) {
                    error_log("Remember Me Error: " . $e->getMessage());
                }
            }

            // MERGE SESSION CART TO DB
            if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                $uid = (int)$user['id'];
                $insertStmt = $pdo->prepare("
                    INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) 
                    VALUES (:uid, :pid, :qty, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE quantity = quantity + :qty, updated_at = NOW()
                ");

                foreach ($_SESSION['cart'] as $item) {
                    // Normalize item structure
                    if (is_array($item)) {
                        $pid = isset($item['id']) ? (int)$item['id'] : 0;
                        $qty = isset($item['qty']) ? (int)$item['qty'] : (isset($item['quantity']) ? (int)$item['quantity'] : 0);
                    } else {
                        // Simple key=>qty format? Unlikely given cart-add structure but possible legacy
                        // If $item is int, it's qty, key is pid. But foreach iterates values.
                        // Let's assume standard structure from cart-add.php
                        continue; 
                    }

                    if ($pid > 0 && $qty > 0) {
                        try {
                            $insertStmt->execute([':uid' => $uid, ':pid' => $pid, ':qty' => $qty]);
                        } catch (Exception $e) {
                            // Ignore errors (e.g. foreign key if product deleted)
                            error_log("Cart merge error: " . $e->getMessage());
                        }
                    }
                }
                // Optional: Clear session cart so it gets rebuilt fresh from DB on next load?
                // Actually, cart.php will overwrite it anyway.
            }

            // HANDLE PENDING CART ITEM (From guest add attempt)
            if (!empty($_POST['pending_product_id'])) {
                $pPid = (int)$_POST['pending_product_id'];
                $pQty = (int)($_POST['pending_qty'] ?? 1);
                $uid = (int)$user['id'];

                if ($pPid > 0 && $pQty > 0) {
                   try {
                       $pdo->prepare("
                           INSERT INTO cart (user_id, product_id, quantity, created_at, updated_at) 
                           VALUES (?, ?, ?, NOW(), NOW())
                           ON DUPLICATE KEY UPDATE quantity = quantity + ?, updated_at = NOW()
                       ")->execute([$uid, $pPid, $pQty, $pQty]);
                       
                       flash_set('success', 'Logged in & Item added to cart successfully.');
                       // Redirect to cart to show the added item
                       header('Location: ../cart.php');
                       exit;
                   } catch (Exception $e) {
                       error_log("Pending cart add error: " . $e->getMessage());
                   }
                }
            }
            
            flash_set('success', 'Logged in successfully.');
            
            // Handle redirect
            if (!empty($redirect)) {
                // Validate redirect URL to prevent open redirect vulnerabilities
                // Only allow relative paths or same-domain URLs
                $redirectUrl = filter_var($redirect, FILTER_SANITIZE_URL);
                if ($redirectUrl && (strpos($redirectUrl, '/') === 0 || strpos($redirectUrl, 'http') !== 0)) {
                     // If it's an absolute path (starts with /), use it as-is
                     // If it's a relative path, prepend ../ to go up from auth/ directory
                     $finalRedirect = (strpos($redirectUrl, '/') === 0) ? $redirectUrl : '../' . $redirectUrl;
                     header('Location: ' . $finalRedirect);
                     exit;
                }
            }
            
            // Default redirect
            header('Location: ../account-profile.php');
            exit;
        } else {
            flash_set('error', 'Invalid email or password.');
            header('Location: ../account.php?tab=login&redirect=' . urlencode($redirect));
            exit;
        }
    } catch (Throwable $e) {
        error_log('Login error: ' . $e->getMessage());
        flash_set('error', 'An error occurred. Try again later.');
        header('Location: ../account.php?tab=login&redirect=' . urlencode($redirect));
        exit;
    }
}

// If accessed directly via GET, redirect to the main login page
header('Location: ../account.php?tab=login');
exit;
