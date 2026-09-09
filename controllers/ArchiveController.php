<?php

/**
 * ArchiveController
 * يعرض أرشيف الأخبار المتقادمة/المؤرشفة للزوار بشكل منظم مع
 * تصفية حسب الشهر والبحث.
 */
class ArchiveController extends Controller
{
    public function index()
    {
        $db = new Database();

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) Settings::get('articles_per_page', 12);
        if ($perPage < 1) {
            $perPage = 12;
        }
        $offset = ($page - 1) * $perPage;

        // مرشحات اختيارية
        $month = trim((string) ($_GET['month'] ?? ''));
        $cat   = (int) ($_GET['category'] ?? 0);
        $q     = trim((string) ($_GET['q'] ?? ''));

        $where   = ["a.status = 'archived'"];
        $params  = [];

        if ($cat > 0) {
            $where[] = 'a.category_id = :cat';
            $params[':cat'] = $cat;
        }
        if ($q !== '') {
            $where[] = '(a.title LIKE :q OR a.title_ar LIKE :q)';
            $params[':q'] = "%{$q}%";
        }
        if ($month !== '' && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $where[] = 'DATE_FORMAT(COALESCE(a.published_at, a.created_at), \'%Y-%m\') = :month';
            $params[':month'] = $month;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $countRow = $db->fetch(
            "SELECT COUNT(*) AS c FROM articles a {$whereSql}",
            $params
        );
        $total = (int) ($countRow['c'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
            $offset = ($page - 1) * $perPage;
        }

        $articles = $db->fetchAll(
            "SELECT a.id, a.title, a.slug, a.excerpt, a.featured_image, a.published_at, a.created_at,
                    u.username AS author_name, c.name AS category_name, c.slug AS category_slug
             FROM articles a
             LEFT JOIN users u ON u.id = a.author_id
             LEFT JOIN categories c ON c.id = a.category_id
             {$whereSql}
             ORDER BY COALESCE(a.published_at, a.created_at) DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        // أشهر توفر المحتوى المؤرشف
        $months = $db->fetchAll(
            "SELECT DATE_FORMAT(COALESCE(published_at, created_at), '%Y-%m') AS ym
             FROM articles
             WHERE status = 'archived'
               AND COALESCE(published_at, created_at) IS NOT NULL
             GROUP BY ym
             ORDER BY ym DESC
             LIMIT 24"
        );

        $categories = $db->fetchAll("SELECT id, name, slug FROM categories ORDER BY name ASC");

        $this->view('archive', [
            'articles'    => $articles,
            'categories'  => $categories,
            'months'      => $months,
            'total'       => $total,
            'page'        => $page,
            'totalPages'  => $totalPages,
            'month'       => $month,
            'cat'         => $cat,
            'q'           => $q,
        ]);
    }
}
