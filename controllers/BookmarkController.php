<?php

class BookmarkController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $userId = (int) $user['id'];
        $db = new Database();

        $articles = $db->fetchAll("
            SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.username AS author_name
            FROM bookmarks b
            JOIN articles a ON b.article_id = a.id
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN users u ON a.author_id = u.id
            WHERE b.user_id = :user_id
            ORDER BY b.created_at DESC
        ", array(':user_id' => $userId));

        $pageTitle = 'المقالات المحفوظة والمفضلة | ' . Settings::get('site_name_ar', 'منصة الأخبار التقنية');
        $this->view('bookmarks', [
            'articles'  => $articles,
            'pageTitle' => $pageTitle
        ]);
    }

    public function toggle($id)
    {
        CSRF::verifyRequest(); Auth::requireLogin();
        $db = new Database(); $userId = Auth::user()['id'];
        $existing = $db->fetch('SELECT id FROM bookmarks WHERE user_id=:user_id AND article_id=:article_id', array(':user_id'=>$userId,':article_id'=>(int)$id));
        if ($existing) { $db->query('DELETE FROM bookmarks WHERE id=:id', array(':id'=>$existing['id'])); $saved=false; }
        else { $db->query('INSERT INTO bookmarks (user_id,article_id) VALUES (:user_id,:article_id)', array(':user_id'=>$userId,':article_id'=>(int)$id)); $saved=true; }
        header('Content-Type: application/json; charset=utf-8'); echo json_encode(array('ok'=>true,'saved'=>$saved)); exit;
    }
}
