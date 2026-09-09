<?php

class CommentsApiController
{
    public function index($articleId)
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT c.*, u.username, u.avatar
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.article_id = ? AND c.status = 'approved'
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([(int) $articleId]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $comments], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function store()
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $articleId = (int) ($input['article_id'] ?? 0);
        $content = trim($input['content'] ?? '');
        $parentId = !empty($input['parent_id']) ? (int) $input['parent_id'] : null;
        $user = Auth::user();

        if ($articleId <= 0 || empty($content)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'error' => 'بيانات التعليق غير مكتملة.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO comments (article_id, user_id, parent_id, content, status)
            VALUES (?, ?, ?, ?, 'approved')
        ");
        $stmt->execute([$articleId, $user ? $user['id'] : null, $parentId, htmlspecialchars($content, ENT_QUOTES, 'UTF-8')]);

        echo json_encode(['success' => true, 'id' => $db->lastInsertId()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
