<?php

require_once __DIR__ . '/ApiV1BaseController.php';

class ApiV1CategoriesController extends ApiV1BaseController
{
    /**
     * GET /api/v1/categories
     */
    public function index()
    {
        $this->requireScope('categories:read');
        $db = new Database();
        $categories = $db->fetchAll("
            SELECT c.*, COUNT(a.id) as articles_count 
            FROM categories c 
            LEFT JOIN articles a ON a.category_id = c.id 
            GROUP BY c.id 
            ORDER BY c.name ASC
        ");
        $this->jsonSuccess($categories);
    }

    /**
     * POST /api/v1/categories
     */
    public function store()
    {
        $this->requireScope('categories:manage');
        $body = $this->getJsonBody();
        $name = trim($body['name'] ?? '');
        if (empty($name)) {
            $this->jsonError('Field "name" is required.', 422);
        }

        $slug = trim($body['slug'] ?? strtolower(trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', (string) $name), '-')));
        $db = new Database();
        $db->query("INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :desc)", [
            ':name' => $name,
            ':slug' => $slug,
            ':desc' => $body['description'] ?? null
        ]);

        $id = $db->lastInsertId();
        $category = $db->fetch("SELECT * FROM categories WHERE id = :id", [':id' => $id]);
        $this->jsonSuccess($category, ['message' => 'Category created successfully.'], 201);
    }

    /**
     * PUT /api/v1/categories/{id}
     */
    public function update($id)
    {
        $this->requireScope('categories:manage');
        $body = $this->getJsonBody();
        $db = new Database();

        $old = $db->fetch("SELECT * FROM categories WHERE id = :id", [':id' => (int) $id]);
        if (!$old) {
            $this->jsonError('Category not found.', 404);
        }

        $name = trim($body['name'] ?? $old['name']);
        $slug = trim($body['slug'] ?? $old['slug']);
        $desc = $body['description'] ?? $old['description'];

        $db->query("UPDATE categories SET name = :name, slug = :slug, description = :desc WHERE id = :id", [
            ':name' => $name,
            ':slug' => $slug,
            ':desc' => $desc,
            ':id'   => (int) $id
        ]);

        $updated = $db->fetch("SELECT * FROM categories WHERE id = :id", [':id' => (int) $id]);
        $this->jsonSuccess($updated, ['message' => 'Category updated successfully.']);
    }

    /**
     * DELETE /api/v1/categories/{id}
     */
    public function delete($id)
    {
        $this->requireScope('categories:manage');
        $db = new Database();
        $db->query("DELETE FROM categories WHERE id = :id", [':id' => (int) $id]);
        $this->jsonSuccess(['deleted_id' => (int) $id], ['message' => 'Category deleted successfully.']);
    }
}
