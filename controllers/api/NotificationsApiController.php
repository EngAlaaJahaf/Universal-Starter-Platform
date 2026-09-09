<?php

class NotificationsApiController
{
    public function index()
    {
        header('Content-Type: application/json; charset=utf-8');
        $user = Auth::user();
        if (!$user) {
            echo json_encode(['success' => false, 'error' => 'غير مصرح'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT * FROM notifications
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 15
        ");
        $stmt->execute([$user['id']]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $unreadStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $unreadStmt->execute([$user['id']]);
        $unreadCount = $unreadStmt->fetchColumn();

        echo json_encode([
            'success'      => true,
            'unread_count' => (int) $unreadCount,
            'data'         => $notifications
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function markAllRead()
    {
        header('Content-Type: application/json; charset=utf-8');
        $user = Auth::user();
        if (!$user) {
            echo json_encode(['success' => false], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        $stmt->execute([$user['id']]);

        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
