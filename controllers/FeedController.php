<?php

class FeedController extends Controller
{
    public function rss()
    {
        $db = new Database();
        $freshDays = $this->freshDays();
        $articles = $db->fetchAll("SELECT a.*, c.name AS category_name FROM articles a LEFT JOIN categories c ON c.id = a.category_id WHERE a.status = 'published' AND COALESCE(a.published_at, a.created_at) >= (NOW() - INTERVAL {$freshDays} DAY) ORDER BY a.published_at DESC LIMIT 20");
        header('Content-Type: application/rss+xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<rss version="2.0"><channel><title>' . htmlspecialchars(Settings::get('site_name_ar', 'عصب التقنية'), ENT_XML1, 'UTF-8') . '</title><link>' . htmlspecialchars(app_url(), ENT_XML1, 'UTF-8') . '</link><description>آخر الأخبار التقنية</description>';
        foreach ($articles as $article) {
            $link = app_url('article/' . $article['slug']);
            echo '<item><title>' . htmlspecialchars($article['title'], ENT_XML1, 'UTF-8') . '</title><link>' . htmlspecialchars($link, ENT_XML1, 'UTF-8') . '</link><guid>' . htmlspecialchars($link, ENT_XML1, 'UTF-8') . '</guid><description><![CDATA[' . ($article['excerpt'] ?? mb_substr(strip_tags($article['content']), 0, 240)) . ']]></description><pubDate>' . date(DATE_RSS, strtotime($article['published_at'] ?: $article['created_at'])) . '</pubDate></item>';
        }
        echo '</channel></rss>'; exit;
    }

    public function rssByCategory($slug)
    {
        $db = new Database();
        $category = $db->fetch("SELECT * FROM categories WHERE slug = :slug LIMIT 1", array(':slug' => $slug));
        if (!$category) { http_response_code(404); return $this->view('errors/404'); }
        $freshDays = $this->freshDays();
        $articles = $db->fetchAll("SELECT a.*, c.name AS category_name FROM articles a LEFT JOIN categories c ON c.id = a.category_id WHERE a.status = 'published' AND a.category_id = :cat_id AND COALESCE(a.published_at, a.created_at) >= (NOW() - INTERVAL {$freshDays} DAY) ORDER BY a.published_at DESC LIMIT 20", array(':cat_id' => $category['id']));
        header('Content-Type: application/rss+xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<rss version="2.0"><channel><title>' . htmlspecialchars($category['name'] . ' - ' . Settings::get('site_name_ar', 'عصب التقنية'), ENT_XML1, 'UTF-8') . '</title><link>' . htmlspecialchars(app_url(), ENT_XML1, 'UTF-8') . '</link><description>آخر الأخبار في قسم ' . htmlspecialchars($category['name'], ENT_XML1, 'UTF-8') . '</description>';
        foreach ($articles as $article) {
            $link = app_url('article/' . $article['slug']);
            echo '<item><title>' . htmlspecialchars($article['title'], ENT_XML1, 'UTF-8') . '</title><link>' . htmlspecialchars($link, ENT_XML1, 'UTF-8') . '</link><guid>' . htmlspecialchars($link, ENT_XML1, 'UTF-8') . '</guid><description><![CDATA[' . ($article['excerpt'] ?? mb_substr(strip_tags($article['content']), 0, 240)) . ']]></description><pubDate>' . date(DATE_RSS, strtotime($article['published_at'] ?: $article['created_at'])) . '</pubDate></item>';
        }
        echo '</channel></rss>'; exit;
    }

    /**
     * عدد الأيام المؤهلة لظهور المقالات في خلاصات RSS (إعداد قابل للضبط).
     */
    private function freshDays()
    {
        $days = (int) Settings::get('rss_fresh_days', 30);
        if ($days < 1) {
            $days = 30;
        }
        return min($days, 365);
    }
}
