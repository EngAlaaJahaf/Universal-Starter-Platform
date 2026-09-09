<?php
class PagesController extends AdminSimpleController
{
 protected $resource='pages'; protected $table='pages'; protected $title='الصفحات';
 protected $fields=array('title_ar'=>array('label'=>'العنوان بالعربية','type'=>'text'),'title_en'=>array('label'=>'العنوان بالإنجليزية','type'=>'text'),'slug'=>array('label'=>'Slug','type'=>'text'),'content_ar'=>array('label'=>'المحتوى بالعربية','type'=>'textarea'),'content_en'=>array('label'=>'المحتوى بالإنجليزية','type'=>'textarea'),'status'=>array('label'=>'الحالة','type'=>'select','options'=>array('published'=>'منشور','draft'=>'مسودة')));
}
