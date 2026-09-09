<?php

class SecurityGuard
{
    /**
     * Active IDS Request Scanner
     * Runs at the start of every request to detect intrusions and cyber threats.
     */
    public static function scanRequest()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // 1. Check for Path Traversal & Sensitive File Probing
        if (preg_match('/(\.\.[\/\\\\]|\/etc\/passwd|\/proc\/|boot\.ini|\.env|\.git|\.aws|\.htaccess|wp-login\.php|phpmyadmin|eval-stdin\.php|\/xmlrpc\.php)/i', $uri, $matches)) {
            self::recordAlert('path_traversal_probe', 'critical', "Attempted access to sensitive path: {$matches[0]} via URI: {$uri}", true);
        }

        // 2. Check for SQL Injection patterns in GET parameters
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        if (!empty($queryString) && self::containsSqli($queryString)) {
            self::recordAlert('sqli_attempt_get', 'critical', "SQL Injection signature detected in Query String: " . substr($queryString, 0, 300), true);
        }

        // 3. Check for XSS in GET parameters
        if (!empty($queryString) && self::containsXss($queryString)) {
            self::recordAlert('xss_attempt_get', 'high', "Cross-Site Scripting (XSS) payload detected in URL: " . substr($queryString, 0, 300), true);
        }

        // 4. Check for Automated Vulnerability Scanners
        if (preg_match('/(sqlmap|nikto|wpscan|masscan|zgrab|acunetix|nessus|nmap)/i', $ua, $matches)) {
            self::recordAlert('scanner_probe', 'high', "Automated Security Scanner User-Agent: {$matches[0]}", true);
        }
    }

    /**
     * Check for SQL Injection signatures
     */
    public static function containsSqli($input)
    {
        $input = urldecode($input);
        $pattern = '/(\b(union\s+all\s+select|union\s+select|select\s+.*\s+from|insert\s+into|delete\s+from|drop\s+table|drop\s+database|truncate\s+table|information_schema|benchmark\(|sleep\()\b|(\'|\")\s*(or|and)\s*(\'|\")?[0-9a-zA-Z]+(\'|\")?\s*=\s*(\'|\")?[0-9a-zA-Z]+|--\s*$|\/\*.*\*\/)/i';
        return preg_match($pattern, $input);
    }

    /**
     * Check for XSS signatures
     */
    public static function containsXss($input)
    {
        $input = urldecode($input);
        $pattern = '/(<script\b[^>]*>(.*?)<\/script>|javascript:[^"\'\s]+|<img\s+[^>]*onerror\s*=|onerror\s*=|onload\s*=|onclick\s*=|alert\(|document\.cookie|<svg\s+[^>]*onload=)/is';
        return preg_match($pattern, $input);
    }

    /**
     * Log Security Threat and Notify Admins via In-App Notifications
     */
    public static function recordAlert($alertType, $severity = 'high', $payload = '', $block = true)
    {
        try {
            $db = new Database();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $uri = substr($_SERVER['REQUEST_URI'] ?? '/', 0, 500);
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);

            // 1. Insert into security_alerts table
            $db->query("
                INSERT INTO security_alerts 
                (alert_type, severity, ip_address, target_uri, payload_sample, user_agent, is_blocked, is_resolved, created_at)
                VALUES 
                (:type, :sev, :ip, :uri, :payload, :ua, :blocked, 0, CURRENT_TIMESTAMP)
            ", [
                ':type'    => $alertType,
                ':sev'     => $severity,
                ':ip'      => $ip,
                ':uri'     => $uri,
                ':payload' => substr((string) $payload, 0, 1000),
                ':ua'      => $ua,
                ':blocked' => $block ? 1 : 0,
            ]);

            // 2. Dispatch in-app notifications to all admin users!
            $admins = $db->fetchAll("SELECT id FROM users WHERE role_id = 1 OR role_id IN (SELECT id FROM roles WHERE name = 'admin')");
            foreach ($admins as $admin) {
                $db->query("
                    INSERT INTO notifications 
                    (user_id, type, title_ar, title_en, message_ar, message_en, link, is_read, created_at)
                    VALUES 
                    (:uid, 'security_alert', :t_ar, :t_en, :m_ar, :m_en, :link, 0, CURRENT_TIMESTAMP)
                ", [
                    ':uid'  => (int) $admin['id'],
                    ':t_ar' => "🚨 رصد نشاط مشبوه واشتباه اختراق ({$alertType})",
                    ':t_en' => "Security Intrusion Alert ({$alertType})",
                    ':m_ar' => "تم رصد محاولة هجوم أو نشاط مشبوه من العنوان IP: {$ip} على المسار ({$uri}). تم التصدي للعملية وتوثيقها.",
                    ':m_en' => "Suspicious intrusion attempt detected from IP {$ip} targeting {$uri}.",
                    ':link' => app_url('admin/security-alerts'),
                ]);
            }
        } catch (Throwable $e) {
            error_log("SecurityGuard Error: " . $e->getMessage());
        }

        // 3. Block malicious request if required
        if ($block) {
            http_response_code(403);
            header('Content-Type: text/html; charset=utf-8');
            echo '<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>403 - وصول محظور أمنياً</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css"><link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@600;700&display=swap" rel="stylesheet"><style>body{font-family:\'Readex Pro\',sans-serif;background:#0f172a;color:#fff;display:grid;place-items:center;min-height:100vh;margin:0}</style></head><body><div class="text-center p-4" style="max-width:550px"><div style="font-size:3.5rem" class="mb-3">🛡️</div><h2 class="fw-bold text-danger mb-2">403 - تم حظر الطلب أمنياً</h2><p class="text-secondary mb-3">رصدت منظومة الحماية التلقائية نشاطاً أو حمولة غير مصرح بها. تم تسجيل عنوان الـ IP والبيانات وتنبيه مدراء المنصة.</p><div class="p-3 rounded-3 bg-dark border border-danger text-start font-monospace small mb-3 text-danger">IP: ' . htmlspecialchars($ip) . '<br>Incident Ref: ' . time() . '</div><a href="' . app_url() . '" class="btn btn-outline-light px-4">العودة للرئيسية</a></div></body></html>';
            exit;
        }
    }
}
