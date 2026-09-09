<?php
class MenusController extends AdminSimpleController
{
 protected $resource = 'menus';
 protected $table = 'menus';
 protected $title = 'القوائم';
 protected $fields = array(
 'name' => array('label' => 'اسم القائمة', 'type' => 'text'),
 'location' => array('label' => 'موضع ظهور القائمة في الواجهة', 'type' => 'select', 'options' => array(
 'header_main' => 'القائمة العلوية الرئيسية (Header Navigation)',
 'footer_links' => 'روابط التذييل والفوتر (Footer Links)',
 'sidebar_nav' => 'القائمة الجانبية (Sidebar Menu)',
 'mobile_drawer' => 'قائمة الهواتف الذكية (Mobile Drawer)'
 )),
 'status' => array('label' => 'الحالة', 'type' => 'select', 'options' => array(
 'active' => 'نشطة ومعروضة',
 'inactive' => ' غير نشطة'
 ))
 );
 public function items($id){$this->guardAdmin();$db=new Database();$menu=$db->fetch('SELECT * FROM menus WHERE id=:id',array(':id'=>(int)$id));$items=$db->fetchAll('SELECT i.*,p.title_ar parent_title FROM menu_items i LEFT JOIN menu_items p ON p.id=i.parent_id WHERE i.menu_id=:menu_id ORDER BY i.sort_order,i.id',array(':menu_id'=>(int)$id));$this->view('admin/menus/items',array('menu'=>$menu,'items'=>$items));}
 public function itemStore($id){$this->postGuard();$d=Sanitizer::cleanArray($_POST);$db=new Database();$db->query('INSERT INTO menu_items (menu_id,parent_id,title_ar,title_en,item_type,target_id,url,sort_order,status) VALUES (:menu_id,:parent_id,:title_ar,:title_en,:item_type,:target_id,:url,:sort_order,:status)',array(':menu_id'=>(int)$id,':parent_id'=>(int)($d['parent_id']??0)?:null,':title_ar'=>$d['title_ar']??'',':title_en'=>$d['title_en']??null,':item_type'=>$d['item_type']??'custom',':target_id'=>(int)($d['target_id']??0)?:null,':url'=>$d['url']??null,':sort_order'=>(int)($d['sort_order']??0),':status'=>$d['status']??'active'));$this->audit('create','menu_item',$db->lastInsertId(),null,$d);return $this->redirect('admin/menus/items/'.$id);}
 public function itemDelete($id){$this->postGuard();$db=new Database();$old=$db->fetch('SELECT * FROM menu_items WHERE id=:id',array(':id'=>(int)$id));$menuId=$old['menu_id']??0;$db->query('DELETE FROM menu_items WHERE id=:id',array(':id'=>(int)$id));$this->audit('delete','menu_item',$id,$old);return $this->redirect('admin/menus/items/'.$menuId);}
}
