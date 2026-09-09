<?php

class TutorialController extends Controller
{
    public function index()
    {
        $tutorialModel = new Tutorial();
        $tutorials = $tutorialModel->getPublished(30);

        $db = Database::getInstance();
        $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name ASC");

        $this->view('tutorials/index', [
            'tutorials'  => $tutorials,
            'categories' => $categories,
        ]);
    }

    public function show($slug)
    {
        $tutorialModel = new Tutorial();
        $tutorial = $tutorialModel->getBySlug($slug);

        if (!$tutorial || ($tutorial['status'] !== 'published' && !Auth::isAdmin())) {
            http_response_code(404);
            return $this->view('errors/404');
        }

        // Increment views
        $tutorialModel->incrementViews($tutorial['id']);
        $tutorial['views_count'] = ((int) ($tutorial['views_count'] ?? 0)) + 1;

        // Fetch other related tutorials
        $db = Database::getInstance();
        $related = $db->fetchAll("
            SELECT id, title, slug, featured_image, difficulty, estimated_minutes, published_at 
            FROM tutorials 
            WHERE id != :tid AND status = 'published' 
            ORDER BY published_at DESC LIMIT 4
        ", [':tid' => (int) $tutorial['id']]);

        $this->view('tutorials/show', [
            'tutorial' => $tutorial,
            'steps'    => $tutorial['steps'] ?? [],
            'related'  => $related,
        ]);
    }
}
