<?php

class ContactController extends Controller
{
    public function show()
    {
        $this->view('pages/contact');
    }

    public function send()
    {
        CSRF::verifyRequest();
        $data = Sanitizer::cleanArray($_POST);
        $user = Auth::user();

        if ($user) {
            if (empty($data['name'])) {
                $data['name'] = $user['username'] ?? $user['name'] ?? 'عضو مسجل';
            }
            if (empty($data['email'])) {
                $data['email'] = $user['email'];
            }
        }

        $validator = new Validator();
        if (!$validator->validate($data, [
            'name'    => 'required|min:2|max:150',
            'email'   => 'required|email|max:190',
            'subject' => 'required|min:3|max:255',
            'message' => 'required|min:5'
        ])) {
            Session::flash('error', 'يرجى التأكد من ملء جميع الحقول المطلوبة بشكل صحيح.');
            return $this->view('pages/contact', [
                'errors' => $validator->errors(),
                'old'    => $data
            ]);
        }

        $db = new Database();
        $db->query(
            'INSERT INTO contact_messages (name, email, subject, message, status, created_at) VALUES (:name, :email, :subject, :message, :status, NOW())',
            [
                ':name'    => $data['name'],
                ':email'   => $data['email'],
                ':subject' => $data['subject'],
                ':message' => $data['message'],
                ':status'  => 'unread'
            ]
        );

        Session::flash('success', 'تم إرسال رسالتك بنجاح! سيتواصل معك فريق التحرير قريباً. 🚀');
        return $this->redirect('contact');
    }
}
