<?php

class PageController extends Controller
{
    public function show($slug)
    {
        $slug = trim((string)$slug);
        $pageModel = new Page();
        $page = $pageModel->getBySlug($slug);

        if ($page) {
            return $this->view('pages/show', [
                'page' => $page
            ]);
        }

        // Built-in aliases fallback
        if (in_array($slug, ['privacy', 'privacy-policy'])) {
            return $this->privacy();
        }
        if (in_array($slug, ['terms', 'terms-of-service', 'terms-and-conditions'])) {
            return $this->terms();
        }
        if (in_array($slug, ['about', 'about-us'])) {
            return $this->about();
        }

        http_response_code(404);
        return $this->view('errors/404');
    }

    public function privacy()
    {
        $pageModel = new Page();
        $page = $pageModel->getBySlug('privacy') ?: $pageModel->getBySlug('privacy-policy');

        if (!$page) {
            $page = [
                'title_ar' => 'سياسة الخصوصية',
                'title_en' => 'Privacy Policy',
                'slug'     => 'privacy',
                'content_ar' => '<p>نحن في <strong>منصتي الذكية</strong> نولي خصوصية زوارنا ومستخدمينا أهمية قصوى. توضح هذه السياسة كيفية جمع واستخدام وحماية البيانات الشخصية وفق أعلى المعايير الأمنية.</p><h3>1. المعلومات التي نجمعها</h3><p>نقوم بجمع معلومات غير شخصية (مثل نوع المتصفح، نظام التشغيل، ونشاط الزيارة) لأغراض تحليلية وتحسين تجربة المستخدم، بالإضافة إلى المعلومات التي يقدمها المستخدم طواعية عند الاشتراك في النشرة البريدية أو التعليق أو استخدام نموذج التواصل.</p><h3>2. ملفات تعريف الارتباط (Cookies)</h3><p>نستخدم ملفات الكوكيز لتخصيص المحتوى وحفظ تفضيلات المظهر (الوضع الداكن/الفاتح) ولأغراض الأمان والحد من إساءة الاستخدام.</p><h3>3. حماية البيانات</h3><p>نطبق بروتوكولات حماية وتشفير متقدمة لمنع أي وصول غير مصرح به لبيانات المنصة.</p>'
            ];
        }

        return $this->view('pages/show', ['page' => $page]);
    }

    public function terms()
    {
        $pageModel = new Page();
        $page = $pageModel->getBySlug('terms') ?: $pageModel->getBySlug('terms-of-service');

        if (!$page) {
            $page = [
                'title_ar' => 'شروط الاستخدام',
                'title_en' => 'Terms of Service',
                'slug'     => 'terms',
                'content_ar' => '<p>أهلاً بكم في <strong>منصتي الذكية</strong>. استخدامك للمنصة يعني موافقتك الكاملة وغير المشروطة على الشروط والأحكام التالية:</p><h3>1. الملكية الفكرية</h3><p>كافة المنشورات والوسائط المقدمة هي ملك حصري للمنصة أو لشركائها ومصادرها المرخصة، ويُحظر إعادة نشرها أو نسخها لأغراض تجارية دون إذن كتابي مسبق مع الإشارة الصريحة للمصدر.</p><h3>2. سلوك المستخدم والتعليقات</h3><p>يلتزم المستخدم بالآداب العامة وعدم نشر أي محتوى مسيء أو مضلل أو ينتهك القوانين المعمول بها في مجتمع النقاش والتعليقات.</p><h3>3. إخلاء المسؤولية</h3><p>المحتوى المنشور يُقدَّم لأغراض إعلامية وتثقيفية، وتبذل المنصة قصارى جهدها لضمان دقة المعلومات المنشورة.</p>'
            ];
        }

        return $this->view('pages/show', ['page' => $page]);
    }

    public function about()
    {
        $pageModel = new Page();
        $page = $pageModel->getBySlug('about') ?: $pageModel->getBySlug('about-us');

        if (!$page) {
            $page = [
                'title_ar' => 'عن المنصة',
                'title_en' => 'About Us',
                'slug'     => 'about',
                'content_ar' => '<p><strong>منصتي الذكية</strong> هي منصة رقمية متكاملة قابلة للتخصيص، صُممت لتناسب أي مشروع أو فكرة: منصات محتوى، تعليم، خدمات، أو مدونات متخصصة، بأسلوب احترافي وموثوق.</p><h3>رؤيتنا</h3><p>تقديم نموذج جاهز ومرن يسهّل على أي فريق إطلاق موقعه بسرعة مع قدرات تحرير وإدارة وتخصيص كاملة، ومحتوى عالي الجودة وتحليلات واضحة.</p>'
            ];
        }

        return $this->view('pages/show', ['page' => $page]);
    }

    public function offline()
    {
        require APP_ROOT . '/views/offline.php';
    }
}
