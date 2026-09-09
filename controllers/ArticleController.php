<?php

class ArticleController extends Controller
{
    public function index()
    {
        $articleModel = new Article();
        $categoryModel = new Category();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) Settings::get('articles_per_page', 12);
        if ($perPage < 1) $perPage = 12;

        $articles = $articleModel->getLatest($perPage * 2);
        $categories = $categoryModel->getAll();

        $this->view('category', [
            'category'   => [
                'name' => 'كافة المقالات والأخبار',
                'slug' => 'all',
                'description' => 'أحدث التغطيات والأخبار التقنية الشاملة على مدار الساعة.'
            ],
            'articles'   => $articles,
            'categories' => $categories
        ]);
    }

    public function show($slug)
    {
        $articleModel = new Article();
        $article = $articleModel->getBySlug($slug);

        if (!$article) {
            http_response_code(404);
            return $this->view('errors/404');
        }

        // Increment view count ONLY if it is a unique legitimate human visit (No refresh spam / No bots / No admin preview)
        if ($this->shouldCountUniqueView($article['id'])) {
            $articleModel->incrementViews($article['id']);
            $article['views_count'] = ((int) ($article['views_count'] ?? 0)) + 1;
        }

        // Fetch Real Database Reactions (No fake stats!)
        $db = new Database();
        $reactionCounts = [
            'fire'     => 0,
            'rocket'   => 0,
            'bulb'     => 0,
            'thinking' => 0,
            'bolt'     => 0,
        ];
        $userReacted = [];

        $countsRows = $db->fetchAll('
            SELECT type, COUNT(*) as total 
            FROM reactions 
            WHERE article_id = :aid 
            GROUP BY type
        ', [':aid' => (int) $article['id']]);

        foreach ($countsRows as $row) {
            if (isset($reactionCounts[$row['type']])) {
                $reactionCounts[$row['type']] = (int) $row['total'];
            }
        }

        $userId = Auth::user()['id'] ?? null;
        $ipHash = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . ($_SERVER['HTTP_USER_AGENT'] ?? ''));

        $userId = Auth::user()['id'] ?? null;
        $ipHash = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . ($_SERVER['HTTP_USER_AGENT'] ?? ''));

        if ($userId) {
            $userRows = $db->fetchAll('SELECT type FROM reactions WHERE article_id = :aid AND user_id = :uid', [
                ':aid' => (int) $article['id'],
                ':uid' => $userId
            ]);
            $userReacted = array_column($userRows, 'type');
        } else {
            $userRows = $db->fetchAll('SELECT type FROM reactions WHERE article_id = :aid AND ip_hash = :ip', [
                ':aid' => (int) $article['id'],
                ':ip'  => $ipHash
            ]);
            $userReacted = array_column($userRows, 'type');
        }

        // Fetch Real Related Articles (Same category or latest published)
        $related = [];
        if (!empty($article['category_id'])) {
            $related = $db->fetchAll("
                SELECT id, title, slug, featured_image, published_at, created_at, reading_time_minutes 
                FROM articles 
                WHERE id != :aid AND category_id = :cid AND status = 'published' 
                ORDER BY published_at DESC LIMIT 4
            ", [
                ':aid' => (int) $article['id'],
                ':cid' => (int) $article['category_id']
            ]);
        }
        if (empty($related)) {
            $related = $db->fetchAll("
                SELECT id, title, slug, featured_image, published_at, created_at, reading_time_minutes 
                FROM articles 
                WHERE id != :aid AND status = 'published' 
                ORDER BY published_at DESC LIMIT 4
            ", [':aid' => (int) $article['id']]);
        }

        $this->view('article', array(
            'article'     => $article,
            'related'     => $related,
            'reactions'   => $reactionCounts,
            'userReacted' => $userReacted,
        ));
    }

    public function category($slug)
    {
        $categoryModel = new Category();
        $articleModel = new Article();
        $category = $categoryModel->getBySlug($slug);

        if (!$category) {
            http_response_code(404);
            return $this->view('errors/404');
        }

        $this->view('category', array(
            'category'   => $category,
            'articles'   => $articleModel->getByCategory($category['id']),
            'categories' => $categoryModel->getAll(),
        ));
    }

    /**
     * Standard Industry Practice for Unique View Tracking:
     * - Filters automated crawlers & bots
     * - Ignores admin/editor preview sessions
     * - Applies sliding session window (2 hours) to avoid refresh inflation
     */
    private function shouldCountUniqueView($articleId)
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (empty($userAgent) || preg_match('/(bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegram|curl|python|wget|preview)/i', $userAgent)) {
            return false;
        }

        if (Auth::isAdmin()) {
            return false;
        }

        Session::start();
        $viewed = Session::get('viewed_articles') ?: [];
        $now = time();
        $windowSeconds = 7200; // 2 hours de-duplication sliding window

        if (isset($viewed[$articleId]) && ($now - (int) $viewed[$articleId]) < $windowSeconds) {
            return false;
        }

        $viewed[$articleId] = $now;
        Session::set('viewed_articles', $viewed);

        return true;
    }
}
