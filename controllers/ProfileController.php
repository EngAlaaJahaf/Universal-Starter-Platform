<?php

class ProfileController extends Controller
{
    public function show() { Auth::requireLogin(); $this->view('profile/show', array('user'=>Auth::user())); }
    public function edit() { Auth::requireLogin(); $this->view('profile/edit', array('user'=>Auth::user())); }
    public function update()
    {
        Auth::requireLogin(); CSRF::verifyRequest(); $data=Sanitizer::cleanArray($_POST); $user=Auth::user();
        $db=new Database(); $db->query('UPDATE users SET username=:username,bio=:bio,preferred_language=:preferred_language,theme_preference=:theme_preference WHERE id=:id', array(':username'=>$data['username']??$user['username'],':bio'=>$data['bio']??'',':preferred_language'=>$data['preferred_language']??'ar',':theme_preference'=>$data['theme_preference']??'auto',':id'=>$user['id']));
        Session::flash('success','تم تحديث الملف الشخصي.'); return $this->redirect('profile');
    }
    public function changePassword()
    {
        Auth::requireLogin(); CSRF::verifyRequest(); $user=Auth::user(); $old=$_POST['current_password']??''; $new=$_POST['password']??'';
        if(!password_verify($old,$user['password_hash'])||strlen($new)<6){Session::flash('error','كلمة المرور الحالية غير صحيحة أو الجديدة قصيرة.');return $this->redirect('profile/edit');}
        (new Database())->query('UPDATE users SET password_hash=:hash WHERE id=:id',array(':hash'=>password_hash($new,PASSWORD_DEFAULT),':id'=>$user['id'])); Session::flash('success','تم تغيير كلمة المرور.'); return $this->redirect('profile');
    }
}
