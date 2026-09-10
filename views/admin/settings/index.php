<?php
$title = 'لوحة التحكم في الإعدادات الشاملة';
$groupTitles = [
 'general' => 'الإعدادات العامة',
 'ai_translation' => 'مزودو الذكاء والترجمة',
 'appearance' => ' المظهر والتصميم',
  'ai_assistant' => ' المحادث الذكي',
 'articles' => 'المقالات والتحرير',
 'comments' => 'التعليقات والمراجعة',
 'users' => 'المستخدمون والمصادقة',
 'notifications' => 'الإشعارات والبريد',
 'seo' => 'محركات البحث (SEO)',
 'security' => 'الأمان والحماية',
 'performance' => 'الأداء والتخزين المؤقت',
 'sharing' => 'المشاركة والتواصل',
 'newsletter' => 'النشرة البريدية',
 'ads' => 'الإعلانات والربح',
 'social' => 'وسائل التواصل الاجتماعي',
 'legal' => 'الخصوصية والقانونية',
 'news_reader' => 'تجربة القارئ'
];

// Predefined Select Dropdown Options for Restricted Settings
$selectDropdowns = [
 // General
 'site_status' => [
 'active' => 'متاح للعامة ونشط (Active)',
 'maintenance' => 'وضع الصيانة (Maintenance Mode)',
 'private' => 'خاص للمسجلين فقط (Private Mode)',
 ],
 'default_language' => [
 'ar' => 'العربية (Arabic)',
 'en' => 'الإنجليزية (English)',
 ],
 'timezone' => [
 'Asia/Riyadh' => 'الرياض (Asia/Riyadh +03:00)',
 'Asia/Dubai' => 'دبي (Asia/Dubai +04:00)',
 'Africa/Cairo' => 'القاهرة (Africa/Cairo +02:00)',
 'Asia/Kuwait' => 'الكويت (Asia/Kuwait +03:00)',
 'Asia/Qatar' => 'الدوحة (Asia/Qatar +03:00)',
 'Asia/Bahrain' => 'المنامة (Asia/Bahrain +03:00)',
 'Asia/Muscat' => 'مسقط (Asia/Muscat +04:00)',
 'Asia/Amman' => 'عمّان (Asia/Amman +03:00)',
 'Asia/Beirut' => 'بيروت (Asia/Beirut +03:00)',
 'Asia/Jerusalem' => 'القدس (Asia/Jerusalem +03:00)',
 'Asia/Baghdad' => 'بغداد (Asia/Baghdad +03:00)',
 'Africa/Casablanca' => 'الدار البيضاء (Africa/Casablanca +01:00)',
 'Africa/Tunis' => 'تونس (Africa/Tunis +01:00)',
 'Africa/Algiers' => 'الجزائر (Africa/Algiers +01:00)',
 'UTC' => 'التوقيت العالمي المنسق (UTC +00:00)',
 'Europe/London' => 'لندن (Europe/London +00:00)',
 'Europe/Paris' => 'باريس (Europe/Paris +01:00)',
 'America/New_York' => 'نيويورك (America/New_York -05:00)',
 ],
 'date_format' => [
 'Y-m-d H:i' => '2026-08-16 14:30 (سنة-شهر-يوم)',
 'd/m/Y H:i' => '16/08/2026 14:30 (يوم/شهر/سنة)',
 'F j, Y' => 'August 16, 2026 (تاريخ نصي كامل)',
 'Y-m-d' => '2026-08-16 (تاريخ فقط)',
 ],

// AI & Translation Provider Management
  'ai_provider' => [
  'omniroute' => 'نفق Omniroute المباشر (Omniroute Gateway & Tunnel - متصل وجاهز)',
  'gemini' => 'Google Gemini (1.5 Flash / 2.0 Flash / Pro)',
  'groq' => 'Groq Cloud (Llama 3.3 70B / 8B - فائق السرعة مجاناً)',
  'deepseek' => 'DeepSeek (DeepSeek V3 / R1)',
  'openai' => 'OpenAI (GPT-4o / GPT-4o-mini)',
  'custom_api' => 'مزود مخصص (OpenRouter / Ollama / Local Endpoint)',
  'opencode' => 'OpenCode Zen المجاني (بدون مفتاح - حصة IP يومية، ينصح به كاحتياط)',
  'mymemory' => 'المترجم المجاني المدمج (MyMemory + القاموس التقني - متاح دائماً)',
  'gtx' => 'Google Translate Web (احتياطي)',
  ],

  // AI Assistant Chat
  'ai_assistant_provider' => [
  'default' => 'نفس مزود المنصة العام (يُفضَّل)',
  'omniroute' => 'نفق Omniroute المباشر',
  'gemini' => 'Google Gemini',
  'groq' => 'Groq Cloud',
  'openai' => 'OpenAI',
  'deepseek' => 'DeepSeek',
  'custom_api' => 'خادم API مخصص',
  'opencode' => 'OpenCode Zen المجاني (بدون مفتاح)',
  ],
  'ai_assistant_tone' => [
  'balanced' => 'متوازن (سرع ما هو قياسي)',
  'friendly' => 'ودود وخفيف',
  'formal' => 'رسمي وصحفي',
  'educational' => 'تعليمي مبسّط للقراء',
  ],
  'ai_assistant_pages' => [
  'all' => 'كل صفحات الموقع',
  'home' => 'الرئيسية فقط',
  'articles' => 'صفحات المقالات فقط',
  'none' => 'معطل - لا يظهر في أي صفحة',
  ],
  'omniroute_model' => [
 'antigravity/gemini-3.7-flash-high' => 'antigravity/gemini-3.7-flash-high (Gemini 3.7 Flash High - موصى به)',
 'opencode/gemini-3.7-flash' => 'opencode/gemini-3.7-flash (Gemini 3.7 Flash - متصل)',
 'auto/gemini' => 'auto/gemini (تلقائي - أسرع نموذج Gemini متاح)',
 'auto/fast' => 'auto/fast (تلقائي - أسرع استجابة)',
 'auto/best-fast' => 'auto/best-fast (تلقائي - توازن السرعة والجودة)',
 'dva/gemini-3-7-flash-high' => 'dva/gemini-3-7-flash-high (Gemini 3.7 High - متصل)',
 'cheaperinference/claude-sonnet-4.5-high' => 'cheaperinference/claude-sonnet-4.5-high (Claude Sonnet 4.5 - متصل)',
 'cheaperinference/claude-haiku-4.5-high' => 'cheaperinference/claude-haiku-4.5-high (Claude Haiku 4.5 - متصل)',
 ],
 'groq_model' => [
 'llama-3.3-70b-versatile' => 'Llama 3.3 70B Versatile (موصى به - ذكي وسريع)',
 'llama-3.1-8b-instant' => 'Llama 3.1 8B Instant (فائق الخفة والسرعة)',
 'mixtral-8x7b-32768' => 'Mixtral 8x7B (32k Context Window)',
 'deepseek-r1-distill-llama-70b' => 'DeepSeek R1 Distill 70B',
 ],
 'deepseek_model' => [
 'deepseek-chat' => 'DeepSeek-V3 (deepseek-chat - ذكي واقتصادي جداً)',
 'deepseek-reasoner' => 'DeepSeek-R1 (deepseek-reasoner - تفكير واستدلال)',
 ],
 'openai_model' => [
 'gpt-4o-mini' => 'GPT-4o-mini (سريع، اقتصادي، ذكي جداً - موصى به)',
 'gpt-4o' => 'GPT-4o (أعلى جودة صياغة صحفية رصينة)',
 'gpt-4-turbo' => 'GPT-4 Turbo',
 'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
 ],
'gemini_model' => [
  'gemini-3.7-flash' => 'gemini-3.7-flash (Gemini 3.7 Flash - موصى به ومفعل)',
  'gemini-3.6-flash' => 'gemini-3.6-flash (Gemini 3.6 Flash - فائق السرعة)',
  'gemini-3.5-flash' => 'gemini-3.5-flash (Gemini 3.5 Flash)',
  'gemini-2.5-pro' => 'gemini-2.5-pro (Gemini 2.5 Pro)',
  ],
  'opencode_model' => [
  'big-pickle' => 'big-pickle (مختبر ويعمل - يوصى به للاحتياط المجاني)',
  'mimo-v2.5-free' => 'mimo-v2.5-free (نموذج مجاني)',
  'nemotron-3-ultra-free' => 'nemotron-3-ultra-free (نموذج مجاني)',
  ],

    // Appearance
    'site_theme_template' => [
        'editorial_verge'    => 'النمط التحريري العالمي (The Verge & Wired Style) — بطاقات سينمائية وخطوط عصرية وتوزيع متوازن',
        'bento_grid'         => 'شبكة بينتو بوكس العصرية (Modern Bento Grid — أحدث معيار عالمي ببطاقات غير متماثلة مستوحاة من Apple و Verge)',
        'digital_broadsheet' => 'الصحيفة الرقمية العريقة (Digital Broadsheet — مانشيتات صحفية رصينة و3 أعمدة متوازنة بأسلوب Bloomberg)',
        'classic_techwd'     => 'مجلة عالم التقنية الكلاسيكية (Tech-WD Style) — بطاقات أفقية وعمودية كلاسيكية مع مظهر مجلة عربية وأرشيف غني',
        'minimal_techcrunch' => 'النمط المبسط والنقي (TechCrunch & Apple Style) — مساحات واسعة وتصميم هادئ يركز على سرعة القراءة',
        'cyber_futuristic'   => 'النمط السيبراني والذكاء الاصطناعي (Next-Gen Cyber AI) — تدرجات نيون ورادار متطور ومظهر مستقبلي',
    ],
 'theme_default' => [
 'dark' => 'النمط الداكن الفاخر (Dark Mode)',
 'light' => 'النمط الفاتح النقي (Light Mode)',
 'auto' => 'تلقائي حسب جهاز الزائر (Auto)',
 ],
 'font_family' => [
 'IBM Plex Sans Arabic' => 'IBM Plex Sans Arabic (الخط التحريري الفاخر - أعلى مقروءة وراحة للعين - موصى به عالمياً)',
 'Tajawal' => 'Tajawal (تجوال - هندسي ناعم وخفيف ومريح للقراءة السريعة)',
 'Readex Pro' => 'Readex Pro (عصري وجذاب ومصمم لتقليل إجهاد العين)',
 'Cairo' => 'Cairo (القاهرة - صحفي جريء وممتاز للعناوين البارزة)',
 ],

 // Articles & Editorial
 'articles_per_page' => [
 '6' => '6 مقالات بالصفحة',
 '9' => '9 مقالات بالصفحة',
 '12' => '12 مقالاً بالصفحة',
 '18' => '18 مقالاً بالصفحة',
 '24' => '24 مقالاً بالصفحة',
 ],
 'articles_expiry_days' => [
 '3' => '3 أيام (سريعة جداً)',
 '7' => '7 أيام (أسبوع)',
 '14' => '14 يوماً (أسبوعان)',
 '30' => '30 يوماً (شهر - موصى به)',
 '60' => '60 يوماً (شهران)',
 '90' => '90 يوماً (ثلاثة أشهر)',
 '0' => 'تعطيل الأرشفة التلقائية',
 ],
 'rss_fresh_days' => [
 '7' => '7 أيام',
 '14' => '14 يوماً',
 '30' => '30 يوماً (موصى به)',
 '60' => '60 يوماً',
 '90' => '90 يوماً',
 ],
 'sitemap_days' => [
 '30' => '30 يوماً',
 '90' => '90 يوماً',
 '180' => '180 يوماً (موصى به)',
 '365' => 'سنة كاملة (365 يوماً)',
 ],
 'default_article_status' => [
 'published' => 'منشور مباشرة (Published)',
 'draft' => 'مسودة للمراجعة (Draft)',
 ],

 // Users
 'default_user_role' => [
 'reader' => 'قارئ / عضو عادي (Reader)',
 'author' => 'كاتب / محرر (Author)',
 'moderator' => 'مشرف مراجعة (Moderator)',
 'admin' => 'مدير نظام (Administrator)',
 ],

 // Mail & SMTP
 'mail_mailer' => [
 'smtp' => ' خادم SMTP مخصص (Real SMTP)',
 'mail' => 'دالة PHP mail() المحلية',
 'log' => 'وضع التطوير وسجل الملفات (Log)',
 ],
 'mail_encryption' => [
 'tls' => 'TLS (الافتراضي لمنفذ 587)',
 'ssl' => 'SSL (لمنفذ 465)',
 'none' => ' بدون تشفير (منفذ 25)',
 ],

 // Performance & Caching
 'cache_driver' => [
 'file' => 'ملفات محلية (File Cache)',
 'array' => 'ذاكرة مؤقتة للطلب فقط (Array/Runtime)',
 ],

 // Reader Experience
 'reader_time_ago_mode' => [
 'relative' => 'الوقت النسبي فقط (قبل 5 دقائق / قبل ساعة...)',
 'absolute' => 'التاريخ الثابت فقط (2026-09-08 14:30)',
 'both' => 'الاثنان معاً (نسبي في البطاقة + التاريخ يظهر عند التمرير)',
 ],
];

// Special Brand Image Keys
$brandImageKeys = ['site_logo', 'site_favicon', 'default_og_image', 'site_logo_dark'];
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1">لوحة التحكم في الإعدادات الشاملة</h2>
 <p class="text-muted mb-0">تحكم كامل ومفصل في كافة جوانب وخصائص المنصة وميزاتها المتقدمة ومزودي الذكاء الاصطناعي.</p>
 </div>
</div>

<?php if ($msg = Session::getFlash('success')): ?>
 <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
 <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($msg) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<?php $dbHealth = class_exists('DbHealth') ? DbHealth::check(Database::getInstance()) : null; ?>
<?php if ($dbHealth): ?>
<!-- Database Health / Migration Status Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
 <div class="d-flex align-items-center gap-2">
 <i class="bi bi-database-check fs-5 <?= $dbHealth['ok'] ? 'text-success' : 'text-warning' ?>"></i>
 <h6 class="card-title mb-0 fw-bold">حالة قاعدة البيانات والترحيلات (تحقق ذاتي)</h6>
 </div>
 <div class="d-flex align-items-center gap-2">
 <span class="badge bg-light text-dark border"><?= (int) $dbHealth['settingsTotal'] ?> إعداد</span>
 <span class="badge bg-light text-dark border"><?= (int) $dbHealth['settingsGroups'] ?> مجموعة</span>
 <span class="badge <?= $dbHealth['ok'] ? 'bg-success' : 'bg-warning text-dark' ?>">
 <?= $dbHealth['ok'] ? 'قاعدة البيانات محدّثة ✓' : 'إجراءات مطلوبة ⚠' ?>
 </span>
 <a class="btn btn-sm btn-outline-secondary py-0" data-bs-toggle="collapse" href="#dbHealthBody" role="button" aria-expanded="false" aria-controls="dbHealthBody">التفاصيل</a>
 </div>
 </div>
 <div class="collapse" id="dbHealthBody">
 <div class="card-body p-3 bg-light">
 <p class="small text-muted mb-2">قائمة تحقق غير مُعدِّلة: صفوف الإعدادات الأساسية بلغاتها الحالية + أعمدة ترحيلات rss_sources. إذا ظهر أي بند ناقص فلن يظهر حقلُه في اللوحة.</p>
 <ul class="list-unstyled row row-cols-1 row-cols-md-2 g-1 mb-0">
 <?php foreach ($dbHealth['checks'] as $c): ?>
 <li class="col small">
 <i class="bi bi-<?= $c['ok'] ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' ?> me-1"></i>
 <span class="fw-bold"><?= htmlspecialchars($c['label'], ENT_QUOTES, 'UTF-8') ?></span>
 <?php if (!$c['required']): ?><span class="badge bg-secondary ms-1">اختياري</span><?php endif; ?>
 <?php if (!$c['ok']): ?><span class="text-muted d-block ps-4"><?= htmlspecialchars($c['hint'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
 </li>
 <?php endforeach; ?>
 </ul>
 </div>
 </div>
</div>
<?php endif; ?>

<!-- Tabs Navigation -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-2">
 <ul class="nav nav-pills flex-wrap gap-2">
 <?php foreach ($allGroups as $g): ?>
 <li class="nav-item">
 <a class="nav-link <?= $currentGroup === $g ? 'active fw-bold' : 'text-dark' ?>" href="<?= app_url('admin/settings?group=' . urlencode($g)) ?>" style="<?= $currentGroup === $g ? 'background:linear-gradient(135deg,#00f2fe,#9d4edd);color:#fff' : '' ?>">
 <?= $groupTitles[$g] ?? ucfirst($g) ?>
 </a>
 </li>
 <?php endforeach; ?>
 </ul>
</div>

<!-- Settings Form -->
<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
 <h5 class="card-title mb-0 fw-bold"><?= $groupTitles[$currentGroup] ?? ucfirst($currentGroup) ?></h5>
 <div class="d-flex align-items-center gap-2">
 <?php if ($currentGroup === 'ai_translation'): ?>
 <a href="<?= app_url('admin/translation-logs') ?>" class="btn btn-sm btn-outline-info fw-bold">
 <i class="bi bi-journal-text me-1"></i> سجلات وأخطاء الترجمة الحية
 </a>
 <?php endif; ?>
 <span class="badge bg-light text-dark border"><?= count($settings) ?> إعداد</span>
 </div>
 </div>
 <div class="card-body p-4">

 <?php if ($currentGroup === 'ai_translation'): ?>
 <!-- Quick Live AI Providers Testing Toolbar in Settings -->
 <div class="p-3 mb-4 rounded-4 bg-light border">
 <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
 <div>
 <strong class="text-dark"><i class="bi bi-cpu text-primary me-1"></i> اختبار اتصال المزودين المباشر (Live Test):</strong>
 <small class="text-muted d-block">اضغط على أي مزود لاختبار صلاحية الاتصال بالمفتاح أو النفق المكتوب أدناه واستلام نتيجة فورية.</small>
 </div>
 </div>
 <div class="d-flex gap-2 flex-wrap mb-2">
 <button type="button" class="btn btn-sm btn-dark fw-bold btn-test-provider" data-provider="omniroute" style="background:#0f172a;border-color:#00d2ff">
 <span class="provider-spinner spinner-border spinner-border-sm me-1 d-none"></span>
 فحص نفق Omniroute
 </button>
 <button type="button" class="btn btn-sm btn-outline-primary fw-bold btn-test-provider" data-provider="gemini">
 <span class="provider-spinner spinner-border spinner-border-sm me-1 d-none"></span>
 فحص Google Gemini
 </button>
 <button type="button" class="btn btn-sm btn-outline-success fw-bold btn-test-provider" data-provider="groq">
 <span class="provider-spinner spinner-border spinner-border-sm me-1 d-none"></span>
 فحص Groq Cloud
 </button>
 <button type="button" class="btn btn-sm btn-outline-info fw-bold btn-test-provider" data-provider="deepseek">
 <span class="provider-spinner spinner-border spinner-border-sm me-1 d-none"></span>
 فحص DeepSeek
 </button>
 <button type="button" class="btn btn-sm btn-outline-secondary fw-bold btn-test-provider" data-provider="openai">
 <span class="provider-spinner spinner-border spinner-border-sm me-1 d-none"></span>
 فحص OpenAI
 </button>
<button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold btn-test-provider" data-provider="mymemory">
  <span class="provider-spinner spinner-border spinner-border-sm me-1 d-none"></span>
  فحص المترجم المجاني
  </button>
  <button type="button" class="btn btn-sm fw-bold btn-test-provider btn-test-oc" data-provider="opencode">
  <span class="provider-spinner spinner-border spinner-border-sm me-1 d-none"></span>
  فحص OpenCode Zen (مجاني)
  </button>
  </div>

 <!-- Test Output Box in Settings -->
 <div id="testResultBox" class="p-3 rounded-3 bg-white border d-none mt-2">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <div id="testResultHeader" class="d-flex align-items-center gap-2"></div>
 <button type="button" class="btn-close btn-sm" onclick="document.getElementById('testResultBox').classList.add('d-none')"></button>
 </div>
 <div id="testResultBody" class="small"></div>
 </div>
 </div>
 <?php endif; ?>

 <form action="<?= app_url('admin/settings/update') ?>" method="post" enctype="multipart/form-data">
 <?= CSRF::field() ?>
 <input type="hidden" name="_group" value="<?= htmlspecialchars($currentGroup) ?>">

 <div class="row g-4">
 <?php foreach ($settings as $s): ?>
 
 <?php if (in_array($s['key'], $brandImageKeys, true)): ?>
 <!-- Brand Assets (Logo / Favicon / OG Image) with live preview & instant upload -->
 <div class="col-md-6">
 <div class="p-3 bg-light rounded-4 border shadow-sm h-100">
 <div class="d-flex justify-content-between align-items-start mb-2">
 <div>
 <label class="form-label fw-bold text-dark mb-1" for="setting_<?= htmlspecialchars($s['key']) ?>">
 <?= htmlspecialchars($s['label_ar'] ?: $s['key']) ?>
 </label>
 <small class="text-muted d-block" style="font-size:0.8rem">
 <?= htmlspecialchars($s['description_ar'] ?: $s['key']) ?>
 </small>
 </div>
 <div id="preview_box_<?= htmlspecialchars($s['key']) ?>" class="p-1 bg-white border rounded-3 text-center" style="min-width: 50px; min-height: 40px; display: <?= !empty($s['value']) ? 'block' : 'none' ?>;">
 <img id="preview_img_<?= htmlspecialchars($s['key']) ?>" src="<?= !empty($s['value']) ? htmlspecialchars(str_starts_with($s['value'], 'http') ? $s['value'] : app_url($s['value'])) : '' ?>" alt="معاينة" style="max-height: 38px; max-width: 60px; object-fit: contain;">
 </div>
 </div>
 
 <div class="input-group mb-2">
 <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
 <input type="text" 
 id="setting_<?= htmlspecialchars($s['key']) ?>" 
 name="settings[<?= htmlspecialchars($s['key']) ?>]" 
 class="form-control font-monospace small" 
 placeholder="رابط الصورة أو مسارها (مثال: uploads/brand/logo.png)"
 value="<?= htmlspecialchars($s['value'] ?? '') ?>">
 </div>

 <div class="d-flex align-items-center gap-2">
 <label id="upload_label_<?= htmlspecialchars($s['key']) ?>" class="btn btn-sm btn-outline-primary mb-0 w-100 text-truncate" style="cursor: pointer;">
 <i class="bi bi-cloud-arrow-up-fill me-1"></i> اختر ملفاً للرفع والتطبيق الفوري
 <input type="file" 
 id="file_input_<?= htmlspecialchars($s['key']) ?>"
 name="<?= htmlspecialchars($s['key']) ?>_upload_file" 
 accept="image/*,.ico" 
 style="display: none;" 
 onchange="uploadBrandAssetInstant('<?= htmlspecialchars($s['key']) ?>', this)">
 </label>
 </div>
 </div>
 </div>

 <?php elseif ($s['key'] === 'site_theme_template'): ?>
 <!-- Visual Template Selector (Verge, Tech-WD, Minimalist, Cyber) -->
 <div class="col-12">
 <div class="p-4 bg-light rounded-4 border shadow-sm">
 <div class="d-flex justify-content-between align-items-center mb-3">
 <div>
 <h5 class="fw-bold text-dark mb-1">
 <i class="bi bi-palette-fill text-primary me-1"></i> <?= htmlspecialchars($s['label_ar'] ?: $s['key']) ?>
 </h5>
 <small class="text-muted"><?= htmlspecialchars($s['description_ar'] ?: $s['key']) ?></small>
 </div>
                                <span class="badge bg-primary px-3 py-2 rounded-pill">6 قوالب حية وتخطيطات متوفرة</span>
                            </div>

                            <div class="row g-3">
                                <!-- 1. Editorial Verge -->
                                <div class="col-md-6 col-xl-4">
                                    <label class="card h-100 border-2 rounded-4 p-3 position-relative cursor-pointer transition-all template-card <?= $s['value'] === 'editorial_verge' ? 'border-primary bg-white shadow' : 'border-light-subtle bg-white' ?>" style="cursor:pointer">
                                        <input type="radio" name="settings[site_theme_template]" value="editorial_verge" class="d-none" <?= $s['value'] === 'editorial_verge' ? 'checked' : '' ?> onchange="document.querySelectorAll('.template-card').forEach(c=>c.classList.remove('border-primary','shadow')); this.closest('.template-card').classList.add('border-primary','shadow');">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-dark text-cyan fw-bold">The Verge & Wired</span>
                                            <span class="badge bg-info-subtle text-info fw-bold">موصى به</span>
                                        </div>
                                        <div class="rounded-3 p-2 mb-2 text-center" style="background:#080c14;color:#00f2fe;font-size:0.75rem">
                                            <div class="fw-bold mb-1">بأسلوب المجلات التحريرية</div>
                                            <div class="text-white small">بطاقات سينمائية 16:9 + خطوط حديثة</div>
                                        </div>
                                        <h6 class="fw-bold mb-1 text-dark">التحريري الفاخر</h6>
                                        <p class="text-muted small mb-0" style="font-size:0.78rem">خطوط عصرية، تباعد أسطر مريح، رادار ذكاء اصطناعي، وتوزيع متوازن للبطاقات.</p>
                                    </label>
                                </div>

                                <!-- 2. Modern Bento Grid -->
                                <div class="col-md-6 col-xl-4">
                                    <label class="card h-100 border-2 rounded-4 p-3 position-relative cursor-pointer transition-all template-card <?= $s['value'] === 'bento_grid' ? 'border-primary bg-white shadow' : 'border-light-subtle bg-white' ?>" style="cursor:pointer">
                                        <input type="radio" name="settings[site_theme_template]" value="bento_grid" class="d-none" <?= $s['value'] === 'bento_grid' ? 'checked' : '' ?> onchange="document.querySelectorAll('.template-card').forEach(c=>c.classList.remove('border-primary','shadow')); this.closest('.template-card').classList.add('border-primary','shadow');">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-primary text-white fw-bold">Modern Bento Grid</span>
                                            <span class="badge bg-primary-subtle text-primary fw-bold">الأحدث عالمياً</span>
                                        </div>
                                        <div class="rounded-3 p-2 mb-2 text-center" style="background:#0f172a;color:#38bdf8;font-size:0.75rem">
                                            <div class="fw-bold mb-1">مصفوفة بينتو تفاعلية</div>
                                            <div class="text-white small">بطاقات غير متماثلة + نبضات حية</div>
                                        </div>
                                        <h6 class="fw-bold mb-1 text-dark">شبكة بينتو بوكس</h6>
                                        <p class="text-muted small mb-0" style="font-size:0.78rem">تخطيط هندسي مبتكر بأسلوب Apple و Linear يبرز قصة الغلاف والنبضات العاجلة.</p>
                                    </label>
                                </div>

                                <!-- 3. Digital Broadsheet -->
                                <div class="col-md-6 col-xl-4">
                                    <label class="card h-100 border-2 rounded-4 p-3 position-relative cursor-pointer transition-all template-card <?= $s['value'] === 'digital_broadsheet' ? 'border-primary bg-white shadow' : 'border-light-subtle bg-white' ?>" style="cursor:pointer">
                                        <input type="radio" name="settings[site_theme_template]" value="digital_broadsheet" class="d-none" <?= $s['value'] === 'digital_broadsheet' ? 'checked' : '' ?> onchange="document.querySelectorAll('.template-card').forEach(c=>c.classList.remove('border-primary','shadow')); this.closest('.template-card').classList.add('border-primary','shadow');">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-dark text-warning fw-bold">Bloomberg & NYT</span>
                                            <span class="badge bg-secondary-subtle text-dark fw-bold">صحافة رصينة</span>
                                        </div>
                                        <div class="rounded-3 p-2 mb-2 text-center" style="background:#111827;color:#f3f4f6;font-size:0.75rem">
                                            <div class="fw-bold mb-1">مانشيت + 3 أعمدة متوازنة</div>
                                            <div class="text-white small">موجز عاجل + مقالات الرأي</div>
                                        </div>
                                        <h6 class="fw-bold mb-1 text-dark">الصحيفة الرقمية العريقة</h6>
                                        <p class="text-muted small mb-0" style="font-size:0.78rem">تنسيق جريدة ورقية حديثة بأعلى معايير المصداقية الصحفية والفصل الدقيق للمحاور.</p>
                                    </label>
                                </div>

                                <!-- 4. Classic Tech-WD -->
                                <div class="col-md-6 col-xl-4">
                                    <label class="card h-100 border-2 rounded-4 p-3 position-relative cursor-pointer transition-all template-card <?= $s['value'] === 'classic_techwd' ? 'border-primary bg-white shadow' : 'border-light-subtle bg-white' ?>" style="cursor:pointer">
                                        <input type="radio" name="settings[site_theme_template]" value="classic_techwd" class="d-none" <?= $s['value'] === 'classic_techwd' ? 'checked' : '' ?> onchange="document.querySelectorAll('.template-card').forEach(c=>c.classList.remove('border-primary','shadow')); this.closest('.template-card').classList.add('border-primary','shadow');">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-danger text-white fw-bold">عالم التقنية Tech-WD</span>
                                            <span class="badge bg-danger-subtle text-danger fw-bold">مجلة عربية</span>
                                        </div>
                                        <div class="rounded-3 p-2 mb-2 text-center" style="background:#f8fafc;border:1px solid #e2e8f0;color:#e11d48;font-size:0.75rem">
                                            <div class="fw-bold mb-1">كلاسيكي معتمد</div>
                                            <div class="text-dark small">بطاقات أفقية + شريط جانبي غني</div>
                                        </div>
                                        <h6 class="fw-bold mb-1 text-dark">مجلة عالم التقنية</h6>
                                        <p class="text-muted small mb-0" style="font-size:0.78rem">بطاقات أفقية وعمودية كلاسيكية مع أيقونات التاريخ والكاتب وشريط جانبي وتنسيق أرشيفي.</p>
                                    </label>
                                </div>

                                <!-- 5. Minimalist TechCrunch -->
                                <div class="col-md-6 col-xl-4">
                                    <label class="card h-100 border-2 rounded-4 p-3 position-relative cursor-pointer transition-all template-card <?= $s['value'] === 'minimal_techcrunch' ? 'border-primary bg-white shadow' : 'border-light-subtle bg-white' ?>" style="cursor:pointer">
                                        <input type="radio" name="settings[site_theme_template]" value="minimal_techcrunch" class="d-none" <?= $s['value'] === 'minimal_techcrunch' ? 'checked' : '' ?> onchange="document.querySelectorAll('.template-card').forEach(c=>c.classList.remove('border-primary','shadow')); this.closest('.template-card').classList.add('border-primary','shadow');">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-success text-white fw-bold">TechCrunch & Apple</span>
                                            <span class="badge bg-success-subtle text-success fw-bold">نقي وسريع</span>
                                        </div>
                                        <div class="rounded-3 p-2 mb-2 text-center" style="background:#ffffff;border:1px dashed #cbd5e1;color:#0f172a;font-size:0.75rem">
                                            <div class="fw-bold mb-1">بساطة مطلقة</div>
                                            <div class="text-muted small">مساحات واسعة + تركيز عالي</div>
                                        </div>
                                        <h6 class="fw-bold mb-1 text-dark">التقني المبسط والنقي</h6>
                                        <p class="text-muted small mb-0" style="font-size:0.78rem">تصميم نظيف وهادئ بدون بهرجة يركز بالكامل على سرعة القراءة والوضوح.</p>
                                    </label>
                                </div>

                                <!-- 6. Next-Gen Cyber AI -->
                                <div class="col-md-6 col-xl-4">
                                    <label class="card h-100 border-2 rounded-4 p-3 position-relative cursor-pointer transition-all template-card <?= $s['value'] === 'cyber_futuristic' ? 'border-primary bg-white shadow' : 'border-light-subtle bg-white' ?>" style="cursor:pointer">
                                        <input type="radio" name="settings[site_theme_template]" value="cyber_futuristic" class="d-none" <?= $s['value'] === 'cyber_futuristic' ? 'checked' : '' ?> onchange="document.querySelectorAll('.template-card').forEach(c=>c.classList.remove('border-primary','shadow')); this.closest('.template-card').classList.add('border-primary','shadow');">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-dark text-warning fw-bold">Next-Gen Cyber</span>
                                            <span class="badge bg-warning-subtle text-warning fw-bold">مستقبلي</span>
                                        </div>
                                        <div class="rounded-3 p-2 mb-2 text-center" style="background:#090d16;color:#a855f7;font-size:0.75rem;border:1px solid rgba(168,85,247,0.3)">
                                            <div class="fw-bold mb-1">سايبر وذكاء اصطناعي</div>
                                            <div class="text-cyan small">نيون متوهج + شارات تفاعلية</div>
                                        </div>
                                        <h6 class="fw-bold mb-1 text-dark">السايبر المستقبلي</h6>
                                        <p class="text-muted small mb-0" style="font-size:0.78rem">وضع ليلي داكن، تدرجات نيون ناعمة، شريط مالي مباشر، وتأثيرات هولوجرامية.</p>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

 <?php elseif (isset($selectDropdowns[$s['key']])): ?>
 <!-- Structured Dropdown Select Box -->
 <div class="col-md-6">
 <div class="p-3 bg-light rounded-4 border shadow-sm h-100">
 <div class="d-flex justify-content-between align-items-center mb-1">
 <label class="form-label fw-bold text-dark mb-0" for="setting_<?= htmlspecialchars($s['key']) ?>">
 <?= htmlspecialchars($s['label_ar'] ?: $s['key']) ?>
 </label>
 <?php if (str_ends_with($s['key'], '_model')): 
 $provName = str_replace('_model', '', $s['key']);
 ?>
 <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 btn-fetch-live-models" 
 data-provider="<?= htmlspecialchars($provName) ?>" 
 data-target="setting_<?= htmlspecialchars($s['key']) ?>"
 title="فحص واستدعاء النماذج الحية المتوفرة عبر المزود مع حالة الاتصال">
 <span class="refresh-spinner spinner-border spinner-border-sm me-1 d-none"></span>
 <i class="bi bi-arrow-repeat"></i> فحص النماذج المباشرة
 </button>
 <?php endif; ?>
 </div>
 <small class="text-muted d-block mb-2" style="font-size:0.8rem">
 <?= htmlspecialchars($s['description_ar'] ?: $s['key']) ?>
 </small>
 <select name="settings[<?= htmlspecialchars($s['key']) ?>]" id="setting_<?= htmlspecialchars($s['key']) ?>" class="form-select form-select-lg">
 <?php foreach ($selectDropdowns[$s['key']] as $optValue => $optLabel): ?>
 <option value="<?= htmlspecialchars((string) $optValue) ?>" <?= (string) $s['value'] === (string) $optValue ? 'selected' : '' ?>>
 <?= htmlspecialchars($optLabel) ?>
 </option>
 <?php endforeach; ?>
 </select>
 </div>
 </div>

 <?php elseif ($s['value_type'] === 'boolean'): ?>
 <!-- Modern Interactive Toggle Switch -->
 <div class="col-md-6">
 <div class="p-3 bg-light rounded-4 border d-flex justify-content-between align-items-center h-100 shadow-sm">
 <div style="max-width:80%">
 <label class="form-check-label fw-bold text-dark mb-1 d-block" for="setting_<?= htmlspecialchars($s['key']) ?>" style="cursor:pointer">
 <?= htmlspecialchars($s['label_ar'] ?: $s['key']) ?>
 </label>
 <small class="text-muted d-block" style="font-size:0.8rem;line-height:1.4">
 <?= htmlspecialchars($s['description_ar'] ?: $s['key']) ?>
 </small>
 </div>
 <div class="form-check form-switch m-0">
 <input type="hidden" name="settings[<?= htmlspecialchars($s['key']) ?>]" value="0">
 <input class="form-check-input" type="checkbox" role="switch" 
 id="setting_<?= htmlspecialchars($s['key']) ?>" 
 name="settings[<?= htmlspecialchars($s['key']) ?>]" 
 value="1" 
 <?= $s['value'] == '1' ? 'checked' : '' ?> 
 style="width:3rem;height:1.6rem;cursor:pointer">
 </div>
 </div>
 </div>

 <?php elseif (in_array($s['value_type'], ['textarea', 'json'])): ?>
 <!-- Textarea -->
 <div class="col-12">
 <div class="p-3 bg-light rounded-4 border shadow-sm">
 <label class="form-label fw-bold text-dark mb-1" for="setting_<?= htmlspecialchars($s['key']) ?>">
 <?= htmlspecialchars($s['label_ar'] ?: $s['key']) ?>
 </label>
 <small class="text-muted d-block mb-2" style="font-size:0.8rem">
 <?= htmlspecialchars($s['description_ar'] ?: $s['key']) ?>
 </small>
 <textarea name="settings[<?= htmlspecialchars($s['key']) ?>]" id="setting_<?= htmlspecialchars($s['key']) ?>" class="form-control font-monospace" rows="4" dir="auto"><?= htmlspecialchars($s['value'] ?? '') ?></textarea>
 </div>
 </div>

 <?php elseif ($s['value_type'] === 'color'): ?>
 <!-- Color Picker with live HEX sync -->
 <div class="col-md-6">
 <div class="p-3 bg-light rounded-4 border shadow-sm">
 <label class="form-label fw-bold text-dark mb-1" for="setting_<?= htmlspecialchars($s['key']) ?>">
 <?= htmlspecialchars($s['label_ar'] ?: $s['key']) ?>
 </label>
 <small class="text-muted d-block mb-2" style="font-size:0.8rem">
 <?= htmlspecialchars($s['description_ar'] ?: $s['key']) ?>
 </small>
 <div class="d-flex gap-2">
 <input type="color" id="setting_<?= htmlspecialchars($s['key']) ?>_color" class="form-control form-control-color" value="<?= htmlspecialchars($s['value'] ?: '#00f2fe') ?>" oninput="document.getElementById('setting_<?= htmlspecialchars($s['key']) ?>').value = this.value">
 <input type="text" id="setting_<?= htmlspecialchars($s['key']) ?>" name="settings[<?= htmlspecialchars($s['key']) ?>]" class="form-control font-monospace" value="<?= htmlspecialchars($s['value'] ?: '#00f2fe') ?>" oninput="document.getElementById('setting_<?= htmlspecialchars($s['key']) ?>_color').value = this.value">
 </div>
 </div>
 </div>

 <?php else: ?>
 <!-- Standard Text / Number Input -->
 <div class="col-md-6">
 <div class="p-3 bg-light rounded-4 border shadow-sm h-100">
 <label class="form-label fw-bold text-dark mb-1" for="setting_<?= htmlspecialchars($s['key']) ?>">
 <?= htmlspecialchars($s['label_ar'] ?: $s['key']) ?>
 </label>
 <small class="text-muted d-block mb-2" style="font-size:0.8rem">
 <?= htmlspecialchars($s['description_ar'] ?: $s['key']) ?>
 </small>
 <input type="<?= $s['value_type'] === 'number' ? 'number' : 'text' ?>" 
 id="setting_<?= htmlspecialchars($s['key']) ?>" 
 name="settings[<?= htmlspecialchars($s['key']) ?>]" 
 class="form-control" 
 value="<?= htmlspecialchars($s['value'] ?? '') ?>">
 </div>
 </div>

 <?php endif; ?>

 <?php endforeach; ?>
 </div>

 <hr class="my-4">
 <div class="d-flex justify-content-end">
 <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm" style="background:linear-gradient(135deg,#00f2fe,#9d4edd);border:none">
 حفظ وتطبيق إعدادات هذه المجموعة
 </button>
 </div>
 </form>
 </div>
</div>

<script>
function uploadBrandAssetInstant(key, inputElement) {
 if (!inputElement.files || !inputElement.files[0]) return;
 const file = inputElement.files[0];
 const labelBtn = document.getElementById('upload_label_' + key);
 const textInput = document.getElementById('setting_' + key);
 const previewImg = document.getElementById('preview_img_' + key);
 const previewBox = document.getElementById('preview_box_' + key);
 const originalLabelHtml = labelBtn.innerHTML;

 labelBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري الرفع والتطبيق...';
 labelBtn.classList.add('disabled');

 const formData = new FormData();
 formData.append('key', key);
 formData.append('asset_file', file);
 formData.append('_csrf', '<?= CSRF::token() ?>');

 fetch('<?= app_url('admin/settings/upload-asset') ?>', {
 method: 'POST',
 body: formData
 })
 .then(res => res.json())
 .then(data => {
 labelBtn.classList.remove('disabled');
 if (data.success) {
 labelBtn.className = 'btn btn-sm btn-success mb-0 w-100 text-truncate';
 labelBtn.innerHTML = ' تم التطبيق بنجاح: ' + file.name;
 if (textInput) textInput.value = data.url;
 if (previewImg) {
 previewImg.src = data.full_url + '?t=' + new Date().getTime();
 if (previewBox) previewBox.style.display = 'block';
 } else if (previewBox) {
 previewBox.innerHTML = '<img id="preview_img_' + key + '" src="' + data.full_url + '?t=' + new Date().getTime() + '" alt="معاينة" style="max-height: 38px; max-width: 60px; object-fit: contain;">';
 previewBox.style.display = 'block';
 }
 } else {
 labelBtn.className = 'btn btn-sm btn-danger mb-0 w-100 text-truncate';
 labelBtn.innerHTML = 'خطأ: ' + (data.message || 'فشل الرفع');
 alert(data.message || 'حدث خطأ أثناء رفع الصورة');
 }
 })
 .catch(err => {
 labelBtn.classList.remove('disabled');
 labelBtn.className = 'btn btn-sm btn-danger mb-0 w-100 text-truncate';
 labelBtn.innerHTML = 'خطأ في الاتصال';
 console.error(err);
 });
}

// AI Provider Live Testing Handler
document.addEventListener('DOMContentLoaded', () => {
 const testBtns = document.querySelectorAll('.btn-test-provider');
 const resultBox = document.getElementById('testResultBox');
 const resultHeader = document.getElementById('testResultHeader');
 const resultBody = document.getElementById('testResultBody');

 if (!testBtns.length || !resultBox) return;

 testBtns.forEach(btn => {
 btn.addEventListener('click', async () => {
 const provider = btn.getAttribute('data-provider');
 const spinner = btn.querySelector('.provider-spinner');

 testBtns.forEach(b => b.disabled = true);
 if (spinner) spinner.classList.remove('d-none');

 resultBox.classList.remove('d-none');
 resultHeader.innerHTML = `<span class="badge bg-secondary">جاري الفحص...</span> <span class="fw-bold">جاري اختبار (${provider})...</span>`;
 resultBody.innerHTML = `<div class="text-muted p-2"><div class="spinner-border spinner-border-sm me-2"></div>جاري إرسال الطلب التجريبي...</div>`;

 // Collect current input values from the page if user modified them before saving
 const payload = new URLSearchParams({
 provider: provider,
 _csrf: '<?= CSRF::getToken() ?>'
 });

 const omniEndpoint = document.getElementById('setting_omniroute_endpoint');
 const omniKey = document.getElementById('setting_omniroute_api_key');
 const omniModel = document.getElementById('setting_omniroute_model');
 const geminiKey = document.getElementById('setting_gemini_api_key');
 const groqKey = document.getElementById('setting_groq_api_key');
 const deepseekKey = document.getElementById('setting_deepseek_api_key');
 const openaiKey = document.getElementById('setting_openai_api_key');
 const customEndpoint = document.getElementById('setting_custom_api_endpoint');

 if (omniEndpoint) payload.append('omniroute_endpoint', omniEndpoint.value);
 if (omniKey) payload.append('omniroute_api_key', omniKey.value);
 if (omniModel) payload.append('omniroute_model', omniModel.value);
 if (geminiKey) payload.append('gemini_api_key', geminiKey.value);
 if (groqKey) payload.append('groq_api_key', groqKey.value);
 if (deepseekKey) payload.append('deepseek_api_key', deepseekKey.value);
 if (openaiKey) payload.append('openai_api_key', openaiKey.value);
 if (customEndpoint) payload.append('custom_api_endpoint', customEndpoint.value);
 const opencodeModel = document.getElementById('setting_opencode_model');
 if (opencodeModel) payload.append('opencode_model', opencodeModel.value);

 try {
 const response = await fetch('<?= admin_e(app_url('admin/ai/test-provider')) ?>', {
 method: 'POST',
 headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
 body: payload
 });

 const data = await response.json();

 if (data.ok) {
 resultHeader.innerHTML = `
 <span class="badge bg-success">اتصال ناجح (200 OK)</span>
 <span class="fw-bold text-dark">المزود: ${provider.toUpperCase()}</span>
 <span class="badge bg-light text-dark border font-monospace">${data.duration_ms} ms</span>
 `;
 const resData = data.data || {};
 resultBody.innerHTML = `
 <div class="p-3 bg-white rounded-3 border">
 <div class="fw-bold text-primary mb-1">العنوان المترجم:</div>
 <div class="mb-2 fs-6 fw-bold text-dark">${escapeHtml(resData.title_ar || 'بدون عنوان')}</div>
 ${resData.excerpt ? `<div class="text-muted mb-2"><strong>المقدمة:</strong> ${escapeHtml(resData.excerpt)}</div>` : ''}
 ${resData.mode ? `<div class="badge bg-info-subtle text-info font-monospace">${escapeHtml(resData.mode)}</div>` : ''}
 </div>
 `;
 } else {
 resultHeader.innerHTML = `
 <span class="badge bg-danger">فشل الاتصال</span>
 <span class="fw-bold text-dark">المزود: ${provider.toUpperCase()}</span>
 <span class="badge bg-light text-dark border font-monospace">${data.duration_ms || '--'} ms</span>
 `;
 resultBody.innerHTML = `
 <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-3 border border-danger-subtle">
 <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon me-1"></i>تفاصيل الخطأ:</div>
 <div class="font-monospace small">${escapeHtml(data.error || 'حدث خطأ أثناء الاتصال بالمزود.')}</div>
 </div>
 `;
 }
 } catch (err) {
 resultHeader.innerHTML = `<span class="badge bg-danger">خطأ شبكة</span> <span class="fw-bold">فشل إرسال الطلب</span>`;
 resultBody.innerHTML = `<div class="p-2 text-danger">تعذر إتمام الفحص: ${escapeHtml(err.message)}</div>`;
 } finally {
 testBtns.forEach(b => b.disabled = false);
 if (spinner) spinner.classList.add('d-none');
 }
 });
 });

 // Live Models Fetcher & Health Prober Handler
 const modelFetchBtns = document.querySelectorAll('.btn-fetch-live-models');
 modelFetchBtns.forEach(btn => {
 btn.addEventListener('click', async () => {
 const provider = btn.getAttribute('data-provider');
 const targetSelectId = btn.getAttribute('data-target');
 const selectEl = document.getElementById(targetSelectId);
 const spinner = btn.querySelector('.refresh-spinner');

 if (!selectEl) return;

 btn.disabled = true;
 if (spinner) spinner.classList.remove('d-none');

 // Gather any endpoint / key overrides
 const params = new URLSearchParams({
 provider: provider,
 _csrf: '<?= CSRF::getToken() ?>'
 });

 const omniEndpoint = document.getElementById('setting_omniroute_endpoint');
 const omniKey = document.getElementById('setting_omniroute_api_key');
 const geminiKey = document.getElementById('setting_gemini_api_key');
 const groqKey = document.getElementById('setting_groq_api_key');
 const deepseekKey = document.getElementById('setting_deepseek_api_key');
 const openaiKey = document.getElementById('setting_openai_api_key');

 if (omniEndpoint) params.append('omniroute_endpoint', omniEndpoint.value);
 if (omniKey) params.append('omniroute_api_key', omniKey.value);
 if (geminiKey) params.append('gemini_api_key', geminiKey.value);
 if (groqKey) params.append('groq_api_key', groqKey.value);
 if (deepseekKey) params.append('deepseek_api_key', deepseekKey.value);
 if (openaiKey) params.append('openai_api_key', openaiKey.value);

 try {
 const response = await fetch('<?= admin_e(app_url('admin/ai/models')) ?>?' + params.toString());
 const res = await response.json();

 if (res.ok && res.models && res.models.length> 0) {
 const currentSelected = selectEl.value;
 selectEl.innerHTML = '';
 res.models.forEach(m => {
 const opt = document.createElement('option');
 opt.value = m.id;
 const icon = m.status === 'online' ? '' : (m.status === 'offline' ? '' : '');
 opt.textContent = `${icon} ${m.name}`;
 if (m.id === currentSelected || m.selected) {
 opt.selected = true;
 }
 selectEl.appendChild(opt);
 });
 btn.classList.remove('btn-outline-primary');
 btn.classList.add('btn-success');
 btn.innerHTML = `<i class="bi bi-check-circle"></i> تم تحديث ${res.models.length} نموذجاً`;
 setTimeout(() => {
 btn.classList.remove('btn-success');
 btn.classList.add('btn-outline-primary');
 btn.innerHTML = `<span class="refresh-spinner spinner-border spinner-border-sm me-1 d-none"></span><i class="bi bi-arrow-repeat"></i> فحص النماذج المباشرة`;
 }, 3000);
 } else {
 alert('تعذر جلب النماذج الحية: تحقق من صحة المفتاح ورابط المزود.');
 }
 } catch (e) {
 console.error('Fetch models error:', e);
 alert('حدث خطأ أثناء فحص النماذج المباشرة.');
 } finally {
 btn.disabled = false;
 if (spinner) spinner.classList.add('d-none');
 }
 });
 });

 function escapeHtml(str) {
 return (str || '').toString().replace(/[&<>"']/g, m => ({
 '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
 }[m]));
 }
});
</script>

