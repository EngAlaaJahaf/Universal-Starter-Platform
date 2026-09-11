<?php

class Newsletter extends Model
{
    public function subscribe($email, $name = '')
    {
        $email = strtolower(trim($email));
        $existing = $this->db->fetch("SELECT id, status FROM newsletters WHERE email = :email", [':email' => $email]);
        if ($existing && ($existing['status'] ?? '') === 'active') {
            return 'already_subscribed';
        }
        if ($existing && ($existing['status'] ?? '') === 'unsubscribed') {
            $this->db->query(
                "UPDATE newsletters SET status = 'active', unsubscribed_at = NULL, name = COALESCE(NULLIF(:name, ''), name), subscribed_at = CURRENT_TIMESTAMP WHERE id = :id",
                [':name' => $name, ':id' => $existing['id']]
            );
            return 'resubscribed';
        }
        $this->db->query(
            "INSERT INTO newsletters (email, name, status, subscribed_at) VALUES (:email, :name, 'active', CURRENT_TIMESTAMP)",
            [':email' => $email, ':name' => $name]
        );
        return 'subscribed';
    }

    public function unsubscribe($email)
    {
        return $this->db->query(
            "UPDATE newsletters SET status = 'unsubscribed', unsubscribed_at = CURRENT_TIMESTAMP WHERE email = :email",
            [':email' => strtolower(trim($email))]
        )->rowCount();
    }

    public function getActive()
    {
        return $this->db->fetchAll("SELECT * FROM newsletters WHERE status = 'active' ORDER BY subscribed_at DESC");
    }

    public function getCount()
    {
        $row = $this->db->fetch("SELECT COUNT(*) total FROM newsletters WHERE status = 'active'");
        return (int)($row['total'] ?? 0);
    }
}
