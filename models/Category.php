<?php

class Category extends Model
{
    public function getAll()
    {
        return $this->db->fetchAll(
            "SELECT * FROM categories ORDER BY sort_order ASC, name ASC"
        );
    }

    public function getBySlug($slug)
    {
        return $this->db->fetch(
            "SELECT * FROM categories WHERE slug = :slug LIMIT 1",
            array(':slug' => $slug)
        );
    }
}
