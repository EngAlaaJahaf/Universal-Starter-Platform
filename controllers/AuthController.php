<?php

class AuthController extends Controller
{
    public function showLogin()
    {
        $this->view('auth/login', array('errors' => array(), 'old' => array()));
    }

    public function login()
    {
        CSRF::verifyRequest();
        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        
        $clientIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (strpos($clientIp, ',') !== false) {
            $clientIp = trim(explode(',', $clientIp)[0]);
        }
        $key = 'login_' . md5($clientIp . '_' . strtolower($email));

        if (!RateLimiter::attempt($key, 10, 5)) {
            Session::flash('error', 'تم تجاوز عدد محاولات الدخول (10 محاولات). يرجى الانتظار 5 دقائق والمحاولة مجدداً.');
            return $this->redirect('login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || !Auth::login($email, $password)) {
            Session::flash('error', 'بيانات الدخول غير صحيحة.');
            return $this->redirect('login');
        }

        RateLimiter::clear($key);
        Session::flash('success', 'تم تسجيل الدخول بنجاح.');
        return $this->redirect('');
    }

    public function showRegister()
    {
        if (Settings::get('allow_registration', '1') != '1') {
            Session::flash('error', 'عذراً، التسجيل مقفل حالياً من قبل إدارة المنصة.');
            return $this->redirect('login');
        }
        $this->view('auth/register', array('errors' => array(), 'old' => array()));
    }

    public function register()
    {
        if (Settings::get('allow_registration', '1') != '1') {
            Session::flash('error', 'عذراً، التسجيل مقفل حالياً من قبل إدارة المنصة.');
            return $this->redirect('login');
        }

        CSRF::verifyRequest();
        $validator = new Validator();
        $data = Sanitizer::cleanArray($_POST);
        $valid = $validator->validate($data, array('username' => 'required|min:3|max:100', 'email' => 'required|email', 'password' => 'required|min:6', 'password_confirmation' => 'required'));
        if ($data['password'] !== ($data['password_confirmation'] ?? '')) {
            $valid = false;
            $errors = array('password' => array('كلمتا المرور غير متطابقتين.'));
        } else {
            $errors = $validator->errors();
        }

        $db = new Database();
        if ($db->fetch('SELECT id FROM users WHERE email = :email OR username = :username LIMIT 1', array(':email' => $data['email'], ':username' => $data['username']))) {
            $valid = false;
            $errors['email'][] = 'البريد أو اسم المستخدم مستخدم مسبقاً.';
        }
        if (!$valid) return $this->view('auth/register', array('errors' => $errors, 'old' => $data));

        Auth::register($data);
        Session::flash('success', 'تم إنشاء الحساب. يمكنك تسجيل الدخول الآن.');
        return $this->redirect('login');
    }

    public function logout()
    {
        Auth::logout();
        return $this->redirect('');
    }

    public function showForgotPassword()
    {
        $this->view('auth/forgot_password', array('errors' => array()));
    }

    public function forgotPassword()
    {
        CSRF::verifyRequest();
        $email = strtolower(trim($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->view('auth/forgot_password', array('errors' => array('email' => array('البريد غير صحيح.'))));
        }
        $db = new Database();
        $user = $db->fetch('SELECT id, username FROM users WHERE email = :email LIMIT 1', array(':email' => $email));
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $db->query('DELETE FROM password_resets WHERE email = :email', array(':email' => $email));
            $db->query('INSERT INTO password_resets (email, token) VALUES (:email, :token)', array(':email' => $email, ':token' => password_hash($token, PASSWORD_DEFAULT)));
            
            $resetLink = app_url('reset-password/' . $token);
            $siteName  = Settings::get('site_name_ar', 'عصب التقنية');
            $username  = htmlspecialchars($user['username'] ?? 'عضو المنصة');

            $subject = '🔐 رابط إعادة تعيين كلمة المرور | ' . $siteName;
            $htmlBody = '
            <div dir="rtl" style="font-family:Segoe UI,Tahoma,sans-serif;text-align:right;background:#0b1120;padding:36px 20px;color:#f8fafc">
                <div style="max-width:540px;margin:0 auto;background:#111c35;border:1px solid rgba(0,210,255,0.25);border-radius:20px;padding:36px 30px;box-shadow:0 15px 35px rgba(0,0,0,0.5)">
                    <div style="text-align:center;margin-bottom:24px">
                        <h2 style="color:#00d2ff;margin:0 0 8px;font-size:1.5rem">' . htmlspecialchars($siteName) . '</h2>
                        <span style="display:inline-block;padding:4px 12px;background:rgba(0,210,255,0.1);color:#00d2ff;border-radius:20px;font-size:0.8rem;font-weight:bold">أمان الحسابات</span>
                    </div>
                    <p style="font-size:1rem;color:#f8fafc;line-height:1.7;margin-bottom:16px">
                        مرحباً <strong>' . $username . '</strong>،
                    </p>
                    <p style="font-size:0.95rem;color:#94a3b8;line-height:1.7;margin-bottom:28px">
                        تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك. يمكنك تعيين كلمة مرور جديدة ومباشرة بالضغط على الزر التالي:
                    </p>
                    <div style="text-align:center;margin-bottom:32px">
                        <a href="' . htmlspecialchars($resetLink) . '" style="display:inline-block;background:linear-gradient(135deg,#00d2ff,#2563eb);color:#ffffff;text-decoration:none;font-weight:bold;font-size:1rem;padding:14px 32px;border-radius:12px;box-shadow:0 4px 15px rgba(0,210,255,0.3)">
                            🔑 إعادة تعيين كلمة المرور الآن
                        </a>
                    </div>
                    <div style="background:rgba(0,0,0,0.3);border:1px dashed rgba(255,255,255,0.1);border-radius:10px;padding:12px 16px;margin-bottom:24px;font-size:0.82rem;color:#94a3b8;word-break:break-all">
                        <strong>إذا لم يعمل الزر معك، انسخ الرابط المباشر:</strong><br>
                        <a href="' . htmlspecialchars($resetLink) . '" style="color:#00d2ff">' . htmlspecialchars($resetLink) . '</a>
                    </div>
                    <p style="font-size:0.82rem;color:#64748b;line-height:1.6;margin:0;border-top:1px solid rgba(255,255,255,0.08);padding-top:18px">
                        ⚠️ <strong>تنبيه أمني:</strong> هذا الرابط صالح لمدة <strong>60 دقيقة فقط</strong>. إذا لم تكن أنت من طلب إعادة التعيين، فيمكنك تجاهل هذه الرسالة بأمان وستظل كلمة المرور الحالية دون أي تغيير.
                    </p>
                </div>
            </div>';

            // Send actual email via SMTP
            Mailer::send($email, $subject, $htmlBody);
            
            // Keep flash for local simulation/convenience
            Session::flash('reset_link', $resetLink);
        }
        Session::flash('success', 'إذا كان البريد مسجلاً لدينا، فقد تم إرسال رابط إعادة تعيين كلمة المرور إلى صندوق الوارد.');
        return $this->redirect('forgot-password');
    }

    public function showResetPassword($token)
    {
        $this->view('auth/reset_password', array('token' => $token, 'errors' => array()));
    }

    public function resetPassword()
    {
        CSRF::verifyRequest();
        $token = $_POST['token'] ?? '';
        $password = (string) ($_POST['password'] ?? '');
        $db = new Database();
        $resets = $db->fetchAll('SELECT * FROM password_resets WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 MINUTE)');
        $reset = null;
        foreach ($resets as $candidate) if (password_verify($token, $candidate['token'])) { $reset = $candidate; break; }
        if (!$reset || strlen($password) < 6) return $this->view('auth/reset_password', array('token' => $token, 'errors' => array('password' => array('الرابط منتهي أو كلمة المرور قصيرة.'))));
        $db->query('UPDATE users SET password_hash = :password_hash WHERE email = :email', array(':password_hash' => password_hash($password, PASSWORD_DEFAULT), ':email' => $reset['email']));
        $db->query('DELETE FROM password_resets WHERE id = :id', array(':id' => $reset['id']));
        Session::flash('success', 'تم تحديث كلمة المرور.');
        return $this->redirect('login');
    }
}
