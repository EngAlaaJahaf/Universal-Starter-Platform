<?php
/**
 * ─────────────────────────────────────────────────────────────────
 *  config/hosting.example.php — قالب بيانات الاستضافة الحية
 * ─────────────────────────────────────────────────────────────────
 *  1. انسخ هذا الملف إلى:  config/hosting.php
 *  2. عبّئ القيم الحقيقية من لوحة تحكم الاستضافة.
 *  3. ارفع config/hosting.php «مرة واحدة يدوياً» عبر FTP (مستثنى من git
 *     ومن النشر التلقائي، فلن يُمسح أو يُستبدل أبداً).
 *
 *  أي قيمة معرّفة هنا تتفوق على متغيرات البيئة والقيم الافتراضية.
 */

// ─── قاعدة البيانات (من لوحة الاستضافة — ليس localhost في الغالب) ───
define('DB_HOST', 'sqlXXX.epizy.com');
define('DB_NAME', 'epiz_XXXXXX_starter_platform');
define('DB_USER', 'epiz_XXXXXX_admin');
define('DB_PASS', 'PASTE_STRONG_DB_PASSWORD_HERE');

// ─── النطاق الأساسي (اختياري — يُكتشف تلقائياً عادة) ───
// define('BASE_URL', 'https://your-domain.com/');

// ─── مفتاح مهام Cron (اختياري — غيّره لسلسلة عشوائية طويلة) ───
// define('CRON_SECRET', 'PASTE_LONG_RANDOM_STRING_HERE');

// ─── مفتاح Brevo HTTP API (اختياري — يُستخدم بدل SMTP ويعمل على
// الاستضافات التي تحجب منافذ SMTP، ويُضبط أيضاً من لوحة التحكم) ───
// define('BREVO_API_KEY', 'xkeysib-...');

// ─── البريد الصادر SMTP (اختياري — الاستضافات المجانية تحجبه غالباً) ───
// define('SMTP_HOST', 'smtp.brevo.com');
// define('SMTP_PORT', 587);
// define('SMTP_USERNAME', 'your-smtp-login');
// define('SMTP_PASSWORD', 'your-smtp-key');
// define('MAIL_FROM_ADDRESS', 'news@your-domain.com');
