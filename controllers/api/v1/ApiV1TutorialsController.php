<?php

require_once __DIR__ . '/ApiV1BaseController.php';

class ApiV1TutorialsController extends ApiV1BaseController
{
    /**
     * GET /api/v1/tutorials
     * List tutorials with filtering & pagination
     */
    public function index()
    {
        $this->requireScope('articles:read');
        $db = new Database();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int) ($_GET['limit'] ?? 15)));
        $offset = ($page - 1) * $limit;

        $q = trim((string) ($_GET['q'] ?? ''));
        $difficulty = trim((string) ($_GET['difficulty'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));

        $where = [];
        $params = [];

        if ($q !== '') {
            $where[] = '(t.title LIKE :q OR t.summary LIKE :q)';
            $params[':q'] = "%{$q}%";
        }
        if ($difficulty !== '') {
            $where[] = 't.difficulty = :diff';
            $params[':diff'] = $difficulty;
        }
        if ($status !== '') {
            $where[] = 't.status = :status';
            $params[':status'] = $status;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = $db->fetch("SELECT COUNT(*) as count FROM tutorials t {$whereSql}", $params)['count'] ?? 0;

        $sql = "SELECT t.id, t.title, t.slug, t.summary, t.category_id, c.name as category_name,
                       t.featured_image, t.difficulty, t.estimated_minutes, t.author_id, u.username as author_name,
                       t.status, t.views_count, t.published_at, t.created_at,
                       COUNT(s.id) as steps_count
                FROM tutorials t
                LEFT JOIN categories c ON c.id = t.category_id
                LEFT JOIN users u ON u.id = t.author_id
                LEFT JOIN tutorial_steps s ON s.tutorial_id = t.id
                {$whereSql}
                GROUP BY t.id
                ORDER BY t.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        $tutorials = $db->fetchAll($sql, $params);

        $this->jsonSuccess($tutorials, [
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $limit,
                'total_items'  => (int) $total,
                'total_pages'  => ceil($total / $limit)
            ]
        ]);
    }

    /**
     * GET /api/v1/tutorials/{id_or_slug}
     * Retrieve single tutorial with all structured steps
     */
    public function show($idOrSlug)
    {
        $this->requireScope('articles:read');
        $db = new Database();

        $where = is_numeric($idOrSlug) ? 't.id = :val' : 't.slug = :val';
        $tutorial = $db->fetch("
            SELECT t.*, c.name as category_name, u.username as author_name
            FROM tutorials t
            LEFT JOIN categories c ON c.id = t.category_id
            LEFT JOIN users u ON u.id = t.author_id
            WHERE {$where}
        ", [':val' => $idOrSlug]);

        if (!$tutorial) {
            $this->jsonError('الشرح المطلوب غير موجود.', 404);
            return;
        }

        $steps = $db->fetchAll("
            SELECT * FROM tutorial_steps 
            WHERE tutorial_id = :tid 
            ORDER BY sort_order ASC, step_number ASC, id ASC
        ", [':tid' => $tutorial['id']]);

        $tutorial['steps'] = $steps;

        $this->jsonSuccess($tutorial);
    }

    /**
     * POST /api/v1/tutorials
     * Create tutorial with steps via AI Agent / API
     */
    public function store()
    {
        $this->requireScope('articles:write');
        $input = $this->getJsonInput();

        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') {
            $this->jsonError('حقل عنوان الشرح (title) إجباري.', 422);
            return;
        }

        $tutorialModel = new Tutorial();
        $authorId = $this->apiKey['user_id'] ?? 1;

        $slug = !empty($input['slug']) ? Helper::slugify($input['slug']) : Helper::slugify($title);
        $db = Database::getInstance();
        $check = $db->fetch("SELECT id FROM tutorials WHERE slug = :s", [':s' => $slug]);
        if ($check) {
            $slug .= '-' . rand(100, 999);
        }

        $tutorialId = $tutorialModel->create([
            'title'             => $title,
            'slug'              => $slug,
            'summary'           => $input['summary'] ?? '',
            'category_id'       => (int) ($input['category_id'] ?? 1),
            'featured_image'    => $input['featured_image'] ?? '',
            'difficulty'        => in_array($input['difficulty'] ?? '', ['beginner', 'intermediate', 'advanced']) ? $input['difficulty'] : 'beginner',
            'estimated_minutes' => max(1, (int) ($input['estimated_minutes'] ?? 5)),
            'author_id'         => $authorId,
            'status'            => ($input['status'] ?? 'draft') === 'published' ? 'published' : 'draft',
            'published_at'      => ($input['status'] ?? '') === 'published' ? date('Y-m-d H:i:s') : null,
        ]);

        if (!$tutorialId) {
            $this->jsonError('فشل حفظ الشرح في قاعدة البيانات.', 500);
            return;
        }

        // Save Steps if provided
        if (!empty($input['steps']) && is_array($input['steps'])) {
            foreach ($input['steps'] as $idx => $step) {
                $db->query("
                    INSERT INTO tutorial_steps 
                    (tutorial_id, step_number, title, image_url, image_caption, content, code_snippet, code_language, callout_type, callout_text, sort_order)
                    VALUES 
                    (:tid, :num, :title, :img, :cap, :cnt, :code, :lang, :ctype, :ctext, :sort)
                ", [
                    ':tid'   => $tutorialId,
                    ':num'   => $step['step_number'] ?? ($idx + 1),
                    ':title' => $step['title'] ?? ('الخطوة ' . ($idx + 1)),
                    ':img'   => $step['image_url'] ?? '',
                    ':cap'   => $step['image_caption'] ?? '',
                    ':cnt'   => $step['content'] ?? '',
                    ':code'  => $step['code_snippet'] ?? '',
                    ':lang'  => $step['code_language'] ?? 'bash',
                    ':ctype' => $step['callout_type'] ?? 'none',
                    ':ctext' => $step['callout_text'] ?? '',
                    ':sort'  => $step['sort_order'] ?? ($idx * 10)
                ]);
            }
        }

        $created = $tutorialModel->getById($tutorialId);
        $created['steps'] = $tutorialModel->getSteps($tutorialId);

        $this->jsonSuccess($created, ['message' => 'تم إنشاء ونشر الشرح وخطواته بنجاح.'], 201);
    }

    /**
     * DELETE /api/v1/tutorials/{id}
     */
    public function delete($id)
    {
        $this->requireScope('articles:delete');
        $tutorialModel = new Tutorial();
        $existing = $tutorialModel->getById((int) $id);
        if (!$existing) {
            $this->jsonError('الشرح المطلوب غير موجود.', 404);
            return;
        }

        $tutorialModel->delete((int) $id);
        $this->jsonSuccess(['id' => (int) $id], ['message' => 'تم حذف الشرح بنجاح.']);
    }
}
