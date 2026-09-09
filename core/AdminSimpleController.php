<?php

class AdminSimpleController extends AdminController
{
    protected $resource = '';
    protected $table = '';
    protected $primary = 'id';
    protected $fields = array();
    protected $listFields = array();
    protected $title = '';
    protected $order = 'id DESC';

    public function index()
    {
        $this->guardAdmin();
        $db = Database::getInstance();
        $rows = $db->fetchAll('SELECT * FROM ' . $this->table . ' ORDER BY ' . $this->order);
        
        $displayFields = !empty($this->listFields) ? $this->listFields : array_filter($this->fields, function($meta) {
            return ($meta['type'] ?? 'text') !== 'textarea';
        });

        $this->view('admin/simple/index', array(
            'title'      => $this->title,
            'resource'   => $this->resource,
            'rows'       => $rows,
            'fields'     => $displayFields,
            'allFields'  => $this->fields
        ));
    }

    public function create()
    {
        $this->guardAdmin();
        $this->view('admin/simple/form', array(
            'title'    => 'إضافة ' . $this->title,
            'resource' => $this->resource,
            'row'      => null,
            'fields'   => $this->fields
        ));
    }

    public function store()
    {
        $this->postGuard();
        $data = Sanitizer::cleanArray($_POST);
        $columns = array();
        $placeholders = array();
        $params = array();

        foreach ($this->fields as $field => $meta) {
            $columns[] = '`' . $field . '`';
            $placeholders[] = ':' . $field;
            $params[':' . $field] = $data[$field] ?? null;
        }

        $db = Database::getInstance();
        $db->query('INSERT INTO ' . $this->table . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $placeholders) . ')', $params);
        $id = $db->lastInsertId();
        $this->audit('create', $this->resource, $id, null, $data);
        Session::flash('success', 'تمت إضافة السجل بنجاح.');
        return $this->redirect('admin/' . $this->resource);
    }

    public function edit($id)
    {
        $this->guardAdmin();
        $db = Database::getInstance();
        $row = $db->fetch('SELECT * FROM ' . $this->table . ' WHERE ' . $this->primary . ' = :id', array(':id' => (int) $id));
        $this->view('admin/simple/form', array(
            'title'    => 'تعديل ' . $this->title,
            'resource' => $this->resource,
            'row'      => $row,
            'fields'   => $this->fields
        ));
    }

    public function update($id)
    {
        $this->postGuard();
        $data = Sanitizer::cleanArray($_POST);
        $sets = array();
        $params = array(':id' => (int) $id);

        foreach ($this->fields as $field => $meta) {
            $sets[] = '`' . $field . '` = :' . $field;
            $params[':' . $field] = $data[$field] ?? null;
        }

        $db = Database::getInstance();
        $old = $db->fetch('SELECT * FROM ' . $this->table . ' WHERE ' . $this->primary . ' = :id', array(':id' => (int) $id));
        $db->query('UPDATE ' . $this->table . ' SET ' . implode(',', $sets) . ' WHERE ' . $this->primary . ' = :id', $params);
        $this->audit('update', $this->resource, $id, $old, $data);
        Session::flash('success', 'تم تحديث السجل بنجاح.');
        return $this->redirect('admin/' . $this->resource);
    }

    public function delete($id)
    {
        $this->postGuard();
        $db = Database::getInstance();
        $old = $db->fetch('SELECT * FROM ' . $this->table . ' WHERE ' . $this->primary . ' = :id', array(':id' => (int) $id));
        $db->query('DELETE FROM ' . $this->table . ' WHERE ' . $this->primary . ' = :id', array(':id' => (int) $id));
        $this->audit('delete', $this->resource, $id, $old);
        Session::flash('success', 'تم حذف السجل بنجاح.');
        return $this->redirect('admin/' . $this->resource);
    }
}
