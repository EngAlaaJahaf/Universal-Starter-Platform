<?php

class Tutorial extends Model
{
    protected $table = 'tutorials';

    public function getAllWithStepCount()
    {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT t.*, 
                   c.name as category_name, 
                   u.username as author_name,
                   COUNT(s.id) as steps_count
            FROM tutorials t
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN users u ON t.author_id = u.id
            LEFT JOIN tutorial_steps s ON t.id = s.tutorial_id
            GROUP BY t.id
            ORDER BY t.created_at DESC
        ");
    }

    public function getPublished($limit = 20)
    {
        $db = Database::getInstance();
        return $db->fetchAll("
            SELECT t.*, 
                   c.name as category_name, 
                   c.slug as category_slug,
                   u.username as author_name,
                   COUNT(s.id) as steps_count
            FROM tutorials t
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN users u ON t.author_id = u.id
            LEFT JOIN tutorial_steps s ON t.id = s.tutorial_id
            WHERE t.status = 'published'
            GROUP BY t.id
            ORDER BY t.published_at DESC
            LIMIT " . (int)$limit
        );
    }

    public function getBySlug($slug)
    {
        $db = Database::getInstance();
        $tutorial = $db->fetch("
            SELECT t.*, 
                   c.name as category_name, 
                   c.slug as category_slug,
                   u.username as author_name,
                   u.avatar as author_avatar
            FROM tutorials t
            LEFT JOIN categories c ON t.category_id = c.id
            LEFT JOIN users u ON t.author_id = u.id
            WHERE t.slug = :slug
            LIMIT 1
        ", [':slug' => $slug]);

        if (!$tutorial) {
            return null;
        }

        // Fetch steps ordered by sort_order / step_number
        $tutorial['steps'] = $db->fetchAll("
            SELECT * FROM tutorial_steps 
            WHERE tutorial_id = :tid 
            ORDER BY sort_order ASC, step_number ASC, id ASC
        ", [':tid' => (int) $tutorial['id']]);

        return $tutorial;
    }

    public function getByIdWithSteps($id)
    {
        $db = Database::getInstance();
        $tutorial = $db->fetch("SELECT * FROM tutorials WHERE id = :id", [':id' => (int) $id]);
        if (!$tutorial) return null;

        $tutorial['steps'] = $db->fetchAll("
            SELECT * FROM tutorial_steps 
            WHERE tutorial_id = :tid 
            ORDER BY sort_order ASC, step_number ASC, id ASC
        ", [':tid' => (int) $tutorial['id']]);

        return $tutorial;
    }

    public function incrementViews($id)
    {
        $db = Database::getInstance();
        $db->query("UPDATE tutorials SET views_count = views_count + 1 WHERE id = :id", [':id' => (int) $id]);
    }
}
