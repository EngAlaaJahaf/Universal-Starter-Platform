<?php

class Comment extends Model
{
    public function getByArticle($articleId)
    {
        return $this->db->fetchAll('SELECT c.*, u.username, u.avatar FROM comments c LEFT JOIN users u ON u.id = c.user_id WHERE c.article_id = :id AND c.status = \'approved\' ORDER BY c.is_pinned DESC, c.created_at ASC', array(':id' => (int) $articleId));
    }

    public function create(array $data)
    {
        $this->db->query('INSERT INTO comments (article_id, user_id, guest_name, guest_email, parent_id, content, status, ip_address) VALUES (:article_id, :user_id, :guest_name, :guest_email, :parent_id, :content, :status, :ip_address)', array(
            ':article_id' => (int) $data['article_id'], ':user_id' => $data['user_id'] ?: null, ':guest_name' => $data['guest_name'] ?? null,
            ':guest_email' => $data['guest_email'] ?? null, ':parent_id' => $data['parent_id'] ?: null, ':content' => $data['content'],
            ':status' => $data['status'] ?? 'pending', ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ));
        return $this->db->lastInsertId();
    }

    public function update($id, $content)
    {
        return $this->db->query('UPDATE comments SET content = :content WHERE id = :id', array(':content' => $content, ':id' => (int) $id))->rowCount();
    }

    public function delete($id)
    {
        return $this->db->query('DELETE FROM comments WHERE id = :id', array(':id' => (int) $id))->rowCount();
    }

    public function getPending()
    {
        return $this->db->fetchAll('SELECT c.*, a.title, u.username FROM comments c INNER JOIN articles a ON a.id = c.article_id LEFT JOIN users u ON u.id = c.user_id WHERE c.status = \'pending\' ORDER BY c.created_at DESC');
    }

    public function setStatus($id, $status)
    {
        return $this->db->query('UPDATE comments SET status = :status WHERE id = :id', array(':status' => $status, ':id' => (int) $id))->rowCount();
    }

    public function countByArticle($articleId)
    {
        $row = $this->db->fetch('SELECT COUNT(*) AS total FROM comments WHERE article_id = :id AND status = \'approved\'', array(':id' => (int) $articleId));
        return (int) ($row['total'] ?? 0);
    }

    public function getRecent($limit = 5)
    {
        $limit = max(1, min(50, (int) $limit));
        return $this->db->fetchAll('SELECT c.*, a.title, u.username FROM comments c INNER JOIN articles a ON a.id = c.article_id LEFT JOIN users u ON u.id = c.user_id ORDER BY c.created_at DESC LIMIT ' . $limit);
    }
}
