<?php

class Page extends Model
{
    private static $tableChecked = false;

    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    private function ensureTable()
    {
        if (self::$tableChecked) return;
        self::$tableChecked = true;

        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS `pages` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `title_ar` varchar(255) NOT NULL,
                `title_en` varchar(255) DEFAULT NULL,
                `slug` varchar(190) NOT NULL,
                `content_ar` longtext DEFAULT NULL,
                `content_en` longtext DEFAULT NULL,
                `status` varchar(20) NOT NULL DEFAULT 'published',
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_pages_slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // Seed default essential pages if table is empty
            $count = (int) ($this->db->fetch("SELECT COUNT(*) as cnt FROM pages")['cnt'] ?? 0);
            if ($count === 0) {
                $this->db->query(
                    "INSERT INTO pages (title_ar, title_en, slug, content_ar, status) VALUES 
                    (:p_title_ar, :p_title_en, 'privacy', :p_content_ar, 'published'),
                    (:t_title_ar, :t_title_en, 'terms', :t_content_ar, 'published'),
                    (:a_title_ar, :a_title_en, 'about', :a_content_ar, 'published')",
                    [
                        ':p_title_ar' => 'سياسة الخصوصية',
                        ':p_title_en' => 'Privacy Policy',
                        ':p_content_ar' => '<p>نحن في <strong>منصة الأخبار التقنية</strong> نولي خصوصية زوارنا ومستخدمينا أهمية قصوى. توضح هذه السياسة كيفية جمع واستخدام وحماية البيانات الشخصية وفق أعلى المعايير الأمنية.</p><h3>1. المعلومات التي نجمعها</h3><p>نقوم بجمع معلومات غير شخصية (مثل نوع المتصفح، نظام التشغيل، ونشاط الزيارة) لأغراض تحليلية وتحسين تجربة المستخدم، بالإضافة إلى المعلومات التي يقدمها المستخدم طواعية عند الاشتراك في النشرة البريدية أو التعليق أو استخدام نموذج التواصل.</p><h3>2. ملفات تعريف الارتباط (Cookies)</h3><p>نستخدم ملفات الكوكيز لتخصيص المحتوى وحفظ تفضيلات المظهر (الوضع الداكن/الفاتح) ولأغراض الأمان والحد من إساءة الاستخدام.</p><h3>3. حماية البيانات</h3><p>نطبق بروتوكولات حماية وتشفير متقدمة لمنع أي وصول غير مصرح به لبيانات الموقع.</p>',

                        ':t_title_ar' => 'شروط الاستخدام',
                        ':t_title_en' => 'Terms of Service',
                        ':t_content_ar' => '<p>أهلاً بكم في <strong>منصة الأخبار التقنية</strong>. استخدامك للمنصة يعني موافقتك الكاملة وغير المشروطة على الشروط والأحكام التالية:</p><h3>1. الملكية الفكرية</h3><p>كافة المقالات والشروحات والتقارير والوسائط المنشورة هي ملك حصري للمنصة أو لشركائها ومصادرها المرخصة، ويُحظر إعادة نشرها أو نسخها لأغراض تجارية دون إذن كتابي مسبق مع الإشارة الصريحة للمصدر.</p><h3>2. سلوك المستخدم والتعليقات</h3><p>يلتزم المستخدم بالآداب العامة وعدم نشر أي محتوى مسيء أو مضلل أو ينتهك القوانين المعمول بها في مجتمع النقاش والتعليقات.</p><h3>3. إخلاء المسؤولية</h3><p>المحتوى المنشور يُقدَّم لأغراض إخبارية وتثقيفية، وتبذل المنصة قصارى جهدها لضمان دقة المعلومات والبيانات التقنية.</p>',

                        ':a_title_ar' => 'عن المنصة',
                        ':a_title_en' => 'About Us',
                        ':a_content_ar' => '<p><strong>منصة الأخبار التقنية</strong> هي وجهة عربية رقمية متكاملة تهدف إلى تغطية أحدث تطورات التكنولوجيا والذكاء الاصطناعي، الأمن السيبراني، الهواتف والأجهزة الذكية، والبرمجيات وشروحات التقنية بأسلوب احترافي وموثوق.</p><h3>رؤيتنا</h3><p>تمكين القارئ العربي من مواكبة الثورة التقنية العالمية بمحتوى عالي الجودة وتحليلات موضوعية معمقة وشروحات عملية خطوة بخطوة.</p>'
                    ]
                );
            }
        } catch (Throwable $e) {
            // Silently ignore if table already exists or DB read-only
        }
    }

    public function getBySlug($slug)
    {
        return $this->db->fetch(
            "SELECT * FROM pages WHERE slug = :slug AND status = 'published' LIMIT 1",
            [':slug' => $slug]
        );
    }

    public function getAllPublished()
    {
        return $this->db->fetchAll(
            "SELECT * FROM pages WHERE status = 'published' ORDER BY title_ar ASC"
        );
    }
}
