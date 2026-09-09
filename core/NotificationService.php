<?php

class NotificationService
{
    public static function notifyUser($userId, $type, $titleAr, $messageAr, $link = null, $titleEn = null, $messageEn = null)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, type, title_ar, title_en, message_ar, message_en, link, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([
            (int) $userId,
            $type,
            $titleAr,
            $titleEn ?: $titleAr,
            $messageAr,
            $messageEn ?: $messageAr,
            $link
        ]);

        // If WebPush is configured and available
        if (class_exists('WebPush')) {
            try {
                WebPush::sendToUser((int) $userId, $titleAr, $messageAr, $link);
            } catch (Exception $e) {
                // Ignore WebPush delivery failure
            }
        }
        return true;
    }

    public static function notifyAdmins($type, $titleAr, $messageAr, $link = null)
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT u.id FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE r.name = 'admin' AND u.status = 'active'
        ");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($admins as $admin) {
            self::notifyUser($admin['id'], $type, $titleAr, $messageAr, $link);
        }
        return true;
    }
}
