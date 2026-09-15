# حزمة التسليم — Universal Starter Platform

> الفرع: `sh` — خطة المعالجة الكاملة SH-01 → SH-12 (كل بنود Backlog التقرير منفَّذة).
> إصدار الحزمة: 1.0.0

## 1. ماذا تتضمن هذه الحزمة

منصة PHP خالصة (بدون Composer / Node) تشمل: بوابة محتوى عربية، لوحة تحكم CRUD تلقائية، مساعد AI مع failover، خلاصات RSS/Atom، API وسم (v1) بتوثيق `Bearer` + توثيق OpenAPI حي، نظام أمان متعدد الطبقات، وأدوات عمليات مرتبة.

### خطة المعالجة المنفَّذة (SH-01 → SH-12)

| # | المحور | النتيجة الرئيسية | الدليل |
|---|--------|------------------|--------|
| SH-01 | API | دمج مسارات `/api/v1/*` واختبار دخاني: صفر 404 كاذبة | `tests/`, هذا الدليل §7 |
| SH-02 | Security | `CRON_SECRET` من البيئة فقط، بلا default، تدوير عبر `craft cron:secret` | `core/CronGuard.php` |
| SH-03 | Security | إزالة الحساب الافتراضي + قفل `install.php` | README/متغيرات الإعداد |
| SH-04 | Security | توسيع IDS لفحص `php://input` وأجسام POST/JSON | `core/SecurityGuard.php` |
| SH-05 | API/Security | CORS قائمة بيضاء + Rate Limit لكل مفتاح API | `core/Cors.php`, `core/RateLimiter.php` |
| SH-06 | Security | تعقيم SVG + `EMULATE_PREPARES=false` + CSP/HSTS + IP موثوق | `core/Upload.php`, `.htaccess` |
| SH-07 | Testing | 12 جناح اختبار / ~290 توكيدًا + بوابة CI | `tests/run.php`, `.github/workflows` |
| SH-08 | Architecture | فصل المسارات (web/admin/api) + تفكيك المتحكمات العملاقة | `routes/*.php` |
| SH-09 | Performance | كاش بثلاثة مشغلات + طابور خلفية + فهارس | `core/Cache.php`, `core/Queue.php` |
| SH-10 | DevOps | ترحيلات بدفتر + نسخ احتياطي باحتفاظ + نبض Uptime | `core/DbMigrations.php`, `craft db:*` |
| SH-11 | Auth | RBAC دقيق (`Acl`) + فصل editor + سجل `acl_denied` | `core/Acl.php`, `core/Auth.php` |
| SH-12 | Platform | OpenAPI حي لكل مسار API + Plugin hooks + Webhooks موقّعة | `core/OpenApiGenerator.php`, `core/Plugin.php`, `core/Webhook.php` |

## 2. متطلبات التشغيل

- PHP **8.0+** (موصى به 8.2) مع `curl`, `pdo_mysql`, `fileinfo`, `openssl`.
- MySQL **8.x** (utf8mb4).
- Apache/LiteSpeed مع `mod_rewrite` وملف `.htaccess` مفعّل (المعاينة المحلية: `php craft serve`).

## 3. خطوات التثبيت على الخادم

```bash
# 1) رفع الملفات ثم ضبط المتغيرات
cp .env.example .env            # ثم عبّئه بالواقع (القيم مطلوبة أسفل)

# 2) إنشاء قاعدة البيانات وتطبيق المخطط ثم الترحيلات
#    استيراد database/schema.sql أولاً (phpMyAdmin أو CLI)، ثم:
php craft db:migrate            # يطبّق migrations*.sql ويسجّلها في schema_migrations
php craft db:migrate --preview  # لمعاينة القائمة بلا استدعاء MySQL

# 3) تعيين سر cron (مطلوب قبل تفعيل أي مجدول HTTP)
php craft cron:secret           # يطبع سِرًا مرة واحدة فقط — خزّنه في CRON_SECRET

# 4) المستخدم الأول
#    سجّل عبر /register ثم ارفع دوره في جدول users إلى role_id الخاص بـ admin
#    (لا يوجد حساب افتراضي — بالإجبار بما تتطلبه SH-03).

# 5) فحص قبل الإطلاق
php craft release:check         # بوابة إصدار أوتوماتيكية (Lint + اختبارات + ربط)
```

## 4. الملفات والإعدادات الضرورية

| الملف | ماذا يضبط |
|-------|-----------|
| `.env` | DB creds، `CRON_SECRET`، `CACHE_DRIVER`، `TRUST_PROXY`، `CORS_ALLOWED_ORIGINS`، `UPTIME_HEARTBEAT_URL` |
| `config/database.php` | إعدادات PDO + تعريفات env (لا تُعدَّل عادة) |
| `config/hosting.php` | تجاوزات الاستضافة الفعلية (اختياري) |
| `.htaccess` | حجب `core|config|storage|...`، CSP/HSTS |

## 5. المهام المجدولة (cron)

- **نبض المراقبة (اختياري):** كل 10 دقائق
  ```
  */10 * * * * php /path-app/cron/health_heartbeat.php
  ```
  لا يفعل شيئًا ما لم يُضبط `UPTIME_HEARTBEAT_URL`؛ وسجلّه `storage/logs/uptime_heartbeat.log`.
- **عامل الطابور (للعمليات الثقيلة):**
  ```
  * * * * * php /path-app/craft queue:work >/dev/null 2>&1
  ```
- سكربتات cron أخرى (RSS/نشر) تستدعى عبر HTTP بالسر: `/cron/rss_auto_publish.php?secret=...`.

## 6. أدوات `craft` المتاحة

`serve`, `make:controller|api|model|crud`, `doctor`, `cron:secret`, `ai:test`, `test`,
`queue:work`, `db:indexes`, `db:backup [keep]`, `db:migrate [--preview]`, `acl:matrix`,
`webhook:send`, `release:check`, `help`.

## 7. الاختبارات

```bash
php craft test         # كل الأجنحة (DB-backed تُتخطى بأمان عند غياب MySQL)
php craft doctor       # فحص بيئة النظام
php craft release:check  # بوابة الإصدار الكاملة (216 ملف lint + 12 جناحًا + فحوص الالتزام)
```

مخرجات `release:check` المطلوبة قبل أي إصدار:

```
RELEASE READY — 19 checks passed.
```

## 8. قائمة أمان الإطلاق (اعتمدها بالترتيب)

1. `.env` فقط بسر cron غير فارغ، والحساب admin الحقيقي بكلمة مرور قوية.
2. `CORS_ALLOWED_ORIGINS` = نطاقاتك الفعلية فقط (بلا `*`).
3. `TRUST_PROXY` = `true` فقط خلف Cloudflare/عكسي موثوق.
4. مفاتيح API مفعّلة بـ `scopes` أدنى صلاحية والتدوير عبر تبويب مفاتيح API.
5. إغلاق `install.php` بعد أول تثبيت.
6. تشغيل `php craft release:check` من فرع نظيف قبل النشر.

## 9. التنبيهات والقيود

- لم تُضف أي اعتماديات خارجية؛ كل شيء PHP قياسي.
- فهارس وأماكن الترحيل توضع في `database/migrations_sh*.sql` — الخادم ينفذها عبر `db:migrate` منفردة مرة واحدة مسجَّلة في `schema_migrations`.
- في المعاينة المحلية بدون MySQL: الاختبارات المرتبطة بالقاعدة تُتخطى بأمان؛ `db:backup` يفشل برسالة اتصال مغلقة (سلوك مقصود).
- النسخ الاحتياطي: `php craft db:backup 14` يحفظ أحدث 14 نسخة في `storage/backups` (غير مت تبعه git).