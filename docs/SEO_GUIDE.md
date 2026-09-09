# دليل تحسين محركات البحث والأرشفة الذكية (SEO & Discovery Guide)
## منصة الأخبار التقنية — Comprehensive SEO & Discovery Suite

تم بناء وتفعيل منظومة متكاملة لتحسين ظهور المنصة في محركات البحث (Google, Bing, Yahoo) وخدمات الأخبار والمحتوى الذكي (**Google News**, **Google Discover**) وفق أعلى المعايير العالمية لصحافة التقنية والـ **Core Web Vitals**.

---

## 🚀 1. ما تم إنجازه وتفعيله برمجياً داخل المنصة

### أ. البيانات الوصفية وبطاقات التواصل الاجتماعي الذكية (Dynamic Meta & OpenGraph Suite)
* **وسوم OpenGraph و Twitter Cards:** توليد بطاقات المشاركة من نوع `summary_large_image` تلقائياً لكل مقال، تصنيف، وتغطية حية مع الصورة البارزة والمقتطف الصحفي.
* **الرابط الدائم المعتمد (Canonical URL):** إدراج الوسم `<link rel="canonical" href="...">` لمنع أي مشاكل تتعلق بالمحتوى المكرر.
* **وسوم روبوتات البحث المتقدمة (Advanced Robots Meta):**
  ```html
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
  ```
  هذا الوسم يتيح لمحرك بحث Google عرض الصور بالحجم الكامل في **Google Discover** وصناديق الأخبار المميزة.

---

### ب. البيانات المنظمة والنتائج المنسقة (Schema.org & JSON-LD Rich Snippets)
* **Schema `NewsArticle`:** تضمين بيانات الناشر، الكاتب، تاريخ النشر وتاريخ التعديل، وصورة الغلاف لتأهيل المقالات للظهور في قسم **Top Stories** ونتائج الأخبار.
* **Schema `BreadcrumbList`:** إظهار مسار التصفح الهرمي (فتات الخبز) داخل نتائج Google مباشرة.
* **Schema `WebSite` & `SearchAction`:** تفعيل مربع البحث الداخلي المباشر الخاص بالمنصة في نتائج بحث Google (Sitelinks Search Box).
* **Schema `Organization`:** لتوثيق هوية وشعار المنصة الرسمي كجهة إعلامية تقنية موثوقة.

---

### ج. خرائط الموقع وملف الروبوتات الذكي (Smart XML Sitemap & Robots.txt)
* **خريطة الموقع الشاملة (`/sitemap.xml`):**
  * الرابط: [http://127.0.0.1:8000/sitemap.xml](http://127.0.0.1:8000/sitemap.xml)
  * تدعم ملحقات **Google News XML** (`<news:news>`) للأخبار الحديثة المنشورة خلال آخر 48 ساعة.
  * تشمل المقالات، التغطيات الحية (Live Blogs)، الشروحات والدروس (Tutorials)، التصنيفات، والصفحات الثابتة.
  * تحتوي على حقول الأولويات (`<priority>`) وتردد التحديث (`<changefreq>`) وتاريخ آخر تعديل (`<lastmod>`).
* **ملف الروبوتات الديناميكي (`/robots.txt`):**
  * الرابط: [http://127.0.0.1:8000/robots.txt](http://127.0.0.1:8000/robots.txt)
  * يُرشد عناكب البحث إلى خريطة الموقع تلقائياً ويحمي المسارات الإدارية والخاصة من الفهرسة العشوائية.

---

### د. لوحة تحكم إعدادات الـ SEO (Admin Management)
يمكن تخصيص كافة وسوم الموقع والشعارات ورموز التحقق مباشرة من لوحة الإدارة:
👉 **[http://127.0.0.1:8000/admin/settings?group=seo](http://127.0.0.1:8000/admin/settings?group=seo)**

وتتضمن:
1. **العنوان الافتراضي لمحركات البحث (`meta_title_default`)**.
2. **الوصف التعريفي الافتراضي (`meta_description_default`)**.
3. **الكلمات المفتاحية العامة (`meta_keywords`)**.
4. **صورة المشاركة الافتراضية للشبكات (`og_default_image`)**.
5. **كود التحقق من Google Search Console (`google_site_verification`)**.
6. **كود التحقق من Bing Webmaster Tools (`bing_site_verification`)**.
7. **معرّف تحليلات جوجل (`google_analytics_id` - GA4)**.
8. **حساب تويتر / إكس الرسمي (`twitter_site_handle`)**.
9. **محرر ملف الروبوتات المخصص (`robots_txt_custom`)**.

---

## 🌐 2. الخطوات الخارجية الموصى بالقيام بها (External Actions Required)

لضمان الفهرسة السريعة وتصدر المقالات في محركات البحث، يُرجى تطبيق الخطوات التالية:

### 1️⃣ تسجيل الموقع في Google Search Console
1. توجّه إلى [Google Search Console](https://search.google.com/search-console).
2. أضف نطاق موقعك عبر خيار **URL Prefix**.
3. اختر التحقق عبر **HTML Tag**.
4. انسخ كود الـ `content` فقط وضعه في حقل **`google_site_verification`** داخل [لوحة إعدادات الـ SEO](http://127.0.0.1:8000/admin/settings?group=seo).
5. بعد تأكيد الملكية، انتقل إلى قسم **Sitemaps** في القائمة الجانبية وأدخل: `sitemap.xml` ثم اضغط **Submit**.

---

### 2️⃣ التقديم كناشر أخباري معتمد في Google News Publisher Center
1. توجّه إلى [Google News Publisher Center](https://publishercenter.google.com/).
2. أضف موقعك وحدد:
   * **اسم المنصة:** منصة الأخبار التقنية.
   * **اللغة الأساسية:** العربية.
   * **التصنيف:** تكنولوجيا وعلوم (Technology & Science).
3. في قسم الأقسام (Sections):
   * أضف خريطة الموقع: `https://your-domain.com/sitemap.xml` أو `https://your-domain.com/news-sitemap.xml`.
4. ارفع شعار المنصة بخلفية شفافة (PNG) وشعار داكن للمظهر الليلي.
5. أرسل المنصة للمراجعة لاعتمادها كمصدر إخباري رسمي.

---

### 3️⃣ الربط مع Bing Webmaster Tools
1. توجّه إلى [Bing Webmaster Tools](https://www.bing.com/webmasters).
2. اضغط على **Import from Google Search Console** لاستيراد كافة إعدادات النطاق وخريطة الموقع بنقرة واحدة تلقائياً دون أي خطوات يدوية إضافية.
3. أو انسخ رمز التحقق وضعه في حقل **`bing_site_verification`** في لوحة التحكم.

---

### 4️⃣ تفعيل تحليلات Google Analytics 4 (GA4)
1. أنشئ حساباً وخصائص موقع في [Google Analytics](https://analytics.google.com/).
2. انسخ **معرّف القياس (Measurement ID)** الذي يبدأ بـ `G-` (مثال: `G-XXXXXXXXXX`).
3. ضعه في حقل **`google_analytics_id`** داخل لوحة الإعدادات.
4. بمجرد الحفظ، سيبدأ النظام تلقائياً في تتبع الزوار ومصادر الزيارات والأجهزة ومعدل القراءة والتفاعل دون الحاجة لتعديل أي ملف برمجي.

---

### 5️⃣ تحسينات خادم الويب والـ CDN (موصى بها عند النشر الحي)
* **Cloudflare:** تفعيل الـ SSL التلقائي (Always Use HTTPS)، وضغط Gzip/Brotli، وتفعيل Auto Minify للـ CSS و JS.
* **ذاكرة التخزين المؤقت:** ضبط رأس الاستجابة `Cache-Control` للملفات الثابتة والصور لتسريع معدل Core Web Vitals (LCP & FID).

---

> 📝 **ملاحظة:** تم حفظ هذا التوثيق ليكون مرجعاً تقنياً وإدارياً دائماً لفريق العمل وإدارة المنصة.
