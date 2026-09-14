<?php

class AggregatorController extends AdminController
{
 /**
  * ط·ع¾ط·آµط¸â€ ط¸ظ¹ط¸ظ¾ ط·ع¾ط¸â€‍ط¸â€ڑط·آ§ط·آ¦ط¸ظ¹ ط·آ°ط¸ئ’ط¸ظ¹ ط¸â€‍ط¸â€‍ط¸â€¦ط¸â€ڑط·آ§ط¸â€‍ ط·آ§ط¸â€‍ط¸â€¦ط¸â€ ط·آ´ط¸ث†ط·آ± ط·آ¹ط·آ¨ط·آ± ط¸â€‍ط¸ث†ط·آ­ط·آ© ط·آ§ط¸â€‍ط¸â€¦ط·آ¬ط¸â€¦ط¸â€کط·آ¹ ط¸â€¦ط·آ¹ ط¸â€¦ط·آ±ط·آ§ط·آ¹ط·آ§ط·آ© ط·آ§ط¸â€‍ط¸â€¦ط·آµط·آ¯ط·آ±
  * ط¸ث†ط·آ§ط¸â€‍ط¸â€¦ط·آ­ط·ع¾ط¸ث†ط¸â€° ط¸ث†ط·آ±ط·آ§ط·آ¨ط·آ· ط·آ§ط¸â€‍ط¸â€¦ط¸â€ڑط·آ§ط¸â€‍ (ط¸â€ ط¸ظ¾ط·آ³ ط¸â€¦ط·آ­ط·آ±ط¸ئ’ ط·آ§ط¸â€‍ط·ع¾ط·آµط¸â€ ط¸ظ¹ط¸ظ¾ ط·آ§ط¸â€‍ط¸â€¦ط·آ³ط·ع¾ط·آ®ط·آ¯ط¸â€¦ ط¸ظ¾ط¸ظ¹ ط·آ§ط¸â€‍ط¸â‚¬ Cron).
  * ط¸ظ¹ط¸â€ڑط·آ¹ ط·آ¹ط¸â€‍ط¸â€° ط·آ§ط¸â€‍ط¸â€¦ط·آµط·آ¯ط·آ± ط·آ§ط¸â€‍ط¸â€¦ط·آ³ط·آ¬ط¸â€‍ ط·آ£ط¸ث† ط·آ§ط¸â€‍ط¸ظ¾ط·آ¦ط·آ© ط·آ§ط¸â€‍ط·آ§ط¸ظ¾ط·ع¾ط·آ±ط·آ§ط·آ¶ط¸ظ¹ط·آ© ط·آ¥ط¸â€  ط·ع¾ط·آ¹ط·آ°ط¸â€کط·آ± ط·آ§ط¸â€‍ط·ع¾ط·آµط¸â€ ط¸ظ¹ط¸ظ¾.
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

 /**
  * ط¸ظ¹ط·آ±ط·آ¯ط¸â€ک JSON ط·آ¹ط¸â€ ط·آ¯ط¸â€¦ط·آ§ ط¸ظ¹ط¸ئ’ط¸ث†ط¸â€  ط·آ§ط¸â€‍ط·آ·ط¸â€‍ط·آ¨ ط¸â€ڑط·آ§ط·آ¯ط¸â€¦ط·آ§ط¸â€¹ ط¸â€¦ط¸â€  ط¸ث†ط·آ§ط·آ¬ط¸â€،ط·آ© AJAX (ط¸â€ ط·آ´ط·آ± ط·آ¨ط·آ¯ط¸ث†ط¸â€  ط·آ¥ط·آ¹ط·آ§ط·آ¯ط·آ© ط·ع¾ط·آ­ط¸â€¦ط¸ظ¹ط¸â€‍ ط·آ§ط¸â€‍ط·آµط¸ظ¾ط·آ­ط·آ©)ط·إ’
  * ط¸ث†ط¸ظ¹ط·آ¹ط¸ظ¹ط·آ¯ false ط¸â€‍ط¸ظ¹ط·ع¾ط·آ§ط·آ¨ط·آ¹ ط·آ§ط¸â€‍ط¸â€¦ط¸عˆط·آ­ط·آ¯ط¸ع¯ط¸â€کط·آ« ط¸â€¦ط·آ³ط·آ§ط·آ± ط·آ§ط¸â€‍ط¸â‚¬ redirect ط·آ§ط¸â€‍ط¸â€¦ط·آ¹ط·ع¾ط·آ§ط·آ¯ ط¸ظ¾ط¸ظ¹ ط·آ§ط¸â€‍ط¸â€¦ط·ع¾ط·آµط¸ظ¾ط·آ­ ط·آ§ط¸â€‍ط·آ¹ط·آ§ط·آ¯ط¸ظ¹.
  */
 private function ajaxOut(array $payload)
 {
 if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
 && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
 exit;
 }
 return false;
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
 $activeSourceName = parse_url($customFeedUrl, PHP_URL_HOST) ?: 'ط¸â€¦ط·آµط·آ¯ط·آ± ط¸â€¦ط·آ®ط·آµط·آµ';
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
 $unpublishedCount = 0;

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

// ط·آ¹ط·آ¯ط¸â€کط·آ§ط·آ¯ ط·آ§ط¸â€‍ط·آ£ط·آ®ط·آ¨ط·آ§ط·آ± ط·آ§ط¸â€‍ط·آ¬ط·آ¯ط¸ظ¹ط·آ¯ط·آ© ط·ط›ط¸ظ¹ط·آ± ط·آ§ط¸â€‍ط¸â€¦ط¸â€ ط·آ´ط¸ث†ط·آ±ط·آ© (ط¸â€‍ط¸ئ’ط¸â€‍ ط·آ¨ط·آ·ط·آ§ط¸â€ڑط·آ© + ط¸â€‍ط¸â€‍ط¸â€¦ط·آµط·آ¯ط·آ± ط·آ§ط¸â€‍ط¸â€¦ط·آ­ط·آ¯ط·آ¯)
  foreach ($items as $it) {
  if (($it['import_status'] ?? null) !== 'published') $unpublishedCount++;
  }

  // ط·ع¾ط·آ³ط·آ®ط¸ظ¹ط¸â€  ط¸ئ’ط·آ§ط·آ´ ط·آ§ط¸â€‍ط·آµط¸ث†ط·آ± ط¸â€‍ط¸â€‍ط¸â€¦ط·آ¹ط·آ§ط¸ظ¹ط¸â€ ط·آ©: ط¸â€ ط·آ­ط¸â€‍ ط¸â€¦ط·آ¨ط·آ§ط·آ´ط·آ±ط·آ© ط·آ¹ط·آ¯ط·آ¯ط·آ§ط¸â€¹ ط¸â€¦ط·آ­ط·آ¯ط¸ث†ط·آ¯ط·آ§ط¸â€¹ ط¸â€¦ط¸â€  ط·آ§ط¸â€‍ط·آ¹ط¸â€ ط·آ§ط·آµط·آ± ط·آ¨ط¸â€‍ط·آ§ ط·آµط¸ث†ط·آ±
  // ط·آ¶ط¸â€¦ط¸â€  ط¸â€¦ط¸ظ¹ط·آ²ط·آ§ط¸â€ ط¸ظ¹ط·آ© ط·آ²ط¸â€¦ط¸â€ ط¸ظ¹ط·آ© ط¸â€ڑط·آµط¸ظ¹ط·آ±ط·آ© ط·آ­ط·ع¾ط¸â€° ط¸â€‍ط·آ§ ط·ع¾ط·آ¨ط·آ·ط·آ¦ ط·آ§ط¸â€‍ط·آµط¸ظ¾ط·آ­ط·آ©ط·â€؛ ط¸ث†ط·آ§ط¸â€‍ط·آ¨ط·آ§ط¸â€ڑط¸ظ¹ ط¸ظ¹ط¸عˆط¸ئ’ط¸â€¦ط¸â€‍ ط·آ¹ط¸â€ ط·آ¯ ط·آ§ط¸â€‍ط¸â€ ط·آ´ط·آ± ط·آ£ط¸ث†
  // ط·آ§ط¸â€‍ط·ع¾ط·آ­ط¸â€¦ط¸ظ¹ط¸â€‍ط·آ§ط·ع¾ ط·آ§ط¸â€‍ط·ع¾ط·آ§ط¸â€‍ط¸ظ¹ط·آ© (ط·آ§ط¸â€‍ط¸ئ’ط·آ§ط·آ´ ط¸â€¦ط·آ®ط·آ²ط¸â€کط¸â€  ط·آ¹ط¸â€‍ط¸â€° ط·آ§ط¸â€‍ط¸â€ڑط·آ±ط·آµ ط¸ظ¾ط¸ظ¹ storage/cache).
  $warmDeadline = microtime(true) + 8;
  $warmedCount = 0;
  foreach ($items as &$it) {
  $itImg = trim((string) ($it['featured_image'] ?? ''));
  if (!empty($itImg) && !FetchOg::isPlaceholder($itImg)) continue;
  if (microtime(true) >= $warmDeadline || $warmedCount >= 8) break;
  $itLink = trim((string) ($it['link'] ?? ''));
  if ($itLink === '' || !filter_var($itLink, FILTER_VALIDATE_URL)) continue;
  $resolvedImg = FetchOg::resolve($itLink);
  if ($resolvedImg !== '') {
  $it['featured_image'] = $resolvedImg;
  $warmedCount++;
  }
  }
  unset($it);

  // ط·ع¾ط·آ­ط·آ¯ط¸ظ¹ط·آ« ط·آ¹ط·آ¯ط¸â€کط·آ§ط·آ¯ ط·آ®ط¸â€‍ط·آ§ط·آµط·آ© ط·آ§ط¸â€‍ط¸â€¦ط·آµط·آ¯ط·آ± ط·آ§ط¸â€‍ط¸â€¦ط·آ³ط·آ¬ط¸â€کط¸â€‍ ط¸ظ¾ط¸â€ڑط·آ· (ط¸ث†ط¸â€‍ط¸ظ¹ط·آ³ ط·آ±ط·آ§ط·آ¨ط·آ· ط¸â€¦ط·آ®ط·آµط·آµ ط·آ¹ط·آ§ط·آ¨ط·آ±)
 if (empty($customFeedUrl) && $selectedSourceId > 0) {
 $publishedLinks = [];
 foreach ($items as $it) {
 if (($it['import_status'] ?? null) === 'published') {
 $publishedLinks[$it['link']] = true;
 }
 }
 require_once __DIR__ . '/../../core/FeedFreshness.php';
 FeedFreshness::recount($db, (int) $selectedSourceId, $publishedLinks, $items);
 }
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
 'unpublishedCount' => $unpublishedCount,
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
 $sourceName = $this->cleanTextEntity(trim($data['source_name'] ?? 'ط¸â€¦ط·آµط·آ¯ط·آ± ط·آ®ط·آ§ط·آ±ط·آ¬ط¸ظ¹'));
 $content = $this->cleanTextEntity($_POST['content'] ?? ($data['excerpt'] ?? ''));
 $excerpt = $this->cleanTextEntity(trim($data['excerpt'] ?? mb_strimwidth(strip_tags($content), 0, 200, 'أ¢â‚¬آ¦', 'UTF-8')));
$featuredImage = trim($data['featured_image'] ?? '');
  // Drop absurdly long image URLs (feed junk) so the INSERT never overflows.
  if (strlen($featuredImage) > 1000) $featuredImage = '';
  // ط·آ¹ط¸â€ ط·آ§ط·آµط·آ± ط·آ®ط¸â€‍ط·آ§ط·آµط·آ§ط·ع¾ ط¸â€¦ط·آ«ط¸â€‍ Google News ط¸â€‍ط·آ§ ط·ع¾ط·آ­ط¸â€¦ط¸â€‍ ط·آµط¸ث†ط·آ±ط·آ§ط¸â€¹ ط·آ¯ط·آ§ط·آ®ط¸â€‍ XMLط·â€؛ ط¸â€ ط·آ¬ط¸â€‍ط·آ¨ ط·آ§ط¸â€‍ط·آµط¸ث†ط·آ±ط·آ© ط·آ§ط¸â€‍ط·آ¨ط·آ§ط·آ±ط·آ²ط·آ©
  // ط·آ§ط¸â€‍ط·آ­ط¸â€ڑط¸ظ¹ط¸â€ڑط¸ظ¹ط·آ© ط¸â€¦ط¸â€  ط·آµط¸ظ¾ط·آ­ط·آ© ط·آ§ط¸â€‍ط¸â€¦ط¸â€ڑط·آ§ط¸â€‍ ط·آ§ط¸â€‍ط·آ£ط·آµط¸â€‍ط¸ظ¹ط·آ© (og:image ط¸â€¦ط·آ¹ ط¸ئ’ط·آ§ط·آ´ ط·آ¹ط¸â€‍ط¸â€° ط·آ§ط¸â€‍ط¸â€ڑط·آ±ط·آµ) ط·آ¹ط¸â€ ط·آ¯ ط·ط›ط¸ظ¹ط·آ§ط·آ¨ط¸â€،ط·آ§.
  $featuredImage = FetchOg::resolveFor($sourceUrl, $featuredImage);

 if (empty($title)) {
 if ($this->ajaxOut(['success' => false, 'error' => 'ط·آ¹ط¸â€ ط¸ث†ط·آ§ط¸â€  ط·آ§ط¸â€‍ط¸â€¦ط¸â€ڑط·آ§ط¸â€‍ ط¸â€¦ط·آ·ط¸â€‍ط¸ث†ط·آ¨ ط¸â€‍ط¸â€‍ط¸â€ ط·آ´ط·آ±.'])) return;
 Session::flash('error', 'ط·آ¹ط¸â€ ط¸ث†ط·آ§ط¸â€  ط·آ§ط¸â€‍ط¸â€¦ط¸â€ڑط·آ§ط¸â€‍ ط¸â€¦ط·آ·ط¸â€‍ط¸ث†ط·آ¨ ط¸â€‍ط¸â€‍ط¸â€ ط·آ´ط·آ±.');
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
 Session::flash('success', "ط·ع¾ط¸â€¦ ط·آ§ط¸â€‍ط·آ¹ط·آ«ط¸ث†ط·آ± ط·آ¹ط¸â€‍ط¸â€° ط·آ§ط¸â€‍ط¸â€¦ط¸â€ڑط·آ§ط¸â€‍ ط·آ§ط¸â€‍ط¸â€¦ط¸â€ ط·آ´ط¸ث†ط·آ± ط¸â€¦ط·آ³ط·آ¨ط¸â€ڑط·آ§ط¸â€¹ ط¸ث†ط·ع¾ط·آ­ط·آ¯ط¸ظ¹ط·آ«ط¸â€، ط¸ث†ط·ع¾ط·آ±ط·آ¬ط¸â€¦ط·ع¾ط¸â€، ط¸ظ¾ط¸ث†ط·آ±ط·آ§ط¸â€¹ ط·آ¥ط¸â€‍ط¸â€° ط·آ§ط¸â€‍ط·آ¹ط·آ±ط·آ¨ط¸ظ¹ط·آ© ط·آ¨ط·آ¹ط¸â€ ط¸ث†ط·آ§ط¸â€ : \"{$titleAr}\"!");
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
 Session::flash('success', "ط·ع¾ط¸â€¦ط·ع¾ ط·ع¾ط·آ±ط·آ¬ط¸â€¦ط·آ© ط¸ث†ط·آµط¸ظ¹ط·آ§ط·ط›ط·آ© ط¸ث†ط¸â€ ط·آ´ط·آ± ط·آ§ط¸â€‍ط·آ®ط·آ¨ط·آ± ط¸ظ¾ط¸ث†ط·آ±ط·آ§ط¸â€¹ ط·آ¨ط¸â€ ط·آ¬ط·آ§ط·آ­ ط·آ¨ط·آ§ط¸â€‍ط¸â€‍ط·ط›ط·آ© ط·آ§ط¸â€‍ط·آ¹ط·آ±ط·آ¨ط¸ظ¹ط·آ© ط·آ¨ط·آ¹ط¸â€ ط¸ث†ط·آ§ط¸â€ : \"{$titleAr}\"!");
 }
 } catch (Throwable $e) {
 error_log('translatePublish error: ' . $e->getMessage());
 if ($this->ajaxOut(['success' => false, 'error' => 'ط·ع¾ط·آ¹ط·آ°ط·آ± ط·آ¥ط·ع¾ط¸â€¦ط·آ§ط¸â€¦ ط·آ§ط¸â€‍ط·ع¾ط·آ±ط·آ¬ط¸â€¦ط·آ© ط¸ث†ط·آ§ط¸â€‍ط¸â€ ط·آ´ط·آ±: ' . $e->getMessage()])) return;
 Session::flash('error', 'ط·ع¾ط·آ¹ط·آ°ط·آ± ط·آ¥ط·ع¾ط¸â€¦ط·آ§ط¸â€¦ ط·آ§ط¸â€‍ط·ع¾ط·آ±ط·آ¬ط¸â€¦ط·آ© ط¸ث†ط·آ§ط¸â€‍ط¸â€ ط·آ´ط·آ±: ' . $e->getMessage());
 }
 
 $okArticleId = (int) ($newId ?? $existing['id'] ?? 0);
 if ($this->ajaxOut([
 'success' => true,
 'article_id' => $okArticleId,
 'edit_url' => app_url('admin/articles/' . $okArticleId . '/edit'),
 'message' => "ط·ع¾ط¸â€¦ط·ع¾ ط·آ§ط¸â€‍ط·ع¾ط·آ±ط·آ¬ط¸â€¦ط·آ© ط¸ث†ط·آ§ط¸â€‍ط¸â€ ط·آ´ط·آ± ط·آ¨ط¸â€ ط·آ¬ط·آ§ط·آ­ ط·آ¨ط·آ¹ط¸â€ ط¸ث†ط·آ§ط¸â€ : \"{$titleAr}\"!",
 ])) return;

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
 $sourceName = $this->cleanTextEntity(trim($data['source_name'] ?? 'ط¸â€¦ط·آµط·آ¯ط·آ± ط·آ®ط·آ§ط·آ±ط·آ¬ط¸ظ¹'));
 $content = $this->cleanTextEntity($_POST['content'] ?? ($data['excerpt'] ?? ''));
 $excerpt = $this->cleanTextEntity(trim($data['excerpt'] ?? mb_strimwidth(strip_tags($content), 0, 200, 'أ¢â‚¬آ¦', 'UTF-8')));
$featuredImage = trim($data['featured_image'] ?? '');
  // Drop absurdly long image URLs (feed junk) so the INSERT never overflows.
  if (strlen($featuredImage) > 1000) $featuredImage = '';
  // ط·آ¹ط¸â€ ط·آ§ط·آµط·آ± ط·آ®ط¸â€‍ط·آ§ط·آµط·آ§ط·ع¾ ط¸â€¦ط·آ«ط¸â€‍ Google News ط¸â€‍ط·آ§ ط·ع¾ط·آ­ط¸â€¦ط¸â€‍ ط·آµط¸ث†ط·آ±ط·آ§ط¸â€¹ ط·آ¯ط·آ§ط·آ®ط¸â€‍ XMLط·â€؛ ط¸â€ ط·آ¬ط¸â€‍ط·آ¨ ط·آ§ط¸â€‍ط·آµط¸ث†ط·آ±ط·آ© ط·آ§ط¸â€‍ط·آ¨ط·آ§ط·آ±ط·آ²ط·آ©
  // ط·آ§ط¸â€‍ط·آ­ط¸â€ڑط¸ظ¹ط¸â€ڑط¸ظ¹ط·آ© ط¸â€¦ط¸â€  ط·آµط¸ظ¾ط·آ­ط·آ© ط·آ§ط¸â€‍ط¸â€¦ط¸â€ڑط·آ§ط¸â€‍ ط·آ§ط¸â€‍ط·آ£ط·آµط¸â€‍ط¸ظ¹ط·آ© (og:image ط¸â€¦ط·آ¹ ط¸ئ’ط·آ§ط·آ´ ط·آ¹ط¸â€‍ط¸â€° ط·آ§ط¸â€‍ط¸â€ڑط·آ±ط·آµ) ط·آ¹ط¸â€ ط·آ¯ ط·ط›ط¸ظ¹ط·آ§ط·آ¨ط¸â€،ط·آ§.
  $featuredImage = FetchOg::resolveFor($sourceUrl, $featuredImage);

 if (empty($title)) {
 if ($this->ajaxOut(['success' => false, 'error' => 'ط·آ¹ط¸â€ ط¸ث†ط·آ§ط¸â€  ط·آ§ط¸â€‍ط¸â€¦ط¸â€ڑط·آ§ط¸â€‍ ط¸â€¦ط·آ·ط¸â€‍ط¸ث†ط·آ¨ ط¸â€‍ط¸â€‍ط¸â€ ط·آ´ط·آ±.'])) return;
 Session::flash('error', 'ط·آ¹ط¸â€ ط¸ث†ط·آ§ط¸â€  ط·آ§ط¸â€‍ط¸â€¦ط¸â€ڑط·آ§ط¸â€‍ ط¸â€¦ط·آ·ط¸â€‍ط¸ث†ط·آ¨ ط¸â€‍ط¸â€‍ط¸â€ ط·آ´ط·آ±.');
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
            Session::flash('success', "ط·ع¾ط¸â€¦ ط·آ§ط¸â€‍ط·آ¹ط·آ«ط¸ث†ط·آ± ط·آ¹ط¸â€‍ط¸â€° ط·آ§ط¸â€‍ط¸â€¦ط¸â€ڑط·آ§ط¸â€‍ ط·آ§ط¸â€‍ط¸â€¦ط¸â€ ط·آ´ط¸ث†ط·آ± ط¸â€¦ط·آ³ط·آ¨ط¸â€ڑط·آ§ط¸â€¹ ط¸ث†ط·ع¾ط·آ­ط·آ¯ط¸ظ¹ط·آ« ط¸â€¦ط·آ­ط·ع¾ط¸ث†ط·آ§ط¸â€، ط¸ظ¾ط¸ث†ط·آ±ط·آ§ط¸â€¹ ط·آ¨ط·آ¹ط¸â€ ط¸ث†ط·آ§ط¸â€ : \"{$title}\"!");
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
 Session::flash('success', "ط·ع¾ط¸â€¦ ط·آ§ط¸â€‍ط¸â€ ط·آ´ط·آ± ط·آ§ط¸â€‍ط¸ظ¾ط¸ث†ط·آ±ط¸ظ¹ ط·آ§ط¸â€‍ط¸â€¦ط·آ¨ط·آ§ط·آ´ط·آ± ط·آ¨ط¸â€ ط·آ¬ط·آ§ط·آ­ ط·آ¨ط·آ¯ط¸ث†ط¸â€  ط·ع¾ط·آ±ط·آ¬ط¸â€¦ط·آ© ط·آ¨ط·آ¹ط¸â€ ط¸ث†ط·آ§ط¸â€ : \"{$title}\"!");
 }
 
 $okArticleId = (int) ($newId ?? $existing['id'] ?? 0);
 if ($this->ajaxOut([
 'success' => true,
 'article_id' => $okArticleId,
 'edit_url' => app_url('admin/articles/' . $okArticleId . '/edit'),
 'message' => "ط·ع¾ط¸â€¦ ط·آ§ط¸â€‍ط¸â€ ط·آ´ط·آ± ط·آ§ط¸â€‍ط¸ظ¾ط¸ث†ط·آ±ط¸ظ¹ ط·آ§ط¸â€‍ط¸â€¦ط·آ¨ط·آ§ط·آ´ط·آ± ط·آ¨ط¸â€ ط·آ¬ط·آ§ط·آ­ ط·آ¨ط·آ¹ط¸â€ ط¸ث†ط·آ§ط¸â€ : \"{$title}\"!",
 ])) return;

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
        $sourceName = $this->cleanTextEntity(trim($data['source_name'] ?? 'ط¸â€¦ط·آµط·آ¯ط·آ± ط·آ®ط·آ§ط·آ±ط·آ¬ط¸ظ¹'));
        $content = $this->cleanTextEntity($_POST['content'] ?? ($data['excerpt'] ?? ''));
        $excerpt = $this->cleanTextEntity(trim($data['excerpt'] ?? mb_strimwidth(strip_tags($content), 0, 200, 'أ¢â‚¬آ¦', 'UTF-8')));
        $featuredImage = trim($data['featured_image'] ?? '');
        // Drop absurdly long image URLs (feed junk) so the INSERT never overflows.
        if (strlen($featuredImage) > 1000) $featuredImage = '';
        // ط·آ¹ط¸â€ ط·آ§ط·آµط·آ± ط·آ®ط¸â€‍ط·آ§ط·آµط·آ§ط·ع¾ ط¸â€¦ط·آ«ط¸â€‍ Google News ط¸â€‍ط·آ§ ط·ع¾ط·آ­ط¸â€¦ط¸â€‍ ط·آµط¸ث†ط·آ±ط·آ§ط¸â€¹ ط·آ¯ط·آ§ط·آ®ط¸â€‍ XMLط·â€؛ ط¸â€ ط·آ¬ط¸â€‍ط·آ¨ ط·آ§ط¸â€‍ط·آµط¸ث†ط·آ±ط·آ© ط·آ§ط¸â€‍ط·آ¨ط·آ§ط·آ±ط·آ²ط·آ©
        // ط·آ§ط¸â€‍ط·آ­ط¸â€ڑط¸ظ¹ط¸â€ڑط¸ظ¹ط·آ© ط¸â€¦ط¸â€  ط·آµط¸ظ¾ط·آ­ط·آ© ط·آ§ط¸â€‍ط¸â€¦ط¸â€ڑط·آ§ط¸â€‍ ط·آ§ط¸â€‍ط·آ£ط·آµط¸â€‍ط¸ظ¹ط·آ© (og:image ط¸â€¦ط·آ¹ ط¸ئ’ط·آ§ط·آ´ ط·آ¹ط¸â€‍ط¸â€° ط·آ§ط¸â€‍ط¸â€ڑط·آ±ط·آµ) ط·آ¹ط¸â€ ط·آ¯ ط·ط›ط¸ظ¹ط·آ§ط·آ¨ط¸â€،ط·آ§.
        $featuredImage = FetchOg::resolveFor($sourceUrl, $featuredImage);

if (empty($title)) {
 if ($this->ajaxOut(['success' => false, 'error' => 'ط·آ¹ط¸â€ ط¸ث†ط·آ§ط¸â€  ط·آ§ط¸â€‍ط¸â€¦ط¸â€ڑط·آ§ط¸â€‍ ط¸â€¦ط·آ·ط¸â€‍ط¸ث†ط·آ¨.'])) return;
 Session::flash('error', 'ط·آ¹ط¸â€ ط¸ث†ط·آ§ط¸â€  ط·آ§ط¸â€‍ط¸â€¦ط¸â€ڑط·آ§ط¸â€‍ ط¸â€¦ط·آ·ط¸â€‍ط¸ث†ط·آ¨.');
 return $this->redirect('admin/news-feeds');
 }

 $db = new Database();

 // Check if this source_url is already in articles
 if (!empty($sourceUrl)) {
 $existing = $db->fetch("SELECT id FROM articles WHERE source_url = :url", [':url' => $sourceUrl]);
 if ($existing) {
 $editUrl = app_url('admin/articles/' . (int) $existing['id'] . '/edit');
 if ($this->ajaxOut(['success' => true, 'article_id' => (int) $existing['id'], 'edit_url' => $editUrl, 'redirect' => (int) $existing['id']])) return;
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
 Session::flash('success', "ط·ع¾ط¸â€¦ ط·آ§ط·آ³ط·ع¾ط¸ظ¹ط·آ±ط·آ§ط·آ¯ ط·آ§ط¸â€‍ط·آ®ط·آ¨ط·آ± ط¸ئ’ط¸â€¦ط·آ³ط¸ث†ط·آ¯ط·آ© ط·آ¨ط¸â€ ط·آ¬ط·آ§ط·آ­! ط¸ظ¹ط¸â€¦ط¸ئ’ط¸â€ ط¸ئ’ ط·آ§ط¸â€‍ط·آ¢ط¸â€  ط¸â€¦ط·آ±ط·آ§ط·آ¬ط·آ¹ط·ع¾ط¸â€، ط¸ث†ط·آµط¸ظ¹ط·آ§ط·ط›ط·ع¾ط¸â€، ط¸ث†ط¸â€ ط·آ´ط·آ±ط¸â€،.");
 if ($this->ajaxOut([
 'success' => true,
 'article_id' => (int) $newId,
 'edit_url' => app_url('admin/articles/' . (int) $newId . '/edit'),
 'redirect' => (int) $newId,
 ])) return;
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
 Session::flash('error', 'ط·آ§ط·آ³ط¸â€¦ ط·آ§ط¸â€‍ط¸â€¦ط·آµط·آ¯ط·آ± ط¸ث†ط·آ±ط·آ§ط·آ¨ط·آ· ط·آ§ط¸â€‍ط¸â‚¬ RSS ط¸â€¦ط·آ·ط¸â€‍ط¸ث†ط·آ¨ط·آ§ط¸â€ .');
 return $this->redirect('admin/news-feeds');
 }

 $db = new Database();
 $db->query("INSERT INTO rss_sources (name, url, category_id) VALUES (:name, :url, :category_id)", [
 ':name' => $name,
 ':url' => $url,
 ':category_id' => $categoryId
 ]);

 Session::flash('success', "ط·ع¾ط¸â€¦ط·ع¾ ط·آ¥ط·آ¶ط·آ§ط¸ظ¾ط·آ© ط·آ§ط¸â€‍ط¸â€¦ط·آµط·آ¯ط·آ± ط·آ§ط¸â€‍ط·ع¾ط¸â€ڑط¸â€ ط¸ظ¹ \"{$name}\" ط·آ¨ط¸â€ ط·آ¬ط·آ§ط·آ­.");
 return $this->redirect('admin/news-feeds');
 }

 public function deleteSource($id)
 {
 $this->postGuard();
 $db = new Database();
 $db->query("DELETE FROM rss_sources WHERE id = :id", [':id' => (int) $id]);
 Session::flash('success', 'ط·ع¾ط¸â€¦ ط·آ­ط·آ°ط¸ظ¾ ط·آ§ط¸â€‍ط¸â€¦ط·آµط·آ¯ط·آ± ط·آ¨ط¸â€ ط·آ¬ط·آ§ط·آ­.');
 return $this->redirect('admin/news-feeds');
 }

 /**
 * ط¸ظ¾ط·آ­ط·آµ ط·آµط·آ­ط·آ© ط¸ئ’ط¸â€‍ ط¸â€¦ط·آµط·آ§ط·آ¯ط·آ± RSS ط·آ§ط¸â€‍ط¸â€¦ط·آ³ط·آ¬ط¸â€‍ط·آ© (ط·آ£ط¸ث† ط¸â€¦ط·آµط·آ¯ط·آ± ط¸ث†ط·آ§ط·آ­ط·آ¯) ط¸ث†ط·ع¾ط·آ®ط·آ²ط¸ظ¹ط¸â€  ط·آ§ط¸â€‍ط¸â€ ط·ع¾ط¸ظ¹ط·آ¬ط·آ© ط¸ظ¾ط¸ظ¹ ط¸â€ڑط·آ§ط·آ¹ط·آ¯ط·آ© ط·آ§ط¸â€‍ط·آ¨ط¸ظ¹ط·آ§ط¸â€ ط·آ§ط·ع¾.
 * ط¸ظ¹ط¸عˆط·آ±ط·آ¬ط·آ¹ JSON ط¸â€‍ط·آ§ط·آ³ط·ع¾ط¸â€،ط¸â€‍ط·آ§ط¸ئ’ط¸â€، ط¸â€¦ط¸â€  ط¸ث†ط·آ§ط·آ¬ط¸â€،ط·آ© ط¸â€‍ط¸ث†ط·آ­ط·آ© ط·آ§ط¸â€‍ط·ع¾ط·آ­ط¸ئ’ط¸â€¦.
 */
public function healthCheck()
 {
 $this->guardAdmin();

 // ط¸â€¦ط¸ظ¹ط·آ²ط·آ§ط¸â€ ط¸ظ¹ط·آ© ط·آ²ط¸â€¦ط¸â€ ط¸ظ¹ط·آ© ط·آ«ط·آ§ط·آ¨ط·ع¾ط·آ© ط¸â€‍ط¸ئ’ط¸â€‍ ط·آ·ط¸â€‍ط·آ¨: ط·آ¨ط·ط›ط·آ¶ط¸â€ک ط·آ§ط¸â€‍ط¸â€ ط·آ¸ط·آ± ط·آ¹ط¸â€  ط·آ¨ط·آ·ط·طŒ ط·آ§ط¸â€‍ط·آ®ط¸â€‍ط·آ§ط·آµط·آ§ط·ع¾ ط¸â€ ط·آ¶ط¸â€¦ط¸â€  ط·آ¥ط·آ±ط·آ¬ط·آ§ط·آ¹ ط·آ§ط·آ³ط·ع¾ط·آ¬ط·آ§ط·آ¨ط·آ©
 // ط¸ئ’ط·آ§ط¸â€¦ط¸â€‍ط·آ© ط¸â€ڑط·آ¨ط¸â€‍ ط¸â€¦ط¸â€،ط¸â€‍ط·آ© ط·آ§ط¸â€‍ط·آ§ط·آ³ط·ع¾ط·آ¶ط·آ§ط¸ظ¾ط·آ© ط·آ§ط¸â€‍ط¸â€ڑط·آµط¸ث†ط¸â€° (~30 ط·آ«)ط·إ’ ط·آ¹ط·آ¨ط·آ± ط·ع¾ط¸â€ڑط·آ³ط¸ظ¹ط¸â€¦ ط·آ§ط¸â€‍ط¸ظ¾ط·آ­ط·آµ ط·آ¥ط¸â€‍ط¸â€° ط·آ¯ط¸ظ¾ط·آ¹ط·آ§ط·ع¾ ط·آµط·ط›ط¸ظ¹ط·آ±ط·آ©
 // ط·ع¾ط¸عˆط·ع¾ط·آ§ط·آ¨ط·آ¹ط¸â€،ط·آ§ ط·آ§ط¸â€‍ط¸ث†ط·آ§ط·آ¬ط¸â€،ط·آ© (offset/limit). ط¸ئ’ط¸â€‍ ط·آ·ط¸â€‍ط·آ¨ ط¸ظ¹ط·آ¹ط·آ§ط¸â€‍ط·آ¬ ط¸â€¦ط·آµط·آ¯ط·آ±ط·آ§ط¸â€¹ ط¸ث†ط·آ§ط·آ­ط·آ¯ط·آ§ط¸â€¹ ط·آ¹ط¸â€‍ط¸â€° ط·آ§ط¸â€‍ط·آ£ط¸â€ڑط¸â€‍.
 $started = microtime(true);
 $budget = 10; // ط·آ«ط¸ث†ط·آ§ط¸â€ ط¸ع† ط¸ئ’ط·آ­ط·آ¯ط¸â€ک ط·آ£ط¸â€ڑط·آµط¸â€° ط¸â€‍ط¸â€¦ط·آ¹ط·آ§ط¸â€‍ط·آ¬ط·آ© ط·آ·ط¸â€‍ط·آ¨ ط¸ث†ط·آ§ط·آ­ط·آ¯
 @set_time_limit($budget + 20);

 $db = new Database();
 $singleId = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);

 // Batching: shared hosting caps max_execution_time (30أ¢â‚¬â€œ60s), so the full
 // 31-source scan is split into small requests the frontend chains together.
 $offset = max(0, (int) ($_GET['offset'] ?? 0));
 $limit  = max(0, (int) ($_GET['limit'] ?? 0));

 if ($singleId > 0) {
  $sources = $db->fetchAll('SELECT id, name, url FROM rss_sources WHERE id = :id', [':id' => $singleId]);
  $sourceCount = count($sources);
 } else {
  $sourceCount = (int) ($db->fetch('SELECT COUNT(*) as c FROM rss_sources')['c'] ?? 0);
  if ($limit > 0) {
  $sources = $db->fetchAll('SELECT id, name, url FROM rss_sources ORDER BY id LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset);
  } else {
  $sources = $db->fetchAll('SELECT id, name, url FROM rss_sources ORDER BY id');
  }
 }

 $results = [];
 $okCount = 0;
 $failCount = 0;
 $didWork = false;

 foreach ($sources as $s) {
  // ط¸â€ ط·آ¶ط¸â€¦ط¸â€  ط¸â€¦ط·آ¹ط·آ§ط¸â€‍ط·آ¬ط·آ© ط¸â€¦ط·آµط·آ¯ط·آ± ط¸ث†ط·آ§ط·آ­ط·آ¯ ط·آ¹ط¸â€‍ط¸â€° ط·آ§ط¸â€‍ط·آ£ط¸â€ڑط¸â€‍ط·إ’ ط·آ«ط¸â€¦ ط¸â€ ط·ع¾ط¸ث†ط¸â€ڑط¸ظ¾ ط·آ¹ط¸â€ ط·آ¯ ط·آ§ط¸â€ڑط·ع¾ط·آ±ط·آ§ط·آ¨ ط·آ§ط¸â€‍ط¸â€¦ط¸ظ¹ط·آ²ط·آ§ط¸â€ ط¸ظ¹ط·آ©
  // ط¸â€‍ط·ع¾ط·آ±ط¸ئ’ ط·آ§ط¸â€‍ط·آ¯ط¸ظ¾ط·آ¹ط·آ© ط·آ§ط¸â€‍ط·ع¾ط·آ§ط¸â€‍ط¸ظ¹ط·آ© (done=false) ط·ع¾ط¸ئ’ط¸â€¦ط¸â€‍ ط·آ§ط¸â€‍ط·آ¨ط·آ§ط¸â€ڑط¸ظ¹ ط¸â€ڑط·آ¨ط¸â€‍ ط¸ث†ط·آµط¸ث†ط¸â€‍ PHP ط¸â€‍ط¸â€‍ط¸â€¦ط¸â€،ط¸â€‍ط·آ©.
  if ($didWork && (microtime(true) - $started) >= $budget) {
  break;
  }
  $didWork = true;

  // ط·آ§ط¸â€‍ط¸ظ¾ط·آ­ط·آµ ط·آ§ط¸â€‍ط·آ¬ط¸â€¦ط·آ§ط·آ¹ط¸ظ¹: ط¸ث†ط·آ¶ط·آ¹ ط·آ³ط·آ±ط¸ظ¹ط·آ¹ ط¸â€¦ط·آ¹ ط·ع¾ط¸â€ڑط¸â€‍ط¸ظ¹ط·آµ ط¸â€¦ط¸â€،ط¸â€‍ط·آ© ط¸â€،ط·آ°ط·آ§ ط·آ§ط¸â€‍ط¸â€¦ط·آµط·آ¯ط·آ± ط·آ¨ط¸â€¦ط·آ§ ط·ع¾ط·آ¨ط¸â€ڑط¸â€کط¸â€° ط¸â€¦ط¸â€  ط·آ§ط¸â€‍ط¸â€¦ط¸ظ¹ط·آ²ط·آ§ط¸â€ ط¸ظ¹ط·آ©ط·â€؛
  // ط·آ§ط¸â€‍ط¸ظ¾ط·آ­ط·آµ ط·آ§ط¸â€‍ط¸â€¦ط¸ظ¾ط·آ±ط·آ¯: ط¸â€¦ط¸â€،ط¸â€‍ط·آ© ط·آ¹ط¸â€‍ط¸ظ¹ط·آ§ 15 ط·آ« ط·آ­ط·ع¾ط¸â€° ط¸â€‍ط·آ§ ط·ع¾ط·ع¾ط·آ¬ط·آ§ط¸ث†ط·آ² ط¸â€¦ط¸â€،ط¸â€‍ط·آ© ط·آ§ط¸â€‍ط·آ§ط·آ³ط·ع¾ط·آ¶ط·آ§ط¸ظ¾ط·آ©.
  $remainingMs = (int) (($budget - (microtime(true) - $started)) * 1000);
  $fetch = FeedFetcher::fetchRaw($s['url'], $singleId > 0 ? false : true, $singleId > 0 ? 15000 : max(1000, min(6000, $remainingMs)));

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
 // ط·آ®ط¸â€‍ط·آ§ط·آµط·آ© XML ط·ط›ط¸ظ¹ط·آ± ط·آµط·آ§ط¸â€‍ط·آ­ط·آ© أ¢â‚¬â€‌ ط·آ¬ط·آ±ط¸â€کط·آ¨ ط·آ§ط¸â€‍ط¸ئ’ط·آ§ط·آ´ط·آ· ط·آ§ط¸â€‍ط·آ°ط¸ئ’ط¸ظ¹ ط¸ئ’ط·آ®ط¸ظ¹ط·آ§ط·آ± ط·آ§ط·آ­ط·ع¾ط¸ظ¹ط·آ§ط·آ·ط¸ظ¹
 $scraped = $this->scrapeHtmlPage($fetch['body'], $s['url']);
 if (!empty($scraped)) {
 $status = 'ok';
 $itemCount = count($scraped);
 $sampleTitle = $scraped[0]['title'] ?? '';
 $error = '';
 } else {
 $status = 'empty';
 $error = 'ط·ع¾ط¸â€¦ ط·آ§ط¸â€‍ط·آ§ط·ع¾ط·آµط·آ§ط¸â€‍ ط·آ¨ط¸â€ ط·آ¬ط·آ§ط·آ­ ط¸â€‍ط¸ئ’ط¸â€  ط¸â€‍ط¸â€¦ ط¸ظ¹ط¸عˆط·آ¹ط·آ«ط·آ± ط·آ¹ط¸â€‍ط¸â€° ط·آ¹ط¸â€ ط·آ§ط·آµط·آ± ط·آ¥ط·آ®ط·آ¨ط·آ§ط·آ±ط¸ظ¹ط·آ© ط·آ¯ط·آ§ط·آ®ط¸â€‍ ط·آ§ط¸â€‍ط·آ®ط¸â€‍ط·آ§ط·آµط·آ©.';
 }
 }
 }

$suggestions = [];
  if ($status !== 'ok' && $singleId > 0 && (microtime(true) - $started) < 6) {
  // ط·آ§ط¸â€ڑط·ع¾ط·آ±ط·آ§ط·آ­ط·آ§ط·ع¾ ط·آ§ط¸â€‍ط·آ¨ط·آ¯ط·آ§ط·آ¦ط¸â€‍ ط·ع¾ط·آ­ط·ع¾ط·آ§ط·آ¬ ط·آ·ط¸â€‍ط·آ¨ط·آ§ط·ع¾ ط·آ´ط·آ¨ط¸ئ’ط·آ© ط·آ¥ط·آ¶ط·آ§ط¸ظ¾ط¸ظ¹ط·آ©ط·â€؛ ط·ع¾ط¸عˆط¸â€ ط¸ظ¾ط¸عکط¸â€کط·آ° ط¸ظ¾ط¸â€ڑط·آ· ط¸ظ¾ط¸ظ¹ ط·آ§ط¸â€‍ط¸ظ¾ط·آ­ط·آµ ط·آ§ط¸â€‍ط¸â€¦ط¸ظ¾ط·آ±ط·آ¯ط·إ’
  // ط¸ث†ط¸ظ¾ط¸â€ڑط·آ· ط·آ¥ط·آ°ط·آ§ ط·آ¨ط¸â€ڑط¸ظ¹ ط¸â€¦ط¸â€  ط·آ§ط¸â€‍ط¸â€¦ط¸ظ¹ط·آ²ط·آ§ط¸â€ ط¸ظ¹ط·آ© ط¸â€¦ط·ع¾ط¸â€کط·آ³ط·آ¹ط·إ’ ط·آ­ط·ع¾ط¸â€° ط¸â€‍ط·آ§ ط·ع¾ط·آ·ط¸â€‍ط¸â€ڑ ط·آ§ط¸â€‍ط¸â€¦ط¸â€،ط¸â€‍ط·آ© ط·آ¹ط¸â€‍ط¸â€° ط·آ§ط¸â€‍ط·آ§ط·آ³ط·ع¾ط·آ¶ط·آ§ط¸ظ¾ط·آ©.
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

// done ط·آµط·آ­ط¸ظ¹ط·آ­ ط¸ظ¾ط¸â€ڑط·آ· ط·آ¹ط¸â€ ط·آ¯ ط·آ§ط·آ³ط·ع¾ط¸ئ’ط¸â€¦ط·آ§ط¸â€‍ ط¸ئ’ط¸â€‍ ط·آ§ط¸â€‍ط¸â€¦ط·آµط·آ§ط·آ¯ط·آ± (ط·آ£ط¸ث† ط·آ§ط¸â€ ط·ع¾ط¸â€،ط·آ§ط·طŒ ط·آ§ط¸â€‍ط·آ¯ط¸ظ¾ط·آ¹ط·آ© ط·آ¨ط¸â€‍ط·آ§ ط·آ¨ط¸â€ڑط¸ظ¹ط¸â€کط·آ©)ط·â€؛
  // ط·آ£ط¸â€¦ط·آ§ ط·آ¥ط·آ°ط·آ§ ط·ع¾ط¸ث†ط¸â€ڑط¸ظ¾ط¸â€ ط·آ§ ط·آ¨ط·آ³ط·آ¨ط·آ¨ ط·آ§ط¸â€‍ط¸â€¦ط¸ظ¹ط·آ²ط·آ§ط¸â€ ط¸ظ¹ط·آ© ط¸ظ¾ط·ع¾ط¸عˆط¸ئ’ط¸â€¦ط¸â€کط¸â€‍ ط·آ§ط¸â€‍ط¸ث†ط·آ§ط·آ¬ط¸â€،ط·آ© ط¸â€¦ط¸â€  offset ط·آ§ط¸â€‍ط¸â€ ط·آ§ط·ع¾ط·آ¬.
  $done = ($offset + count($results)) >= $sourceCount;

 header('Content-Type: application/json; charset=utf-8');
  echo json_encode([
   'success' => true,
   'total' => count($results),
   'ok' => $okCount,
   'failed' => $failCount,
   'results' => $results,
   'sourceCount' => $sourceCount,
   'offset' => $offset + count($results),
   'done' => $done,
   ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
 }

 /** ط·ع¾ط·آ³ط¸â€¦ط¸ظ¹ط·آ© ط¸â€¦ط¸â€ڑط·آ±ط¸ث†ط·طŒط·آ© ط¸â€‍ط¸ث†ط¸ئ’ط¸ظ¹ط¸â€‍ ط·آ§ط¸â€‍ط¸â€¦ط·آ³ط·ع¾ط·آ®ط·آ¯ط¸â€¦ ط·آ§ط¸â€‍ط·آ°ط¸ظ¹ ط¸â€ ط·آ¬ط·آ­ ط¸ظ¾ط¸ظ¹ ط·آ§ط¸â€‍ط·آ¬ط¸â€‍ط·آ¨ */
 private function uaLabel($ua)
 {
 if (str_contains($ua, 'Googlebot')) return 'Googlebot';
 if (str_contains($ua, 'Feedly')) return 'Feedly';
 if (str_contains($ua, 'SimplePie')) return 'SimplePie';
 if (str_contains($ua, 'StarterPlatform'))  return 'StarterBot';
 return 'Chrome';
 }

 public function exportOpml()
 {
 $this->guardAdmin();
 $db = new Database();
 $sources = $db->fetchAll("SELECT * FROM rss_sources ORDER BY id ASC");

 header('Content-Type: text/xml; charset=utf-8');
 header('Content-Disposition: attachment; filename="platform_feeds_' . date('Y-m-d') . '.opml"');

 echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
 echo '<opml version="2.0">' . "\n";
 echo ' <head><title>SmartPlatform RSS Feeds</title><dateCreated>' . date('r') . '</dateCreated></head>' . "\n";
 echo ' <body>' . "\n";
 echo ' <outline text="ط¸â€¦ط·آµط·آ§ط·آ¯ط·آ± ط·آ§ط¸â€‍ط·آ£ط·آ®ط·آ¨ط·آ§ط·آ± ط·آ§ط¸â€‍ط·ع¾ط¸â€ڑط¸â€ ط¸ظ¹ط·آ© ط·آ§ط¸â€‍ط¸â€¦ط·آ¹ط·ع¾ط¸â€¦ط·آ¯ط·آ©" title="Tech Feeds">' . "\n";
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
 header('Content-Disposition: attachment; filename="platform_feeds_' . date('Y-m-d') . '.json"');
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
 Session::flash('success', "ط·ع¾ط¸â€¦ ط·آ§ط·آ³ط·ع¾ط¸ظ¹ط·آ±ط·آ§ط·آ¯ ({$importedCount}) ط¸â€¦ط·آµط·آ¯ط·آ± RSS ط·آ¨ط¸â€ ط·آ¬ط·آ§ط·آ­ ط·آ¥ط¸â€‍ط¸â€° ط¸â€ڑط·آ§ط·آ¦ط¸â€¦ط·آ© ط·آ§ط¸â€‍ط¸â€¦ط·آµط·آ§ط·آ¯ط·آ±!");
 } else {
 Session::flash('error', "ط¸â€‍ط¸â€¦ ط¸ظ¹ط·ع¾ط¸â€¦ ط·آ§ط¸â€‍ط·آ¹ط·آ«ط¸ث†ط·آ± ط·آ¹ط¸â€‍ط¸â€° ط·آ±ط¸ث†ط·آ§ط·آ¨ط·آ· RSS ط·آµط·آ§ط¸â€‍ط·آ­ط·آ© ط¸ظ¾ط¸ظ¹ ط·آ§ط¸â€‍ط¸â€¦ط¸â€‍ط¸ظ¾ ط·آ£ط¸ث† ط·آ§ط¸â€‍ط¸â€ ط·آµ ط·آ§ط¸â€‍ط¸â€¦ط·آ¯ط·آ®ط¸â€‍.");
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
 $name = trim($item['name'] ?? parse_url($item['url'], PHP_URL_HOST) ?: 'ط¸â€¦ط·آµط·آ¯ط·آ± ط¸â€¦ط·آ³ط·ع¾ط¸ث†ط·آ±ط·آ¯');
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
 $name = (string) ($out['text'] ?? $out['title'] ?? parse_url($url, PHP_URL_HOST) ?: 'ط¸â€¦ط·آµط·آ¯ط·آ± RSS');
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
 $host = parse_url($line, PHP_URL_HOST) ?: 'ط¸â€¦ط·آµط·آ¯ط·آ± ط·ع¾ط¸â€ڑط¸â€ ط¸ظ¹';
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
 // ط¸â€¦ط·آ­ط·آ±ط¸ئ’ ط¸â€¦ط¸â€ڑط·آ§ط¸ث†ط¸â€¦ ط¸â€‍ط¸â€‍ط·آ­ط·آ¬ط·آ¨: ط¸ظ¹ط·آ¯ط¸ث†ط¸â€کط·آ± ط¸ث†ط¸ئ’ط¸ظ¹ط¸â€‍ ط·آ§ط¸â€‍ط¸â€¦ط·آ³ط·ع¾ط·آ®ط·آ¯ط¸â€¦ ط·آ¹ط¸â€ ط·آ¯ 403ط·إ’ ط¸ث†ط¸ظ¹ط·آ¹ط¸ظ¹ط·آ¯ ط·آ§ط¸â€‍ط¸â€¦ط·آ­ط·آ§ط¸ث†ط¸â€‍ط·آ© ط·آ¹ط¸â€ ط·آ¯ 429ط·إ’
 // ط¸ث†ط¸ظ¹ط¸ئ’ط·آ´ط¸ظ¾ ط·آ§ط¸â€‍ط·ع¾ط·آ­ط¸ث†ط¸ظ¹ط¸â€‍ط·آ§ط·ع¾ ط·آ¥ط¸â€‍ط¸â€° ط·آ®ط·آ¯ط¸â€¦ط·آ§ط·ع¾ ط¸â€¦ط·ع¾ط¸ث†ط¸â€ڑط¸ظ¾ط·آ© ط¸â€¦ط·آ«ط¸â€‍ FeedBurner.
 $fetch = FeedFetcher::fetchRaw($url);

 if (!$fetch['success']) {
 $hint = '';
 if ((int) $fetch['http_code'] === 410 || (int) $fetch['http_code'] === 404) {
 $alts = FeedFetcher::suggestAlternatives($url);
 if ($alts) {
 $hint = ' أ¢â‚¬â€‌ ط·آ±ط¸ث†ط·آ§ط·آ¨ط·آ· ط¸â€¦ط¸â€ڑط·ع¾ط·آ±ط·آ­ط·آ© ط¸â€‍ط¸â€‍ط·ع¾ط·آ¬ط·آ±ط·آ¨ط·آ©: ' . implode(' ط·إ’ ', array_slice($alts, 0, 3));
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
 return ['success' => false, 'error' => 'ط·ع¾ط·آ¹ط·آ°ط·آ± ط·آ§ط·آ³ط·ع¾ط·آ®ط·آ±ط·آ§ط·آ¬ ط¸â€¦ط¸â€ڑط·آ§ط¸â€‍ط·آ§ط·ع¾ ط¸â€¦ط¸â€  ط·آ§ط¸â€‍ط·آ±ط·آ§ط·آ¨ط·آ· (ط¸â€‍ط·آ§ ط·ع¾ط·ع¾ط¸ث†ط¸ظ¾ط·آ± ط·آ®ط¸â€‍ط·آ§ط·آµط·آ© XML ط·آµط·آ§ط¸â€‍ط·آ­ط·آ© ط·آ£ط¸ث† ط·ع¾ط·آ¹ط·آ°ط·آ± ط·آ§ط·آ³ط·ع¾ط·آ®ط·آ±ط·آ§ط·آ¬ ط·آ¹ط¸â€ ط·آ§ط·آµط·آ± HTML).'];
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
 return ['success' => false, 'error' => 'ط¸â€‍ط¸â€¦ ط¸ظ¹ط·ع¾ط¸â€¦ ط·آ§ط¸â€‍ط·آ¹ط·آ«ط¸ث†ط·آ± ط·آ¹ط¸â€‍ط¸â€° ط·آ£ط¸ظ¹ ط·آ¹ط¸â€ ط·آ§ط·آµط·آ± ط·آ¥ط·آ®ط·آ¨ط·آ§ط·آ±ط¸ظ¹ط·آ© ط·آ¯ط·آ§ط·آ®ط¸â€‍ ط¸â€¦ط¸â€‍ط¸ظ¾ ط·آ§ط¸â€‍ط·آ®ط¸â€‍ط·آ§ط·آµط·آ©.'];
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
  'pubDate' => fmt_date('now', 'Y-m-d H:i'),
 'excerpt' => mb_strimwidth($desc, 0, 220, 'أ¢â‚¬آ¦', 'UTF-8'),
 'content' => $desc,
 'featured_image' => $image ?: \FallbackImage::general()
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
  // ط·آ®ط¸â€‍ط·آ§ط·آµط·آ§ط·ع¾ ط¸â€¦ط·آ«ط¸â€‍ Google News ط·آ¨ط¸â€‍ط·آ§ ط·آµط¸ث†ط·آ± ط·آ¯ط·آ§ط·آ®ط¸â€‍ XML: ط¸â€ ط·آ³ط·ع¾ط·آ¹ط¸ظ¹ط¸â€  ط·آ¨ط¸ئ’ط·آ§ط·آ´ ط·آ§ط¸â€‍ط·آµط¸ث†ط·آ± ط·آ§ط¸â€‍ط¸â€¦ط·آ®ط·آ²ط¸â€کط¸â€  ط¸â€¦ط·آ³ط·آ¨ط¸â€ڑط·آ§ط¸â€¹
  // (ط·آ­ط¸â€‍ ط¸ظ¾ط¸ث†ط·آ±ط¸ظ¹ ط·آ¨ط¸â€‍ط·آ§ ط·آ´ط·آ¨ط¸ئ’ط·آ©) ط¸ث†ط¸â€ ط·ع¾ط¸ظ¹ط·آ­ ط¸â€‍ط¸â€‍ط¸â€¦ط·آ¹ط·آ§ط¸ظ¹ط¸â€ ط·آ© ط·آ¹ط·آ±ط·آ¶ ط·آ§ط¸â€‍ط·آµط¸ث†ط·آ± ط·آ§ط¸â€‍ط·آ­ط¸â€ڑط¸ظ¹ط¸â€ڑط¸ظ¹ط·آ© ط¸â€¦ط·ع¾ط¸â€° ط·ع¾ط¸ث†ط·آ§ط¸ظ¾ط·آ±ط·ع¾.
  if (empty($image) && !empty(trim($link))) {
  $cachedImg = FetchOg::cacheGet(trim($link));
  if ($cachedImg !== '') $image = $cachedImg;
  }

  $cleanDesc = $this->cleanTextEntity(strip_tags($description ?: $content));
  $cleanContent = $this->cleanTextEntity(strip_tags($content ?: $description));

  return [
  'title' => $this->cleanTextEntity($title),
  'link' => trim($link),
  'pubDate' => $pubDate ? fmt_date($pubDate, 'Y-m-d H:i') : fmt_date('now', 'Y-m-d H:i'),
  'excerpt' => mb_strimwidth($cleanDesc ?: $cleanContent, 0, 220, 'أ¢â‚¬آ¦', 'UTF-8'),
  'content' => $cleanContent,
  'featured_image' => $image ?: \FallbackImage::general(),
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
  if (empty($image) && !empty(trim($link))) {
  $cachedImg = FetchOg::cacheGet(trim($link));
  if ($cachedImg !== '') $image = $cachedImg;
  }

  $cleanSummary = $this->cleanTextEntity(strip_tags($summary));

 return [
 'title' => $this->cleanTextEntity($title),
 'link' => trim($link),
 'pubDate' => $pubDate ? fmt_date($pubDate, 'Y-m-d H:i') : fmt_date('now', 'Y-m-d H:i'),
 'excerpt' => mb_strimwidth($cleanSummary, 0, 220, 'أ¢â‚¬آ¦', 'UTF-8'),
 'content' => $cleanSummary,
 'featured_image' => $image ?: \FallbackImage::general(),
 ];
 }

 private function cleanTextEntity($text)
 {
 if (empty($text)) return '';
 $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
 $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
 $replacements = [
 '&#8211;' => 'أ¢â‚¬â€œ',
 '&#8212;' => 'أ¢â‚¬â€‌',
 '&#8216;' => "'",
 '&#8217;' => "'",
 '&#8220;' => '"',
 '&#8221;' => '"',
 '&#039;' => "'",
 '&#39;' => "'",
 '&quot;' => '"',
 '&amp;' => '&',
 '&nbsp;' => ' ',
 '&ndash;' => 'أ¢â‚¬â€œ',
 '&mdash;' => 'أ¢â‚¬â€‌',
 'amp;#8211;' => 'أ¢â‚¬â€œ',
 'amp;#8212;' => 'أ¢â‚¬â€‌',
 'amp;#039;' => "'",
 'amp;#39;' => "'",
 'amp;quot;' => '"',
 'amp;amp;' => '&',
 ];
 $text = str_replace(array_keys($replacements), array_values($replacements), $text);
 $text = preg_replace('/&?amp;#(\d+);?/i', ' ', $text);
 $text = preg_replace('/&#(\d+);?/', ' ', $text);

 // Strip boilerplate RSS feeder intro/outro phrases
 $text = preg_replace('/^ط¸â€،ط·آ°ط·آ§ ط·آ§ط¸â€‍ط¸â€¦ط¸ث†ط·آ¶ط¸ث†ط·آ¹\s+/u', '', $text);
 $text = preg_replace('/ط·آ¸ط¸â€،ط·آ± ط¸â€،ط·آ°ط·آ§ ط·آ§ط¸â€‍ط¸â€¦ط¸ث†ط·آ¶ط¸ث†ط·آ¹ ط·آ£ط¸ث†ط¸â€‍ط·آ§ط¸â€¹ ط·آ¹ط¸â€‍ط¸â€°.*/u', '', $text);
 $text = preg_replace('/ط·آ¸ط¸â€،ط·آ± ط·آ¹ط¸â€‍ط¸â€° ط·آ§ط¸â€‍ط·ع¾ط¸â€ڑط¸â€ ط¸ظ¹ط·آ© ط·آ¨ط¸â€‍ط·آ§ ط·آ­ط·آ¯ط¸ث†ط·آ¯.*/u', '', $text);
 $text = preg_replace('/ط·آ¸ط¸â€،ط·آ±ط·ع¾ ط·آ£ط¸ث†ط¸â€‍ط·آ§ط¸â€¹ ط·آ¹ط¸â€‍ط¸â€°.*/u', '', $text);
 $text = preg_replace('/The post .* appeared first on .*/i', '', $text);
 $text = preg_replace('/This article was originally published on .*/i', '', $text);

 return trim($text);
 }
}

