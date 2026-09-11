<?php

class NewsletterCampaignController extends AdminController
{
 public function index()
 {
 $this->guardAdmin();
 $db = new Database();
 $campaigns = $db->fetchAll('SELECT c.*, u.username FROM newsletter_campaigns c LEFT JOIN users u ON u.id=c.user_id ORDER BY c.created_at DESC');
 $subscribers = $db->fetchAll('SELECT * FROM newsletters ORDER BY subscribed_at DESC');
 
 $activeCount = 0;
 $unsubCount = 0;
 foreach ($subscribers as $s) {
 if ($s['status'] === 'active') {
 $activeCount++;
 } else {
 $unsubCount++;
 }
 }

 $this->view('admin/newsletter/index', array(
 'campaigns' => $campaigns,
 'subscribers' => $subscribers,
 'activeCount' => $activeCount,
 'unsubCount' => $unsubCount,
 ));
 }

 public function create()
 {
 $this->guardAdmin();
 $this->view('admin/newsletter/form');
 }

 public function store()
 {
 $this->postGuard();
 $d = Sanitizer::cleanArray($_POST);
 $db = new Database();
 $db->query('INSERT INTO newsletter_campaigns (user_id,subject,body_html,status) VALUES (:user_id,:subject,:body_html,\'draft\')', array(
 ':user_id' => Auth::user()['id'],
 ':subject' => $d['subject'] ?? '',
 ':body_html' => $_POST['body_html'] ?? ''
 ));
 $id = $db->lastInsertId();
 $this->audit('create', 'newsletter_campaign', $id, null, $d);
 Session::flash('success', 'تم حفظ مسودة النشرة البريدية بنجاح.');
 return $this->redirect('admin/newsletter');
 }

 public function send($id)
 {
 $this->postGuard();
 $db = new Database();
 $campaign = $db->fetch('SELECT * FROM newsletter_campaigns WHERE id=:id', array(':id' => (int) $id));
 if (!$campaign) {
 return $this->redirect('admin/newsletter');
 }

 $subscribers = $db->fetchAll("SELECT email, name FROM newsletters WHERE status='active'");
 
 if (empty($subscribers)) {
 Session::flash('error', 'لا يوجد أي مشتركين نشطين في النشرة البريدية حالياً.');
 return $this->redirect('admin/newsletter');
 }

 // Never report a fake success: if no real mail server (Brevo API / SMTP) is
 // configured on THIS server, block the run and explain what to fix.
 $transport = Mailer::transport();
 if ($transport === 'simulate') {
 Session::flash('error', 'لم يُرسل أي بريد: لا يوجد خادم بريد مُهيّأ على هذا الخادم. اذهب إلى تبويب SMTP/Brevo في هذه الصفحة وأدخل مفتاح Brevo API أو إعدادات SMTP، ثم أرسل بريداً تجريبياً للتأكد.');
 return $this->redirect('admin/newsletter');
 }

 // Brevo only accepts well-known senders: if the configured From-address is not
 // registered in the account, every message is silently rejected by Brevo.
 $fromAddress = Settings::get('mail_from_address', MAIL_FROM_ADDRESS ?: 'no-reply@technews.local');
 if ($transport === 'brevo' && !Mailer::brevoSenderValid()) {
 Session::flash('error', 'لن يُرسل أي بريد: العنوان «البريد المرسل منه» من (' . $fromAddress . ') غير مسجّل في حساب Brevo. قم بتسجيله والتحقق منه من لوحة Brevo (Senders)، أو غيّر حقل From في تبويب SMTP إلى بريد مسجّل مثل kasperkey106@gmail.com.');
 return $this->redirect('admin/newsletter');
 }

 $sent = 0;
 $failed = 0;
 $lastMsgId = null;
 $lastHttp = null;
 foreach ($subscribers as $subscriber) {
 $lastHttp = Mailer::$lastBrevoHttpCode;
 $lastMsgId = Mailer::$lastBrevoMessageId;
 if (Mailer::send($subscriber['email'], $campaign['subject'], $campaign['body_html'])) {
 $sent++;
 } else {
 $failed++;
 }
 }

 $db->query('UPDATE newsletter_campaigns SET status=\'sent\',sent_count=:sent,failed_count=:failed,sent_at=CURRENT_TIMESTAMP WHERE id=:id', array(
 ':sent' => $sent,
 ':failed' => $failed,
 ':id' => (int) $id
 ));

 $this->audit('send', 'newsletter_campaign', $id, $campaign, array('sent' => $sent, 'failed' => $failed, 'transport' => $transport, 'brevo_http' => $lastHttp, 'brevo_message_id' => $lastMsgId));

 $acceptNote = ($transport === 'brevo' && $sent > 0)
 ? ' (قُبِلت الرسائل في Brevo — تتبّع التسليم من لوحة Brevo: إحصائيات → بريد المعاملات)'
 : '';
 $msgIdNote = (!empty($lastMsgId)) ? ' آخر Message-ID: ' . $lastMsgId : '';
 Session::flash('success', "تم إرسال الحملة البريدية عبر " . ($transport === 'brevo' ? 'Brevo API' : 'SMTP') . " إلى ({$sent}) مشترك، وفشل ({$failed})." . $acceptNote . $msgIdNote);
 return $this->redirect('admin/newsletter');
 }

 public function addSubscriber()
 {
 $this->postGuard();
 $email = strtolower(trim($_POST['email'] ?? ''));
 $name = Sanitizer::clean($_POST['name'] ?? '');

 if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
 Session::flash('error', 'البريد الإلكتروني المدخل غير صالح.');
 return $this->redirect('admin/newsletter?tab=subscribers');
 }

        $db = new Database();
        $existing = $db->fetch("SELECT id, status FROM newsletters WHERE email = :email", [':email' => $email]);
        if ($existing && ($existing['status'] ?? '') === 'active') {
            Session::flash('info', 'هذا البريد الإلكتروني مسجل ومشترك بالفعل في النشرة البريدية.');
            return $this->redirect('admin/newsletter?tab=subscribers');
        }
        if ($existing && ($existing['status'] ?? '') === 'unsubscribed') {
            $db->query("UPDATE newsletters SET status = 'active', unsubscribed_at = NULL, name = COALESCE(NULLIF(:name, ''), name) WHERE id = :id", [
                ':name' => $name,
                ':id' => $existing['id']
            ]);
            $this->audit('update', 'newsletter_subscriber', (int) $existing['id'], null, ['email' => $email, 'status' => 'active']);
            Session::flash('success', 'تمت إعادة تفعيل اشتراك هذا البريد في النشرة البريدية بنجاح.');
            return $this->redirect('admin/newsletter?tab=subscribers');
        }

        $db->query("
            INSERT INTO newsletters (email, name, status, subscribed_at) 
            VALUES (:email, :name, 'active', CURRENT_TIMESTAMP)
        ", array(
            ':email' => $email,
            ':name' => $name
        ));

        $this->audit('create', 'newsletter_subscriber', null, null, ['email' => $email, 'name' => $name]);
        Session::flash('success', 'تمت إضافة المشترك إلى النشرة البريدية بنجاح.');
        return $this->redirect('admin/newsletter?tab=subscribers');
 }

 public function toggleSubscriber($id)
 {
 $this->postGuard();
 $db = new Database();
 $sub = $db->fetch('SELECT * FROM newsletters WHERE id=:id', array(':id' => (int) $id));
 if ($sub) {
 $newStatus = ($sub['status'] === 'active') ? 'unsubscribed' : 'active';
 $unsubAt = ($newStatus === 'unsubscribed') ? date('Y-m-d H:i:s') : null;
 $db->query('UPDATE newsletters SET status=:status, unsubscribed_at=:unsub_at WHERE id=:id', array(
 ':status' => $newStatus,
 ':unsub_at' => $unsubAt,
 ':id' => (int) $id
 ));
 $this->audit('update', 'newsletter_subscriber', $id, $sub, ['new_status' => $newStatus]);
 Session::flash('success', 'تم تعديل حالة المشترك بنجاح.');
 }
 return $this->redirect('admin/newsletter?tab=subscribers');
 }

 public function deleteSubscriber($id)
 {
 $this->postGuard();
 $db = new Database();
 $sub = $db->fetch('SELECT * FROM newsletters WHERE id=:id', array(':id' => (int) $id));
 if ($sub) {
 $db->query('DELETE FROM newsletters WHERE id=:id', array(':id' => (int) $id));
 $this->audit('delete', 'newsletter_subscriber', $id, $sub, null);
 Session::flash('success', 'تم حذف المشترك من النشرة البريدية بنجاح.');
 }
 return $this->redirect('admin/newsletter?tab=subscribers');
 }

 public function saveSmtpSettings()
 {
 $this->postGuard();
 $db = new Database();
 $fields = array(
 'smtp_host' => $_POST['smtp_host'] ?? 'smtp.gmail.com',
 'smtp_port' => (string)($_POST['smtp_port'] ?? '587'),
 'smtp_username' => $_POST['smtp_username'] ?? 'your-email@gmail.com',
 'smtp_password' => $_POST['smtp_password'] ?? '',
  'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls',
  'brevo_api_key' => trim($_POST['brevo_api_key'] ?? ''),
  'mail_from_address' => $_POST['mail_from_address'] ?? 'news@yourdomain.com',
 'mail_from_name' => $_POST['mail_from_name'] ?? 'عصب التقنية',
 );

 foreach ($fields as $key => $val) {
 $db->query("
 INSERT INTO settings (`group`, `key`, `value`, value_type) 
 VALUES ('newsletter', :k, :v, 'string')
 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
 ", array(
 ':k' => $key,
 ':v' => (string) $val
 ));
 }

  Settings::clear();
  $this->audit('update', 'smtp_settings', null, null, array_diff_key($fields, array('smtp_password' => '', 'brevo_api_key' => '')));
  Session::flash('success', 'تم حفظ إعدادات البريد (SMTP / Brevo) بنجاح.');
 return $this->redirect('admin/newsletter?tab=smtp');
 }

 public function testSmtp()
 {
 $this->postGuard();
 $testEmail = trim($_POST['test_email'] ?? '');
 if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
 Session::flash('error', 'البريد الإلكتروني التجريبي غير صالح.');
 return $this->redirect('admin/newsletter?tab=smtp');
 }

 $siteName = Settings::get('site_name_ar', 'عصب التقنية');
 $subject = 'رسالة تجريبية لاختبار خادم SMTP | ' . $siteName;
 $body = '<div style="font-family:sans-serif;direction:rtl;text-align:right;padding:24px;background:#f8fafc;border-radius:12px;border:1px solid #e2e8f0">
 <h2 style="color:#0284c7;margin-top:0">تهانينا! الاتصال بخادم SMTP يعمل بنجاح</h2>
 <p style="font-size:15px;color:#334155;line-height:1.6">تم استلام هذه الرسالة بنجاح كرسالة اختبارية من ' . htmlspecialchars($siteName) . '.</p>
 <hr style="border:none;border-top:1px solid #e2e8f0;margin:20px 0">
<small style="color:#64748b">تاريخ ووقت الاختبار: ' . date('Y-m-d H:i:s') . '</small>
 </div>';

 $fromAddress = Settings::get('mail_from_address', MAIL_FROM_ADDRESS ?: 'no-reply@technews.local');
 if (Mailer::transport() === 'brevo' && !Mailer::brevoSenderValid()) {
 Session::flash('error', "فشل تنفيذ الاختبار: العنوان المرسل منه ($fromAddress) غير مسجّل في حساب Brevo. سجّله من لوحة Brevo (Senders) أو غيّر حقل From في تبويب SMTP إلى بريد مسجّل مثل kasperkey106@gmail.com.");
 return $this->redirect('admin/newsletter?tab=smtp');
 }

 if (Mailer::send($testEmail, $subject, $body)) {
  Session::flash('success', "تم إرسال البريد التجريبي بنجاح إلى: {$testEmail} ");
  } else {
  Session::flash('error', "فشل إرسال البريد التجريبي. تحقق من مفتاح Brevo API أو من صحة إعدادات SMTP (المضيف، المنفذ، المستخدم، كلمة المرور).");
  }

 return $this->redirect('admin/newsletter?tab=smtp');
 }
}
