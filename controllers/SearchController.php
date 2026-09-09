<?php

class SearchController extends Controller
{
    public function index()
    {
        $q = trim($_GET['q'] ?? '');
        $category = (int) ($_GET['category_id'] ?? 0);
        $from = $_GET['from'] ?? '';
        $to = $_GET['to'] ?? '';
        $sort = in_array($_GET['sort'] ?? '', array('views', 'oldest'), true) ? $_GET['sort'] : 'newest';
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $where = array("a.status = 'published'"); $params = array();
        if ($q !== '') {
            $where[] = '(a.title LIKE :q1 OR a.content LIKE :q2 OR u.username LIKE :q3)';
            $params[':q1'] = '%' . $q . '%';
            $params[':q2'] = '%' . $q . '%';
            $params[':q3'] = '%' . $q . '%';
        }
        if ($category > 0) { $where[] = 'a.category_id = :category_id'; $params[':category_id'] = $category; }
        if ($from !== '') { $where[] = 'DATE(a.published_at) >= :date_from'; $params[':date_from'] = $from; }
        if ($to !== '') { $where[] = 'DATE(a.published_at) <= :date_to'; $params[':date_to'] = $to; }
        $db = new Database(); $condition = implode(' AND ', $where);
        $count = $db->fetch('SELECT COUNT(*) AS total FROM articles a LEFT JOIN users u ON u.id = a.author_id WHERE ' . $condition, $params);
        $total = (int) ($count['total'] ?? 0); $offset = ($page - 1) * $perPage;
        $order = $sort === 'views' ? 'a.views_count DESC' : ($sort === 'oldest' ? 'a.published_at ASC' : 'a.published_at DESC');
        $articles = $db->fetchAll('SELECT a.*, u.username AS author_name, c.name AS category_name FROM articles a LEFT JOIN users u ON u.id = a.author_id LEFT JOIN categories c ON c.id = a.category_id WHERE ' . $condition . ' ORDER BY ' . $order . ' LIMIT ' . $perPage . ' OFFSET ' . $offset, $params);
        $categories = $db->fetchAll('SELECT id, name, slug FROM categories ORDER BY name');
        $this->view('search/results', compact('q', 'articles', 'categories', 'category', 'from', 'to', 'sort', 'page', 'perPage', 'total'));
    }

    public function suggest()
    {
        $q = trim($_GET['q'] ?? '');
        header('Content-Type: application/json; charset=utf-8');
        if (mb_strlen($q) < 3) exit(json_encode(array(), JSON_UNESCAPED_UNICODE));
        $db = new Database();
        $rows = $db->fetchAll('SELECT title, slug FROM articles WHERE status = \'published\' AND title LIKE :q ORDER BY published_at DESC LIMIT 5', array(':q' => '%' . $q . '%'));
        echo json_encode($rows, JSON_UNESCAPED_UNICODE); exit;
    }
}
