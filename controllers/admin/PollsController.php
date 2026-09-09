<?php

class PollsController extends AdminController
{
 private Poll $pollModel;

 public function __construct()
 {
 $this->pollModel = new Poll();
 }

 /**
 * Admin Dashboard: List all polls with analytics
 */
 public function index()
 {
 $this->guardAdmin();
 $polls = $this->pollModel->getAll();

 $totalPolls = count($polls);
 $activePolls = 0;
 $totalVotes = 0;
 $featuredPoll = null;

 foreach ($polls as $p) {
 if ($p['status'] === 'active') $activePolls++;
 $totalVotes += (int) ($p['total_votes'] ?? 0);
 if (!empty($p['is_featured'])) {
 $featuredPoll = $p;
 }
 }

 $this->view('admin/polls/index', [
 'polls' => $polls,
 'totalPolls' => $totalPolls,
 'activePolls' => $activePolls,
 'totalVotes' => $totalVotes,
 'featuredPoll' => $featuredPoll,
 'activeTab' => 'polls'
 ]);
 }

 /**
 * Show create poll form
 */
 public function create()
 {
 $this->guardAdmin();
 $this->view('admin/polls/form', [
 'poll' => null,
 'isEdit' => false,
 'activeTab' => 'polls'
 ]);
 }

 /**
 * Store new poll
 */
 public function store()
 {
 $this->postGuard();

 $question = trim((string)($_POST['question'] ?? ''));
 if (empty($question)) {
 Session::flash('error', 'يرجى كتابة سؤال الاستطلاع.');
 return $this->redirect('admin/polls/create');
 }

 $options = [];
 if (!empty($_POST['options']) && is_array($_POST['options'])) {
 foreach ($_POST['options'] as $idx => $optTitle) {
 $title = trim((string)$optTitle);
 if (!empty($title)) {
 $icon = trim((string)($_POST['option_icons'][$idx] ?? 'sparkle'));
 $options[] = [
 'title' => $title,
 'icon' => !empty($icon) ? $icon : 'sparkle'
 ];
 }
 }
 }

 if (count($options) < 2) {
 Session::flash('error', 'يجب إضافة خيارين على الأقل للاستطلاع.');
 return $this->redirect('admin/polls/create');
 }

 $data = [
 'question' => $question,
 'description' => trim((string)($_POST['description'] ?? '')),
 'status' => in_array($_POST['status'] ?? '', ['active', 'inactive', 'archived']) ? $_POST['status'] : 'active',
 'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
 'expires_at' => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null
 ];

 $pollId = $this->pollModel->create($data, $options);

 ActivityLogger::log('create_poll', "أنشأ استطلاعاً جديداً: {$question}");
 Session::flash('success', 'تم إنشاء استطلاع الرأي بنجاح.');
 return $this->redirect('admin/polls');
 }

 /**
 * Show edit poll form
 */
 public function edit($id)
 {
 $this->guardAdmin();
 $poll = $this->pollModel->getById($id);
 if (!$poll) {
 Session::flash('error', 'الاستطلاع غير موجود.');
 return $this->redirect('admin/polls');
 }

 $this->view('admin/polls/form', [
 'poll' => $poll,
 'isEdit' => true,
 'activeTab' => 'polls'
 ]);
 }

 /**
 * Update existing poll
 */
 public function update($id)
 {
 $this->postGuard();

 $poll = $this->pollModel->getById($id);
 if (!$poll) {
 Session::flash('error', 'الاستطلاع غير موجود.');
 return $this->redirect('admin/polls');
 }

 $question = trim((string)($_POST['question'] ?? ''));
 if (empty($question)) {
 Session::flash('error', 'يرجى كتابة سؤال الاستطلاع.');
 return $this->redirect("admin/polls/{$id}/edit");
 }

 $options = [];
 if (!empty($_POST['options']) && is_array($_POST['options'])) {
 foreach ($_POST['options'] as $idx => $optTitle) {
 $title = trim((string)$optTitle);
 if (!empty($title)) {
 $optId = !empty($_POST['option_ids'][$idx]) ? (int)$_POST['option_ids'][$idx] : 0;
 $icon = trim((string)($_POST['option_icons'][$idx] ?? 'sparkle'));
 $options[] = [
 'id' => $optId,
 'title' => $title,
 'icon' => !empty($icon) ? $icon : 'sparkle'
 ];
 }
 }
 }

 if (count($options) < 2) {
 Session::flash('error', 'يجب أن يحتوي الاستطلاع على خيارين على الأقل.');
 return $this->redirect("admin/polls/{$id}/edit");
 }

 $data = [
 'question' => $question,
 'description' => trim((string)($_POST['description'] ?? '')),
 'status' => in_array($_POST['status'] ?? '', ['active', 'inactive', 'archived']) ? $_POST['status'] : 'active',
 'is_featured' => !empty($_POST['is_featured']) ? 1 : 0,
 'expires_at' => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null
 ];

 $this->pollModel->update($id, $data, $options);

 ActivityLogger::log('update_poll', "حدّث الاستطلاع: {$question}");
 Session::flash('success', 'تم تحديث بيانات الاستطلاع والخيارات بنجاح.');
 return $this->redirect('admin/polls');
 }

 /**
 * Delete poll
 */
 public function delete($id)
 {
 $this->postGuard();

 $poll = $this->pollModel->getById($id);
 if ($poll) {
 $this->pollModel->delete($id);
 ActivityLogger::log('delete_poll', "حذف الاستطلاع: {$poll['question']}");
 Session::flash('success', 'تم حذف الاستطلاع بالكامل.');
 }

 return $this->redirect('admin/polls');
 }

 /**
 * Set as active featured poll on homepage
 */
 public function setFeatured($id)
 {
 $this->postGuard();

 $this->pollModel->setFeatured($id);
 Session::flash('success', 'تم تعيين الاستطلاع كاستطلاع رئيسي نشط على الصفحة الرئيسية.');
 return $this->redirect('admin/polls');
 }

 /**
 * Reset poll votes count
 */
 public function resetVotes($id)
 {
 $this->postGuard();

 $this->pollModel->resetVotes($id);
 Session::flash('success', 'تم تصفير كافة الأصوات المسجلة في هذا الاستطلاع.');
 return $this->redirect('admin/polls');
 }
}
