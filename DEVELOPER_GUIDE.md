# 🚀 دليل المطور الشامل — Universal Starter Platform

مرحباً بك في **Universal Starter Platform**، القالب الأساسي المعياري فائق الاحترافية المخصص لمطوري الويب لبناء تطبيقات الأعمال، منصات SaaS، بوابات المحتوى، ولوحات التحكم المتقدمة بـ **PHP 8+ و MySQL** بدون الحاجة لأي مكتبات أو أطر عمل خارجية ثقيلة (Zero-Dependency Architecture).

---

## 📑 فهرس الدليل

1. [البدء السريع في 60 ثانية](#1-البدء-السريع-في-60-ثانية)
2. [الهيكلية العامة للمشروع](#2-الهيكلية-العامة-للمشروع)
3. [بناء لوحة إدارة وقسم جديد (CRUD) في دقيقة واحدة](#3-بناء-لوحة-إدارة-وقسم-جديد-crud-في-دقيقة-واحدة)
4. [محرك الذكاء الاصطناعي والتبديل التلقائي (Universal AI Engine)](#4-محرك-الذكاء-الاصطناعي-والتبديل-التلقائي-universal-ai-engine)
5. [المساعد الذكي والشات بوت (AI Chatbot & RAG)](#5-المساعد-الذكي-والشات-بوت-ai-chatbot--rag)
6. [نظام الأمان وجدار الحماية وسجل العمليات](#6-نظام-الأمان-وجدار-الحماية-وسجل-العمليات)
7. [بناء وتوسيع واجهات برمجة التطبيقات (REST API v1)](#7-بناء-وتوسيع-واجهات-برمجة-التطبيقات-rest-api-v1)
8. [أداة سطر الأوامر (Craft CLI)](#8-أداة-سطر-الأوامر-craft-cli)
9. [دليل النشر على الاستضافات والخوادم السحابية](#9-دليل-النشر-على-الاستضافات-والخوادم-السحابية)

---

## 1. البدء السريع في 60 ثانية

### الطريقة الأولى: معالج التثبيت الرسومي (GUI Setup Wizard)
1. افتح المتصفح وانتقل إلى: `http://localhost/install.php`
2. اضغط على **"اختبار الاتصال بقاعدة البيانات"**.
3. أدخل اسم المنصة وبيانات حساب المدير الفائق.
4. اضغط **"بدء التثبيت والتشغيل الفوري"** — سيتم بناء الجداول واستيراد الإعدادات وتجهيز المنصة بالكامل في ثوانٍ.

### الطريقة الثانية: تشغيل خادم التطوير عبر CLI
```bash
# تشغيل الخادم المحلي فوراً
php craft serve

# أو تحديد المنفذ
php craft serve 127.0.0.1:8080
```

- **رابط لوحة التحكم:** `http://127.0.0.1:8000/admin`
- **بيانات الدخول:** الحساب الذي تنشئه في معالج التثبيت (لا توجد بيانات افتراضية — SH-03).

---

## 2. الهيكلية العامة للمشروع

```
Template-Platform/
├── craft                         # أداة المطور وسقالة الأكواد (CLI Toolkit)
├── install.php                   # معالج التثبيت السريع
├── index.php                     # نقطة الدخول الموحدة وتوزيع المسارات
├── config/
│   ├── database.php              # إعدادات قاعدة البيانات والبيئة (.env)
│   ├── admin_menu.php            # القائمة الجانبية للوحة التحكم (ديناميكية 100%)
│   ├── ai_providers.php          # إعدادات ونماذج مزودي الذكاء الاصطناعي
│   └── hosting.example.php       # نموذج تخطي الإعدادات للاستضافة السحابية
├── core/
│   ├── AiService.php             # المحرك المركزي للذكاء الاصطناعي وسلاسل Failover
│   ├── AiChatAssistant.php       # محرك الشات بوت ونظام الـ RAG والحصص
│   ├── Router.php                # محرك التوجيه فائق السرعة
│   ├── Database.php              # مشغل PDO المعياري (Singleton + UTC)
│   ├── AdminController.php       # الأساس المحمي للوحة التحكم
│   ├── AdminSimpleController.php # محرك الـ CRUD التلقائي الفوري لأي جدول
│   ├── Auth.php                  # نظام المصادقة والصلاحيات (RBAC)
│   ├── SecurityGuard.php         # جدار الحماية وكشف محاولات الاختراق (IDS)
│   ├── TrafficRadar.php          # رادار الزوار وعناكب البحث وبوتات الـ AI
│   ├── ActivityLogger.php        # سجل التدقيق والعمليات الشامل (Audit Trail)
│   ├── Settings.php              # محرك الإعدادات المخزنة بقاعدة البيانات
│   ├── Upload.php                # مكتبة رفع ومعالجة الصور والملفات
│   └── Mailer.php                # خادم البريد (SMTP) والتنبيهات
├── controllers/
│   ├── admin/                    # متحكمات لوحة التحكم
│   ├── api/v1/                   # متحكمات REST API v1
│   └── ...                       # متحكمات الواجهة العامة
├── models/                       # نماذج البيانات
├── views/
│   ├── layouts/admin.php         # القالب الرئيسي للوحة التحكم (Dark/Light + RTL/LTR)
│   ├── admin/simple/             # قوالب الـ CRUD العامة (Index & Form)
│   ├── partials/ai-assistant.php # واجهة الشات بوت العائمة
│   └── ...                       # واجهات الواجهة الأمامية
└── database/
    ├── schema.sql                # مخطط الجداول المعياري النظيف
    └── seed.sql                  # البيانات الأولية والإعدادات الأساسية
```

---

## 3. بناء لوحة إدارة وقسم جديد (CRUD) في دقيقة واحدة

لإنشاء واجهة إدارة كاملة لأي جدول في قاعدة البيانات (مثل: `products` أو `orders` أو `clients`):

### الخطوة 1: إنشاء المتحكم عبر أداة Craft
```bash
php craft make:crud Products products "إدارة المنتجات"
```

### الخطوة 2: تخصيص حقول النموذج في المتحكم (`controllers/admin/ProductsController.php`):
```php
<?php

class ProductsController extends AdminSimpleController
{
    protected $resource = 'products';
    protected $table    = 'products';
    protected $title    = 'المنتجات';
    
    protected $fields = [
        'name' => [
            'label'    => 'اسم المنتج',
            'type'     => 'text',
            'required' => true
        ],
        'category_id' => [
            'label'   => 'القسم / التصنيف',
            'type'    => 'relation',
            'table'   => 'categories',
            'key'     => 'id',
            'display' => 'name'
        ],
        'price' => [
            'label' => 'السعر ($)',
            'type'  => 'number',
            'step'  => '0.01'
        ],
        'image' => [
            'label' => 'صورة المنتج',
            'type'  => 'image'
        ],
        'description' => [
            'label' => 'الوصف الكامل',
            'type'  => 'rich_text'
        ],
        'status' => [
            'label'   => 'الحالة',
            'type'    => 'select',
            'options' => ['active' => 'نشط ومتاح', 'inactive' => 'غير نشط']
        ],
        'is_featured' => [
            'label'        => 'منتج مميز',
            'type'         => 'switch',
            'switch_label' => 'إظهار المنتج في الواجهة الرئيسية'
        ],
    ];
}
```

### الخطوة 3: تسجيل المسارات في `index.php`:
```php
$router->get('/admin/products', 'ProductsController@index');
$router->get('/admin/products/create', 'ProductsController@create');
$router->post('/admin/products/store', 'ProductsController@store');
$router->get('/admin/products/{id}/edit', 'ProductsController@edit');
$router->post('/admin/products/{id}/update', 'ProductsController@update');
$router->post('/admin/products/{id}/delete', 'ProductsController@delete');
```

### الخطوة 4: إضافة العنصر للشريط الجانبي في `config/admin_menu.php`:
```php
[
    'title' => 'إدارة المنتجات',
    'path'  => 'admin/products',
    'icon'  => 'bi bi-box-seam',
],
```

🎉 **مبروك!** أصبح لديك الآن قسم إدارة كامل يدعم:
- البحث الفوري في الحقول والتصفية.
- رفع الصور ومعاينتها وتخزينها تلقائياً.
- القوائم المنسدلة المرتبطة بالجداول الأخرى تلقائياً.
- الترقيم والصفحات (Pagination) التلقائي.
- الحذف الآمن مع حماية CSRF وتسجيل العمليات في الـ Audit Log.

---

## 4. محرك الذكاء الاصطناعي والتبديل التلقائي (Universal AI Engine)

يوفر القالب محرك ذكاء اصطناعي موحد يدعم **7 مزودين عالميين** مع ميزة **التبديل التلقائي الفوري (Auto-Failover Chain)**:

### 1. توليد نصوص أو إجابة أسئلة:
```php
$response = AiService::prompt("اقترح 5 أفكار تسويقية مبتكرة لمتجر عطور");
```

### 2. محادثة متعددة الأدوار (Multi-turn Chat):
```php
$res = AiService::chat([
    ['role' => 'system', 'content' => 'أنت خبير مالي معتمد.'],
    ['role' => 'user', 'content' => 'كيف أحسب نقطة التعادل لمشروعي؟']
], [
    'temperature' => 0.4,
    'max_tokens'  => 800
]);

if ($res['success']) {
    echo $res['content'];
    // المزود الذي نفذ الطلب فعلياً بعد فحص السلسلة
    echo "تمت المعالجة عبر: " . $res['provider'] . " (" . $res['model'] . ")";
}
```

### 3. توليد مخرجات مهيكلة JSON:
```php
$data = AiService::generateJson("أنشئ مواصفات هاتف سامسونج S24 متضمناً المفاتيح: name, ram, battery, camera");
// يُرجع مصفوفة PHP جاهزة مباشرة
```

### مزودو الخدمة وسلسلة التبديل (`config/ai_providers.php`):
- `gemini` (Google Gemini 3.7 Flash / 2.5 Pro)
- `groq` (Groq Cloud Llama 3.3 70B - فائق السرعة)
- `deepseek` (DeepSeek-V3 / DeepSeek-R1)
- `openai` (GPT-4o / GPT-4o-mini)
- `omniroute` (Omniroute Gateway & Local Tunnels)
- `custom_api` (أي خادم محلي أو مخصص مثل Ollama / vLLM / OpenRouter)
- `opencode` & `mymemory` (محركات مجانية احتياطية بدون مفاتيح)

---

## 5. المساعد الذكي والشات بوت (AI Chatbot & RAG)

يحتوي القالب على شات بوت عائم تفاعلي (`core/AiChatAssistant.php`):
- **نظام RAG مدمج:** يبحث في محتوى وقاعدة بيانات المشروع للإجابة على الزائر بدقة وموثوقية.
- **إدارة الحصص اليومية (Daily Quotas):** تحديد عدد الأسئلة المجانية لكل زائر يومياً من لوحة الإعدادات، مع إمكانية شحن رصيد إضافي (Boosts) لمستخدم معين.
- **تخصيص النبرة:** رسمي، ودود، تعليمي مبسط، أو متوازن.
- **لوحة مراقبة المحادثات (`admin/ai-logs`):** مراجعة كافة محادثات الزوار، أداء المزودين، وتقييمات الإجابات.

---

## 6. نظام الأمان وجدار الحماية وسجل العمليات

1. **جدار الحماية الفوري (`core/SecurityGuard.php`):**
   - يفحص كافة الطلبات الواردة تلقائياً لاعتراض محاولات حقن SQL، هجمات XSS، عبور المسارات (Path Traversal)، والماسحات الآلية، وحظرها مع إرسال تنبيه أمني فوري في لوحة التحكم.
2. **حماية النماذج (`core/CSRF.php`):**
   - حماية تلقائية لكل النماذج عبر `<?= CSRF::field() ?>` والتحقق منها عبر `$this->postGuard()`.
3. **سجل التدقيق الشامل (`core/ActivityLogger.php`):**
   - يسجل آلياً أي عملية إضافة أو تعديل أو حذف ينفذها المشرفون مع فروقات القيم (Before / After diffs) وعنوان الـ IP والمتصفح.
4. **رادار الزوار والعناكب (`core/TrafficRadar.php`):**
   - رصد لحظي لحركة المرور، عناكب محركات البحث، وبوتات الـ AI مع قياس زمن استجابة الخادم.

---

## 7. بناء وتوسيع واجهات برمجة التطبيقات (REST API v1)

لبناء نقطة نهاية API مؤمنة برمز وصول (API Key):

```php
<?php

class ApiProductsController extends ApiV1BaseController
{
    public function index()
    {
        // التحقق من صلاحية مفتاح الـ API ومعدل الطلبات
        $this->requireAuth(['products.read']);
        
        $db = Database::getInstance();
        $products = $db->fetchAll("SELECT id, name, price, status FROM products WHERE status = 'active'");
        
        return $this->jsonSuccess([
            'products' => $products
        ], 'تم جلب المنتجات بنجاح');
    }
}
```

---

## 8. أداة سطر الأوامر (Craft CLI)

```bash
# فحص صحة النظام والبيئة والمجلدات وقاعدة البيانات
php craft doctor

# تجربة اتصال واستجابة مزود الذكاء الاصطناعي
php craft ai:test groq
php craft ai:test gemini

# توليد متحكم عادي
php craft make:controller CustomersController

# توليد متحكم API
php craft make:api ApiCustomersController

# توليد نموذج بيانات
php craft make:model Customer

# توليد لوحة إدارة كاملة
php craft make:crud customers customers "إدارة العملاء"
```

---

## 9. دليل النشر على الاستضافات والخوادم السحابية

### أ) النشر على الاستضافات المشتركة (cPanel / Apache):
1. ارفع ملفات المشروع إلى مجلد `public_html` أو مجلد فرعي.
2. أنشئ قاعدة بيانات من cPanel واستورد `database/schema.sql` ثم `database/seed.sql`.
3. أنشئ ملف `config/hosting.php` (مستنسخاً من `config/hosting.example.php`) وضع فيه بيانات الاتصال:
```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_cpanel_dbname');
define('DB_USER', 'your_cpanel_dbuser');
define('DB_PASS', 'your_strong_password');
```
4. ملف `.htaccess` المرفق مع المشروع جاهز ومعد لدعم الروابط النظيفة وضغط Gzip وحماية الملفات الحساسة تلقائياً.

---

## 🎯 الخلاصة

أصبح لديك الآن **قالب أساسي معياري متكامل**، خفيف الوزن، فائق السرعة، ومحصن أمنياً، يمنحك ولوحة تحكم احترافية ومحرك ذكاء اصطناعي وأدوات سقالة برمجية لبدء أي مشروع بثقة واحترافية مطلقة! 🚀
