<?php

class Newsletter extends Model
{
    public function subscribe($email,$name=''){$this->db->query('INSERT INTO newsletters (email,name,status) VALUES (:email,:name,\'active\') ON DUPLICATE KEY UPDATE name=VALUES(name),status=\'active\',unsubscribed_at=NULL',array(':email'=>strtolower(trim($email)),':name'=>$name));return true;}
    public function unsubscribe($email){return $this->db->query('UPDATE newsletters SET status=\'unsubscribed\',unsubscribed_at=CURRENT_TIMESTAMP WHERE email=:email',array(':email'=>strtolower(trim($email))))->rowCount();}
    public function getActive(){return $this->db->fetchAll("SELECT * FROM newsletters WHERE status='active' ORDER BY subscribed_at DESC");}
    public function getCount(){$row=$this->db->fetch("SELECT COUNT(*) total FROM newsletters WHERE status='active'");return (int)($row['total']??0);}
}
