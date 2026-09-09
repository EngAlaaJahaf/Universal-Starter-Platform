<?php

class Article extends Model
{
    public function getLatest($limit = 10)
    {
        $limit = max(1, min((int) $limit, 100));
        $sql = "SELECT a.*, u.username AS author_name, c.name AS category_name, c.slug AS category_slug
                FROM articles a
                LEFT JOIN users u ON u.id = a.author_id
                LEFT JOIN categories c ON c.id = a.category_id
                WHERE a.status = 'published'
                  AND (a.published_at IS NULL OR a.published_at <= CURRENT_TIMESTAMP)
                ORDER BY COALESCE(a.published_at, a.created_at) DESC
                LIMIT " . $limit;

        return $this->db->fetchAll($sql);
    }

    public function getBySlug($slug)
    {
        $slug = trim((string)$slug);
        $decoded = urldecode($slug);
        
        $isAdmin = (class_exists('Auth') && Auth::check() && Auth::isAdmin()) ? 1 : 0;

        $sql = "SELECT a.*, u.username AS author_name, u.avatar AS author_avatar,
                       c.name AS category_name, c.slug AS category_slug
                FROM articles a
                LEFT JOIN users u ON u.id = a.author_id
                LEFT JOIN categories c ON c.id = a.category_id
                WHERE (a.slug = :slug OR a.slug = :decoded" . (is_numeric($slug) ? " OR a.id = " . (int)$slug : "") . ")
                  AND (a.status = 'published' OR :is_admin = 1)
                LIMIT 1";

        $article = $this->db->fetch($sql, [
            ':slug'     => $slug,
            ':decoded'  => $decoded,
            ':is_admin' => $isAdmin
        ]);

        if ($article) {
            $article['tags'] = $this->getTags($article['id']);
            $article['comments'] = $this->getComments($article['id']);
        }

        return $article;
    }

    public function getByCategory($categoryId)
    {
        $sql = "SELECT a.*, u.username AS author_name, c.name AS category_name, c.slug AS category_slug
                FROM articles a
                LEFT JOIN users u ON u.id = a.author_id
                LEFT JOIN categories c ON c.id = a.category_id
                WHERE a.category_id = :category_id AND a.status = 'published'
                ORDER BY COALESCE(a.published_at, a.created_at) DESC";

        return $this->db->fetchAll($sql, array(':category_id' => (int) $categoryId));
    }

    public function getRelated($articleId, $categoryId)
    {
        $sql = "SELECT a.*, u.username AS author_name, c.name AS category_name, c.slug AS category_slug
                FROM articles a
                LEFT JOIN users u ON u.id = a.author_id
                LEFT JOIN categories c ON c.id = a.category_id
                WHERE a.id <> :article_id
                  AND a.category_id = :category_id
                  AND a.status = 'published'
                ORDER BY COALESCE(a.published_at, a.created_at) DESC
                LIMIT 4";

        return $this->db->fetchAll($sql, array(
            ':article_id' => (int) $articleId,
            ':category_id' => (int) $categoryId,
        ));
    }

    private function getTags($articleId)
    {
        $sql = "SELECT t.id, t.name, t.slug
                FROM tags t
                INNER JOIN article_tags at ON at.tag_id = t.id
                WHERE at.article_id = :article_id
                ORDER BY t.name ASC";

        return $this->db->fetchAll($sql, array(':article_id' => (int) $articleId));
    }

    private function getComments($articleId)
    {
        $sql = "SELECT cm.*, u.username, u.avatar
                FROM comments cm
                LEFT JOIN users u ON u.id = cm.user_id
                WHERE cm.article_id = :article_id AND cm.status = 'approved'
                ORDER BY cm.created_at ASC";

        return $this->db->fetchAll($sql, array(':article_id' => (int) $articleId));
    }

    public function getPopular($limit = 5)
    {
        $limit = max(1, min((int) $limit, 50));
        $sql = "SELECT a.*, u.username AS author_name, c.name AS category_name, c.slug AS category_slug
                FROM articles a
                LEFT JOIN users u ON u.id = a.author_id
                LEFT JOIN categories c ON c.id = a.category_id
                WHERE a.status = 'published'
                ORDER BY a.views_count DESC, COALESCE(a.published_at, a.created_at) DESC
                LIMIT " . $limit;

        return $this->db->fetchAll($sql);
    }

    public function getTrendingRadar()
    {
        // 1. Fetch top tags or categories with active article counts and view metrics
        $sql = "SELECT c.name as topic, COUNT(a.id) as article_count, SUM(a.views_count) as total_views
                FROM categories c
                INNER JOIN articles a ON a.category_id = c.id
                WHERE a.status = 'published'
                GROUP BY c.id
                ORDER BY total_views DESC, article_count DESC
                LIMIT 4";
        
        $results = $this->db->fetchAll($sql);
        $radar = [];

        if (!empty($results)) {
            $badges = [
                ['icon' => 'flame', 'label' => 'ترند صاعد'],
                ['icon' => 'ai', 'label' => 'متصدر القراءة'],
                ['icon' => 'security', 'label' => 'اهتمام قياسي'],
                ['icon' => 'hardware', 'label' => 'تغطية حصرية']
            ];
            foreach ($results as $i => $row) {
                $badge = $badges[$i % count($badges)];
                $radar[] = [
                    'name' => $row['topic'],
                    'desc' => $row['article_count'] . ' مقالات وتقارير منشورة',
                    'icon' => $badge['icon'],
                    'label' => $badge['label'],
                    'views' => number_format((int)$row['total_views']) . ' قراءة'
                ];
            }
        } else {
            // Fallback baseline if no data yet
            $radar = [
                ['name' => 'الذكاء الاصطناعي التوليدي', 'desc' => 'نماذج التفكير والبرمجة', 'icon' => 'ai', 'label' => 'ترند صاعد', 'views' => '940 قراءة'],
                ['name' => 'الأمن السيبراني السحابي', 'desc' => 'حماية البيانات والأنظمة', 'icon' => 'security', 'label' => 'اهتمام قياسي', 'views' => '820 قراءة'],
                ['name' => 'الأجهزة الذكية والرقائق', 'desc' => 'معالجات الحوسبة الفائقة', 'icon' => 'hardware', 'label' => 'طلب واسع', 'views' => '710 قراءة'],
                ['name' => 'أنظمة المصادر المفتوحة', 'desc' => 'تطوير البرمجيات والنماذج', 'icon' => 'dev', 'label' => 'نمو متسارع', 'views' => '650 قراءة']
            ];
        }

        return $radar;
    }

    public function incrementViews($articleId)
    {
        return $this->db->query("UPDATE articles SET views_count = views_count + 1 WHERE id = :id", array(':id' => (int) $articleId));
    }
}
