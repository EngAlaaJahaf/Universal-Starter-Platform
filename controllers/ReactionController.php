<?php

class ReactionController extends Controller
{
    public function react($id, $type)
    {
        $allowedTypes = ['fire', 'rocket', 'bulb', 'thinking', 'bolt', 'like', 'love', 'wow', 'informative'];
        if (!in_array($type, $allowedTypes, true)) {
            http_response_code(422);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'Invalid reaction type.']);
            exit;
        }

        $userId = Auth::user()['id'] ?? null;
        $ipHash = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        $db = new Database();
        $userReacted = false;

        if ($userId) {
            $existing = $db->fetch('SELECT id FROM reactions WHERE article_id = :aid AND user_id = :uid AND type = :t', [
                ':aid' => (int) $id,
                ':uid' => $userId,
                ':t'   => $type
            ]);
            if ($existing) {
                $db->query('DELETE FROM reactions WHERE id = :id', [':id' => $existing['id']]);
                $userReacted = false;
            } else {
                $db->query('INSERT INTO reactions (article_id, user_id, ip_hash, type, created_at) VALUES (:aid, :uid, :ip, :t, CURRENT_TIMESTAMP)', [
                    ':aid' => (int) $id,
                    ':uid' => $userId,
                    ':ip'  => $ipHash,
                    ':t'   => $type
                ]);
                $userReacted = true;
            }
        } else {
            // Guest reaction by IP hash
            $existing = $db->fetch('SELECT id FROM reactions WHERE article_id = :aid AND ip_hash = :ip AND type = :t', [
                ':aid' => (int) $id,
                ':ip'  => $ipHash,
                ':t'   => $type
            ]);
            if ($existing) {
                $db->query('DELETE FROM reactions WHERE id = :id', [':id' => $existing['id']]);
                $userReacted = false;
            } else {
                $db->query('INSERT INTO reactions (article_id, ip_hash, type, created_at) VALUES (:aid, :ip, :t, CURRENT_TIMESTAMP)', [
                    ':aid' => (int) $id,
                    ':ip'  => $ipHash,
                    ':t'   => $type
                ]);
                $userReacted = true;
            }
        }

        $row = $db->fetch('SELECT COUNT(*) AS total FROM reactions WHERE article_id = :aid AND type = :t', [
            ':aid' => (int) $id,
            ':t'   => $type
        ]);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'      => true,
            'reacted' => $userReacted,
            'count'   => (int) ($row['total'] ?? 0)
        ]);
        exit;
    }
}
