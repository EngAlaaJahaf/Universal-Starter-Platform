<?php

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::isLoggedIn()) {
            return $this->redirect(Auth::isAdmin() ? 'admin' : '');
        }
        $this->view('auth/login', ['errors' => [], 'old' => []]);
    }

    public function login()
    {
        CSRF::verifyRequest();
        $identity = trim($_POST['identity'] ?? ($_POST['email'] ?? ($_POST['username'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        
        $clientIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (strpos($clientIp, ',') !== false) {
            $clientIp = trim(explode(',', $clientIp)[0]);
        }
        $key = 'login_' . md5($clientIp . '_' . strtolower($identity));

        if (!RateLimiter::attempt($key, 10, 5)) {
            Session::flash('error', 'تم تجاوز عدد محاولات الدخول (10 محاولات). يرجى الانتظار 5 دقائق والمحاولة مجدداً.');
            return $this->redirect('login');
        }

        if ($identity === '' || $password === '' || !Auth::login($identity, $password)) {
            Session::flash('error', 'بيانات الدخول غير صحيحة (اسم المستخدم/البريد الإلكتروني أو كلمة المرور).');
            return $this->redirect('login');
        }

        RateLimiter::clear($key);
        Session::flash('success', 'تم تسجيل الدخول بنجاح. مرحباً بك!');
        
        if (Auth::isAdmin()) {
            return $this->redirect('admin');
        }
        return $this->redirect('');
    }

    public function showRegister()
    {
        if (Auth::isLoggedIn()) {
            return $this->redirect('');
        }
        if (Settings::get('allow_registration', '1') != '1') {
            Session::flash('error', 'عذراً، التسجيل مقفل حالياً من قبل إدارة المنصة.');
            return $this->redirect('login');
        }
        $this->view('auth/register', ['errors' => [], 'old' => []]);
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
        
        $valid = $validator->validate($data, [
            'username'              => 'required|min:3|max:60',
            'email'                 => 'required|email',
            'password'              => 'required|min:6',
            'password_confirmation' => 'required'
        ]);

        $errors = $validator->errors();

        if ($data['password'] !== ($data['password_confirmation'] ?? '')) {
            $valid = false;
            $errors['password'] = ['كلمتا المرور غير متطابقتين.'];
        }

        $db = new Database();
        $existing = $db->fetch(
            'SELECT id, username, email FROM users WHERE LOWER(email) = :email OR LOWER(username) = :username LIMIT 1', 
            [':email' => strtolower($data['email']), ':username' => strtolower($data['username'])]
        );

        if ($existing) {
            $valid = false;
            if (strtolower($existing['email']) === strtolower($data['email'])) {
                $errors['email'] = ['البريد الإلكتروني مسجل مسبقاً.'];
            }
            if (strtolower($existing['username']) === strtolower($data['username'])) {
                $errors['username'] = ['اسم المستخدم محجوز مسبقاً، يرجى اختيار اسم آخر.'];
            }
        }

        if (!$valid) {
            return $this->view('auth/register', ['errors' => $errors, 'old' => $data]);
        }

        $user = Auth::register($data);
        if ($user) {
            Session::flash('success', 'تم إنشاء حسابك وتسجيل الدخول تلقائياً بنجاح! أهلاً بك.');
            return $this->redirect('');
        }

        Session::flash('error', 'تعذر إنشاء الحساب، يرجى مراجعة البيانات والمحاولة مجدداً.');
        return $this->redirect('register');
    }

    public function logout()
    {
        Auth::logout();
        Session::flash('success', 'تم تسجيل الخروج بنجاح.');
        return $this->redirect('');
    }

    public function showForgotPassword()
    {
        $this->view('auth/forgot_password', ['errors' => []]);
    }

    public function forgotPassword()
    {
        CSRF::verifyRequest();
        $email = strtolower(trim($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->view('auth/forgot_password', ['errors' => ['email' => ['البريد الإلكتروني غير صحيح.']]]);
        }

        $db = new Database();
        $user = $db->fetch('SELECT id, username FROM users WHERE email = :email LIMIT 1', [':email' => $email]);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $db->query('DELETE FROM password_resets WHERE email = :email', [':email' => $email]);
            $db->query(
                'INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, DATE_ADD(NOW(), INTERVAL 2 HOUR))', 
                [':email' => $email, ':token' => password_hash($token, PASSWORD_DEFAULT)]
            );
            
            $resetLink = app_url('reset-password/' . $token);
            $siteName  = Settings::get('site_name_ar', 'منصتي الذكية');
            $username  = htmlspecialchars($user['username'] ?? 'عضو المنصة');

            $subject = '🔐 رابط إعادة تعيين كلمة المرور | ' . $siteName;
            $htmlBody = "
            <div dir='rtl' style='font-family:Tahoma,sans-serif;text-align:right;background:#0b1120;padding:30px;color:#f8fafc'>
                <div style='max-width:500px;margin:0 auto;background:#1e293b;border-radius:16px;padding:30px'>
                    <h3 style='color:#00f2fe;margin-top:0'>{$siteName}</h3>
                    <p>مرحباً <strong>{$username}</strong>،</p>
                    <p>تلقينا طلباً لإعادة تعيين كلمة المرور لحسابك. انقر على الزر التالي للمتابعة:</p>
                    <div style='text-align:center;margin:25px 0'>
                        <a href='{$resetLink}' style='background:#00f2fe;color:#090d16;padding:12px 28px;text-decoration:none;border-radius:8px;font-weight:bold;display:inline-block'>تعيين كلمة مرور جديدة</a>
                    </div>
                    <p style='color:#94a3b8;font-size:0.85rem'>الرابط صالح لمدة ساعتين فقط.</p>
                </div>
            </div>";

            if (class_exists('Mailer')) {
                Mailer::sendHtml($email, $subject, $htmlBody);
            }
        }

        Session::flash('success', 'إذا كان هذا البريد مسجلاً لدينا، فقد تم إرسال رابط استعادة كلمة المرور إليه.');
        return $this->redirect('forgot-password');
    }

    public function showResetPassword($token)
    {
        $this->view('auth/reset_password', ['token' => $token, 'errors' => []]);
    }

    public function resetPassword()
    {
        CSRF::verifyRequest();
        $token = trim($_POST['token'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirmation = (string)($_POST['password_confirmation'] ?? '');

        if (strlen($password) < 6) {
            return $this->view('auth/reset_password', ['token' => $token, 'errors' => ['password' => ['كلمة المرور يجب أن لا تقل عن 6 أحرف.']]]);
        }
        if ($password !== $passwordConfirmation) {
            return $this->view('auth/reset_password', ['token' => $token, 'errors' => ['password' => ['كلمتا المرور غير متطابقتين.']]]);
        }

        $db = new Database();
        $rows = $db->fetchAll('SELECT * FROM password_resets WHERE expires_at > NOW()');
        $matchedReset = null;

        foreach ($rows as $r) {
            if (password_verify($token, $r['token'])) {
                $matchedReset = $r;
                break;
            }
        }

        if (!$matchedReset) {
            Session::flash('error', 'رابط إعادة التعيين منتهي الصلاحية أو غير صالح.');
            return $this->redirect('forgot-password');
        }

        $db->query('UPDATE users SET password = :password WHERE email = :email', [
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':email'    => $matchedReset['email']
        ]);
        $db->query('DELETE FROM password_resets WHERE email = :email', [':email' => $matchedReset['email']]);

        Session::flash('success', 'تم تعيين كلمة المرور الجديدة بنجاح. يمكنك تسجيل الدخول الآن.');
        return $this->redirect('login');
    }
}
