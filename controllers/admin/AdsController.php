<?php
class AdsController extends AdminSimpleController
{
 protected $resource = 'ads';
 protected $table = 'ads';
 protected $title = 'الإعلانات';
 protected $fields = array(
 'name' => array('label' => 'اسم الإعلان أو الحملة', 'type' => 'text'),
 'placement' => array('label' => 'موضع العرض في الموقع', 'type' => 'select', 'options' => array(
 'header_top' => 'أعلى الهيدر (Header Top Banner)',
 'sidebar' => 'الشريط الجانبي (Sidebar Banner)',
 'between_articles' => 'بين المقالات في الرئيسية (In-Feed)',
 'article_inside' => 'داخل نص المقال (In-Article)',
 'footer' => 'أسفل الفوتر (Footer Banner)'
 )),
 'ad_type' => array('label' => 'نوع الإعلان', 'type' => 'select', 'options' => array(
 'image' => 'صورة وبنر مخصص (Custom Banner)',
 'html' => 'كود HTML / Google AdSense',
 'text' => 'نصي ورابط تسويقي (Text Link)'
 )),
 'image_path' => array('label' => 'مسار أو رابط الصورة', 'type' => 'text'),
 'target_url' => array('label' => 'رابط الوجهة (Target URL)', 'type' => 'text'),
 'html_code' => array('label' => 'كود الإعلان / AdSense Code', 'type' => 'textarea'),
 'start_at' => array('label' => 'تاريخ بدء العرض', 'type' => 'datetime-local'),
 'end_at' => array('label' => 'تاريخ انتهاء العرض', 'type' => 'datetime-local'),
 'status' => array('label' => 'حالة الإعلان', 'type' => 'select', 'options' => array(
 'active' => 'نشط ويظهر للزوار',
 'paused' => ' متوقف مؤقتاً',
 'draft' => 'مسودة'
 ))
 );
}
