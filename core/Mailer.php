<?php

class Mailer
{
    public static function send($to, $subject, $html, $text = null)
    {
        $fromEmail = Settings::get('mail_from_address', MAIL_FROM_ADDRESS ?: 'no-reply@technews.local');
        $fromName = Settings::get('mail_from_name', MAIL_FROM_NAME ?: 'منصة الأخبار التقنية');

        // 1. Brevo HTTP API (port 443) — preferred when set, works on shared
        // hosting plans that block outbound SMTP ports (e.g. InfinityFree free).
        $brevoKey = trim((string) Settings::get('brevo_api_key', (defined('BREVO_API_KEY') ? BREVO_API_KEY : '')));
        if ($brevoKey !== '') {
            return self::sendViaBrevoApi($to, $subject, $html, $text, $fromEmail, $fromName, $brevoKey);
        }

        // 2. Fetch SMTP configs from Settings or Constants
        $host = Settings::get('smtp_host', SMTP_HOST);
        $port = (int) Settings::get('smtp_port', SMTP_PORT ?: 587);
        $username = Settings::get('smtp_username', SMTP_USERNAME);
        $password = Settings::get('smtp_password', SMTP_PASSWORD);
        $encryption = Settings::get('smtp_encryption', SMTP_ENCRYPTION ?: 'tls');

        // 3. If no SMTP is configured in localhost, log the email to storage/logs/mail.log
        if (empty($host) || empty($username)) {
            $logEntry = "[" . date('Y-m-d H:i:s') . "] TO: {$to} | SUBJECT: {$subject}\n" . strip_tags($html) . "\n----------------------------------------\n";
            $logDir = __DIR__ . '/../storage/logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0777, true);
            }
            @file_put_contents($logDir . '/mail.log', $logEntry, FILE_APPEND);
            return true; // Marked as simulated/sent successfully in dev mode
        }

        // 3. Real SMTP Transport via Socket Stream
        try {
            $transport = $encryption === 'ssl' ? 'ssl://' : 'tcp://';
            $socket = @stream_socket_client($transport . $host . ':' . $port, $errno, $error, 15, STREAM_CLIENT_CONNECT);
            if (!$socket) {
                error_log("SMTP Connection failed: $error ($errno)");
                return false;
            }

            stream_set_timeout($socket, 15);
            if (!self::expect($socket, 220)) return false;
            self::command($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250);

            if ($encryption === 'tls') {
                self::command($socket, 'STARTTLS', 220);
                if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    fclose($socket);
                    return false;
                }
                self::command($socket, 'EHLO localhost', 250);
            }

            self::command($socket, 'AUTH LOGIN', 334);
            self::command($socket, base64_encode($username), 334);
            self::command($socket, base64_encode($password), 235);

            self::command($socket, 'MAIL FROM:<' . $fromEmail . '>', 250);
            self::command($socket, 'RCPT TO:<' . $to . '>', 250);
            self::command($socket, 'DATA', 354);

            $text = $text ?: trim(strip_tags(str_replace(array('<br>', '<br/>', '</p>'), array("\n", "\n", "\n"), $html)));
            $boundary = 'b_' . bin2hex(random_bytes(8));
            $headers = 'From: =?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromEmail . ">\r\n" .
                       'Reply-To: =?UTF-8?B?' . base64_encode($fromName) . '?= <' . $fromEmail . ">\r\n" .
                       'To: <' . $to . ">\r\n" .
                       'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n" .
                       'MIME-Version: 1.0' . "\r\n" .
                       'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

            $body = $headers . "\r\n\r\n--" . $boundary . "\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n" . $text .
                    "\r\n--" . $boundary . "\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n" . $html . "\r\n--" . $boundary . "--\r\n.";

            fwrite($socket, $body . "\r\n");
            $ok = self::expect($socket, 250);
            @fwrite($socket, "QUIT\r\n");
            fclose($socket);
            return $ok;
        } catch (Throwable $e) {
            error_log("SMTP Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send via Brevo Transactional HTTP API (https://api.brevo.com/v3/smtp/email).
     * Returns true on 2xx, false otherwise (caller surfaces the failure).
     */
    private static function sendViaBrevoApi($to, $subject, $html, $text, $fromEmail, $fromName, $apiKey)
    {
        $text = $text ?: trim(strip_tags(str_replace(array('<br>', '<br/>', '</p>'), array("\n", "\n", "\n"), $html)));
        $payload = array(
            'sender' => array('email' => $fromEmail, 'name' => $fromName),
            'to' => array(array('email' => $to)),
            'subject' => $subject,
            'htmlContent' => $html,
            'textContent' => $text,
        );
        try {
            $ch = curl_init('https://api.brevo.com/v3/smtp/email');
            if (!$ch) {
                error_log('Brevo API: curl init failed');
                return false;
            }
            curl_setopt_array($ch, array(
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'api-key: ' . $apiKey,
                ),
                CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ));
            $result = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);
            if ($result === false) {
                error_log('Brevo API curl error: ' . $curlErr);
                return false;
            }
            if ($httpCode >= 200 && $httpCode < 300) {
                return true;
            }
            error_log('Brevo API error HTTP ' . $httpCode . ': ' . substr((string) $result, 0, 300));
            return false;
        } catch (Throwable $e) {
            error_log('Brevo API exception: ' . $e->getMessage());
            return false;
        }
    }

    private static function command($socket, $command, $expected)
    {
        fwrite($socket, $command . "\r\n");
        return self::expect($socket, $expected);
    }

    private static function expect($socket, $expected)
    {
        $line = '';
        while (($part = fgets($socket, 512)) !== false) {
            $line .= $part;
            if (isset($part[3]) && $part[3] === ' ') break;
        }
        return substr($line, 0, 3) === (string) $expected;
    }
}
