<?php
class PushController extends Controller
{
    public function subscribe(){CSRF::verifyRequest();$data=json_decode(file_get_contents('php://input'),true)?:$_POST;$endpoint=trim($data['endpoint']??'');$keys=$data['keys']??array();if(!$endpoint||empty($keys['p256dh'])||empty($keys['auth']))return $this->json(array('ok'=>false),422);$db=new Database();$db->query('DELETE FROM push_subscriptions WHERE endpoint=:endpoint',array(':endpoint'=>$endpoint));$db->query('INSERT INTO push_subscriptions (user_id,endpoint,p256dh,auth,content_encoding) VALUES (:user_id,:endpoint,:p256dh,:auth,:content_encoding)',array(':user_id'=>Auth::user()['id']??null,':endpoint'=>$endpoint,':p256dh'=>$keys['p256dh'],':auth'=>$keys['auth'],':content_encoding'=>$data['encoding']??'aesgcm'));return $this->json(array('ok'=>true));}
    public function unsubscribe(){CSRF::verifyRequest();$data=json_decode(file_get_contents('php://input'),true)?:$_POST;(new Database())->query('DELETE FROM push_subscriptions WHERE endpoint=:endpoint',array(':endpoint'=>$data['endpoint']??''));return $this->json(array('ok'=>true));}
    private function json($data,$status=200){http_response_code($status);header('Content-Type: application/json; charset=utf-8');echo json_encode($data);exit;}
}
