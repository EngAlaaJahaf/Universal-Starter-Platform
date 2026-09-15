# CHANGELOG

جميع التغييرات المهمة على المنصة تتوثق هنا. الفرع النشط للتسليم: `sh`.

## [1.0.0] — 2026

الحزمة التسليمية الكاملة؛ تنفيذ كل بنود Backlog تقرير التقييم الفني (SH-01 → SH-12).

### الأمان (SH-01 → SH-06)
- `SH-01` دمج وتثبيت مسارات `/api/v1/*` (لا 404 كاذبة)، فحص دخاني لكل نطاق API.
- `SH-02` `CRON_SECRET` من البيئة فقط بلا default؛ توليد/تدوير عبر `craft cron:secret`؛ فحص `hash_equals`؛ رفض HTTP عند غياب السر.
- `SH-03` إزالة الحساب الافتراضي المعلن؛ اشتراط أول مستخدم منفصل.
- `SH-04` IDS يفحص `php://input` وأجسام POST/JSON (انسحاب حقن النماذج والـ API).
- `SH-05` CORS قائمة بيضاء صارمة (لا `*`) + Rate Limiter لكل مفتاح API + تدوير المفاتيح.
- `SH-06` تعقيم/حظر SVG (XXE + onload) + `EMULATE_PREPARES=false` + IP موثوق فقط خلف proxy + CSP/HSTS عبر `.htaccess`.

### اختبارات وأعمال بنيوية (SH-07 → SH-08)
- `SH-07` جناح اختبار ذاتي بدون Composer؛ 12 جناحًا / ~290 توكيدًا؛ بوابة CI.
- `SH-08` فصل مسارات `index.php` إلى `routes/{web,admin,api}.php` (314 مسارًا كما هي) + تفكيك `AggregatorController` (trait parsing).

### الأداء والعمليات (SH-09 → SH-10)
- `SH-09` كاش بمشغلات قابلة للتبديل (`CACHE_DRIVER` = file/apcu/array) + سقالة طابور (`Queue`) + جدول `background_jobs` + 8 فهارس على الجداول الحساسة.
- `SH-10` ترحيلات بدفتر `schema_migrations` (`craft db:migrate`) + نسخ احتياطي `db:backup` (mysqldump/GZIP/احتفاظ) + نبض Uptime اختياري.

### الحوكمة والمنصة (SH-11 → SH-12)
- `SH-11` RBAC دقيق: `core/Acl.php` (مصفوفة أدوار) + `Auth::can()` + `requirePermission()` مع تدوين `acl_denied` في `activity_logs`؛ `craft acl:matrix`.
- `SH-12` مولّد OpenAPI حي لكل مسار API (24 عملية/17 مسارًا) + نظام Plugin hooks + Webhooks موقّعة HMAC + جدول `webhooks`.

### أدوات التطوير
- `craft` يتضمن الآن: `release:check` (بوابة إصدار: lint 216 ملف + كل الاختبارات + فحوص الالتزام) و`webhook:send` و`acl:matrix` و`db:*` و`queue:work`.

### سجل النسخ (فرع `sh`)
- `73399e8` SH-12 — OpenAPI حي + hooks + webhooks
- `ef73eef` SH-11 — RBAC + سجل صلاحيات
- `b15c77f` SH-10 — DevOps: ترحيلات/نسخ/نبض
- `1f327d5` SH-09 — كاش + طابور + فهارس
- `8bca42f` SH-08 — فصل المسارات والتفكيك
- (ما قبل التسلسل الحالي: SH-01…SH-07)