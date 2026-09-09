<?php

class Notification extends Model
{
    public function create($userId,$type,$title,$message,$link=null){$this->db->query('INSERT INTO notifications (user_id,type,title_ar,message_ar,link) VALUES (:user_id,:type,:title,:message,:link)',array(':user_id'=>(int)$userId,':type'=>$type,':title'=>$title,':message'=>$message,':link'=>$link));return $this->db->lastInsertId();}
    public function getByUser($userId,$limit=10){$limit=max(1,min(50,(int)$limit));return $this->db->fetchAll('SELECT * FROM notifications WHERE user_id=:user_id ORDER BY created_at DESC LIMIT '.$limit,array(':user_id'=>(int)$userId));}
    public function getUnreadCount($userId){$row=$this->db->fetch('SELECT COUNT(*) total FROM notifications WHERE user_id=:user_id AND is_read=0',array(':user_id'=>(int)$userId));return (int)($row['total']??0);}
    public function markAsRead($id){return $this->db->query('UPDATE notifications SET is_read=1 WHERE id=:id',array(':id'=>(int)$id))->rowCount();}
    public function markAllAsRead($userId){return $this->db->query('UPDATE notifications SET is_read=1 WHERE user_id=:user_id',array(':user_id'=>(int)$userId))->rowCount();}
    public function delete($id){return $this->db->query('DELETE FROM notifications WHERE id=:id',array(':id'=>(int)$id))->rowCount();}
    public function deleteOld($days){return $this->db->query('DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL '.max(1,(int)$days).' DAY)')->rowCount();}
}
