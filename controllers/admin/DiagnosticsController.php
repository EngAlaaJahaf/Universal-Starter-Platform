<?php

class DiagnosticsController extends AdminController
{
 public function index()
 {
 require_once APP_ROOT . '/diagnostics_hub.php';
 exit;
 }

 public function seo()
 {
 require_once APP_ROOT . '/diagnostics_seo.php';
 exit;
 }

 public function security()
 {
 require_once APP_ROOT . '/diagnostics_security.php';
 exit;
 }

 public function database()
 {
 require_once APP_ROOT . '/diagnostics_database.php';
 exit;
 }

 public function media()
 {
 require_once APP_ROOT . '/diagnostics_media.php';
 exit;
 }

 public function health()
 {
 require_once APP_ROOT . '/health_check.php';
 exit;
 }
}
