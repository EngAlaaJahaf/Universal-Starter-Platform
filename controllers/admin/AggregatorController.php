<?php

class AggregatorController extends AdminController
{
 /**
  * طھطµظ†ظٹظپ طھظ„ظ‚ط§ط¦ظٹ ط°ظƒظٹ ظ„ظ„ظ…ظ‚ط§ظ„ ط§ظ„ظ…ظ†ط´ظˆط± ط¹ط¨ط± ظ„ظˆط­ط© ط§ظ„ظ…ط¬ظ…ظ‘ط¹ ظ…ط¹ ظ…ط±ط§ط¹ط§ط© ط§ظ„ظ…طµط¯ط±
  * ظˆط§ظ„ظ…ط­طھظˆظ‰ ظˆط±ط§ط¨ط· ط§ظ„ظ…ظ‚ط§ظ„ (ظ†ظپط³ ظ…ط­ط±ظƒ ط§ظ„طھطµظ†ظٹظپ ط§ظ„ظ…ط³طھط®ط¯ظ… ظپظٹ ط§ظ„ظ€ Cron).
  * ظٹظ‚ط¹ ط¹ظ„ظ‰ ط§ظ„ظ…طµط¯ط± ط§ظ„ظ…ط³ط¬ظ„ ط£ظˆ ط§ظ„ظپط¦ط© ط§ظ„ط§ظپطھط±ط§ط¶ظٹط© ط¥ظ† طھط¹ط°ظ‘ط± ط§ظ„طھطµظ†ظٹظپ.
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
  * ظٹط±ط¯ظ‘ JSON ط¹ظ†ط¯ظ…ط§ ظٹظƒظˆظ† ط§ظ„ط·ظ„ط¨ ظ‚ط§ط¯ظ…ط§ظ‹ ظ…ظ† ظˆط§ط¬ظ‡ط© AJAX (ظ†ط´ط± ط¨ط¯ظˆظ† ط¥ط¹ط§ط¯ط© طھط­ظ…ظٹظ„ ط§ظ„طµظپط­ط©)طŒ
  * ظˆظٹط¹ظٹط¯ false ظ„ظٹطھط§ط¨ط¹ ط§ظ„ظ…ظڈط­ط¯ظگظ‘ط« ظ…ط³ط§ط± ط§ظ„ظ€ redirect ط§ظ„ظ…ط¹طھط§ط¯ ظپظٹ ط§ظ„ظ…طھطµظپط­ ط§ظ„ط¹ط§ط¯ظٹ.
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
 $activeSourceName = parse_url($customFeedUrl, PHP_URL_HOST) ?: 'ظ…طµط¯ط± ظ…ط®طµطµ';
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

// ط¹ط¯ظ‘ط§ط¯ ط§ظ„ط£ط®ط¨ط§ط± ط§ظ„ط¬ط¯ظٹط¯ط© ط؛ظٹط± ط§ظ„ظ…ظ†ط´ظˆط±ط© (ظ„ظƒظ„ ط¨ط·ط§ظ‚ط© + ظ„ظ„ظ…طµط¯ط± ط§ظ„ظ…ط­ط¯ط¯)
  foreach ($items as $it) {
  if (($it['import_status'] ?? null) !== 'published') $unpublishedCount++;
  }

  // طھط³ط®ظٹظ† ظƒط§ط´ ط§ظ„طµظˆط± ظ„ظ„ظ…ط¹ط§ظٹظ†ط©: ظ†ط­ظ„ ظ…ط¨ط§ط´ط±ط© ط¹ط¯ط¯ط§ظ‹ ظ…ط­ط¯ظˆط¯ط§ظ‹ ظ…ظ† ط§ظ„ط¹ظ†ط§طµط± ط¨ظ„ط§ طµظˆط±
  // ط¶ظ…ظ† ظ…ظٹط²ط§ظ†ظٹط© ط²ظ…ظ†ظٹط© ظ‚طµظٹط±ط© ط­طھظ‰ ظ„ط§ طھط¨ط·ط¦ ط§ظ„طµظپط­ط©ط› ظˆط§ظ„ط¨ط§ظ‚ظٹ ظٹظڈظƒظ…ظ„ ط¹ظ†ط¯ ط§ظ„ظ†ط´ط± ط£ظˆ
  // ط§ظ„طھط­ظ…ظٹظ„ط§طھ ط§ظ„طھط§ظ„ظٹط© (ط§ظ„ظƒط§ط´ ظ…ط®ط²ظ‘ظ† ط¹ظ„ظ‰ ط§ظ„ظ‚ط±طµ ظپظٹ storage/cache).
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

  // طھط­ط¯ظٹط« ط¹ط¯ظ‘ط§ط¯ ط®ظ„ط§طµط© ط§ظ„ظ…طµط¯ط± ط§ظ„ظ…ط³ط¬ظ‘ظ„ ظپظ‚ط· (ظˆظ„ظٹط³ ط±ط§ط¨ط· ظ…ط®طµطµ ط¹ط§ط¨ط±)
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
 $sourceName = $this->cleanTextEntity(trim($data['source_name'] ?? 'ظ…طµط¯ط± ط®ط§ط±ط¬ظٹ'));
 $content = $this->cleanTextEntity($_POST['content'] ?? ($data['excerpt'] ?? ''));
 $excerpt = $this->cleanTextEntity(trim($data['excerpt'] ?? mb_strimwidth(strip_tags($content), 0, 200, 'â€¦', 'UTF-8')));
$featuredImage = trim($data['featured_image'] ?? '');
  // Drop absurdly long image URLs (feed junk) so the INSERT never overflows.
  if (strlen($featuredImage) > 1000) $featuredImage = '';
  // ط¹ظ†ط§طµط± ط®ظ„ط§طµط§طھ ظ…ط«ظ„ Google News ظ„ط§ طھط­ظ…ظ„ طµظˆط±ط§ظ‹ ط¯ط§ط®ظ„ XMLط› ظ†ط¬ظ„ط¨ ط§ظ„طµظˆط±ط© ط§ظ„ط¨ط§ط±ط²ط©
  // ط§ظ„ط­ظ‚ظٹظ‚ظٹط© ظ…ظ† طµظپط­ط© ط§ظ„ظ…ظ‚ط§ظ„ ط§ظ„ط£طµظ„ظٹط© (og:image ظ…ط¹ ظƒط§ط´ ط¹ظ„ظ‰ ط§ظ„ظ‚ط±طµ) ط¹ظ†ط¯ ط؛ظٹط§ط¨ظ‡ط§.
  $featuredImage = FetchOg::resolveFor($sourceUrl, $featuredImage);

 if (empty($title)) {
 if ($this->ajaxOut(['success' => false, 'error' => 'ط¹ظ†ظˆط§ظ† ط§ظ„ظ…ظ‚ط§ظ„ ظ…ط·ظ„ظˆط¨ ظ„ظ„ظ†ط´ط±.'])) return;
 Session::flash('error', 'ط¹ظ†ظˆط§ظ† ط§ظ„ظ…ظ‚ط§ظ„ ظ…ط·ظ„ظˆط¨ ظ„ظ„ظ†ط´ط±.');
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
 Session::flash('success', "طھظ… ط§ظ„ط¹ط«ظˆط± ط¹ظ„ظ‰ ط§ظ„ظ…ظ‚ط§ظ„ ط§ظ„ظ…ظ†ط´ظˆط± ظ…ط³ط¨ظ‚ط§ظ‹ ظˆطھط­ط¯ظٹط«ظ‡ ظˆطھط±ط¬ظ…طھظ‡ ظپظˆط±ط§ظ‹ ط¥ظ„ظ‰ ط§ظ„ط¹ط±ط¨ظٹط© ط¨ط¹ظ†ظˆط§ظ†: \"{$titleAr}\"!");
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
 Session::flash('success', "طھظ…طھ طھط±ط¬ظ…ط© ظˆطµظٹط§ط؛ط© ظˆظ†ط´ط± ط§ظ„ط®ط¨ط± ظپظˆط±ط§ظ‹ ط¨ظ†ط¬ط§ط­ ط¨ط§ظ„ظ„ط؛ط© ط§ظ„ط¹ط±ط¨ظٹط© ط¨ط¹ظ†ظˆط§ظ†: \"{$titleAr}\"!");
 }
 } catch (Throwable $e) {
 error_log('translatePublish error: ' . $e->getMessage());
 if ($this->ajaxOut(['success' => false, 'error' => 'طھط¹ط°ط± ط¥طھظ…ط§ظ… ط§ظ„طھط±ط¬ظ…ط© ظˆط§ظ„ظ†ط´ط±: ' . $e->getMessage()])) return;
 Session::flash('error', 'طھط¹ط°ط± ط¥طھظ…ط§ظ… ط§ظ„طھط±ط¬ظ…ط© ظˆط§ظ„ظ†ط´ط±: ' . $e->getMessage());
 }
 
 $okArticleId = (int) ($newId ?? $existing['id'] ?? 0);
 if ($this->ajaxOut([
 'success' => true,
 'article_id' => $okArticleId,
 'edit_url' => app_url('admin/articles/' . $okArticleId . '/edit'),
 'message' => "طھظ…طھ ط§ظ„طھط±ط¬ظ…ط© ظˆط§ظ„ظ†ط´ط± ط¨ظ†ط¬ط§ط­ ط¨ط¹ظ†ظˆط§ظ†: \"{$titleAr}\"!",
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
 $sourceName = $this->cleanTextEntity(trim($data['source_name'] ?? 'ظ…طµط¯ط± ط®ط§ط±ط¬ظٹ'));
 $content = $this->cleanTextEntity($_POST['content'] ?? ($data['excerpt'] ?? ''));
 $excerpt = $this->cleanTextEntity(trim($data['excerpt'] ?? mb_strimwidth(strip_tags($content), 0, 200, 'â€¦', 'UTF-8')));
$featuredImage = trim($data['featured_image'] ?? '');
  // Drop absurdly long image URLs (feed junk) so the INSERT never overflows.
  if (strlen($featuredImage) > 1000) $featuredImage = '';
  // ط¹ظ†ط§طµط± ط®ظ„ط§طµط§طھ ظ…ط«ظ„ Google News ظ„ط§ طھط­ظ…ظ„ طµظˆط±ط§ظ‹ ط¯ط§ط®ظ„ XMLط› ظ†ط¬ظ„ط¨ ط§ظ„طµظˆط±ط© ط§ظ„ط¨ط§ط±ط²ط©
  // ط§ظ„ط­ظ‚ظٹظ‚ظٹط© ظ…ظ† طµظپط­ط© ط§ظ„ظ…ظ‚ط§ظ„ ط§ظ„ط£طµظ„ظٹط© (og:image ظ…ط¹ ظƒط§ط´ ط¹ظ„ظ‰ ط§ظ„ظ‚ط±طµ) ط¹ظ†ط¯ ط؛ظٹط§ط¨ظ‡ط§.
  $featuredImage = FetchOg::resolveFor($sourceUrl, $featuredImage);

 if (empty($title)) {
 if ($this->ajaxOut(['success' => false, 'error' => 'ط¹ظ†ظˆط§ظ† ط§ظ„ظ…ظ‚ط§ظ„ ظ…ط·ظ„ظˆط¨ ظ„ظ„ظ†ط´ط±.'])) return;
 Session::flash('error', 'ط¹ظ†ظˆط§ظ† ط§ظ„ظ…ظ‚ط§ظ„ ظ…ط·ظ„ظˆط¨ ظ„ظ„ظ†ط´ط±.');
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
            Session::flash('success', "طھظ… ط§ظ„ط¹ط«ظˆط± ط¹ظ„ظ‰ ط§ظ„ظ…ظ‚ط§ظ„ ط§ظ„ظ…ظ†ط´ظˆط± ظ…ط³ط¨ظ‚ط§ظ‹ ظˆطھط­ط¯ظٹط« ظ…ط­طھظˆط§ظ‡ ظپظˆط±ط§ظ‹ ط¨ط¹ظ†ظˆط§ظ†: \"{$title}\"!");
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
 Session::flash('success', "طھظ… ط§ظ„ظ†ط´ط± ط§ظ„ظپظˆط±ظٹ ط§ظ„ظ…ط¨ط§ط´ط± ط¨ظ†ط¬ط§ط­ ط¨ط¯ظˆظ† طھط±ط¬ظ…ط© ط¨ط¹ظ†ظˆط§ظ†: \"{$title}\"!");
 }
 
 $okArticleId = (int) ($newId ?? $existing['id'] ?? 0);
 if ($this->ajaxOut([
 'success' => true,
 'article_id' => $okArticleId,
 'edit_url' => app_url('admin/articles/' . $okArticleId . '/edit'),
 'message' => "طھظ… ط§ظ„ظ†ط´ط± ط§ظ„ظپظˆط±ظٹ ط§ظ„ظ…ط¨ط§ط´ط± ط¨ظ†ط¬ط§ط­ ط¨ط¹ظ†ظˆط§ظ†: \"{$title}\"!",
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
        $sourceName = $this->cleanTextEntity(trim($data['source_name'] ?? 'ظ…طµط¯ط± ط®ط§ط±ط¬ظٹ'));
        $content = $this->cleanTextEntity($_POST['content'] ?? ($data['excerpt'] ?? ''));
        $excerpt = $this->cleanTextEntity(trim($data['excerpt'] ?? mb_strimwidth(strip_tags($content), 0, 200, 'â€¦', 'UTF-8')));
        $featuredImage = trim($data['featured_image'] ?? '');
        // Drop absurdly long image URLs (feed junk) so the INSERT never overflows.
        if (strlen($featuredImage) > 1000) $featuredImage = '';
        // ط¹ظ†ط§طµط± ط®ظ„ط§طµط§طھ ظ…ط«ظ„ Google News ظ„ط§ طھط­ظ…ظ„ طµظˆط±ط§ظ‹ ط¯ط§ط®ظ„ XMLط› ظ†ط¬ظ„ط¨ ط§ظ„طµظˆط±ط© ط§ظ„ط¨ط§ط±ط²ط©
        // ط§ظ„ط­ظ‚ظٹظ‚ظٹط© ظ…ظ† طµظپط­ط© ط§ظ„ظ…ظ‚ط§ظ„ ط§ظ„ط£طµظ„ظٹط© (og:image ظ…ط¹ ظƒط§ط´ ط¹ظ„ظ‰ ط§ظ„ظ‚ط±طµ) ط¹ظ†ط¯ ط؛ظٹط§ط¨ظ‡ط§.
        $featuredImage = FetchOg::resolveFor($sourceUrl, $featuredImage);

if (empty($title)) {
 if ($this->ajaxOut(['success' => false, 'error' => 'ط¹ظ†ظˆط§ظ† ط§ظ„ظ…ظ‚ط§ظ„ ظ…ط·ظ„ظˆط¨.'])) return;
 Session::flash('error', 'ط¹ظ†ظˆط§ظ† ط§ظ„ظ…ظ‚ط§ظ„ ظ…ط·ظ„ظˆط¨.');
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
 Session::flash('success', "طھظ… ط§ط³طھظٹط±ط§ط¯ ط§ظ„ط®ط¨ط± ظƒظ…ط³ظˆط¯ط© ط¨ظ†ط¬ط§ط­! ظٹظ…ظƒظ†ظƒ ط§ظ„ط¢ظ† ظ…ط±ط§ط¬ط¹طھظ‡ ظˆطµظٹط§ط؛طھظ‡ ظˆظ†ط´ط±ظ‡.");
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
 Session::flash('error', 'ط§ط³ظ… ط§ظ„ظ…طµط¯ط± ظˆط±ط§ط¨ط· ط§ظ„ظ€ RSS ظ…ط·ظ„ظˆط¨ط§ظ†.');
 return $this->redirect('admin/news-feeds');
 }

 $db = new Database();
 $db->query("INSERT INTO rss_sources (name, url, category_id) VALUES (:name, :url, :category_id)", [
 ':name' => $name,
 ':url' => $url,
 ':category_id' => $categoryId
 ]);

 Session::flash('success', "طھظ…طھ ط¥ط¶ط§ظپط© ط§ظ„ظ…طµط¯ط± ط§ظ„طھظ‚ظ†ظٹ \"{$name}\" ط¨ظ†ط¬ط§ط­.");
 return $this->redirect('admin/news-feeds');
 }

 public function deleteSource($id)
 {
 $this->postGuard();
 $db = new Database();
 $db->query("DELETE FROM rss_sources WHERE id = :id", [':id' => (int) $id]);
 Session::flash('success', 'طھظ… ط­ط°ظپ ط§ظ„ظ…طµط¯ط± ط¨ظ†ط¬ط§ط­.');
 return $this->redirect('admin/news-feeds');
 }

 /**
 * ظپط­طµ طµط­ط© ظƒظ„ ظ…طµط§ط¯ط± RSS ط§ظ„ظ…ط³ط¬ظ„ط© (ط£ظˆ ظ…طµط¯ط± ظˆط§ط­ط¯) ظˆطھط®ط²ظٹظ† ط§ظ„ظ†طھظٹط¬ط© ظپظٹ ظ‚ط§ط¹ط¯ط© ط§ظ„ط¨ظٹط§ظ†ط§طھ.
 * ظٹظڈط±ط¬ط¹ JSON ظ„ط§ط³طھظ‡ظ„ط§ظƒظ‡ ظ…ظ† ظˆط§ط¬ظ‡ط© ظ„ظˆط­ط© ط§ظ„طھط­ظƒظ….
 */
public function healthCheck()
 {
 $this->guardAdmin();

 // ظ…ظٹط²ط§ظ†ظٹط© ط²ظ…ظ†ظٹط© ط«ط§ط¨طھط© ظ„ظƒظ„ ط·ظ„ط¨: ط¨ط؛ط¶ظ‘ ط§ظ„ظ†ط¸ط± ط¹ظ† ط¨ط·ط، ط§ظ„ط®ظ„ط§طµط§طھ ظ†ط¶ظ…ظ† ط¥ط±ط¬ط§ط¹ ط§ط³طھط¬ط§ط¨ط©
 // ظƒط§ظ…ظ„ط© ظ‚ط¨ظ„ ظ…ظ‡ظ„ط© ط§ظ„ط§ط³طھط¶ط§ظپط© ط§ظ„ظ‚طµظˆظ‰ (~30 ط«)طŒ ط¹ط¨ط± طھظ‚ط³ظٹظ… ط§ظ„ظپط­طµ ط¥ظ„ظ‰ ط¯ظپط¹ط§طھ طµط؛ظٹط±ط©
 // طھظڈطھط§ط¨ط¹ظ‡ط§ ط§ظ„ظˆط§ط¬ظ‡ط© (offset/limit). ظƒظ„ ط·ظ„ط¨ ظٹط¹ط§ظ„ط¬ ظ…طµط¯ط±ط§ظ‹ ظˆط§ط­ط¯ط§ظ‹ ط¹ظ„ظ‰ ط§ظ„ط£ظ‚ظ„.
 $started = microtime(true);
 $budget = 10; // ط«ظˆط§ظ†ظچ ظƒط­ط¯ظ‘ ط£ظ‚طµظ‰ ظ„ظ…ط¹ط§ظ„ط¬ط© ط·ظ„ط¨ ظˆط§ط­ط¯
 @set_time_limit($budget + 20);

 $db = new Database();
 $singleId = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);

 // Batching: shared hosting caps max_execution_time (30â€“60s), so the full
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
  // ظ†ط¶ظ…ظ† ظ…ط¹ط§ظ„ط¬ط© ظ…طµط¯ط± ظˆط§ط­ط¯ ط¹ظ„ظ‰ ط§ظ„ط£ظ‚ظ„طŒ ط«ظ… ظ†طھظˆظ‚ظپ ط¹ظ†ط¯ ط§ظ‚طھط±ط§ط¨ ط§ظ„ظ…ظٹط²ط§ظ†ظٹط©
  // ظ„طھط±ظƒ ط§ظ„ط¯ظپط¹ط© ط§ظ„طھط§ظ„ظٹط© (done=false) طھظƒظ…ظ„ ط§ظ„ط¨ط§ظ‚ظٹ ظ‚ط¨ظ„ ظˆطµظˆظ„ PHP ظ„ظ„ظ…ظ‡ظ„ط©.
  if ($didWork && (microtime(true) - $started) >= $budget) {
  break;
  }
  $didWork = true;

  // ط§ظ„ظپط­طµ ط§ظ„ط¬ظ…ط§ط¹ظٹ: ظˆط¶ط¹ ط³ط±ظٹط¹ ظ…ط¹ طھظ‚ظ„ظٹطµ ظ…ظ‡ظ„ط© ظ‡ط°ط§ ط§ظ„ظ…طµط¯ط± ط¨ظ…ط§ طھط¨ظ‚ظ‘ظ‰ ظ…ظ† ط§ظ„ظ…ظٹط²ط§ظ†ظٹط©ط›
  // ط§ظ„ظپط­طµ ط§ظ„ظ…ظپط±ط¯: ظ…ظ‡ظ„ط© ط¹ظ„ظٹط§ 15 ط« ط­طھظ‰ ظ„ط§ طھطھط¬ط§ظˆط² ظ…ظ‡ظ„ط© ط§ظ„ط§ط³طھط¶ط§ظپط©.
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
 // ط®ظ„ط§طµط© XML ط؛ظٹط± طµط§ظ„ط­ط© â€” ط¬ط±ظ‘ط¨ ط§ظ„ظƒط§ط´ط· ط§ظ„ط°ظƒظٹ ظƒط®ظٹط§ط± ط§ط­طھظٹط§ط·ظٹ
 $scraped = $this->scrapeHtmlPage($fetch['body'], $s['url']);
 if (!empty($scraped)) {
 $status = 'ok';
 $itemCount = count($scraped);
 $sampleTitle = $scraped[0]['title'] ?? '';
 $error = '';
 } else {
 $status = 'empty';
 $error = 'طھظ… ط§ظ„ط§طھطµط§ظ„ ط¨ظ†ط¬ط§ط­ ظ„ظƒظ† ظ„ظ… ظٹظڈط¹ط«ط± ط¹ظ„ظ‰ ط¹ظ†ط§طµط± ط¥ط®ط¨ط§ط±ظٹط© ط¯ط§ط®ظ„ ط§ظ„ط®ظ„ط§طµط©.';
 }
 }
 }

$suggestions = [];
  if ($status !== 'ok' && $singleId > 0 && (microtime(true) - $started) < 6) {
  // ط§ظ‚طھط±ط§ط­ط§طھ ط§ظ„ط¨ط¯ط§ط¦ظ„ طھط­طھط§ط¬ ط·ظ„ط¨ط§طھ ط´ط¨ظƒط© ط¥ط¶ط§ظپظٹط©ط› طھظڈظ†ظپظژظ‘ط° ظپظ‚ط· ظپظٹ ط§ظ„ظپط­طµ ط§ظ„ظ…ظپط±ط¯طŒ
  // ظˆظپظ‚ط· ط¥ط°ط§ ط¨ظ‚ظٹ ظ…ظ† ط§ظ„ظ…ظٹط²ط§ظ†ظٹط© ظ…طھظ‘ط³ط¹طŒ ط­طھظ‰ ظ„ط§ طھط·ظ„ظ‚ ط§ظ„ظ…ظ‡ظ„ط© ط¹ظ„ظ‰ ط§ظ„ط§ط³طھط¶ط§ظپط©.
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

// done طµط­ظٹط­ ظپظ‚ط· ط¹ظ†ط¯ ط§ط³طھظƒظ…ط§ظ„ ظƒظ„ ط§ظ„ظ…طµط§ط¯ط± (ط£ظˆ ط§ظ†طھظ‡ط§ط، ط§ظ„ط¯ظپط¹ط© ط¨ظ„ط§ ط¨ظ‚ظٹظ‘ط©)ط›
  // ط£ظ…ط§ ط¥ط°ط§ طھظˆظ‚ظپظ†ط§ ط¨ط³ط¨ط¨ ط§ظ„ظ…ظٹط²ط§ظ†ظٹط© ظپطھظڈظƒظ…ظ‘ظ„ ط§ظ„ظˆط§ط¬ظ‡ط© ظ…ظ† offset ط§ظ„ظ†ط§طھط¬.
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

 /** طھط³ظ…ظٹط© ظ…ظ‚ط±ظˆط،ط© ظ„ظˆظƒظٹظ„ ط§ظ„ظ…ط³طھط®ط¯ظ… ط§ظ„ط°ظٹ ظ†ط¬ط­ ظپظٹ ط§ظ„ط¬ظ„ط¨ */
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
 echo ' <head><title>AsabTech RSS Feeds</title><dateCreated>' . date('r') . '</dateCreated></head>' . "\n";
 echo ' <body>' . "\n";
 echo ' <outline text="ظ…طµط§ط¯ط± ط§ظ„ط£ط®ط¨ط§ط± ط§ظ„طھظ‚ظ†ظٹط© ط§ظ„ظ…ط¹طھظ…ط¯ط©" title="Tech Feeds">' . "\n";
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
 Session::flash('success', "طھظ… ط§ط³طھظٹط±ط§ط¯ ({$importedCount}) ظ…طµط¯ط± RSS ط¨ظ†ط¬ط§ط­ ط¥ظ„ظ‰ ظ‚ط§ط¦ظ…ط© ط§ظ„ظ…طµط§ط¯ط±!");
 } else {
 Session::flash('error', "ظ„ظ… ظٹطھظ… ط§ظ„ط¹ط«ظˆط± ط¹ظ„ظ‰ ط±ظˆط§ط¨ط· RSS طµط§ظ„ط­ط© ظپظٹ ط§ظ„ظ…ظ„ظپ ط£ظˆ ط§ظ„ظ†طµ ط§ظ„ظ…ط¯ط®ظ„.");
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
 $name = trim($item['name'] ?? parse_url($item['url'], PHP_URL_HOST) ?: 'ظ…طµط¯ط± ظ…ط³طھظˆط±ط¯');
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
 $name = (string) ($out['text'] ?? $out['title'] ?? parse_url($url, PHP_URL_HOST) ?: 'ظ…طµط¯ط± RSS');
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
 $host = parse_url($line, PHP_URL_HOST) ?: 'ظ…طµط¯ط± طھظ‚ظ†ظٹ';
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
 // ظ…ط­ط±ظƒ ظ…ظ‚ط§ظˆظ… ظ„ظ„ط­ط¬ط¨: ظٹط¯ظˆظ‘ط± ظˆظƒظٹظ„ ط§ظ„ظ…ط³طھط®ط¯ظ… ط¹ظ†ط¯ 403طŒ ظˆظٹط¹ظٹط¯ ط§ظ„ظ…ط­ط§ظˆظ„ط© ط¹ظ†ط¯ 429طŒ
 // ظˆظٹظƒط´ظپ ط§ظ„طھط­ظˆظٹظ„ط§طھ ط¥ظ„ظ‰ ط®ط¯ظ…ط§طھ ظ…طھظˆظ‚ظپط© ظ…ط«ظ„ FeedBurner.
 $fetch = FeedFetcher::fetchRaw($url);

 if (!$fetch['success']) {
 $hint = '';
 if ((int) $fetch['http_code'] === 410 || (int) $fetch['http_code'] === 404) {
 $alts = FeedFetcher::suggestAlternatives($url);
 if ($alts) {
 $hint = ' â€” ط±ظˆط§ط¨ط· ظ…ظ‚طھط±ط­ط© ظ„ظ„طھط¬ط±ط¨ط©: ' . implode(' طŒ ', array_slice($alts, 0, 3));
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
 return ['success' => false, 'error' => 'طھط¹ط°ط± ط§ط³طھط®ط±ط§ط¬ ظ…ظ‚ط§ظ„ط§طھ ظ…ظ† ط§ظ„ط±ط§ط¨ط· (ظ„ط§ طھطھظˆظپط± ط®ظ„ط§طµط© XML طµط§ظ„ط­ط© ط£ظˆ طھط¹ط°ط± ط§ط³طھط®ط±ط§ط¬ ط¹ظ†ط§طµط± HTML).'];
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
 return ['success' => false, 'error' => 'ظ„ظ… ظٹطھظ… ط§ظ„ط¹ط«ظˆط± ط¹ظ„ظ‰ ط£ظٹ ط¹ظ†ط§طµط± ط¥ط®ط¨ط§ط±ظٹط© ط¯ط§ط®ظ„ ظ…ظ„ظپ ط§ظ„ط®ظ„ط§طµط©.'];
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
 'excerpt' => mb_strimwidth($desc, 0, 220, 'â€¦', 'UTF-8'),
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
  // ط®ظ„ط§طµط§طھ ظ…ط«ظ„ Google News ط¨ظ„ط§ طµظˆط± ط¯ط§ط®ظ„ XML: ظ†ط³طھط¹ظٹظ† ط¨ظƒط§ط´ ط§ظ„طµظˆط± ط§ظ„ظ…ط®ط²ظ‘ظ† ظ…ط³ط¨ظ‚ط§ظ‹
  // (ط­ظ„ ظپظˆط±ظٹ ط¨ظ„ط§ ط´ط¨ظƒط©) ظˆظ†طھظٹط­ ظ„ظ„ظ…ط¹ط§ظٹظ†ط© ط¹ط±ط¶ ط§ظ„طµظˆط± ط§ظ„ط­ظ‚ظٹظ‚ظٹط© ظ…طھظ‰ طھظˆط§ظپط±طھ.
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
  'excerpt' => mb_strimwidth($cleanDesc ?: $cleanContent, 0, 220, 'â€¦', 'UTF-8'),
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
 'excerpt' => mb_strimwidth($cleanSummary, 0, 220, 'â€¦', 'UTF-8'),
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
 '&#8211;' => 'â€“',
 '&#8212;' => 'â€”',
 '&#8216;' => "'",
 '&#8217;' => "'",
 '&#8220;' => '"',
 '&#8221;' => '"',
 '&#039;' => "'",
 '&#39;' => "'",
 '&quot;' => '"',
 '&amp;' => '&',
 '&nbsp;' => ' ',
 '&ndash;' => 'â€“',
 '&mdash;' => 'â€”',
 'amp;#8211;' => 'â€“',
 'amp;#8212;' => 'â€”',
 'amp;#039;' => "'",
 'amp;#39;' => "'",
 'amp;quot;' => '"',
 'amp;amp;' => '&',
 ];
 $text = str_replace(array_keys($replacements), array_values($replacements), $text);
 $text = preg_replace('/&?amp;#(\d+);?/i', ' ', $text);
 $text = preg_replace('/&#(\d+);?/', ' ', $text);

 // Strip boilerplate RSS feeder intro/outro phrases
 $text = preg_replace('/^ظ‡ط°ط§ ط§ظ„ظ…ظˆط¶ظˆط¹\s+/u', '', $text);
 $text = preg_replace('/ط¸ظ‡ط± ظ‡ط°ط§ ط§ظ„ظ…ظˆط¶ظˆط¹ ط£ظˆظ„ط§ظ‹ ط¹ظ„ظ‰.*/u', '', $text);
 $text = preg_replace('/ط¸ظ‡ط± ط¹ظ„ظ‰ ط§ظ„طھظ‚ظ†ظٹط© ط¨ظ„ط§ ط­ط¯ظˆط¯.*/u', '', $text);
 $text = preg_replace('/ط¸ظ‡ط±طھ ط£ظˆظ„ط§ظ‹ ط¹ظ„ظ‰.*/u', '', $text);
 $text = preg_replace('/The post .* appeared first on .*/i', '', $text);
 $text = preg_replace('/This article was originally published on .*/i', '', $text);

 return trim($text);
 }
}
