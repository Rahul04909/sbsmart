<?php
declare(strict_types=1);

/**
 * mailer.php
 *
 * Priority:
 * 1) Use PHPMailer if installed (recommended).
 * 2) If not available, use the embedded Simple SMTP sender (supports AUTH + STARTTLS).
 * 3) If SMTP not configured or fails, falls back to PHP mail().
 *
 * Public API unchanged:
 *   $m = new Mailer();
 *   $m->send($to, $subject, $htmlBody, $plainBody = '', array $attachments = []);
 *
 * Notes:
 * - Keep SMTP credentials in config.php (or .env) as earlier discussed.
 * - For robust production mail features use PHPMailer + proper configuration.
 */

$config = require __DIR__ . '/config.php';
$mailCfg = $config['mail'] ?? [];

/**
 * Simple helper: normalize recipients into array and validate basic email format.
 */
function normalize_recipients($to): array
{
    $list = is_array($to) ? $to : [$to];
    $out = [];
    foreach ($list as $addr) {
        $addr = trim((string)$addr);
        if ($addr === '') continue;
        // basic validation
        if (filter_var($addr, FILTER_VALIDATE_EMAIL)) {
            $out[] = $addr;
        } else {
            error_log("[mailer] invalid email skipped: {$addr}");
        }
    }
    return array_values(array_unique($out));
}

/**
 * Main Mailer class (public API)
 */
class Mailer
{
    private array $config;

    public function __construct(array $config = [])
    {
        // accept either injected config or fall back to includes/config.php mail section
        $this->config = $config ?: (require __DIR__ . '/config.php')['mail'] ?? [];
    }

    /**
     * Send an email.
     *
     * @param string|array $to Single email or array of emails
     * @param string $subject
     * @param string $htmlBody
     * @param string $plainBody
     * @param array $attachments File paths
     * @return bool
     */
    public function send($to, string $subject, string $htmlBody, string $plainBody = '', array $attachments = []): bool
    {
        $toList = normalize_recipients($to);
        if (empty($toList)) {
            error_log('[mailer] no valid recipients provided');
            return false;
        }

        // 1) PHPMailer if present
        if (class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
            $ok = $this->sendWithPHPMailer($toList, $subject, $htmlBody, $plainBody, $attachments);
            if ($ok) return true;
            // if PHPMailer fails, continue to other fallbacks
        }

        // 2) Internal SMTP sender (if config indicates an smtp block)
        $smtpConfig = $this->config['smtp'] ?? null;
        if (!empty($smtpConfig) && is_array($smtpConfig)) {
            $smtpSender = new SimpleSmtpMailer($this->config);
            $ok = $smtpSender->send($toList, $subject, $htmlBody, $plainBody, $attachments);
            if ($ok) return true;
        }

        // 3) Fallback to PHP mail()
        return $this->sendWithMailFunction($toList, $subject, $htmlBody, $plainBody, $attachments);
    }

    /**
     * PHPMailer path - preserves your previous logic but uses normalized recipients.
     * Expects PHPMailer autoloaded by composer or included elsewhere.
     */
    private function sendWithPHPMailer(array $toList, string $subject, string $htmlBody, string $plainBody = '', array $attachments = []): bool
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // SMTP settings (if configured via mail.smt[...] shape)
            $s = $this->config['smtp'] ?? [];
            if (!empty($s) && is_array($s)) {
                $mail->isSMTP();
                $mail->Host = $s['host'] ?? ($this->config['smtp_host'] ?? '');
                $mail->Port = (int)($s['port'] ?? ($this->config['smtp_port'] ?? 587));
                $mail->SMTPAuth = !empty($s['username'] ?? $s['user'] ?? $this->config['smtp_user'] ?? false);
                if ($mail->SMTPAuth) {
                    $mail->Username = $s['username'] ?? $s['user'] ?? $this->config['smtp_user'];
                    $mail->Password = $s['password'] ?? $s['pass'] ?? $this->config['smtp_pass'];
                }
                $enc = strtolower($s['encryption'] ?? ($this->config['smtp_encryption'] ?? ($this->config['smtp_secure'] ?? 'tls')));
                if ($enc === 'ssl') {
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($enc === 'tls') {
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                }
            } elseif (!empty($this->config['use_smtp'])) {
                // legacy support
                $mail->isSMTP();
                $mail->Host = $this->config['smtp_host'] ?? '';
                $mail->Port = (int)($this->config['smtp_port'] ?? 587);
                $mail->SMTPAuth = !empty($this->config['smtp_user']);
                if ($mail->SMTPAuth) {
                    $mail->Username = $this->config['smtp_user'];
                    $mail->Password = $this->config['smtp_pass'];
                }
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            }

            $fromEmail = $this->config['from_email'] ?? ($this->config['from'] ?? 'noreply@example.com');
            $fromName  = $this->config['from_name'] ?? ($this->config['fromName'] ?? 'Website');

            $mail->setFrom($fromEmail, $fromName);

            foreach ($toList as $r) {
                $mail->addAddress($r);
            }

            // attachments
            foreach ($attachments as $file) {
                if (is_file($file) && is_readable($file)) {
                    $mail->addAttachment($file);
                } else {
                    error_log("[mailer] attachment skipped (missing): {$file}");
                }
            }

            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody;
            $mail->AltBody = $plainBody ?: strip_tags($htmlBody);

            return $mail->send();
        } catch (Throwable $e) {
            if (!empty($this->config['debug'])) {
                error_log('PHPMailer error: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Fallback using PHP mail()
     */
    private function sendWithMailFunction(array $toList, string $subject, string $htmlBody, string $plainBody = '', array $attachments = []): bool
    {
        $toAddress = implode(',', $toList);

        $boundary = '==Multipart_Boundary_x' . md5((string)microtime(true));
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        if (!empty($attachments)) {
            $headers[] = "Content-Type: multipart/mixed; boundary=\"{$boundary}\"";
        } else {
            $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
        }

        $fromEmail = $this->config['from_email'] ?? ($this->config['from'] ?? 'noreply@example.com');
        $fromName  = $this->config['from_name'] ?? ($this->config['fromName'] ?? 'Website');

        $headers[] = "From: " . $this->encodeHeader("$fromName <$fromEmail>");
        // optional reply-to if configured
        if (!empty($this->config['reply_to'])) {
            $headers[] = "Reply-To: " . $this->config['reply_to'];
        }

        $message = [];
        // plain part
        $message[] = "--{$boundary}";
        $message[] = "Content-Type: text/plain; charset=UTF-8";
        $message[] = "Content-Transfer-Encoding: 7bit";
        $message[] = "";
        $message[] = $plainBody ?: strip_tags($htmlBody);
        $message[] = "";

        // html part
        $message[] = "--{$boundary}";
        $message[] = "Content-Type: text/html; charset=UTF-8";
        $message[] = "Content-Transfer-Encoding: 7bit";
        $message[] = "";
        $message[] = $htmlBody;
        $message[] = "";

        // attachments (note: large attachments may be memory heavy)
        foreach ($attachments as $file) {
            if (!is_file($file) || !is_readable($file)) {
                error_log("[mailer] attachment missing/skipped: {$file}");
                continue;
            }
            $content = chunk_split(base64_encode(file_get_contents($file)));
            $fname = basename($file);
            $message[] = "--{$boundary}";
            $message[] = "Content-Type: application/octet-stream; name=\"{$fname}\"";
            $message[] = "Content-Transfer-Encoding: base64";
            $message[] = "Content-Disposition: attachment; filename=\"{$fname}\"";
            $message[] = "";
            $message[] = $content;
            $message[] = "";
        }

        $message[] = "--{$boundary}--";
        $body = implode("\r\n", $message);
        $headersStr = implode("\r\n", $headers);

        // Try to set Return-Path using additional parameters (works on many systems)
        $additionalParams = '';
        if (!empty($this->config['return_path'])) {
            $additionalParams = '-f' . escapeshellarg($this->config['return_path']);
        } elseif (!empty($this->config['from_email'])) {
            $additionalParams = '-f' . escapeshellarg($this->config['from_email']);
        }

        if ($additionalParams !== '') {
            return (bool) @mail($toAddress, $subject, $body, $headersStr, $additionalParams);
        }
        return (bool) @mail($toAddress, $subject, $body, $headersStr);
    }

    /**
     * Basic UTF-8 header encoder used for From/Subject when needed
     */
    private function encodeHeader(string $text): string
    {
        if (preg_match('//u', $text)) {
            return '=?UTF-8?B?' . base64_encode($text) . '?=';
        }
        return $text;
    }
}

/**
 * SimpleSmtpMailer
 * Lightweight SMTP mailer as fallback when PHPMailer not installed.
 * Supports AUTH LOGIN + STARTTLS and basic multipart messages.
 *
 * Not feature-complete — intended as fallback only.
 */
class SimpleSmtpMailer
{
    private array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Send via SMTP
     * @param string|array $to
     */
    public function send($to, string $subject, string $htmlBody, string $plainBody = '', array $attachments = []): bool
    {
        $toList = is_array($to) ? $to : [$to];

        $smtp = $this->config['smtp'] ?? [];
        // support legacy keys
        $host = $smtp['host'] ?? ($this->config['smtp_host'] ?? '');
        $port = (int)($smtp['port'] ?? ($this->config['smtp_port'] ?? 587));
        $user = $smtp['username'] ?? ($smtp['user'] ?? ($this->config['smtp_user'] ?? null));
        $pass = $smtp['password'] ?? ($smtp['pass'] ?? ($this->config['smtp_pass'] ?? null));
        $enc  = strtolower($smtp['encryption'] ?? ($this->config['smtp_encryption'] ?? ($this->config['smtp_secure'] ?? 'tls')));
        $timeout = (int)($smtp['timeout'] ?? 30);

        if (empty($host)) {
            error_log('[SimpleSmtpMailer] smtp host not configured');
            return false;
        }

        $remote = ($enc === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;

        $flags = STREAM_CLIENT_CONNECT;
        $ctx = stream_context_create([]);
        $fp = @stream_socket_client($remote, $errno, $errstr, $timeout, $flags, $ctx);
        if (!$fp) {
            error_log("[SimpleSmtpMailer] connect failed: {$errno} - {$errstr}");
            return false;
        }
        stream_set_timeout($fp, $timeout);

        // greet
        $res = $this->smtpRead($fp);
        if (!$this->smtpCodeIs($res, 220)) { fclose($fp); return false; }

        $this->smtpWrite($fp, "EHLO " . $this->getLocalHost());
        $res = $this->smtpReadMulti($fp);
        if (!$this->smtpCodeIs($res, 250)) {
            $this->smtpWrite($fp, "HELO " . $this->getLocalHost());
            $res = $this->smtpRead($fp);
            if (!$this->smtpCodeIs($res, 250)) { fclose($fp); return false; }
        }

        // STARTTLS if requested and supported
        if ($enc === 'tls' && stripos($res, 'STARTTLS') !== false) {
            $this->smtpWrite($fp, "STARTTLS");
            $res = $this->smtpRead($fp);
            if ($this->smtpCodeIs($res, 220)) {
                if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    fclose($fp);
                    return false;
                }
                // re-EHLO
                $this->smtpWrite($fp, "EHLO " . $this->getLocalHost());
                $res = $this->smtpReadMulti($fp);
            } else {
                fclose($fp);
                return false;
            }
        }

        // AUTH LOGIN if username provided
        if (!empty($user)) {
            $this->smtpWrite($fp, "AUTH LOGIN");
            $res = $this->smtpRead($fp); if (!$this->smtpCodeIs($res, 334)) { fclose($fp); return false; }
            $this->smtpWrite($fp, base64_encode($user));
            $res = $this->smtpRead($fp); if (!$this->smtpCodeIs($res, 334)) { fclose($fp); return false; }
            $this->smtpWrite($fp, base64_encode($pass));
            $res = $this->smtpRead($fp); if (!$this->smtpCodeIs($res, 235)) { fclose($fp); return false; }
        }

        // MAIL FROM
        $from = $this->config['from_email'] ?? ($this->config['from'] ?? 'noreply@example.com');
        $this->smtpWrite($fp, "MAIL FROM:<{$from}>");
        $res = $this->smtpRead($fp); if (!$this->smtpCodeIs($res, 250)) { fclose($fp); return false; }

        // RCPT TO
        foreach ($toList as $rcpt) {
            $this->smtpWrite($fp, "RCPT TO:<{$rcpt}>");
            $res = $this->smtpRead($fp);
            if (!($this->smtpCodeIs($res, 250) || $this->smtpCodeIs($res, 251))) { fclose($fp); return false; }
        }

        // DATA
        $this->smtpWrite($fp, "DATA");
        $res = $this->smtpRead($fp); if (!$this->smtpCodeIs($res, 354)) { fclose($fp); return false; }

        // Build message
        $boundary = 'b1_' . md5((string)microtime(true));
        $headers = [];
        $fromName = $this->config['from_name'] ?? 'Website';
        $headers[] = "From: " . $this->encodeHeader($fromName) . " <{$from}>";
        $headers[] = "To: " . implode(', ', $toList);
        $headers[] = "Subject: " . $this->encodeHeader($subject);
        $headers[] = "MIME-Version: 1.0";
        if (!empty($attachments)) {
            $headers[] = "Content-Type: multipart/mixed; boundary=\"{$boundary}\"";
        } else {
            $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";
        }
        $headers[] = "";
        $body = implode("\r\n", $headers);

        // plain part
        $plain = $plainBody ?: strip_tags($htmlBody);
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= $plain . "\r\n\r\n";

        // html part
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= $htmlBody . "\r\n\r\n";

        // attachments
        foreach ($attachments as $file) {
            if (!is_file($file) || !is_readable($file)) continue;
            $content = chunk_split(base64_encode(file_get_contents($file)));
            $fname = basename($file);
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: application/octet-stream; name=\"{$fname}\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "Content-Disposition: attachment; filename=\"{$fname}\"\r\n\r\n";
            $body .= $content . "\r\n\r\n";
        }

        $body .= "--{$boundary}--\r\n.\r\n";

        $this->smtpWrite($fp, $body, false);
        $res = $this->smtpRead($fp);
        if (!$this->smtpCodeIs($res, 250)) { fclose($fp); error_log("[SimpleSmtpMailer] DATA send failed: {$res}"); return false; }

        $this->smtpWrite($fp, "QUIT");
        $this->smtpRead($fp);
        fclose($fp);
        return true;
    }

    /* ---------- helper methods ---------- */

    private function smtpWrite($fp, string $data, bool $appendCRLF = true): void
    {
        fwrite($fp, $data . ($appendCRLF ? "\r\n" : ""));
    }

    private function smtpRead($fp): string
    {
        $data = fgets($fp, 515);
        return $data === false ? '' : rtrim($data, "\r\n");
    }

    private function smtpReadMulti($fp): string
    {
        $res = '';
        while (($line = fgets($fp, 515)) !== false) {
            $res .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return rtrim($res, "\r\n");
    }

    private function smtpCodeIs(string $response, int $code): bool
    {
        return (int)substr(trim($response), 0, 3) === $code;
    }

    private function getLocalHost(): string
    {
        $h = gethostname();
        return $h ?: 'localhost';
    }

    private function encodeHeader(string $text): string
    {
        if (preg_match('//u', $text)) {
            return '=?UTF-8?B?' . base64_encode($text) . '?=';
        }
        return $text;
    }
}
