<?php

/**
 * AggregatorFeedParsing - feed/RSS parsing + sanitisation helpers
 * extracted from AggregatorController (SH-08).
 * Applied via `use AggregatorFeedParsing;` - same class scope, no behaviour change.
 */

trait AggregatorFeedParsing
{
 private function uaLabel($ua)
 {
 if (str_contains($ua, 'Googlebot')) return 'Googlebot';
 if (str_contains($ua, 'Feedly')) return 'Feedly';
 if (str_contains($ua, 'SimplePie')) return 'SimplePie';
 if (str_contains($ua, 'StarterPlatform'))  return 'StarterBot';
 return 'Chrome';
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
