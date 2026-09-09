<?php

class AggregatorController extends AdminController
{
 /**
  * تصنيف تلقائي ذكي للمقال المنشور عبر لوحة المجمّع مع مراعاة المصدر
  * والمحتوى ورابط المقال (نفس محرك التصنيف المستخدم في الـ Cron).
  * يقع على المصدر المسجل أو الفئة الافتراضية إن تعذّر التصنيف.
  */
 private function resolveCategoryId($db, $titleEn, $titleAr, $content, $sourceName, $sourceUrl, $fallbackCatId)
 {
 require_once __DIR__ . '/../../core/CategoryClassifier.php';

 $srcCatId = 0;
 if (!empty($sourceName)) {
 $src = $db->fetch("SELECT category_id FROM rss_sources WHERE name = :name LIMIT 1", [':name' => $sourceName]);
 $srcCatId = (int) ($src['category_id'] ?? 0);
 }

 $catSlugMap = [];
 foreach ($db->fetchAll('SELECT id, slug FROM categories ORDER BY id ASC') as $c) {
 $catSlugMap[$c['slug']] = (int) $c['id'];
 }

 try {
 $cat = (int) CategoryClassifier::classify($titleEn, (string) $content, (string) $titleAr, $srcCatId, $catSlugMap, (string) $sourceName, (string) $sourceUrl);
 if ($cat > 0 && in_array($cat, $catSlugMap, true)) {
 return $cat;
 }
 } catch (Throwable $e) {
 // fall back to the requested/default category below
 }

 if ($fallbackCatId > 0) {
 return $fallbackCatId;
 }
 return get_default_category_id($db);
 }

 public function index()
 {
 $this->guardAdmin();
 $db = new Database();
 
 $sources = $db->fetchAll('SELECT s.*, c.name as category_name FROM rss_sources s LEFT JOIN categories c ON c.id = s.category_id ORDER BY s.id ASC');
 $categories = $db->fetchAll('SELECT id, name FROM categories ORDER BY name ASC');

 if (isset($_GET['source_id'])) {
 $selectedSourceId = (int) $_GET['source_id'];
 Session::set('aggregator_active_source_id', $selectedSourceId);
 } elseif (Session::has('aggregator_active_source_id')) {
 $savedId = (int) Session::get('aggregator_active_source_id');
 $exists = false;
 foreach ($sources as $s) {
 if ((int) $s['id'] === $savedId) {
 $exists = true;
 break;
 }
 }
 $selectedSourceId = $exists ? $savedId : (int) ($sources[0]['id'] ?? 0);
 } else {
 $selectedSourceId = (int) ($sources[0]['id'] ?? 0);
 }

 $customFeedUrl = trim((string) ($_GET['custom_url'] ?? ''));

 $activeFeedUrl = '';
 $activeSourceName = '';
 $activeCategoryId = null;

 if (!empty($customFeedUrl)) {
 $activeFeedUrl = $customFeedUrl;
 $activeSourceName = parse_url($customFeedUrl, PHP_URL_HOST) ?: 'مصدر مخصص';
 } elseif ($selectedSourceId > 0) {
 foreach ($sources as $s) {
 if ((int) $s['id'] === $selectedSourceId) {
 $activeFeedUrl = $s['url'];
 $activeSourceName = $s['name'];
 $activeCategoryId = $s['category_id'];
 break;
 }
 }
 }

 $items = [];
 $error = null;

 if (!empty($activeFeedUrl)) {
 $feedData = $this->fetchRss($activeFeedUrl);
 if ($feedData['success']) {
 $items = $feedData['items'];
 // Check against existing articles in DB with real status
 $existingArticles = $db->fetchAll("SELECT source_url, status FROM articles WHERE source_url IS NOT NULL");
 $urlStatusMap = [];
 foreach ($existingArticles as $row) {
 $urlStatusMap[$row['source_url']] = $row['status'];
 }

 foreach ($items as &$it) {
 $it['import_status'] = $urlStatusMap[$it['link']] ?? null;
 $it['is_imported'] = !empty($it['import_status']);
 $it['source_name'] = $activeSourceName;
 $it['category_id'] = $activeCategoryId;
 }
 unset($it);
 } else {
 $error = $feedData['error'];
 }
 }

 $this->view('admin/aggregator/index', [
 'sources' => $sources,
 'categories' => $categories,
 'selectedSourceId' => $selectedSourceId,
 'customFeedUrl' => $customFeedUrl,
 'activeSourceName' => $activeSourceName,
 'activeFeedUrl' => $activeFeedUrl,
 'items' => $items,
 'error' => $error,
 ]);
 }

 /**
 * Translate & Publish:
 * Automatically translates English/foreign news using AI / Free translation and publishes to Arabic audience.
 */
 public function translatePublish()
 {
 $this->postGuard();
 require_once __DIR__ . '/../../core/AiTranslator.php';

 try {
 $data = Sanitizer::cleanArray($_POST);

 $title = $this->cleanTextEntity(trim($data['title'] ?? ''));
 $sourceUrl = trim($data['source_url'] ?? '');
 $sourceName = $this->cleanTextEntity(trim($data['source_name'] ?? 'مصدر خارجي'));
 $content = $this->cleanTextEntity($_POST['content'] ?? ($data['excerpt'] ?? ''));
 $excerpt = $this->cleanTextEntity(trim($data['excerpt'] ?? mb_strimwidth(strip_tags($content), 0, 200, '…', 'UTF-8')));
 $featuredImage = trim($data['featured_image'] ?? '');
 // Drop absurdly long image URLs (feed junk) so the INSERT never overflows.
 if (strlen($featuredImage) > 1000) $featuredImage = '';

 if (empty($title)) {
 Session::flash('error', 'عنوان المقال مطلوب للنشر.');
 return $this->redirect('admin/news-feeds');
 }

 // Automatic Strategic AI / Free Translation & Tech News Re-authoring
 $transResult = AiTranslator::translateArticle($title, $content ?: $excerpt);
 
 $titleAr = $this->cleanTextEntity($transResult['title_ar'] ?? $title);
 $excerptAr = $this->cleanTextEntity($transResult['excerpt'] ?? $excerpt);
 $contentAr = $transResult['content_ar'] ?? "<p>{$excerptAr}</p>";
 $readingTime = (int) ($transResult['reading_time_minutes'] ?? 2);

 $db = new Database();

 $requestedCategoryId = (int) ($data['category_id'] ?? 0);
 $categoryId = $this->resolveCategoryId($db, $title, $titleAr, $content ?: $excerpt, $sourceName, $sourceUrl, $requestedCategoryId);
 
 // Smart Deduplication Check: Check if article was already published from this source URL or title
 $existing = null;
 if (!empty($sourceUrl)) {
 $existing = $db->fetch("SELECT id, slug, title, status FROM articles WHERE source_url = :url LIMIT 1", [':url' => $sourceUrl]);
 }
 if (!$existing && !empty($title)) {
 $existing = $db->fetch("SELECT id, slug, title, status FROM articles WHERE title_en = :t1 OR title = :t2 LIMIT 1", [
 ':t1' => $title,
 ':t2' => $title
 ]);
 }

 $slug = strtolower(trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', (string) $titleAr), '-'));
 if (!$slug || strlen($slug) < 3) {
 $slug = strtolower(trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', (string) $title), '-'));
 }
 if (!$slug) $slug = 'news-' . time();
 
 $check = $db->fetch("SELECT id FROM articles WHERE slug = :slug AND id != :aid", [
 ':slug' => $slug,
 ':aid' => $existing ? (int) $existing['id'] : 0
 ]);
 if ($check) {
 $slug .= '-' . time();
 }

        $finalFormattedContent = $contentAr;

        $userId = Auth::user()['id'] ?? 1;

 if ($existing) {
 // Update existing article into Arabic translation seamlessly
 $db->query("
 UPDATE articles SET 
 title = :title,
 title_ar = :title_ar,
 title_en = :title_en,
 slug = :slug,
 excerpt = :excerpt,
 content = :content,
 content_ar = :content_ar,
 content_en = :content_en,
 featured_image = COALESCE(:featured_image, featured_image),
 category_id = :category_id,
 reading_time_minutes = :reading_time,
 status = 'published',
 updated_at = CURRENT_TIMESTAMP
 WHERE id = :id
 ", [
 ':title' => $titleAr,
 ':title_ar' => $titleAr,
 ':title_en' => $title,
 ':slug' => $slug,
 ':excerpt' => $excerptAr,
 ':content' => $finalFormattedContent,
 ':content_ar' => $finalFormattedContent,
 ':content_en' => $content,
 ':featured_image' => $featuredImage ?: null,
 ':category_id' => $categoryId,
 ':reading_time' => $readingTime,
 ':id' => (int) $existing['id'],
 ]);

 $this->audit('translate_update_publish', 'article', (int) $existing['id'], null, ['source' => $sourceName, 'url' => $sourceUrl, 'title_ar' => $titleAr]);
 Session::flash('success', "تم العثور على المقال المنشور مسبقاً وتحديثه وترجمته فوراً إلى العربية بعنوان: \"{$titleAr}\"!");
 } else {
 // Insert fresh translated article
 $db->query("
 INSERT INTO articles 
 (title, title_ar, title_en, slug, excerpt, content, content_ar, content_en, featured_image, category_id, author_id, status, source_name, source_url, allow_comments, published_at, reading_time_minutes, created_at)
 VALUES 
 (:title, :title_ar, :title_en, :slug, :excerpt, :content, :content_ar, :content_en, :featured_image, :category_id, :author_id, 'published', :source_name, :source_url, 1, CURRENT_TIMESTAMP, :reading_time, CURRENT_TIMESTAMP)
 ", [
 ':title' => $titleAr,
 ':title_ar' => $titleAr,
 ':title_en' => $title,
 ':slug' => $slug,
 ':excerpt' => $excerptAr,
 ':content' => $finalFormattedContent,
 ':content_ar' => $finalFormattedContent,
 ':content_en' => $content,
 ':featured_image' => $featuredImage ?: null,
 ':category_id' => $categoryId,
 ':author_id' => $userId,
 ':source_name' => $sourceName,
 ':source_url' => $sourceUrl ?: null,
 ':reading_time' => $readingTime,
 ]);

 $newId = $db->lastInsertId();
 $this->audit('translate_publish', 'article', $newId, null, ['source' => $sourceName, 'url' => $sourceUrl, 'title_ar' => $titleAr]);
 Session::flash('success', "تمت ترجمة وصياغة ونشر الخبر فوراً بنجاح باللغة العربية بعنوان: \"{$titleAr}\"!");
 }
 } catch (Throwable $e) {
 error_log('translatePublish error: ' . $e->getMessage());
 Session::flash('error', 'تعذر إتمام الترجمة والنشر: ' . $e->getMessage());
 }
 
 $referer = $_SERVER['HTTP_REFERER'] ?? app_url('admin/news-feeds');
 header('Location: ' . $referer);
 exit;
 }

 /**
 * Direct / Fast Publish (No Translation):
 * Publishes article directly using original text (Ideal for Arabic sources).
 */
 public function quickPublish()
 {
 $this->postGuard();
 $data = Sanitizer::cleanArray($_POST);

 $title = $this->cleanTextEntity(trim($data['title'] ?? ''));
 $sourceUrl = trim($data['source_url'] ?? '');
 $sourceName = $this->cleanTextEntity(trim($data['source_name'] ?? 'مصدر خارجي'));
 $content = $this->cleanTextEntity($_POST['content'] ?? ($data['excerpt'] ?? ''));
 $excerpt = $this->cleanTextEntity(trim($data['excerpt'] ?? mb_strimwidth(strip_tags($content), 0, 200, '…', 'UTF-8')));
 $featuredImage = trim($data['featured_image'] ?? '');
 // Drop absurdly long image URLs (feed junk) so the INSERT never overflows.
 if (strlen($featuredImage) > 1000) $featuredImage = '';

 if (empty($title)) {
 Session::flash('error', 'عنوان المقال مطلوب للنشر.');
 return $this->redirect('admin/news-feeds');
 }

 $db = new Database();

 $requestedCategoryId = (int) ($data['category_id'] ?? 0);
 $categoryId = $this->resolveCategoryId($db, $title, ($data['title_ar'] ?? $title), $content ?: $excerpt, $sourceName, $sourceUrl, $requestedCategoryId);
 
 // Smart Deduplication Check
 $existing = null;
 if (!empty($sourceUrl)) {
 $existing = $db->fetch("SELECT id, slug, title, status FROM articles WHERE source_url = :url LIMIT 1", [':url' => $sourceUrl]);
 }
 if (!$existing && !empty($title)) {
 $existing = $db->fetch("SELECT id, slug, title, status FROM articles WHERE title = :t LIMIT 1", [':t' => $title]);
 }

 $slug = strtolower(trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', (string) $title), '-'));
 if (!$slug) $slug = 'news-' . time();
 
 $check = $db->fetch("SELECT id FROM articles WHERE slug = :slug AND id != :aid", [
 ':slug' => $slug,
 ':aid' => $existing ? (int) $existing['id'] : 0
 ]);
 if ($check) {
 $slug .= '-' . time();
 }

        $formattedContent = "<p class='lead'>" . htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') . "</p>\n";
        $formattedContent .= "<div class='news-body'>" . nl2br(htmlspecialchars(strip_tags($content), ENT_QUOTES, 'UTF-8')) . "</div>\n";

        $userId = Auth::user()['id'] ?? 1;

        if ($existing) {
            $db->query("
                UPDATE articles SET 
                title = :title,
                title_ar = :title_ar,
                slug = :slug,
                excerpt = :excerpt,
                content = :content,
                featured_image = COALESCE(:featured_image, featured_image),
                category_id = :category_id,
                status = 'published',
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ", [
                ':title' => $title,
                ':title_ar' => $title,
                ':slug' => $slug,
                ':excerpt' => $excerpt,
                ':content' => $formattedContent,
                ':featured_image' => $featuredImage ?: null,
                ':category_id' => $categoryId,
                ':id' => (int) $existing['id'],
            ]);

            $this->audit('direct_update_publish', 'article', (int) $existing['id'], null, ['source' => $sourceName, 'url' => $sourceUrl, 'title' => $title]);
            Session::flash('success', "تم العثور على المقال المنشور مسبقاً وتحديث محتواه فوراً بعنوان: \"{$title}\"!");
        } else {
            $db->query("
                INSERT INTO articles 
                (title, title_ar, slug, excerpt, content, featured_image, category_id, author_id, status, source_name, source_url, allow_comments, published_at, reading_time_minutes, created_at)
                VALUES 
                (:title, :title_ar, :slug, :excerpt, :content, :featured_image, :category_id, :author_id, 'published', :source_name, :source_url, 1, CURRENT_TIMESTAMP, 2, CURRENT_TIMESTAMP)
            ", [
                ':title' => $title,
                ':title_ar' => $title,
                ':slug' => $slug,
                ':excerpt' => $excerpt,
                ':content' => $formattedContent,
                ':featured_image' => $featuredImage ?: null,
                ':category_id' => $categoryId,
                ':author_id' => $userId,
                ':source_name' => $sourceName,
                ':source_url' => $sourceUrl ?: null,
            ]);

            $newId = $db->lastInsertId();
            $this->audit('direct_publish', 'article', $newId, null, ['source' => $sourceName, 'url' => $sourceUrl, 'title' => $title]);
            Session::flash('success', "تم النشر الفوري المباشر بنجاح بدون ترجمة بعنوان: \"{$title}\"!");
        }
        
        $referer = $_SERVER['HTTP_REFERER'] ?? app_url('admin/news-feeds');
        header('Location: ' . $referer);
        exit;
    }

    /**
     * POST /admin/news-feeds/draft-article
     * Converts a feed item into a draft article and opens the editor immediately
     */
    public function draftArticle()
    {
        $this->postGuard();
        $data = Sanitizer::cleanArray($_POST);

        $title = $this->cleanTextEntity(trim($data['title'] ?? ''));
        $sourceUrl = trim($data['source_url'] ?? '');
        $sourceName = $this->cleanTextEntity(trim($data['source_name'] ?? 'مصدر خارجي'));
        $content = $this->cleanTextEntity($_POST['content'] ?? ($data['excerpt'] ?? ''));
        $excerpt = $this->cleanTextEntity(trim($data['excerpt'] ?? mb_strimwidth(strip_tags($content), 0, 200, '…', 'UTF-8')));
        $featuredImage = trim($data['featured_image'] ?? '');
        // Drop absurdly long image URLs (feed junk) so the INSERT never overflows.
        if (strlen($featuredImage) > 1000) $featuredImage = '';

        if (empty($title)) {
            Session::flash('error', 'عنوان المقال مطلوب.');
            return $this->redirect('admin/news-feeds');
        }

        $db = new Database();

        // Check if this source_url is already in articles
        if (!empty($sourceUrl)) {
            $existing = $db->fetch("SELECT id FROM articles WHERE source_url = :url", [':url' => $sourceUrl]);
            if ($existing) {
                return $this->redirect('admin/articles/' . (int) $existing['id'] . '/edit');
            }
        }

        // Validate category
        $categoryId = $this->resolveCategoryId($db, $title, ($data['title_ar'] ?? $title), $excerpt, $sourceName, $sourceUrl, 0);

        // Generate unique slug
        $slug = strtolower(trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', (string) $title), '-'));
        if (!$slug) $slug = 'draft-' . time();
        $check = $db->fetch("SELECT id FROM articles WHERE slug = :slug", [':slug' => $slug]);
        if ($check) {
            $slug .= '-' . time();
        }

        $formattedContent = "<p class='lead'>" . htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8') . "</p>\n";
        $formattedContent .= "<div class='news-body'>" . nl2br(htmlspecialchars(strip_tags($content), ENT_QUOTES, 'UTF-8')) . "</div>\n";

        $userId = Auth::user()['id'] ?? 1;

 $db->query("
 INSERT INTO articles 
 (title, title_ar, slug, excerpt, content, content_ar, featured_image, category_id, author_id, status, source_name, source_url, allow_comments, reading_time_minutes, created_at)
 VALUES 
 (:title, :title_ar, :slug, :excerpt, :content, :content_ar, :featured_image, :category_id, :author_id, 'draft', :source_name, :source_url, 1, 3, CURRENT_TIMESTAMP)
 ", [
 ':title' => $title,
 ':title_ar' => $title,
 ':slug' => $slug,
 ':excerpt' => $excerpt,
 ':content' => $formattedContent,
 ':content_ar' => $formattedContent,
 ':featured_image' => $featuredImage ?: null,
 ':category_id' => $categoryId,
 ':author_id' => $userId,
 ':source_name' => $sourceName,
 ':source_url' => $sourceUrl ?: null,
 ]);

 $newId = $db->lastInsertId();
 $this->audit('draft_article', 'article', $newId, null, ['source' => $sourceName, 'url' => $sourceUrl]);
 Session::flash('success', "تم استيراد الخبر كمسودة بنجاح! يمكنك الآن مراجعته وصياغته ونشره.");
 return $this->redirect('admin/articles/' . (int) $newId . '/edit');
 }

 public function addSource()
 {
 $this->postGuard();
 $data = Sanitizer::cleanArray($_POST);
 $name = trim($data['name'] ?? '');
 $url = trim($data['url'] ?? '');
 $categoryId = (int) ($data['category_id'] ?? 0) ?: null;

 if (empty($name) || empty($url)) {
 Session::flash('error', 'اسم المصدر ورابط الـ RSS مطلوبان.');
 return $this->redirect('admin/news-feeds');
 }

 $db = new Database();
 $db->query("INSERT INTO rss_sources (name, url, category_id) VALUES (:name, :url, :category_id)", [
 ':name' => $name,
 ':url' => $url,
 ':category_id' => $categoryId
 ]);

 Session::flash('success', "تمت إضافة المصدر التقني \"{$name}\" بنجاح.");
 return $this->redirect('admin/news-feeds');
 }

 public function deleteSource($id)
 {
 $this->postGuard();
 $db = new Database();
 $db->query("DELETE FROM rss_sources WHERE id = :id", [':id' => (int) $id]);
 Session::flash('success', 'تم حذف المصدر بنجاح.');
 return $this->redirect('admin/news-feeds');
 }

 /**
 * فحص صحة كل مصادر RSS المسجلة (أو مصدر واحد) وتخزين النتيجة في قاعدة البيانات.
 * يُرجع JSON لاستهلاكه من واجهة لوحة التحكم.
 */
 public function healthCheck()
 {
 $this->guardAdmin();
 @set_time_limit(300); // فحص 31 مصدراً قد يستغرق دقائق

 $db = new Database();
 $singleId = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);

 if ($singleId > 0) {
 $sources = $db->fetchAll('SELECT id, name, url FROM rss_sources WHERE id = :id', [':id' => $singleId]);
 } else {
 $sources = $db->fetchAll('SELECT id, name, url FROM rss_sources ORDER BY id');
 }

 $results = [];
 $okCount = 0;
 $failCount = 0;

 foreach ($sources as $s) {
 // الفحص الجماعي: وضع سريع بمهلة 10 ثوانٍ للمصدر الواحد لضمان إنهاء الفحص كاملاً
 $fetch = FeedFetcher::fetchRaw($s['url'], $singleId > 0 ? false : true, $singleId > 0 ? 0 : 10000);

 $itemCount = 0;
 $sampleTitle = '';
 $status = 'failed';
 $error = $fetch['error'] ?? '';

 if (!empty($fetch['success'])) {
 libxml_use_internal_errors(true);
 $xml = @simplexml_load_string($fetch['body'], 'SimpleXMLElement', LIBXML_NOCDATA);
 if ($xml) {
 if (isset($xml->channel->item)) {
 $itemCount = count($xml->channel->item);
 $sampleTitle = (string) $xml->channel->item[0]->title;
 } elseif (isset($xml->entry)) {
 $itemCount = count($xml->entry);
 $sampleTitle = (string) $xml->entry[0]->title;
 }
 }

 if ($itemCount > 0) {
 $status = 'ok';
 $error = '';
 } else {
 // خلاصة XML غير صالحة — جرّب الكاشط الذكي كخيار احتياطي
 $scraped = $this->scrapeHtmlPage($fetch['body'], $s['url']);
 if (!empty($scraped)) {
 $status = 'ok';
 $itemCount = count($scraped);
 $sampleTitle = $scraped[0]['title'] ?? '';
 $error = '';
 } else {
 $status = 'empty';
 $error = 'تم الاتصال بنجاح لكن لم يُعثر على عناصر إخبارية داخل الخلاصة.';
 }
 }
 }

 $suggestions = [];
 if ($status !== 'ok') {
 $suggestions = array_slice(FeedFetcher::suggestAlternatives($s['url']), 0, 4);
 }

 if ($status === 'ok') {
 $okCount++;
 $db->query(
 'UPDATE rss_sources SET last_status = :st, last_http_code = :code, last_error = NULL,
 last_item_count = :items, last_checked_at = NOW(), fail_count = 0 WHERE id = :id',
 [
 ':st' => $status,
 ':code' => (int) $fetch['http_code'],
 ':items' => $itemCount,
 ':id' => (int) $s['id'],
 ]
 );
 } else {
 $failCount++;
 $db->query(
 'UPDATE rss_sources SET last_status = :st, last_http_code = :code, last_error = :err,
 last_item_count = 0, last_checked_at = NOW(), fail_count = fail_count + 1 WHERE id = :id',
 [
 ':st' => $status,
 ':code' => (int) $fetch['http_code'],
 ':err' => mb_substr($error, 0, 480),
 ':id' => (int) $s['id'],
 ]
 );
 }

 $results[] = [
 'id' => (int) $s['id'],
 'name' => $s['name'],
 'url' => $s['url'],
 'status' => $status,
 'http_code' => (int) $fetch['http_code'],
 'items' => $itemCount,
 'sample' => mb_substr($this->cleanTextEntity($sampleTitle), 0, 90),
 'error' => $error,
 'ua' => FeedFetcher::lastSuccessUa() ? $this->uaLabel(FeedFetcher::lastSuccessUa()) : '',
 'suggestions' => $suggestions,
 ];
 }

 header('Content-Type: application/json; charset=utf-8');
 echo json_encode([
 'success' => true,
 'total' => count($results),
 'ok' => $okCount,
 'failed' => $failCount,
 'results' => $results,
 ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
 exit;
 }

 /** تسمية مقروءة لوكيل المستخدم الذي نجح في الجلب */
 private function uaLabel($ua)
 {
 if (str_contains($ua, 'Googlebot')) return 'Googlebot';
 if (str_contains($ua, 'Feedly')) return 'Feedly';
 if (str_contains($ua, 'SimplePie')) return 'SimplePie';
 if (str_contains($ua, 'TechNewsPlatform')) return 'TechNews';
 return 'Chrome';
 }

 public function exportOpml()
 {
 $this->guardAdmin();
 $db = new Database();
 $sources = $db->fetchAll("SELECT * FROM rss_sources ORDER BY id ASC");

 header('Content-Type: text/xml; charset=utf-8');
 header('Content-Disposition: attachment; filename="tech_news_feeds_' . date('Y-m-d') . '.opml"');

 echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
 echo '<opml version="2.0">' . "\n";
 echo ' <head><title>Tech News Platform RSS Feeds</title><dateCreated>' . date('r') . '</dateCreated></head>' . "\n";
 echo ' <body>' . "\n";
 echo ' <outline text="مصادر الأخبار التقنية المعتمدة" title="Tech Feeds">' . "\n";
 foreach ($sources as $s) {
 $name = htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8');
 $url = htmlspecialchars($s['url'], ENT_QUOTES, 'UTF-8');
 echo ' <outline type="rss" text="' . $name . '" title="' . $name . '" xmlUrl="' . $url . '" htmlUrl="' . $url . '" />' . "\n";
 }
 echo ' </outline>' . "\n";
 echo ' </body>' . "\n";
 echo '</opml>';
 exit;
 }

 public function exportJson()
 {
 $this->guardAdmin();
 $db = new Database();
 $sources = $db->fetchAll("SELECT id, name, url, category_id, auto_fetch FROM rss_sources ORDER BY id ASC");

 header('Content-Type: application/json; charset=utf-8');
 header('Content-Disposition: attachment; filename="tech_news_feeds_' . date('Y-m-d') . '.json"');
 echo json_encode([
 'exported_at' => date('Y-m-d H:i:s'),
 'total' => count($sources),
 'sources' => $sources
 ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
 exit;
 }

 public function importFeeds()
 {
 $this->postGuard();
 $db = new Database();
 $importedCount = 0;

 // 1. File Upload (OPML / XML / JSON)
 if (!empty($_FILES['import_file']['tmp_name']) && is_uploaded_file($_FILES['import_file']['tmp_name'])) {
 $content = file_get_contents($_FILES['import_file']['tmp_name']);
 $importedCount += $this->processFeedImportContent($content, $db);
 }

 // 2. Text Paste (URLs line by line)
 $bulkText = trim($_POST['bulk_urls'] ?? '');
 if (!empty($bulkText)) {
 $importedCount += $this->processFeedImportContent($bulkText, $db);
 }

 if ($importedCount > 0) {
 Session::flash('success', "تم استيراد ({$importedCount}) مصدر RSS بنجاح إلى قائمة المصادر!");
 } else {
 Session::flash('error', "لم يتم العثور على روابط RSS صالحة في الملف أو النص المدخل.");
 }

 return $this->redirect('admin/news-feeds');
 }

 private function processFeedImportContent($raw, $db)
 {
 $count = 0;
 $raw = trim($raw);

 // Try JSON
 $json = @json_decode($raw, true);
 if ($json && (isset($json['sources']) || is_array($json))) {
 $list = $json['sources'] ?? $json;
 foreach ($list as $item) {
 if (is_array($item) && !empty($item['url'])) {
 $name = trim($item['name'] ?? parse_url($item['url'], PHP_URL_HOST) ?: 'مصدر مستورد');
 $url = trim($item['url']);
 $catId = !empty($item['category_id']) ? (int) $item['category_id'] : 1;
 $db->query("INSERT INTO rss_sources (name, url, category_id, auto_fetch) VALUES (:name, :url, :cat, 1)", [
 ':name' => $name,
 ':url' => $url,
 ':cat' => $catId
 ]);
 $count++;
 }
 }
 return $count;
 }

 // Try OPML / XML
 if (str_contains($raw, '<opml') || str_contains($raw, '<outline')) {
 libxml_use_internal_errors(true);
 $xml = @simplexml_load_string($raw);
 if ($xml) {
 $outlines = $xml->xpath('//outline[@xmlUrl]');
 foreach ($outlines as $out) {
 $url = (string) $out['xmlUrl'];
 $name = (string) ($out['text'] ?? $out['title'] ?? parse_url($url, PHP_URL_HOST) ?: 'مصدر RSS');
 if (!empty($url)) {
 $db->query("INSERT INTO rss_sources (name, url, category_id, auto_fetch) VALUES (:name, :url, 1, 1)", [
 ':name' => trim($name),
 ':url' => trim($url)
 ]);
 $count++;
 }
 }
 return $count;
 }
 }

 // Fallback: Plain text line by line
 $lines = preg_split('/[\r\n]+/', $raw);
 foreach ($lines as $line) {
 $line = trim($line);
 if (filter_var($line, FILTER_VALIDATE_URL)) {
 $host = parse_url($line, PHP_URL_HOST) ?: 'مصدر تقني';
 $db->query("INSERT INTO rss_sources (name, url, category_id, auto_fetch) VALUES (:name, :url, 1, 1)", [
 ':name' => $host,
 ':url' => $line
 ]);
 $count++;
 }
 }

 return $count;
 }

 private function fetchRss($url)
 {
 // محرك مقاوم للحجب: يدوّر وكيل المستخدم عند 403، ويعيد المحاولة عند 429،
 // ويكشف التحويلات إلى خدمات متوقفة مثل FeedBurner.
 $fetch = FeedFetcher::fetchRaw($url);

 if (!$fetch['success']) {
 $hint = '';
 if ((int) $fetch['http_code'] === 410 || (int) $fetch['http_code'] === 404) {
 $alts = FeedFetcher::suggestAlternatives($url);
 if ($alts) {
 $hint = ' — روابط مقترحة للتجربة: ' . implode(' ، ', array_slice($alts, 0, 3));
 }
 }
 return ['success' => false, 'error' => $fetch['error'] . $hint];
 }

 $xmlContent = $fetch['body'];
 $httpCode = (int) $fetch['http_code'];

 libxml_use_internal_errors(true);
 $xml = @simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
 if (!$xml) {
 // Smart Fallback: Parse regular HTML webpage and extract news cards automatically!
 $htmlItems = $this->scrapeHtmlPage($xmlContent, $url);
 if (!empty($htmlItems)) {
 return ['success' => true, 'items' => $htmlItems, 'is_html_scraped' => true];
 }
 return ['success' => false, 'error' => 'تعذر استخراج مقالات من الرابط (لا تتوفر خلاصة XML صالحة أو تعذر استخراج عناصر HTML).'];
 }

 $items = [];
 
 // RSS 2.0
 if (isset($xml->channel->item)) {
 foreach ($xml->channel->item as $entry) {
 $items[] = $this->parseRssItem($entry);
 }
 } 
 // Atom Feed
 elseif (isset($xml->entry)) {
 foreach ($xml->entry as $entry) {
 $items[] = $this->parseAtomItem($entry);
 }
 }

 if (empty($items)) {
 // Try HTML scraper as secondary fallback
 $htmlItems = $this->scrapeHtmlPage($xmlContent, $url);
 if (!empty($htmlItems)) {
 return ['success' => true, 'items' => $htmlItems, 'is_html_scraped' => true];
 }
 return ['success' => false, 'error' => 'لم يتم العثور على أي عناصر إخبارية داخل ملف الخلاصة.'];
 }

 return ['success' => true, 'items' => array_slice($items, 0, 30)];
 }

 /**
 * Smart HTML to RSS Converter & Web Scraper
 * Extracts news headlines, links, images, and snippets from ANY regular webpage.
 */
 private function scrapeHtmlPage($html, $baseUrl)
 {
 $dom = new DOMDocument();
 libxml_use_internal_errors(true);
 @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
 libxml_clear_errors();

 $xpath = new DOMXPath($dom);
 $items = [];
 $seenLinks = [];

 $parsedBase = parse_url($baseUrl);
 $baseHost = ($parsedBase['scheme'] ?? 'https') . '://' . ($parsedBase['host'] ?? '');

 // Common article containers
 $queries = [
 '//article',
 '//div[contains(@class, "post") or contains(@class, "article") or contains(@class, "card") or contains(@class, "news-item")]',
 '//li[contains(@class, "post") or contains(@class, "item")]',
 '//h2/parent::* | //h3/parent::*'
 ];

 foreach ($queries as $query) {
 $nodes = $xpath->query($query);
 if ($nodes && $nodes->length > 0) {
 foreach ($nodes as $node) {
 // Extract Title & Link
 $linkNode = $xpath->query('.//h1//a | .//h2//a | .//h3//a | .//h4//a | .//a[.//h2 or .//h3 or .//h4]', $node)->item(0);
 if (!$linkNode) {
 $linkNode = $xpath->query('.//a[@href]', $node)->item(0);
 }

 if (!$linkNode) continue;

 $title = trim($linkNode->textContent);
 $href = trim($linkNode->getAttribute('href'));

 if (empty($title) || mb_strlen($title, 'UTF-8') < 10 || empty($href)) continue;

 // Normalize URL
 if (str_starts_with($href, '/')) {
 $href = $baseHost . $href;
 } elseif (!str_starts_with($href, 'http')) {
 $href = rtrim($baseUrl, '/') . '/' . ltrim($href, '/');
 }

 if (isset($seenLinks[$href])) continue;
 $seenLinks[$href] = true;

 // Extract Image
 $imgNode = $xpath->query('.//img[@src or @data-src]', $node)->item(0);
 $image = '';
 if ($imgNode) {
 $image = $imgNode->getAttribute('data-src') ?: $imgNode->getAttribute('src');
 if (str_starts_with($image, '/')) {
 $image = $baseHost . $image;
 }
 }

 // Extract Description / Paragraph
 $pNode = $xpath->query('.//p', $node)->item(0);
 $desc = $pNode ? trim($pNode->textContent) : $title;

 $items[] = [
 'title' => $title,
 'link' => $href,
 'pubDate' => date('Y-m-d H:i'),
 'excerpt' => mb_strimwidth($desc, 0, 220, '…', 'UTF-8'),
 'content' => $desc,
 'featured_image' => $image ?: 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80'
 ];

 if (count($items) >= 25) break 2;
 }
 }
 }

 return $items;
 }

 private function parseRssItem($item)
 {
 $title = (string) $item->title;
 $link = (string) $item->link;
 $pubDate = (string) $item->pubDate;
 $description = (string) ($item->description ?? '');
 $content = (string) ($item->children('content', true)->encoded ?? $description);

 $image = '';
 // Check enclosure
 if (isset($item->enclosure) && str_starts_with((string) $item->enclosure['type'], 'image')) {
 $image = (string) $item->enclosure['url'];
 }
 // Check media:content / media:thumbnail
 if (!$image && isset($item->children('media', true)->content)) {
 $image = (string) $item->children('media', true)->content->attributes()->url;
 }
 if (!$image && isset($item->children('media', true)->thumbnail)) {
 $image = (string) $item->children('media', true)->thumbnail->attributes()->url;
 }
 // Check <img> in content or description
 if (!$image && preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $content ?: $description, $m)) {
 $image = $m[1];
 }

 $cleanDesc = $this->cleanTextEntity(strip_tags($description ?: $content));
 $cleanContent = $this->cleanTextEntity(strip_tags($content ?: $description));

 return [
 'title' => $this->cleanTextEntity($title),
 'link' => trim($link),
 'pubDate' => $pubDate ? date('Y-m-d H:i', strtotime($pubDate)) : date('Y-m-d H:i'),
 'excerpt' => mb_strimwidth($cleanDesc ?: $cleanContent, 0, 220, '…', 'UTF-8'),
 'content' => $cleanContent,
 'featured_image' => $image ?: 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80',
 ];
 }

 private function parseAtomItem($entry)
 {
 $title = (string) $entry->title;
 $link = '';
 if (isset($entry->link)) {
 $attrs = $entry->link->attributes();
 $link = (string) ($attrs['href'] ?? '');
 }
 $pubDate = (string) ($entry->published ?? $entry->updated ?? '');
 $summary = (string) ($entry->summary ?? $entry->content ?? '');

 $image = '';
 if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', (string) $entry->content, $m)) {
 $image = $m[1];
 }

 $cleanSummary = $this->cleanTextEntity(strip_tags($summary));

 return [
 'title' => $this->cleanTextEntity($title),
 'link' => trim($link),
 'pubDate' => $pubDate ? date('Y-m-d H:i', strtotime($pubDate)) : date('Y-m-d H:i'),
 'excerpt' => mb_strimwidth($cleanSummary, 0, 220, '…', 'UTF-8'),
 'content' => $cleanSummary,
 'featured_image' => $image ?: 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80',
 ];
 }

 private function cleanTextEntity($text)
 {
 if (empty($text)) return '';
 $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
 $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
 $replacements = [
 '&#8211;' => '–',
 '&#8212;' => '—',
 '&#8216;' => "'",
 '&#8217;' => "'",
 '&#8220;' => '"',
 '&#8221;' => '"',
 '&#039;' => "'",
 '&#39;' => "'",
 '&quot;' => '"',
 '&amp;' => '&',
 '&nbsp;' => ' ',
 '&ndash;' => '–',
 '&mdash;' => '—',
 'amp;#8211;' => '–',
 'amp;#8212;' => '—',
 'amp;#039;' => "'",
 'amp;#39;' => "'",
 'amp;quot;' => '"',
 'amp;amp;' => '&',
 ];
 $text = str_replace(array_keys($replacements), array_values($replacements), $text);
 $text = preg_replace('/&?amp;#(\d+);?/i', ' ', $text);
 $text = preg_replace('/&#(\d+);?/', ' ', $text);

 // Strip boilerplate RSS feeder intro/outro phrases
 $text = preg_replace('/^هذا الموضوع\s+/u', '', $text);
 $text = preg_replace('/ظهر هذا الموضوع أولاً على.*/u', '', $text);
 $text = preg_replace('/ظهر على التقنية بلا حدود.*/u', '', $text);
 $text = preg_replace('/ظهرت أولاً على.*/u', '', $text);
 $text = preg_replace('/The post .* appeared first on .*/i', '', $text);
 $text = preg_replace('/This article was originally published on .*/i', '', $text);

 return trim($text);
 }
}
