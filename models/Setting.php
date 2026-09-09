<?php

class Setting extends Model
{
    public function get($key, $default = null)
    {
        $row = $this->db->fetch('SELECT value, value_type FROM settings WHERE `key`=:key LIMIT 1', array(':key'=>$key));
        if (!$row) return $default;
        if ($row['value_type']==='boolean') return filter_var($row['value'],FILTER_VALIDATE_BOOLEAN);
        if ($row['value_type']==='number') return $row['value']+0;
        if ($row['value_type']==='json') return json_decode($row['value'],true);
        return $row['value'];
    }
    public function set($key,$value)
    {
        $this->db->query('INSERT INTO settings (`group`,`key`,`value`) VALUES (\'general\',:key,:value) ON DUPLICATE KEY UPDATE value=VALUES(value)',array(':key'=>$key,':value'=>is_array($value)?json_encode($value):$value)); Settings::clear(); return true;
    }
    public function getGroup($group){return $this->db->fetchAll('SELECT * FROM settings WHERE `group`=:group ORDER BY sort_order',array(':group'=>$group));}
    public function getAll(){return $this->db->fetchAll('SELECT `key`,value FROM settings');}
    public function setMultiple(array $data){foreach($data as $key=>$value)$this->set($key,$value);return true;}
}
