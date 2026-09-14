<?php
/**
 * ─────────────────────────────────────────────────────────────────
 *  health_heartbeat.php — Uptime heartbeat (SH-10)
 * ─────────────────────────────────────────────────────────────────
 *  Optional outbound ping so an external uptime monitor (UptimeRobot,
 *  Better Uptime, custom watchdog) can confirm the site's crontab is
 *  alive. No-op unless UPTIME_HEARTBEAT_URL is configured.
 *
 *  Cron entry (every 10 minutes), e.g.:
 *     every 10 min:  php /path/to/cron/health_heartbeat.php
 *  or via HTTP (only with secret):
 *     https://domain.com/cron/health_heartbeat.php?secret=CRON_SECRET
 *
 *  It never fails the exit code: success/failure is only logged.
 * ─────────────────────────────────────────────────────────────────
 */

define('CRON_START', microtime(true));
@ini_set('max_execution_time', 60);
@set_time_limit(60);

$root = dirname(__DIR__);
require_once $root . '/config/database.php';
require_once $root . '/core/CronGuard.php';

date_default_timezone_set('UTC');

// ─── Security: fail-closed cron protection (SH-02) ────────────
CronGuard::checkHttp();

$url = defined('UPTIME_HEARTBEAT_URL') ? (string) UPTIME_HEARTBEAT_URL : '';
$url = trim($url);

if ($url === '') {
    echo "[heartbeat] UPTIME_HEARTBEAT_URL not configured — skipping (no-op).\n";
    exit(0);
}

// Scheme allow-list (https recommended; http kept for private monitors/tests).
if (!preg_match('#^https?://#i', $url)) {
    echo "[heartbeat] UPTIME_HEARTBEAT_URL must start with http(s):// — skipping.\n";
    exit(0);
}

$ok = false;
$attempts = 0;
$lastError = '';
while (!$ok && $attempts < 2) {
    $attempts++;
    $start = microtime(true);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_USERAGENT      => 'StarterPlatform-Heartbeat/1.0',
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_NOBODY         => true,
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $ms = (int) round((microtime(true) - $start) * 1000);
    if ($err === '' && $http >= 200 && $http < 400) {
        $ok = true;
        $lastError = "HTTP {$http} in {$ms}ms";
    } else {
        $lastError = ($err !== '' ? $err : "HTTP {$http}") . " (attempt {$attempts}) in {$ms}ms";
    }
}

$line = sprintf(
    '[%s] %s — latency/attempt %s',
    date('Y-m-d H:i:s'),
    $ok ? 'PING OK' : 'PING FAILED',
    $lastError
);

$logDir = $root . '/storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
@file_put_contents($logDir . '/uptime_heartbeat.log', $line . "\n", FILE_APPEND);

echo $line . "\n";
// No exit(1) here: a down monitor pings repeatedly; the log is the record.
exit(0);