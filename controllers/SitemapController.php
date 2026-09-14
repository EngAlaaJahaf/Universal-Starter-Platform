<?php

class SitemapController extends Controller
{
    /**
     * Comprehensive XML Sitemap with Google News Extensions
     */
    public function index()
    {
        $db = Database::getInstance();
        $siteName = Settings::get('site_name_ar', 'منصتي الذكية');

        // 1. Published Articles (limited to a fresh window to keep sitemap lean)
        $sitemapDays = (int) Settings::get('sitemap_days', 180);
        if ($sitemapDays < 1) {
            $sitemapDays = 180;
        }
        $artStmt = $db->query("
            SELECT id, slug, title, title_ar, created_at, updated_at, category_id
            FROM articles 
            WHERE status = 'published' 
              AND COALESCE(published_at, created_at) >= (NOW() - INTERVAL {$sitemapDays} DAY)
            ORDER BY updated_at DESC 
            LIMIT 5000
        ");
        $articles = $artStmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Categories
        $categories = $db->fetchAll("SELECT slug, name FROM categories ORDER BY name ASC");

        // 3. Static & Dynamic Pages
        $pages = $db->fetchAll("SELECT slug, created_at FROM pages WHERE status = 'published'");

        // 4. Live Blogs
        $liveBlogs = $db->fetchAll("SELECT id, title, created_at FROM live_blogs WHERE status != 'archived'");

        // 5. Tutorials
        $tutorials = $db->fetchAll("SELECT id, slug, title, created_at FROM tutorials WHERE status = 'published'");

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
            . 'xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" '
            . 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        // Homepage
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars(app_url(), ENT_XML1, 'UTF-8') . "</loc>\n";
        echo "    <changefreq>always</changefreq>\n";
        echo "    <priority>1.0</priority>\n";
        echo "  </url>\n";

        // Articles (with Google News tags for recent news <= 48h)
        $now = time();
        foreach ($articles as $row) {
            $pubTime = strtotime($row['created_at']);
            $modTime = strtotime($row['updated_at'] ?? $row['created_at']);
            $isRecentNews = ($now - $pubTime) <= (48 * 3600);
            $artTitle = $row['title_ar'] ?: $row['title'];

            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars(app_url('article/' . $row['slug']), ENT_XML1, 'UTF-8') . "</loc>\n";
            echo "    <lastmod>" . date('c', $modTime) . "</lastmod>\n";
            echo "    <changefreq>" . ($isRecentNews ? 'hourly' : 'daily') . "</changefreq>\n";
            echo "    <priority>" . ($isRecentNews ? '0.9' : '0.8') . "</priority>\n";

            // Google News specific tag for articles published within the last 48 hours
            if ($isRecentNews) {
                echo "    <news:news>\n";
                echo "      <news:publication>\n";
                echo "        <news:name>" . htmlspecialchars($siteName, ENT_XML1, 'UTF-8') . "</news:name>\n";
                echo "        <news:language>ar</news:language>\n";
                echo "      </news:publication>\n";
                echo "      <news:publication_date>" . date('c', $pubTime) . "</news:publication_date>\n";
                echo "      <news:title>" . htmlspecialchars($artTitle, ENT_XML1, 'UTF-8') . "</news:title>\n";
                echo "    </news:news>\n";
            }

            echo "  </url>\n";
        }

        // Categories
        foreach ($categories as $row) {
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars(app_url('category/' . $row['slug']), ENT_XML1, 'UTF-8') . "</loc>\n";
            if (!empty($row['updated_at'])) {
                echo "    <lastmod>" . date('c', strtotime($row['updated_at'])) . "</lastmod>\n";
            }
            echo "    <changefreq>daily</changefreq>\n";
            echo "    <priority>0.7</priority>\n";
            echo "  </url>\n";
        }

        // Live Blogs
        foreach ($liveBlogs as $row) {
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars(app_url('live-blog/' . $row['id']), ENT_XML1, 'UTF-8') . "</loc>\n";
            echo "    <lastmod>" . date('c', strtotime($row['updated_at'] ?? $row['created_at'])) . "</lastmod>\n";
            echo "    <changefreq>always</changefreq>\n";
            echo "    <priority>0.85</priority>\n";
            echo "  </url>\n";
        }

        // Tutorials
        foreach ($tutorials as $row) {
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars(app_url('tutorials/' . ($row['slug'] ?? $row['id'])), ENT_XML1, 'UTF-8') . "</loc>\n";
            echo "    <lastmod>" . date('c', strtotime($row['updated_at'] ?? $row['created_at'])) . "</lastmod>\n";
            echo "    <changefreq>weekly</changefreq>\n";
            echo "    <priority>0.75</priority>\n";
            echo "  </url>\n";
        }

        // Pages
        foreach ($pages as $row) {
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars(app_url('page/' . $row['slug']), ENT_XML1, 'UTF-8') . "</loc>\n";
            echo "    <lastmod>" . date('c', strtotime($row['updated_at'] ?? $row['created_at'])) . "</lastmod>\n";
            echo "    <changefreq>monthly</changefreq>\n";
            echo "    <priority>0.5</priority>\n";
            echo "  </url>\n";
        }

        echo '</urlset>';
        exit;
    }

    /**
     * Dynamic robots.txt output with configured directives & Sitemap link
     */
    public function robotsTxt()
    {
        header('Content-Type: text/plain; charset=utf-8');
        $customRobots = Settings::get('robots_txt_custom');
        $sitemapUrl = app_url('sitemap.xml');

        if (!empty($customRobots)) {
            $output = str_replace('{SITEMAP_URL}', $sitemapUrl, $customRobots);
            echo $output;
        } else {
            echo "User-agent: *\n";
            echo "Allow: /\n";
            echo "Disallow: /admin/\n";
            echo "Disallow: /login\n";
            echo "Disallow: /api/\n";
            echo "\n";
            echo "Sitemap: " . $sitemapUrl . "\n";
        }
        exit;
    }

    /**
     * Dynamic ads.txt output for IAB & Google AdSense Publisher verification
     */
    public function adsTxt()
    {
        header('Content-Type: text/plain; charset=utf-8');
        $adsTxt = Settings::get('ads_txt_content');
        if (!empty($adsTxt)) {
            echo trim($adsTxt) . "\n";
        } else {
            echo "google.com, pub-0000000000000000, DIRECT, f08c47fec0942fa0\n";
        }
        exit;
    }
}
