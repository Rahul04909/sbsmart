<?php
declare(strict_types=1);

/**
 * includes/helpers.php
 * Common helper functions
 */

if (!function_exists('esc')) {
    /**
     * Escape string for HTML output
     * @param string|null $str
     * @return string
     */
    function esc(?string $str): string {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('html')) {
    /**
     * Alias for esc()
     * @param string|null $str
     * @return string
     */
    function html(?string $str): string {
        return esc($str);
    }
}

if (!function_exists('format_price')) {
    /**
     * Format price with currency symbol
     * @param float $amount
     * @param string $currency
     * @return string
     */
    function format_price(float $amount, string $currency = 'INR'): string {
        if ($currency === 'INR') {
            return '₹' . number_format($amount, 2);
        }
        return number_format($amount, 2) . ' ' . esc($currency);
    }
}

if (!function_exists('get_base_url')) {
    function get_base_url(): string {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? '';
        
        $basePath = dirname($scriptName);
        $basePath = str_replace('\\', '/', $basePath);
        
        $currentDir = rtrim(str_replace('\\', '/', dirname($scriptFilename)), '/');
        $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');

        if ($projectRoot !== '' && strpos($currentDir, $projectRoot) === 0) {
            $relative = trim(substr($currentDir, strlen($projectRoot)), '/');
            
            if ($relative === '') {
                // If we are at the project root, the basePath is the correct base URL
                return ($basePath === '/' || $basePath === '.') ? '/' : rtrim($basePath, '/') . '/';
            } else {
                // If we are in a subfolder, we need to go up by the depth of that subfolder
                $depth = count(explode('/', $relative));
                $parts = explode('/', trim($basePath, '/'));
                for ($i = 0; $i < $depth; $i++) {
                    array_pop($parts);
                }
                $base = implode('/', $parts);
                return '/' . ($base ? $base . '/' : '');
            }
        }
        
        return ($basePath === '/' || $basePath === '.') ? '/' : rtrim($basePath, '/') . '/';
    }
}

if (!function_exists('resolve_image')) {
    /**
     * Resolve image path with correct base URL
     * @param string|null $img
     * @return string
     */
    function resolve_image(?string $img): string {
        $base = get_base_url();
        $uploads_dir = 'uploads/products';
        $placeholder = 'noimage.webp';
        
        $img = trim((string)($img ?? ''));
        if ($img === '') {
            return $base . $placeholder;
        }
        
        // If external URL
        if (strpos($img, 'http') === 0) {
            return $img;
        }

        // Clean filename
        $filename = basename($img);
        $relPath = $uploads_dir . '/' . $filename;
        
        // Check file existence relative to project root (assuming one level up from includes)
        // We need the project root. Since this file is in /includes, root is __DIR__ . '/../'
        $projectRoot = realpath(__DIR__ . '/..');
        
        if ($projectRoot && file_exists($projectRoot . '/' . $uploads_dir . '/' . $filename)) {
            return $base . $uploads_dir . '/' . $filename;
        }

        return $base . $placeholder;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to a URL
     * @param string $url
     */
    function redirect(string $url): void {
        if (!headers_sent()) {
            header("Location: " . $url);
        } else {
            echo '<script>window.location.href="' . esc($url) . '";</script>';
        }
        exit;
    }
}

if (!function_exists('safe_redirect')) {
    /**
     * Safe redirect to a URL (same host or relative)
     * @param string $url
     */
    function safe_redirect(string $url): void {
        // Allow relative paths (e.g. 'account.php', './page.php')
        // Allow absolute paths (e.g. '/page.php')
        // Allow full URLs (http/https)
        
        // Simple check: just ensure it doesn't contain CRLF injection (header handles this mostly but good practice)
        $url = str_replace(["\r", "\n"], '', $url);
        
        header('Location: ' . $url);
        exit;
    }
}

if (!function_exists('flash')) {
    /**
     * Set or get flash message
     * @param string|null $key
     * @param string|null $message
     * @return mixed
     */
    function flash(?string $key = null, ?string $message = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($key !== null && $message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return;
        }
        
        if ($key !== null && $message === null) {
            if (isset($_SESSION['_flash'][$key])) {
                $msg = $_SESSION['_flash'][$key];
                unset($_SESSION['_flash'][$key]);
                return $msg;
            }
            return null;
        }
        
        return $_SESSION['_flash'] ?? [];
    }
}

// Compatibility wrappers for flash
if (!function_exists('flash_set')) {
    function flash_set(string $key, string $message): void {
        flash($key, $message);
    }
}

if (!function_exists('flash_get')) {
    function flash_get(?string $key = null) {
        return flash($key);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Generate or retrieve CSRF token
     * @return string
     */
    function csrf_token(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            try {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } catch (Exception $e) {
                $_SESSION['csrf_token'] = md5(uniqid((string)mt_rand(), true));
            }
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_input')) {
    /**
     * Generate hidden input for CSRF token
     * @return string
     */
    function csrf_input(): string {
        return '<input type="hidden" name="csrf_token" value="' . esc(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_check')) {
    /**
     * Verify CSRF token
     * @return bool
     */
    function csrf_check(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('current_user')) {
    /**
     * Get current logged in user or null
     * @return array|null
     */
    function current_user(): ?array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // 1. Return session user if present
        if (!empty($_SESSION['user'])) {
            return $_SESSION['user'];
        }

        // 2. Check Remember Me cookie
        if (!empty($_COOKIE['remember_me']) && function_exists('get_db')) {
            $parts = explode(':', $_COOKIE['remember_me']);
            if (count($parts) === 2) {
                [$selector, $validator] = $parts;
                try {
                    $pdo = get_db();
                    $stmt = $pdo->prepare("SELECT * FROM user_tokens WHERE selector = ? AND expires_at > NOW() LIMIT 1");
                    $stmt->execute([$selector]);
                    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($tokenData && hash_equals($tokenData['hashed_validator'], hash('sha256', $validator))) {
                        // Valid token! Log them in.
                         $uStmt = $pdo->prepare("SELECT id, name, email, phone FROM users WHERE id = ? LIMIT 1");
                         $uStmt->execute([$tokenData['user_id']]);
                         $user = $uStmt->fetch(PDO::FETCH_ASSOC);

                         if ($user) {
                             session_regenerate_id(true);
                             $_SESSION['user'] = [
                                 'id' => (int)$user['id'],
                                 'name' => $user['name'],
                                 'email' => $user['email'],
                                 'phone' => $user['phone'] ?? ''
                             ];
                             // Optional: refresh token expiry here? Keeping it simple for now.
                             return $_SESSION['user'];
                         }
                    }
                } catch (Exception $e) {
                    // Ignore DB errors during auto-login
                }
            }
        }

        return null;
    }
}

if (!function_exists('require_login')) {
    /**
     * Enforce login
     * @param string|null $returnTo
     */
    function require_login(?string $returnTo = null): void {
        if (!current_user()) {
            // Get the base path (e.g., /sbsnewbackup/ for localhost or / for production)
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $basePath = dirname($scriptName);
            // Remove /auth, /admin, /includes etc. to get project root
            $basePath = preg_replace('#/(auth|admin|includes|account).*$#', '', $basePath);
            if ($basePath === '' || $basePath === '.') {
                $basePath = '/';
            } else {
                $basePath = rtrim($basePath, '/') . '/';
            }
            
            if ($returnTo) {
                // Use absolute path with base
                safe_redirect($basePath . 'account.php?redirect=' . urlencode($returnTo));
            } else {
                safe_redirect($basePath . 'account.php');
            }
        }
    }
}

if (!function_exists('money_inr')) {
    /**
     * Format amount as INR
     * @param float|int $amount
     * @return string
     */
    function money_inr($amount): string {
        return '₹' . number_format((float)$amount, 2);
    }
}

if (!function_exists('cart_summary')) {
    /**
     * Calculate cart summary (items, total, etc.)
     * @param array $cart
     * @return array
     */
    function cart_summary(array $cart): array {
        $items = [];
        $total = 0.0;

        foreach ($cart as $key => $item) {
            // Normalize item structure
            if (is_array($item)) {
                $id = (int)($item['id'] ?? $key);
                $title = (string)($item['title'] ?? $item['name'] ?? 'Product');
                $price = (float)($item['price'] ?? 0.0);
                $qty = (int)($item['qty'] ?? $item['quantity'] ?? 0);
                $image = (string)($item['image'] ?? $item['img'] ?? '');
            } else {
                // Simple format: key=id, value=qty
                $id = (int)$key;
                $title = 'Product'; // Would need DB lookup to get real title if not stored in session
                $price = 0.0; // Would need DB lookup
                $qty = (int)$item;
                $image = '';
            }

            if ($qty > 0) {
                $subtotal = $price * $qty;
                $total += $subtotal;
                $items[] = [
                    'id' => $id,
                    'title' => $title,
                    'price' => $price,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                    'image' => $image
                ];
            }
        }

        return [
            'items' => $items,
            'total' => $total,
            'count' => count($items),
            'total_qty' => array_sum(array_column($items, 'qty'))
        ];
    }
}
