<?php

/**
 * Universal Generic Admin CRUD Scaffold Controller
 *
 * Provides instant, zero-boilerplate CRUD capabilities for any database table.
 * Supports:
 * - Text, Number, Email, Password, Date, Datetime, Textarea, RichText (WYSIWYG), Select, Switch/Boolean
 * - Foreign Key Relations (automatic dropdowns & joined display)
 * - Automatic Secure File & Image Uploads
 * - Live Search, Sorting, and Pagination
 * - Automated Activity Audit Logging & CSRF Protection
 */
class AdminSimpleController extends AdminController
{
    protected $resource   = '';
    protected $table      = '';
    protected $primary    = 'id';
    protected $title      = '';
    protected $fields     = [];
    protected $listFields = [];
    protected $searchable = [];
    protected $perPage    = 25;
    protected $order      = 'id DESC';

    public function index()
    {
        $this->guardAdmin();
        $db = Database::getInstance();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $q = trim($_GET['q'] ?? '');
        $offset = ($page - 1) * $this->perPage;

        $where = [];
        $params = [];

        // Dynamic Search
        if ($q !== '') {
            $searchCols = !empty($this->searchable) ? $this->searchable : array_keys(array_filter($this->fields, function ($f) {
                $t = $f['type'] ?? 'text';
                return in_array($t, ['text', 'textarea', 'rich_text', 'email']);
            }));

            if (!empty($searchCols)) {
                $searchWheres = [];
                foreach ($searchCols as $col) {
                    $searchWheres[] = "`{$col}` LIKE :search_{$col}";
                    $params[":search_{$col}"] = '%' . $q . '%';
                }
                $where[] = '(' . implode(' OR ', $searchWheres) . ')';
            }
        }

        $whereSql = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

        // Total count
        $totalRow = $db->fetch("SELECT COUNT(*) as cnt FROM `{$this->table}` {$whereSql}", $params);
        $totalItems = (int)($totalRow['cnt'] ?? 0);
        $totalPages = max(1, (int)ceil($totalItems / $this->perPage));

        // Fetch paginated rows
        $sql = "SELECT * FROM `{$this->table}` {$whereSql} ORDER BY {$this->order} LIMIT {$this->perPage} OFFSET {$offset}";
        $rows = $db->fetchAll($sql, $params);

        // Preload Relation Options for display
        $relationData = $this->loadRelationData($db);

        $displayFields = !empty($this->listFields) ? $this->listFields : array_filter($this->fields, function ($meta) {
            $t = $meta['type'] ?? 'text';
            return !in_array($t, ['textarea', 'rich_text', 'json', 'password']);
        });

        $this->view('admin/simple/index', [
            'title'        => $this->title,
            'resource'     => $this->resource,
            'rows'         => $rows,
            'fields'       => $displayFields,
            'allFields'    => $this->fields,
            'relationData' => $relationData,
            'search'       => $q,
            'page'         => $page,
            'totalPages'   => $totalPages,
            'totalItems'   => $totalItems
        ]);
    }

    public function create()
    {
        $this->guardAdmin();
        $db = Database::getInstance();
        $relationData = $this->loadRelationData($db);

        $this->view('admin/simple/form', [
            'title'        => 'إضافة ' . $this->title,
            'resource'     => $this->resource,
            'row'          => null,
            'fields'       => $this->fields,
            'relationData' => $relationData
        ]);
    }

    public function store()
    {
        $this->postGuard();
        $db = Database::getInstance();

        $data = Sanitizer::cleanArray($_POST);
        $processed = $this->processFormInput($data, $_FILES);

        if (!empty($processed['errors'])) {
            Session::flash('error', implode('<br>', $processed['errors']));
            return $this->redirect('admin/' . $this->resource . '/create');
        }

        $columns = [];
        $placeholders = [];
        $params = [];

        foreach ($processed['values'] as $field => $val) {
            $columns[] = '`' . $field . '`';
            $placeholders[] = ':' . $field;
            $params[':' . $field] = $val;
        }

        $sql = "INSERT INTO `{$this->table}` (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
        $db->query($sql, $params);
        $id = $db->lastInsertId();

        $this->audit('create', $this->resource, $id, null, $processed['values']);
        Session::flash('success', 'تمت إضافة السجل بنجاح.');
        return $this->redirect('admin/' . $this->resource);
    }

    public function edit($id)
    {
        $this->guardAdmin();
        $db = Database::getInstance();

        $row = $db->fetch("SELECT * FROM `{$this->table}` WHERE `{$this->primary}` = :id", [':id' => (int)$id]);
        if (!$row) {
            Session::flash('error', 'السجل غير موجود.');
            return $this->redirect('admin/' . $this->resource);
        }

        $relationData = $this->loadRelationData($db);

        $this->view('admin/simple/form', [
            'title'        => 'تعديل ' . $this->title,
            'resource'     => $this->resource,
            'row'          => $row,
            'fields'       => $this->fields,
            'relationData' => $relationData
        ]);
    }

    public function update($id)
    {
        $this->postGuard();
        $db = Database::getInstance();

        $old = $db->fetch("SELECT * FROM `{$this->table}` WHERE `{$this->primary}` = :id", [':id' => (int)$id]);
        if (!$old) {
            Session::flash('error', 'السجل المطلوب غير موجود.');
            return $this->redirect('admin/' . $this->resource);
        }

        $data = Sanitizer::cleanArray($_POST);
        $processed = $this->processFormInput($data, $_FILES, $old);

        if (!empty($processed['errors'])) {
            Session::flash('error', implode('<br>', $processed['errors']));
            return $this->redirect('admin/' . $this->resource . '/' . $id . '/edit');
        }

        $sets = [];
        $params = [':id' => (int)$id];

        foreach ($processed['values'] as $field => $val) {
            $sets[] = "`{$field}` = :{$field}";
            $params[':' . $field] = $val;
        }

        if (!empty($sets)) {
            $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets) . " WHERE `{$this->primary}` = :id";
            $db->query($sql, $params);
            $this->audit('update', $this->resource, $id, $old, $processed['values']);
        }

        Session::flash('success', 'تم تحديث السجل بنجاح.');
        return $this->redirect('admin/' . $this->resource);
    }

    public function delete($id)
    {
        $this->postGuard();
        $db = Database::getInstance();

        $old = $db->fetch("SELECT * FROM `{$this->table}` WHERE `{$this->primary}` = :id", [':id' => (int)$id]);
        if ($old) {
            $db->query("DELETE FROM `{$this->table}` WHERE `{$this->primary}` = :id", [':id' => (int)$id]);
            $this->audit('delete', $this->resource, $id, $old);
            Session::flash('success', 'تم حذف السجل بنجاح.');
        }

        return $this->redirect('admin/' . $this->resource);
    }

    /**
     * Process Form Inputs (handles files, passwords, switches, relations)
     */
    protected function processFormInput(array $data, array $files, array $existing = [])
    {
        $values = [];
        $errors = [];

        foreach ($this->fields as $field => $meta) {
            $type = $meta['type'] ?? 'text';
            $required = !empty($meta['required']);

            // Image / File Uploads
            if (in_array($type, ['image', 'file'])) {
                if (!empty($files[$field]['tmp_name']) && is_uploaded_file($files[$field]['tmp_name'])) {
                    if (class_exists('Upload')) {
                        $res = $type === 'image' ? Upload::image($files[$field]) : Upload::file($files[$field]);
                        if ($res['success']) {
                            $values[$field] = $res['path'];
                        } else {
                            $errors[] = "خطأ في رفع ملف {$meta['label']}: " . $res['error'];
                        }
                    } else {
                        $targetDir = dirname(__DIR__) . '/uploads/' . $this->resource;
                        if (!is_dir($targetDir)) @mkdir($targetDir, 0755, true);
                        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($files[$field]['name']));
                        move_uploaded_file($files[$field]['tmp_name'], $targetDir . '/' . $filename);
                        $values[$field] = 'uploads/' . $this->resource . '/' . $filename;
                    }
                } elseif (!empty($existing[$field])) {
                    $values[$field] = $existing[$field];
                } elseif ($required && empty($existing[$field])) {
                    $errors[] = "حقل {$meta['label']} مطلوب.";
                }
                continue;
            }

            // Password hashing
            if ($type === 'password') {
                $rawPass = trim($data[$field] ?? '');
                if ($rawPass !== '') {
                    $values[$field] = password_hash($rawPass, PASSWORD_DEFAULT);
                } elseif (!empty($existing[$field])) {
                    $values[$field] = $existing[$field];
                } elseif ($required && empty($existing[$field])) {
                    $errors[] = "كلمة المرور مطلوبة.";
                }
                continue;
            }

            // Switch / Boolean
            if ($type === 'switch' || $type === 'boolean') {
                $values[$field] = isset($data[$field]) && ($data[$field] == '1' || $data[$field] === 'on') ? 1 : 0;
                continue;
            }

            // Standard inputs
            $val = $data[$field] ?? null;
            if ($required && ($val === null || trim((string)$val) === '')) {
                $errors[] = "حقل «" . ($meta['label'] ?? $field) . "» مطلوب.";
            }

            $values[$field] = $val;
        }

        return ['values' => $values, 'errors' => $errors];
    }

    /**
     * Load Relation Data for Foreign Key Selects
     */
    protected function loadRelationData($db)
    {
        $data = [];
        foreach ($this->fields as $field => $meta) {
            if (($meta['type'] ?? '') === 'relation' && !empty($meta['table'])) {
                $table = $meta['table'];
                $keyCol = $meta['key'] ?? 'id';
                $dispCol = $meta['display'] ?? 'name';
                try {
                    $rows = $db->fetchAll("SELECT `{$keyCol}`, `{$dispCol}` FROM `{$table}` ORDER BY `{$dispCol}` ASC");
                    $map = [];
                    foreach ($rows as $r) {
                        $map[$r[$keyCol]] = $r[$dispCol];
                    }
                    $data[$field] = $map;
                } catch (Throwable $e) {
                    $data[$field] = [];
                }
            }
        }
        return $data;
    }
}
