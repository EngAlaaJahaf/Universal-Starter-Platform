<?php
if (!function_exists('admin_e')) {
 function admin_e($value)
 {
 return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
 }
}
$user = Auth::user();

// Current path helper for active sidebar item
$currentUri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');
$adminBase = trim(parse_url(app_url('admin'), PHP_URL_PATH) ?? 'admin', '/');

if (!function_exists('is_admin_active')) {
 function is_admin_active($path, $currentUri, $adminBase) {
 $target = trim(parse_url(app_url($path), PHP_URL_PATH) ?? $path, '/');
 if ($target === $adminBase) {
 return ($currentUri === $adminBase || $currentUri === $adminBase . '/dashboard') ? 'active' : '';
 }
 return str_starts_with($currentUri, $target) ? 'active' : '';
 }
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <title><?= admin_e($title ?? 'لوحة التحكم') ?> | <?= admin_e(Settings::get('site_name_ar', 'عصب التقنية')) ?></title>
 <?= function_exists('site_favicon_tag') ? site_favicon_tag() : '' ?>
 <!-- Bootstrap 5 RTL with Local Robust Fallback -->
 <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/admin-bootstrap.css'), ENT_QUOTES, 'UTF-8') ?>">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
 <!-- Bootstrap Icons -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
 <script>
 // Instant non-blocking initialization before paint (sidebar + theme)
 (function () {
 if (window.innerWidth>= 992 && localStorage.getItem('admin_sidebar_collapsed') === 'true') {
 document.documentElement.classList.add('sidebar-collapsed');
 }
 if (localStorage.getItem('admin_theme') === 'dark') {
 document.documentElement.classList.add('admin-dark');
 }
 })();
 </script>
 <style>
 :root {
 --bs-body-font-family: 'Readex Pro', sans-serif;
 /* Light theme tokens */
 --adm-bg: #f8fafc;
 --adm-topbar-bg: #ffffff;
 --adm-topbar-bd: #e2e8f0;
 --adm-content-bg: #f8fafc;
 --adm-text: #0f172a;
 --adm-text-mute: #64748b;
 --adm-card-bg: #ffffff;
 --adm-border: #e2e8f0;
 --adm-badge-bg: #f1f5f9;
 --adm-badge-bd: #e2e8f0;
 --adm-badge-cl: #334155;
 --adm-btn-bg: #ffffff;
 --adm-btn-cl: #475569;
 --adm-btn-bd: #e2e8f0;
 --adm-toggle-bg: rgba(0,0,0,0.06);
 }
 html.admin-dark {
 --adm-bg: #0d1117;
 --adm-topbar-bg: #161b22;
 --adm-topbar-bd: rgba(255,255,255,0.08);
 --adm-content-bg: #0d1117;
 --adm-text: #e2e8f0;
 --adm-text-mute: #94a3b8;
 --adm-card-bg: #1a2233;
 --adm-border: rgba(255,255,255,0.08);
 --adm-badge-bg: #1e293b;
 --adm-badge-bd: rgba(255,255,255,0.1);
 --adm-badge-cl: #94a3b8;
 --adm-btn-bg: #1e293b;
 --adm-btn-cl: #cbd5e1;
 --adm-btn-bd: rgba(255,255,255,0.1);
 --adm-toggle-bg: rgba(255,255,255,0.06);
 }
 body { 
 font-family: 'Readex Pro', sans-serif; 
 background-color: var(--adm-bg); 
 color: var(--adm-text); 
 margin: 0;
 overflow-x: hidden;
 transition: background-color 0.25s ease, color 0.25s ease;
 }
 .admin-shell { 
 display: flex; 
 min-height: 100vh; 
 width: 100%;
 }
 .admin-sidebar { 
 width: 270px; 
 min-width: 270px;
 flex-shrink: 0;
 background: #090d16; 
 color: #f8fafc; 
 border-left: 1px solid rgba(255,255,255,0.08); 
 display: flex; 
 flex-direction: column; 
 position: sticky;
 top: 0;
 height: 100vh;
 transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1), min-width 0.22s cubic-bezier(0.4, 0, 0.2, 1);
 z-index: 1000;
 }
 .sidebar-brand { 
 padding: 18px 16px; 
 border-bottom: 1px solid rgba(255,255,255,0.08); 
 display: flex; 
 align-items: center; 
 gap: 12px; 
 transition: all 0.2s ease;
 }
 .sidebar-brand .brand-logo-badge {
 width: 36px;
 height: 36px;
 background: linear-gradient(135deg,#00f2fe,#9d4edd);
 border-radius: 8px;
 display: grid;
 place-items: center;
 font-weight: 800;
 color: #fff;
 flex-shrink: 0;
 }
 .sidebar-brand .brand-text {
 overflow: hidden;
 white-space: nowrap;
 transition: opacity 0.2s ease;
 }
 .sidebar-nav { 
 padding: 14px 10px; 
 flex-grow: 1; 
 overflow-y: auto; 
 display: flex; 
 flex-direction: column; 
 gap: 4px; 
 scroll-behavior: auto !important;
 }
 .sidebar-nav::-webkit-scrollbar {
 width: 5px;
 }
 .sidebar-nav::-webkit-scrollbar-track {
 background: rgba(255,255,255,0.02);
 }
 .sidebar-nav::-webkit-scrollbar-thumb {
 background: rgba(255,255,255,0.15);
 border-radius: 4px;
 }
 .sidebar-nav::-webkit-scrollbar-thumb:hover {
 background: rgba(0, 242, 254, 0.4);
 }
 .nav-link-admin { 
 display: flex; 
 align-items: center; 
 gap: 12px; 
 padding: 10px 14px; 
 border-radius: 10px; 
 color: #94a3b8; 
 text-decoration: none; 
 font-size: 0.88rem; 
 font-weight: 500; 
 transition: all 0.18s ease-in-out; 
 position: relative;
 white-space: nowrap;
 }
 .nav-link-admin i {
 font-size: 1.15rem;
 flex-shrink: 0;
 transition: transform 0.18s ease-in-out;
 }
 .nav-link-admin .nav-text {
 overflow: hidden;
 text-overflow: ellipsis;
 white-space: nowrap;
 transition: opacity 0.15s ease;
 }
 .nav-link-admin:hover { 
 background: rgba(0, 242, 254, 0.08); 
 color: #ffffff; 
 transform: translateX(-2px);
 }
 .nav-link-admin:hover i {
 transform: scale(1.15);
 color: #00f2fe;
 }
 .nav-link-admin.active {
 background: linear-gradient(135deg, rgba(0, 242, 254, 0.18), rgba(157, 78, 221, 0.25)) !important;
 color: #00f2fe !important;
 font-weight: 700 !important;
 border-right: 4px solid #00f2fe !important;
 box-shadow: 0 4px 14px rgba(0, 242, 254, 0.12);
 }
 .nav-link-admin.active i {
 color: #00f2fe !important;
 filter: drop-shadow(0 0 6px rgba(0, 242, 254, 0.7));
 transform: scale(1.1);
 }

 /* Desktop Collapsed Sidebar States (ONLY>= 992px) */
 @media (min-width: 992px) {
 html.sidebar-collapsed .admin-sidebar,
 .sidebar-collapsed .admin-sidebar {
 width: 76px !important;
 min-width: 76px !important;
 }
 html.sidebar-collapsed .sidebar-brand,
 .sidebar-collapsed .sidebar-brand {
 justify-content: center;
 padding: 18px 0;
 }
 html.sidebar-collapsed .sidebar-brand .brand-text,
 .sidebar-collapsed .sidebar-brand .brand-text {
 display: none !important;
 }
 html.sidebar-collapsed .nav-link-admin,
 .sidebar-collapsed .nav-link-admin {
 justify-content: center;
 padding: 12px 0;
 gap: 0;
 }
 html.sidebar-collapsed .nav-link-admin .nav-text,
 .sidebar-collapsed .nav-link-admin .nav-text {
 display: none !important;
 }
 html.sidebar-collapsed .nav-link-admin i,
 .sidebar-collapsed .nav-link-admin i {
 font-size: 1.25rem;
 margin: 0;
 }
 html.sidebar-collapsed .sidebar-footer,
 .sidebar-collapsed .sidebar-footer {
 padding: 14px 6px !important;
 }
 html.sidebar-collapsed .sidebar-footer a,
 .sidebar-collapsed .sidebar-footer a {
 justify-content: center;
 padding: 10px 0;
 }
 html.sidebar-collapsed .sidebar-footer a span,
 .sidebar-collapsed .sidebar-footer a span {
 display: none !important;
 }
 }

 .admin-content-wrap { 
 flex: 1 1 0; 
 min-width: 0; 
 display: flex; 
 flex-direction: column; 
 background: var(--adm-content-bg);
 transition: background-color 0.25s ease;
 width: 100%;
 }
 .admin-topbar { 
 height: 60px; 
 background: var(--adm-topbar-bg); 
 border-bottom: 1px solid var(--adm-topbar-bd); 
 padding: 0 20px; 
 display: flex; 
 align-items: center; 
 justify-content: space-between; 
 position: sticky;
 top: 0;
 z-index: 100;
 transition: background-color 0.25s ease, border-color 0.25s ease;
 }
 .topbar-page-title {
 margin: 0;
 font-weight: 700;
 font-size: 1rem;
 color: var(--adm-text);
 white-space: nowrap;
 overflow: hidden;
 text-overflow: ellipsis;
 max-width: 260px;
 }
 .admin-main { 
 padding: 24px; 
 flex-grow: 1; 
 }

 /* Topbar Clean Action Buttons */
 .btn-topbar-action {
 height: 36px;
 padding: 0 14px;
 border-radius: 20px;
 display: inline-flex;
 align-items: center;
 gap: 6px;
 font-size: 0.84rem;
 font-weight: 600;
 text-decoration: none;
 border: 1px solid var(--adm-border);
 background: var(--adm-btn-bg);
 color: var(--adm-btn-cl);
 transition: all 0.15s ease;
 position: relative;
 white-space: nowrap;
 }
 .btn-topbar-action:hover {
 border-color: #00f2fe;
 color: #0284c7;
 transform: translateY(-1px);
 }
 html.admin-dark .btn-topbar-action:hover {
 color: #00f2fe;
 }
 .topbar-badge {
 background: #f43f5e;
 color: #ffffff;
 font-size: 0.7rem;
 font-weight: 700;
 padding: 2px 6px;
 border-radius: 10px;
 line-height: 1;
 }
 .topbar-user-badge {
 display: inline-flex;
 align-items: center;
 gap: 8px;
 padding: 4px 12px 4px 6px;
 background: var(--adm-badge-bg);
 border: 1px solid var(--adm-badge-bd);
 border-radius: 20px;
 color: var(--adm-text);
 text-decoration: none;
 font-size: 0.85rem;
 transition: all 0.15s ease;
 }
 .topbar-user-badge:hover {
 border-color: #00f2fe;
 color: #00f2fe;
 }
 .user-avatar-circle {
 width: 26px;
 height: 26px;
 border-radius: 50%;
 background: linear-gradient(135deg, #00f2fe, #38bdf8);
 color: #050d1a;
 font-weight: 800;
 font-size: 0.8rem;
 display: grid;
 place-items: center;
 }

 /* Theme Toggle Button */
 #themeToggleBtn {
 width: 36px;
 height: 36px;
 border-radius: 10px;
 border: 1px solid var(--adm-btn-bd);
 background: var(--adm-btn-bg);
 color: var(--adm-btn-cl);
 display: inline-flex;
 align-items: center;
 justify-content: center;
 cursor: pointer;
 font-size: 1rem;
 transition: all 0.2s ease;
 position: relative;
 overflow: hidden;
 }
 #themeToggleBtn:hover {
 transform: translateY(-1px);
 box-shadow: 0 4px 12px rgba(0,0,0,0.12);
 background: var(--adm-toggle-bg);
 }
 #themeToggleBtn .theme-icon {
 transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1), opacity 0.2s ease;
 position: absolute;
 }
 #themeToggleBtn .icon-sun { opacity: 1; transform: rotate(0deg) scale(1); }
 #themeToggleBtn .icon-moon { opacity: 0; transform: rotate(-90deg) scale(0.6); }
 html.admin-dark #themeToggleBtn .icon-sun { opacity: 0; transform: rotate(90deg) scale(0.6); }
 html.admin-dark #themeToggleBtn .icon-moon { opacity: 1; transform: rotate(0deg) scale(1); }

 /* Dark mode - content cards, tables, badges, inputs */
 html.admin-dark .card,
 html.admin-dark .modal-content {
 background-color: var(--adm-card-bg) !important;
 border-color: var(--adm-border) !important;
 color: var(--adm-text) !important;
 }
 html.admin-dark .card-header,
 html.admin-dark .card-footer {
 background-color: rgba(255,255,255,0.03) !important;
 border-color: var(--adm-border) !important;
 }
 html.admin-dark .table {
 --bs-table-bg: transparent;
 --bs-table-striped-bg: rgba(255,255,255,0.03);
 --bs-table-hover-bg: rgba(0,242,254,0.05);
 color: var(--adm-text) !important;
 border-color: var(--adm-border) !important;
 }
 html.admin-dark .table thead th {
 background: rgba(255,255,255,0.04) !important;
 color: var(--adm-text-mute) !important;
 border-color: var(--adm-border) !important;
 }
 html.admin-dark .table td,
 html.admin-dark .table th {
 border-color: var(--adm-border) !important;
 color: var(--adm-text) !important;
 }
 html.admin-dark .form-control,
 html.admin-dark .form-select {
 background-color: #0d1117 !important;
 border-color: rgba(255,255,255,0.12) !important;
 color: var(--adm-text) !important;
 }
 html.admin-dark .form-control:focus,
 html.admin-dark .form-select:focus {
 background-color: #0d1117 !important;
 border-color: #00f2fe !important;
 box-shadow: 0 0 0 3px rgba(0,242,254,0.15) !important;
 color: var(--adm-text) !important;
 }
 html.admin-dark .form-label,
 html.admin-dark label {
 color: var(--adm-text) !important;
 }
 html.admin-dark .badge.bg-light {
 background: var(--adm-badge-bg) !important;
 color: var(--adm-badge-cl) !important;
 border-color: var(--adm-badge-bd) !important;
 }
 html.admin-dark .btn-light,
 html.admin-dark .btn-outline-secondary {
 background: var(--adm-btn-bg) !important;
 color: var(--adm-btn-cl) !important;
 border-color: var(--adm-btn-bd) !important;
 }
 html.admin-dark .btn-action-icon {
 background: var(--adm-btn-bg) !important;
 color: var(--adm-btn-cl) !important;
 border-color: var(--adm-btn-bd) !important;
 }
 html.admin-dark .text-dark { color: var(--adm-text) !important; }
 html.admin-dark .text-muted { color: var(--adm-text-mute) !important; }
 html.admin-dark .bg-white { background-color: var(--adm-card-bg) !important; }
 html.admin-dark .bg-light { background-color: rgba(255,255,255,0.04) !important; }
 html.admin-dark .border { border-color: var(--adm-border) !important; }
 html.admin-dark .border-bottom { border-bottom-color: var(--adm-border) !important; }
 html.admin-dark .border-top { border-top-color: var(--adm-border) !important; }
 html.admin-dark .border-start { border-start-color: var(--adm-border) !important; }
 html.admin-dark .border-end { border-end-color: var(--adm-border) !important; }
 html.admin-dark .shadow-sm { box-shadow: 0 4px 12px rgba(0,0,0,0.4) !important; }
 html.admin-dark hr { border-color: var(--adm-border) !important; }

 /* List Groups in Dark Mode */
 html.admin-dark .list-group {
 --bs-list-group-bg: transparent;
 --bs-list-group-border-color: var(--adm-border);
 --bs-list-group-color: var(--adm-text);
 }
 html.admin-dark .list-group-item {
 background-color: transparent !important;
 color: var(--adm-text) !important;
 border-color: var(--adm-border) !important;
 }
 html.admin-dark .list-group-item strong {
 color: #f1f5f9 !important;
 }
 html.admin-dark .list-group-item code {
 background: rgba(0, 242, 254, 0.08) !important;
 color: #38bdf8 !important;
 padding: 2px 6px;
 border-radius: 4px;
 }
 html.admin-dark code {
 color: #38bdf8 !important;
 background-color: rgba(255,255,255,0.06) !important;
 }

 /* Subtle Badges in Dark Mode */
 html.admin-dark .bg-primary-subtle {
 background-color: rgba(14, 165, 233, 0.15) !important;
 color: #38bdf8 !important;
 }
 html.admin-dark .bg-success-subtle {
 background-color: rgba(16, 185, 129, 0.15) !important;
 color: #34d399 !important;
 }
 html.admin-dark .bg-warning-subtle {
 background-color: rgba(245, 158, 11, 0.15) !important;
 color: #fbbf24 !important;
 }
 html.admin-dark .bg-danger-subtle {
 background-color: rgba(244, 63, 94, 0.15) !important;
 color: #f87171 !important;
 }
 html.admin-dark .bg-info-subtle {
 background-color: rgba(6, 182, 212, 0.15) !important;
 color: #22d3ee !important;
 }

 /* Outline Buttons in Dark Mode */
 html.admin-dark .btn-outline-dark {
 color: #e2e8f0 !important;
 border-color: rgba(255,255,255,0.25) !important;
 background-color: transparent !important;
 }
 html.admin-dark .btn-outline-dark:hover {
 color: #00f2fe !important;
 border-color: #00f2fe !important;
 background-color: rgba(0,242,254,0.1) !important;
 }
 html.admin-dark .btn-outline-primary {
 color: #38bdf8 !important;
 border-color: rgba(56, 189, 248, 0.4) !important;
 }
 html.admin-dark .btn-outline-primary:hover {
 background-color: rgba(56, 189, 248, 0.15) !important;
 border-color: #38bdf8 !important;
 color: #fff !important;
 }
 html.admin-dark .btn-outline-warning {
 color: #fbbf24 !important;
 border-color: rgba(251, 191, 36, 0.4) !important;
 }
 html.admin-dark .btn-outline-warning:hover {
 background-color: rgba(251, 191, 36, 0.15) !important;
 border-color: #fbbf24 !important;
 color: #fff !important;
 }
 html.admin-dark .btn-outline-danger {
 color: #f87171 !important;
 border-color: rgba(248, 113, 113, 0.4) !important;
 }
 html.admin-dark .btn-outline-danger:hover {
 background-color: rgba(248, 113, 113, 0.15) !important;
 border-color: #f87171 !important;
 color: #fff !important;
 }
 html.admin-dark .btn-outline-success {
 color: #34d399 !important;
 border-color: rgba(16, 185, 129, 0.4) !important;
 }
 html.admin-dark .btn-outline-success:hover {
 background-color: rgba(16, 185, 129, 0.15) !important;
 border-color: #34d399 !important;
 color: #fff !important;
 }
html.admin-dark .btn-outline-info {
  color: #22d3ee !important;
  border-color: rgba(6, 182, 212, 0.4) !important;
  }

  /* OpenCode Zen free-fallback test button (works in light & dark) */
  .btn-test-oc {
  color: #7b2d8b !important;
  border-color: #9d4edd !important;
  background-color: transparent !important;
  }
  .btn-test-oc:hover {
  color: #ffffff !important;
  background-color: #9d4edd !important;
  border-color: #9d4edd !important;
  }
  html.admin-dark .btn-test-oc {
  color: #c9b8f5 !important;
  border-color: rgba(157, 78, 221, 0.6) !important;
  }
  html.admin-dark .btn-test-oc:hover {
  color: #ffffff !important;
  background-color: #9d4edd !important;
  border-color: #9d4edd !important;
  }

  /* AI conversation bubbles (ai_logs conversation view) */
  .chat-bubble {
  border-radius: 1rem;
  }
  .chat-bubble-user {
  max-width: 75%;
  background-color: #eef2ff;
  border-start-end-radius: 4px;
  border-end-end-radius: 4px;
  }
  .chat-bubble-user .chat-bubble-text {
  color: #0f172a;
  }
  .chat-bubble-ai {
  max-width: 80%;
  width: 100%;
  background-color: #f8f9fa;
  border-end-start-radius: 4px;
  border-start-start-radius: 4px;
  }
  .chat-bubble-ai .text-dark {
  color: #0f172a;
  white-space: pre-wrap;
  }
  html.admin-dark .chat-bubble-user {
  background-color: rgba(99, 102, 241, 0.18) !important;
  }
  html.admin-dark .chat-bubble-ai {
  background-color: rgba(255, 255, 255, 0.06) !important;
  }
  html.admin-dark .chat-bubble-user .chat-bubble-text,
  html.admin-dark .chat-bubble-ai .text-dark {
  color: var(--adm-text) !important;
  }
  html.admin-dark .chat-bubble-user .text-muted,
  html.admin-dark .chat-bubble-ai .text-muted {
  color: var(--adm-text-mute) !important;
  }
  html.admin-dark .chat-bubble-ai a {
  color: #7dd3fc !important;
  }
 html.admin-dark .btn-outline-info:hover {
 background-color: rgba(6, 182, 212, 0.15) !important;
 border-color: #22d3ee !important;
 color: #fff !important;
 }

 /* Table Light Overrides in Dark Mode */
 html.admin-dark .table-light,
 html.admin-dark thead.table-light,
 html.admin-dark thead.table-light th,
 html.admin-dark thead.table-light td,
 html.admin-dark tr.table-light,
 html.admin-dark td.table-light {
 --bs-table-bg: rgba(255,255,255,0.04) !important;
 --bs-table-color: var(--adm-text) !important;
 --bs-table-border-color: var(--adm-border) !important;
 background-color: rgba(255,255,255,0.04) !important;
 color: var(--adm-text) !important;
 border-color: var(--adm-border) !important;
 }

 /* Cards & Containers in Dark Mode */
 html.admin-dark .card {
 background-color: var(--adm-card-bg) !important;
 border-color: var(--adm-border) !important;
 color: var(--adm-text) !important;
 }
 html.admin-dark .card-header,
 html.admin-dark .card-footer {
 background-color: transparent !important;
 border-color: var(--adm-border) !important;
 }

 /* Dropdowns & Modals */
 html.admin-dark .dropdown-menu {
 background-color: #161f30 !important;
 border-color: rgba(255,255,255,0.12) !important;
 color: #f1f5f9 !important;
 box-shadow: 0 10px 25px rgba(0,0,0,0.5) !important;
 }
 html.admin-dark .dropdown-item {
 color: #cbd5e1 !important;
 }
 html.admin-dark .dropdown-item:hover,
 html.admin-dark .dropdown-item:focus {
 background-color: rgba(0,242,254,0.1) !important;
 color: #00f2fe !important;
 }
 html.admin-dark .dropdown-divider {
 border-color: rgba(255,255,255,0.08) !important;
 }
 html.admin-dark .modal-content,
 html.admin-dark .offcanvas {
 background-color: #161f30 !important;
 border: 1px solid var(--adm-border) !important;
 color: var(--adm-text) !important;
 box-shadow: 0 20px 40px rgba(0,0,0,0.6) !important;
 }
 html.admin-dark .modal-header,
 html.admin-dark .modal-footer,
 html.admin-dark .offcanvas-header {
 border-color: var(--adm-border) !important;
 }
 html.admin-dark .btn-close {
 filter: invert(1) grayscale(100%) brightness(200%);
 }

 /* Form Checks in Dark Mode */
 html.admin-dark .form-check-input {
 background-color: #0d1117 !important;
 border-color: rgba(255,255,255,0.2) !important;
 }
 html.admin-dark .form-check-input:checked {
 background-color: #00f2fe !important;
 border-color: #00f2fe !important;
 }
 html.admin-dark .form-check-label {
 color: var(--adm-text) !important;
 }

 /* Pagination in Dark Mode */
 html.admin-dark .page-link {
 background-color: #161f30 !important;
 border-color: rgba(255,255,255,0.1) !important;
 color: #cbd5e1 !important;
 }
 html.admin-dark .page-item.active .page-link {
 background-color: #00f2fe !important;
 color: #041122 !important;
 border-color: #00f2fe !important;
 font-weight: bold;
 }
 html.admin-dark .page-item.disabled .page-link {
 background-color: rgba(255,255,255,0.02) !important;
 color: #64748b !important;
 }

 html.admin-dark .alert-info {
 background: rgba(0,242,254,0.08) !important;
 border-color: rgba(0,242,254,0.2) !important;
 color: #67e8f9 !important;
 }
 html.admin-dark .alert-warning {
 background: rgba(251,191,36,0.08) !important;
 border-color: rgba(251,191,36,0.2) !important;
 color: #fde68a !important;
 }
 html.admin-dark .alert-success {
 background: rgba(16,185,129,0.08) !important;
 border-color: rgba(16,185,129,0.2) !important;
 color: #6ee7b7 !important;
 }
 html.admin-dark .alert-danger {
 background: rgba(244,63,94,0.08) !important;
 border-color: rgba(244,63,94,0.2) !important;
 color: #fca5a5 !important;
 }
 html.admin-dark .nav-tabs {
 border-color: var(--adm-border) !important;
 }
 html.admin-dark .nav-tabs .nav-link {
 color: var(--adm-text-mute) !important;
 }
 html.admin-dark .nav-tabs .nav-link.active {
 background: var(--adm-card-bg) !important;
 border-color: var(--adm-border) !important;
 color: var(--adm-text) !important;
 }
 html.admin-dark .input-group-text {
 background: rgba(255,255,255,0.05) !important;
 border-color: rgba(255,255,255,0.12) !important;
 color: var(--adm-text-mute) !important;
 }
 html.admin-dark .admin-topbar h5 { color: var(--adm-text) !important; }

 /* Unified Action Icon Buttons */
 .btn-action-icon {
 width: 32px;
 height: 32px;
 display: inline-flex;
 align-items: center;
 justify-content: center;
 border-radius: 8px;
 border: 1px solid #e2e8f0;
 background: #ffffff;
 color: #475569;
 transition: all 0.18s ease-in-out;
 text-decoration: none;
 padding: 0;
 font-size: 0.92rem;
 cursor: pointer;
 }
 .btn-action-icon:hover {
 transform: translateY(-1px);
 box-shadow: 0 4px 8px rgba(0,0,0,0.06);
 }
 .btn-action-edit:hover {
 background: rgba(14,165,233,0.1);
 color: #0284c7;
 border-color: #38bdf8;
 }
 .btn-action-view:hover {
 background: rgba(15,23,42,0.08);
 color: #0f172a;
 border-color: #64748b;
 }
 .btn-action-ban:hover {
 background: rgba(245,158,11,0.1);
 color: #d97706;
 border-color: #fbbf24;
 }
 .btn-action-delete:hover {
 background: rgba(244,63,94,0.1);
 color: #e11d48;
 border-color: #fb7185;
 }
 .btn-action-activate:hover {
 background: rgba(16,185,129,0.1);
 color: #059669;
 border-color: #34d399;
 }

 /* ============================================================
 Responsive Mobile & Tablet Admin Layout (< 992px)
 ============================================================ */
 .admin-sidebar-backdrop {
 position: fixed;
 inset: 0;
 background: rgba(0, 0, 0, 0.65);
 backdrop-filter: blur(4px);
 -webkit-backdrop-filter: blur(4px);
 z-index: 9998;
 display: none;
 opacity: 0;
 transition: opacity 0.25s ease;
 }
 .btn-sidebar-close-mobile {
 background: rgba(255,255,255,0.06);
 border: 1px solid rgba(255,255,255,0.15);
 color: #fff;
 width: 32px;
 height: 32px;
 border-radius: 8px;
 display: flex;
 align-items: center;
 justify-content: center;
 font-size: 1.1rem;
 cursor: pointer;
 margin-inline-start: auto;
 transition: background 0.15s ease, color 0.15s ease;
 }
 .btn-sidebar-close-mobile:hover {
 background: rgba(244,63,94,0.2);
 color: #f43f5e;
 border-color: #f43f5e;
 }

 @media (max-width: 991.98px) {
 .admin-shell {
 display: block !important;
 width: 100% !important;
 min-height: 100vh;
 position: relative;
 }
 .admin-sidebar {
 position: fixed !important;
 top: 0 !important;
 right: 0 !important;
 bottom: 0 !important;
 width: 280px !important;
 min-width: 280px !important;
 max-width: 85vw !important;
 height: 100vh !important;
 z-index: 99999 !important;
 transform: translateX(100%) !important;
 transition: transform 0.26s cubic-bezier(0.4, 0, 0.2, 1) !important;
 box-shadow: -10px 0 35px rgba(0, 0, 0, 0.6);
 }
 html.sidebar-mobile-open .admin-sidebar,
 .admin-shell.sidebar-mobile-open .admin-sidebar {
 transform: translateX(0) !important;
 }
 html.sidebar-mobile-open .admin-sidebar-backdrop,
 .admin-shell.sidebar-mobile-open .admin-sidebar-backdrop {
 display: block !important;
 opacity: 1 !important;
 }
 .admin-content-wrap {
 width: 100% !important;
 min-width: 100% !important;
 margin: 0 !important;
 }
 .admin-topbar {
 height: 58px !important;
 min-height: 58px !important;
 padding: 0 14px !important;
 display: flex !important;
 align-items: center !important;
 justify-content: space-between !important;
 flex-wrap: nowrap !important;
 }
 .admin-main {
 padding: 16px 12px !important;
 }
 .table-responsive {
 border-radius: 12px;
 box-shadow: 0 2px 8px rgba(0,0,0,0.04);
 }
 }

 @media (min-width: 992px) {
 .admin-sidebar-backdrop,
 .btn-sidebar-close-mobile {
 display: none !important;
 }
 }

 /* Floating Scroll Navigation (Scroll to Top / Bottom) */
 .floating-scroll-nav {
 position: fixed;
 bottom: 24px;
 inset-inline-end: 24px;
 display: flex;
 flex-direction: column;
 gap: 10px;
 z-index: 9990;
 pointer-events: none;
 }
 .floating-scroll-btn {
 width: 44px;
 height: 44px;
 border-radius: 50%;
 background: #ffffff;
 color: #0284c7;
 border: 1px solid #e2e8f0;
 box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
 display: flex;
 align-items: center;
 justify-content: center;
 cursor: pointer;
 pointer-events: auto;
 opacity: 0;
 visibility: hidden;
 transform: translateY(16px) scale(0.85);
 transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
 user-select: none;
 }
 html.admin-dark .floating-scroll-btn {
 background: #161f30;
 color: #00f2fe;
 border-color: rgba(255,255,255,0.12);
 box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45);
 }
 .floating-scroll-btn.visible {
 opacity: 1;
 visibility: visible;
 transform: translateY(0) scale(1);
 }
 .floating-scroll-btn:hover {
 background: linear-gradient(135deg, #00f2fe, #0ea5e9);
 color: #041122;
 border-color: #00f2fe;
 transform: translateY(-3px) scale(1.08);
 box-shadow: 0 12px 28px rgba(0, 242, 254, 0.35);
 }
 .floating-scroll-btn:active {
 transform: scale(0.95);
 }
 @media (max-width: 768px) {
 .floating-scroll-nav {
 bottom: 16px;
 inset-inline-end: 16px;
 gap: 8px;
 }
 .floating-scroll-btn {
 width: 40px;
 height: 40px;
 }
 }

 /* ============================================================
 Universal Table Sorter Engine — Styles
 ============================================================ */
 .table thead th.sortable {
 cursor: pointer;
 user-select: none;
 white-space: nowrap;
 position: relative;
 padding-left: 6px;
 padding-right: 22px !important;
 transition: background 0.15s ease, color 0.15s ease;
 }
 .table thead th.sortable::after {
 content: '';
 position: absolute;
 left: 6px;
 top: 50%;
 transform: translateY(-50%);
 width: 14px;
 height: 14px;
 background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2394a3b8'%3E%3Cpath d='M7 10l5-5 5 5H7zm0 4l5 5 5-5H7z'/%3E%3C/svg%3E");
 background-repeat: no-repeat;
 background-size: 14px;
 opacity: 0.5;
 transition: opacity 0.15s ease;
 }
 .table thead th.sortable:hover::after {
 opacity: 1;
 }
 .table thead th.sortable:hover {
 background: rgba(0,0,0,0.04);
 color: #0284c7;
 }
 .table thead th.sort-asc::after {
 background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%230284c7'%3E%3Cpath d='M7 14l5-5 5 5H7z'/%3E%3C/svg%3E");
 opacity: 1;
 }
 .table thead th.sort-desc::after {
 background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%230284c7'%3E%3Cpath d='M7 10l5 5 5-5H7z'/%3E%3C/svg%3E");
 opacity: 1;
 }
 .table thead th.sort-asc,
 .table thead th.sort-desc {
 background: rgba(2,132,199,0.07) !important;
 color: #0284c7 !important;
 font-weight: 700;
 }
 html.admin-dark .table thead th.sortable:hover {
 background: rgba(0,242,254,0.07) !important;
 color: #00f2fe !important;
 }
 html.admin-dark .table thead th.sort-asc,
 html.admin-dark .table thead th.sort-desc {
 background: rgba(0,242,254,0.08) !important;
 color: #00f2fe !important;
 }
 html.admin-dark .table thead th.sort-asc::after {
 background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2300f2fe'%3E%3Cpath d='M7 14l5-5 5 5H7z'/%3E%3C/svg%3E");
 }
 html.admin-dark .table thead th.sort-desc::after {
 background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2300f2fe'%3E%3Cpath d='M7 10l5 5 5-5H7z'/%3E%3C/svg%3E");
 }
 /* Sorted row highlight flash */
 @keyframes rowSortFlash {
 0% { background: rgba(2,132,199,0.10); }
 100% { background: transparent; }
 }
 .table tbody tr.sort-flash td {
 animation: rowSortFlash 0.45s ease forwards;
 }
 </style>
</head>
<body>

<!-- Mobile Sidebar Backdrop -->
<div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>

<div class="admin-shell">
 <!-- Sidebar -->
 <aside class="admin-sidebar">
 <div class="sidebar-brand">
 <?php $adminLogo = Settings::get('site_logo', ''); ?>
 <?php if (!empty($adminLogo)): ?>
 <img src="<?= admin_e(str_starts_with($adminLogo, 'http') ? $adminLogo : app_url($adminLogo)) ?>" alt="Logo" style="height:32px;max-width:38px;object-fit:contain;border-radius:6px;margin-inline-end:8px">
 <?php else: ?>
 <div class="brand-logo-badge">T</div>
 <?php endif; ?>
 <div class="brand-text">
 <strong style="font-size:1rem;display:block;line-height:1.2;color:#fff"><?= admin_e(Settings::get('site_name_ar', 'لوحة الإدارة')) ?></strong>
 <small style="font-size:0.7rem;color:#00f2fe"><?= admin_e(Settings::get('site_name_en', 'AsabTech')) ?></small>
 </div>
 <button type="button" class="btn-sidebar-close-mobile d-lg-none" id="sidebarCloseBtn" aria-label="إغلاق القائمة">&times;</button>
 </div>

 <nav class="sidebar-nav">
 <a class="nav-link-admin <?= is_admin_active('admin', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin')) ?>" title="الرئيسية (Dashboard)">
 <i class="bi bi-speedometer2"></i> <span class="nav-text">الرئيسية (Dashboard)</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/analytics', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/analytics')) ?>" title="التحليلات والإحصاءات">
 <i class="bi bi-graph-up-arrow"></i> <span class="nav-text">التحليلات والإحصاءات</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/articles', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/articles')) ?>" title="إدارة المقالات">
 <i class="bi bi-journal-richtext"></i> <span class="nav-text">إدارة المقالات</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/tutorials', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/tutorials')) ?>" title="استوديو الشروحات والدروس المصورة">
 <i class="bi bi-journal-code text-info"></i> <span class="nav-text">استوديو الشروحات المصورة</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/news-feeds', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/news-feeds')) ?>" title="استيراد ونشر الأخبار (RSS)">
 <i class="bi bi-lightning-charge-fill text-warning"></i> <span class="nav-text">استيراد ونشر الأخبار (RSS)</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/rss-sources', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/rss-sources')) ?>" title="مصادر الـ RSS (CRUD)">
 <i class="bi bi-rss-fill text-warning"></i> <span class="nav-text">مصادر الـ RSS (CRUD)</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/cron', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/cron')) ?>" title="النشر التلقائي (Cron Jobs)">
 <i class="bi bi-clock-history text-success"></i> <span class="nav-text">النشر التلقائي (Cron)</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/categories', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/categories')) ?>" title="التصنيفات">
 <i class="bi bi-tags"></i> <span class="nav-text">التصنيفات</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/classifier-rules', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/classifier-rules')) ?>" title="مصطلحات التصنيف الذكي">
 <i class="bi bi-diagram-3-fill text-primary"></i> <span class="nav-text">مصطلحات التصنيف الذكي</span>
 </a>
 <?php
 $unreadMessagesCount = 0;
 if (class_exists('Database')) {
 try {
 $unreadMessagesCount = (int) ((new Database())->fetch("SELECT COUNT(*) as cnt FROM contact_messages WHERE status = 'unread'")['cnt'] ?? 0);
 } catch (Throwable $e) {}
 }
 ?>
 <a class="nav-link-admin <?= is_admin_active('admin/comments', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/comments')) ?>" title="التعليقات والمراجعة">
 <i class="bi bi-chat-dots"></i> <span class="nav-text">التعليقات والمراجعة</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/messages', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/messages')) ?>" title="صندوق الرسائل والاتصالات الواردة">
 <i class="bi bi-inbox text-info"></i> <span class="nav-text">الرسائل والاتصالات</span>
 <?php if ($unreadMessagesCount> 0): ?>
 <span class="badge rounded-pill bg-danger ms-auto" style="font-size:0.7rem"><?= $unreadMessagesCount ?></span>
 <?php endif; ?>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/live-blog', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/live-blog')) ?>" title="التغطيات الحية (Live)">
 <i class="bi bi-broadcast text-danger"></i> <span class="nav-text">التغطيات الحية (Live)</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/polls', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/polls')) ?>" title="استطلاعات الرأي (Polls)">
 <i class="bi bi-bar-chart-line-fill text-warning"></i> <span class="nav-text">استطلاعات الرأي</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/users', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/users')) ?>" title="المستخدمون والصلاحيات">
 <i class="bi bi-people"></i> <span class="nav-text">المستخدمون والصلاحيات</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/settings', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/settings')) ?>" title="الإعدادات الشاملة (14)">
 <i class="bi bi-sliders2"></i> <span class="nav-text">الإعدادات الشاملة (14)</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/api-keys', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/api-keys')) ?>" title="نقاط النهاية والـ API (AI Endpoints)">
 <i class="bi bi-key-fill text-info"></i> <span class="nav-text">نقاط النهاية والـ API</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/translation-logs', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/translation-logs')) ?>" title="سجلات وأخطاء الترجمة (AI & Provider Logs)">
 <i class="bi bi-translate text-success"></i> <span class="nav-text">سجلات وأخطاء الترجمة</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/ai-logs', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/ai-logs')) ?>" title="سجلات محادثات المرشد وحصص الأعضاء">
 <i class="bi bi-robot text-primary"></i> <span class="nav-text">محادثات المرشد والحصص</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/media', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/media')) ?>" title="مكتبة الوسائط">
 <i class="bi bi-images"></i> <span class="nav-text">مكتبة الوسائط</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/pages', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/pages')) ?>" title="الصفحات الثابتة">
 <i class="bi bi-file-earmark-text"></i> <span class="nav-text">الصفحات الثابتة</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/menus', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/menus')) ?>" title="القوائم والروابط">
 <i class="bi bi-list-nested"></i> <span class="nav-text">القوائم والروابط</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/ads', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/ads')) ?>" title="المساحات الإعلانية">
 <i class="bi bi-badge-ad"></i> <span class="nav-text">المساحات الإعلانية</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/newsletter', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/newsletter')) ?>" title="النشرة البريدية">
 <i class="bi bi-envelope-paper"></i> <span class="nav-text">النشرة البريدية</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/activity-log', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/activity-log')) ?>" title="سجل العمليات (Audit Log)">
 <i class="bi bi-shield-check"></i> <span class="nav-text">سجل العمليات (Audit Log)</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/traffic-radar', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/traffic-radar')) ?>" title="رادار الزوار والعناكب (Bots)">
 <i class="bi bi-broadcast-pin text-info"></i> <span class="nav-text">رادار الزوار والعناكب</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/security-alerts', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/security-alerts')) ?>" title="تنبيهات الأمان والاختراق">
 <i class="bi bi-shield-exclamation text-danger"></i> <span class="nav-text">تنبيهات الأمان</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/backup', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/backup')) ?>" title="مركز النسخ الاحتياطي والاستيراد والتصدير">
 <i class="bi bi-database-down text-warning"></i> <span class="nav-text">النسخ الاحتياطي والاستيراد</span>
 </a>
 <a class="nav-link-admin <?= is_admin_active('admin/diagnostics', $currentUri, $adminBase) ?>" href="<?= admin_e(app_url('admin/diagnostics')) ?>" title="مركز التشخيص وفحص النظام الشامل" style="background:rgba(0,210,255,0.06);border-right:3px solid #00d2ff">
 <i class="bi bi-heart-pulse-fill" style="color:#00d2ff"></i> <span class="nav-text" style="color:#00d2ff;font-weight:700">مركز تشخيص النظام </span>
 </a>
 </nav>
 <script>
 (function() {
 try {
 var s = sessionStorage.getItem('admin_sidebar_scroll_top');
 if (s !== null) {
 var el = document.querySelector('.sidebar-nav');
 if (el) el.scrollTop = parseInt(s, 10);
 }
 } catch(e) {}
 })();
 </script>

 <div class="sidebar-footer" style="padding:14px 12px;border-top:1px solid rgba(255,255,255,0.08);display:flex;flex-direction:column;gap:4px">
 <a href="<?= admin_e(app_url()) ?>" target="_blank" class="nav-link-admin" style="color:#00f2fe" title="معاينة الموقع"><i class="bi bi-box-arrow-up-right"></i> <span class="nav-text">معاينة الموقع ↗</span></a>
 <a href="<?= admin_e(app_url('logout')) ?>" class="nav-link-admin" style="color:#f43f5e" title="تسجيل الخروج"><i class="bi bi-box-arrow-right"></i> <span class="nav-text">تسجيل الخروج</span></a>
 </div>
 </aside>

 <!-- Content Area -->
 <div class="admin-content-wrap">
 <header class="admin-topbar">
 <div class="d-flex align-items-center gap-2 overflow-hidden">
 <button id="sidebarToggle" type="button" class="btn btn-sm btn-light border rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" title="القائمة الجانبية">
 <i class="bi bi-layout-sidebar-inset fs-6 text-dark"></i>
 </button>
 <h5 class="topbar-page-title mb-0"><?= admin_e($title ?? 'لوحة الإدارة') ?></h5>
 </div>
 <div class="d-flex align-items-center gap-2 flex-shrink-0">
 <!-- Settings -->
 <a href="<?= admin_e(app_url('admin/settings')) ?>" class="btn-topbar-action d-none d-sm-inline-flex" title="إعدادات الموقع">
 <i class="bi bi-gear-wide-connected"></i> <span class="d-none d-xl-inline">الإعدادات</span>
 </a>

 <!-- Messages -->
 <a href="<?= admin_e(app_url('admin/messages')) ?>" class="btn-topbar-action" title="صندوق الرسائل الواردة">
 <i class="bi bi-inbox"></i> <span class="d-none d-xl-inline">الرسائل</span>
 <?php if ($unreadMessagesCount> 0): ?>
 <span class="topbar-badge"><?= $unreadMessagesCount ?></span>
 <?php endif; ?>
 </a>

 <!-- Live Site Preview -->
 <a href="<?= admin_e(app_url()) ?>" target="_blank" class="btn-topbar-action d-none d-md-inline-flex" title="زيارة الموقع">
 <i class="bi bi-box-arrow-up-right"></i> <span class="d-none d-xl-inline">الموقع</span>
 </a>

 <?php 
 $unresolvedSecAlerts = 0;
 if (class_exists('Database')) {
 try {
 $unresolvedSecAlerts = (int) ((new Database())->fetch("SELECT COUNT(*) as cnt FROM security_alerts WHERE is_resolved = 0")['cnt'] ?? 0);
 } catch (Throwable $e) {}
 }
 ?>
 <?php if ($unresolvedSecAlerts> 0): ?>
 <a href="<?= admin_e(app_url('admin/security-alerts')) ?>" class="btn-topbar-action text-danger border-danger border-opacity-25" title="تنبيهات الأمان">
 <i class="bi bi-shield-exclamation text-danger"></i>
 <span class="topbar-badge"><?= $unresolvedSecAlerts ?></span>
 </a>
 <?php endif; ?>

 <!-- Dark / Light Theme Toggle -->
 <button id="themeToggleBtn" type="button" title="تبديل الثيم (داكن / فاتح)" aria-label="تبديل الثيم">
 <i class="bi bi-sun-fill theme-icon icon-sun"></i>
 <i class="bi bi-moon-stars-fill theme-icon icon-moon"></i>
 </button>

 <!-- Profile -->
 <a href="<?= admin_e(app_url('admin/profile')) ?>" class="topbar-user-badge shadow-sm" title="الملف الشخصي">
 <span class="user-avatar-circle"><?= mb_substr($user ? $user['username'] : 'م', 0, 1) ?></span>
 <span class="d-none d-lg-inline fw-semibold"><?= admin_e($user ? $user['username'] : 'المدير') ?></span>
 </a>
 </div>
 </header>

 <main class="admin-main">
 <?= $content ?? '' ?>
 </main>
 </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
 // 1. Maintain and restore sidebar scroll position instantly without any jump or animation
 const sidebarNav = document.querySelector('.sidebar-nav');
 if (sidebarNav) {
 // Restore scroll position instantly
 const savedScroll = sessionStorage.getItem('admin_sidebar_scroll_top');
 if (savedScroll !== null) {
 sidebarNav.scrollTop = parseInt(savedScroll, 10);
 }

 // Save scroll position on scroll
 sidebarNav.addEventListener('scroll', function () {
 sessionStorage.setItem('admin_sidebar_scroll_top', sidebarNav.scrollTop);
 }, { passive: true });
 }

 // 2. Universal Active Tab & Pill State Memory across reloads & returns
 const pathKey = 'active_tab_' + window.location.pathname.replace(/[^a-zA-Z0-9]/g, '_');
 const hash = window.location.hash;
 const savedTabTarget = hash || sessionStorage.getItem(pathKey);

 if (savedTabTarget) {
 try {
 const triggerEl = document.querySelector(`[data-bs-target="${savedTabTarget}"], [href="${savedTabTarget}"], button#${savedTabTarget.replace('#','')}-tab`);
 if (triggerEl) {
 const tab = bootstrap.Tab.getOrCreateInstance(triggerEl);
 tab.show();
 }
 } catch (err) {
 console.debug('Tab restore skipped:', err);
 }
 }

 // Listen for tab switches and persist
 document.querySelectorAll('[data-bs-toggle="tab"], [data-bs-toggle="pill"]').forEach(function (tabButton) {
 tabButton.addEventListener('shown.bs.tab', function (event) {
 const target = event.target.getAttribute('data-bs-target') || event.target.getAttribute('href') || ('#' + event.target.id);
 if (target && target !== '#') {
 sessionStorage.setItem(pathKey, target);
 if (window.history && window.history.replaceState) {
 window.history.replaceState(null, null, target);
 }
 }
 });
 });

 // 3. Persistent Filter & View Mode Memory (Dropdowns, Pills, List vs Grid)
 document.querySelectorAll('[data-state-memory]').forEach(function (element) {
 const stateKey = 'ui_memory_' + window.location.pathname + '_' + (element.dataset.stateMemory || element.id || element.name);
 const savedVal = localStorage.getItem(stateKey);
 
 if (savedVal !== null && element.value === '') {
 element.value = savedVal;
 }

 element.addEventListener('change', function () {
 localStorage.setItem(stateKey, this.value);
 });
 });

 // 4. Sidebar Toggle (Responsive: Off-canvas Drawer on Mobile, Collapse on Desktop)
 const sidebarToggle = document.getElementById('sidebarToggle');
 const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
 const adminSidebarBackdrop = document.getElementById('adminSidebarBackdrop');
 const adminShell = document.querySelector('.admin-shell');

 // Sync desktop collapsed state from memory
 if (window.innerWidth>= 992 && document.documentElement.classList.contains('sidebar-collapsed') && adminShell) {
 adminShell.classList.add('sidebar-collapsed');
 }

 function closeMobileSidebar() {
 document.documentElement.classList.remove('sidebar-mobile-open');
 if (adminShell) adminShell.classList.remove('sidebar-mobile-open');
 }

 if (sidebarToggle) {
 sidebarToggle.addEventListener('click', function (e) {
 e.preventDefault();
 if (window.innerWidth < 992) {
 const isOpen = document.documentElement.classList.toggle('sidebar-mobile-open');
 if (adminShell) adminShell.classList.toggle('sidebar-mobile-open', isOpen);
 } else {
 const isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
 if (adminShell) adminShell.classList.toggle('sidebar-collapsed', isCollapsed);
 localStorage.setItem('admin_sidebar_collapsed', isCollapsed ? 'true' : 'false');
 }
 });
 }

 if (sidebarCloseBtn) {
 sidebarCloseBtn.addEventListener('click', function (e) {
 e.preventDefault();
 closeMobileSidebar();
 });
 }

 if (adminSidebarBackdrop) {
 adminSidebarBackdrop.addEventListener('click', function () {
 closeMobileSidebar();
 });
 }

 // Close mobile drawer on Escape key or link click
 document.addEventListener('keydown', function (e) {
 if (e.key === 'Escape') closeMobileSidebar();
 });

 document.querySelectorAll('.sidebar-nav .nav-link-admin').forEach(function (link) {
 link.addEventListener('click', function () {
 if (window.innerWidth < 992) closeMobileSidebar();
 });
 });

 // 5. Dark / Light Theme Toggle
 const themeToggleBtn = document.getElementById('themeToggleBtn');
 if (themeToggleBtn) {
 themeToggleBtn.addEventListener('click', function () {
 const isDark = document.documentElement.classList.toggle('admin-dark');
 localStorage.setItem('admin_theme', isDark ? 'dark' : 'light');
 });
 }

 // ============================================================
 // 6. Universal Table Sorter Engine
 // Automatically activates on every <table class="table">
 // Skip columns with class "no-sort" or the last "actions" column
 // ============================================================
 (function initTableSorter() {
 /**
 * Smart cell value extractor:
 * - Reads data-sort attribute if present (for custom sort keys)
 * - Falls back to innerText
 * - Detects numbers, dates, and text automatically
 */
 function getCellValue(td) {
 // Explicit sort key takes full priority
 if (td.dataset.sort !== undefined) return td.dataset.sort;
 // Strip badges/icons — get clean text
 const text = (td.innerText || td.textContent || '').trim();
 return text;
 }

 function parseValue(raw) {
 // Numeric
 const num = parseFloat(raw.replace(/[,،٬%]/g, ''));
 if (!isNaN(num) && raw.trim().length> 0) return { type: 'num', val: num };
 // Date-like: 2026-08-16 or 16/08/2026
 const dateTs = Date.parse(raw.replace(/\//g, '-'));
 if (!isNaN(dateTs)) return { type: 'date', val: dateTs };
 // Arabic/Text
 return { type: 'str', val: raw.toLowerCase() };
 }

 function compareValues(a, b) {
 const pa = parseValue(a), pb = parseValue(b);
 if (pa.type === pb.type) {
 if (pa.type === 'str') return pa.val.localeCompare(pb.val, 'ar');
 return pa.val - pb.val;
 }
 return a.localeCompare(b, 'ar');
 }

 function sortTable(table, colIdx, direction) {
 const tbody = table.querySelector('tbody');
 if (!tbody) return;

 const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.cells.length> 1);
 rows.sort(function (rowA, rowB) {
 const cellA = rowA.cells[colIdx];
 const cellB = rowB.cells[colIdx];
 if (!cellA || !cellB) return 0;
 const valA = getCellValue(cellA);
 const valB = getCellValue(cellB);
 const cmp = compareValues(valA, valB);
 return direction === 'asc' ? cmp : -cmp;
 });

 // Re-append sorted rows with flash animation
 rows.forEach(function (row) {
 row.classList.remove('sort-flash');
 tbody.appendChild(row);
 });
 // Trigger flash on next frame
 requestAnimationFrame(function () {
 rows.forEach(function (row) { row.classList.add('sort-flash'); });
 setTimeout(function () {
 rows.forEach(function (row) { row.classList.remove('sort-flash'); });
 }, 500);
 });
 }

 document.querySelectorAll('table.table').forEach(function (table) {
 const headers = table.querySelectorAll('thead th');
 if (!headers.length) return;

 const totalCols = headers.length;

 headers.forEach(function (th, colIdx) {
 // Skip: "no-sort" class, empty headers, or last column if it looks like "actions"
 const text = th.textContent.trim();
 if (
 th.classList.contains('no-sort') ||
 text === '' ||
 th.querySelector('input, button, select')
) return;

 // Last column is typically "actions" — skip it
 if (colIdx === totalCols - 1 && (text === 'الإجراءات' || th.classList.contains('text-end'))) return;

 th.classList.add('sortable');
 th.dataset.sortDir = '';

 th.addEventListener('click', function () {
 const current = th.dataset.sortDir;
 const newDir = current === 'asc' ? 'desc' : 'asc';

 // Reset all other headers in this table
 headers.forEach(function (h) {
 h.dataset.sortDir = '';
 h.classList.remove('sort-asc', 'sort-desc');
 });

 th.dataset.sortDir = newDir;
 th.classList.add(newDir === 'asc' ? 'sort-asc' : 'sort-desc');

 sortTable(table, colIdx, newDir);
 });
 });
 });
 })();

 // 7. Floating Smart Scroll Navigation (Top & Bottom)
 const btnAdminScrollTop = document.getElementById('btnAdminScrollTop');
 const btnAdminScrollBottom = document.getElementById('btnAdminScrollBottom');

 function updateAdminScrollButtons() {
 const scrollY = window.scrollY || document.documentElement.scrollTop;
 const scrollHeight = document.documentElement.scrollHeight;
 const clientHeight = window.innerHeight || document.documentElement.clientHeight;
 const maxScroll = scrollHeight - clientHeight;

 if (btnAdminScrollTop) {
 if (scrollY> 250) {
 btnAdminScrollTop.classList.add('visible');
 } else {
 btnAdminScrollTop.classList.remove('visible');
 }
 }

 if (btnAdminScrollBottom) {
 if (maxScroll> 350 && scrollY < (maxScroll - 200)) {
 btnAdminScrollBottom.classList.add('visible');
 } else {
 btnAdminScrollBottom.classList.remove('visible');
 }
 }
 }

 if (btnAdminScrollTop) {
 btnAdminScrollTop.addEventListener('click', function () {
 window.scrollTo({ top: 0, behavior: 'smooth' });
 });
 }

 if (btnAdminScrollBottom) {
 btnAdminScrollBottom.addEventListener('click', function () {
 window.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' });
 });
 }

 window.addEventListener('scroll', updateAdminScrollButtons, { passive: true });
 window.addEventListener('resize', updateAdminScrollButtons, { passive: true });
 setTimeout(updateAdminScrollButtons, 150);
});

// Global Modern Modal Helper for Admin Panel (Replaces native browser alert popups)
window.showAppModal = function(title, message, type = 'info', details = null, customActions = []) {
 let modalEl = document.getElementById('globalAppSystemModal');
 if (!modalEl) {
 console.warn(title, message);
 return;
 }
 const titleText = document.getElementById('globalAppSystemModalTitleText');
 const iconSpan = document.getElementById('globalAppSystemModalIcon');
 const messageP = document.getElementById('globalAppSystemModalMessage');
 const detailsWrap = document.getElementById('globalAppSystemModalDetailsWrap');
 const detailsText = document.getElementById('globalAppSystemModalDetailsText');
 const actionsDiv = document.getElementById('globalAppSystemModalCustomActions');

 if (titleText) titleText.textContent = title || 'إشعار';
 if (messageP) messageP.textContent = message || '';

 if (type === 'error') {
 if (iconSpan) iconSpan.textContent = '';
 if (titleText) titleText.className = 'text-danger fw-bold';
 } else if (type === 'success') {
 if (iconSpan) iconSpan.textContent = '';
 if (titleText) titleText.className = 'text-success fw-bold';
 } else if (type === 'warning') {
 if (iconSpan) iconSpan.textContent = '';
 if (titleText) titleText.className = 'text-warning fw-bold';
 } else {
 if (iconSpan) iconSpan.textContent = 'ℹ';
 if (titleText) titleText.className = 'text-primary fw-bold';
 }

 if (details && detailsWrap && detailsText) {
 detailsWrap.classList.remove('d-none');
 detailsText.textContent = typeof details === 'object' ? JSON.stringify(details, null, 2) : String(details);
 } else if (detailsWrap && detailsText) {
 detailsWrap.classList.add('d-none');
 detailsText.textContent = '';
 }

 if (actionsDiv) {
 actionsDiv.innerHTML = '';
 if (Array.isArray(customActions)) {
 customActions.forEach(act => {
 const b = document.createElement('a');
 b.className = act.class || 'btn btn-primary rounded-3';
 b.textContent = act.text;
 if (act.href) {
 b.href = act.href;
 if (act.target) b.target = act.target;
 }
 if (act.onClick) {
 b.addEventListener('click', act.onClick);
 }
 actionsDiv.appendChild(b);
 });
 }
 }

 if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
 const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
 modalInstance.show();
 }
};

// Safe Alert Override: Transform any window.alert() into a clean, modern Bootstrap modal
window.alert = function(msg) {
 window.showAppModal('تنبيه', String(msg), 'info');
};
</script>

<!-- Global Interactive Error & Notification Modal -->
<div class="modal fade" id="globalAppSystemModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered">
 <div class="modal-content rounded-4 border-0 shadow-lg" style="background:var(--adm-card-bg, #ffffff)">
 <div class="modal-header border-bottom py-3">
 <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="globalAppSystemModalTitle">
 <span id="globalAppSystemModalIcon"></span>
 <span id="globalAppSystemModalTitleText">تنبيه</span>
 </h5>
 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
 </div>
 <div class="modal-body p-4">
 <p class="mb-3 fs-6" id="globalAppSystemModalMessage" style="line-height:1.6;color:var(--adm-text, #1e293b)"></p>
 
 <!-- Expandable Technical Details -->
 <div id="globalAppSystemModalDetailsWrap" class="d-none mt-3">
 <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 w-100 justify-content-between mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#globalAppSystemModalDetailsCollapse">
 <span>تفاصيل الخطأ البرمجية (Technical Details)</span>
 <span>▼</span>
 </button>
 <div class="collapse" id="globalAppSystemModalDetailsCollapse">
 <pre class="p-3 bg-dark text-warning rounded-3 small font-monospace mb-0" id="globalAppSystemModalDetailsText" style="max-height:220px;overflow:auto;white-space:pre-wrap;word-break:break-all"></pre>
 </div>
 </div>
 </div>
 <div class="modal-footer border-top py-2 d-flex justify-content-between" style="background:var(--adm-bg, #f8fafc)">
 <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">إغلاق</button>
 <div id="globalAppSystemModalCustomActions" class="d-flex gap-2"></div>
 </div>
 </div>
 </div>
</div>

<!-- Floating Scroll Navigation -->
<div class="floating-scroll-nav" id="floatingScrollNavAdmin">
 <button type="button" class="floating-scroll-btn shadow-sm" id="btnAdminScrollTop" title="الرجوع إلى أعلى الصفحة" aria-label="أعلى الصفحة">
 <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg>
 </button>
 <button type="button" class="floating-scroll-btn shadow-sm" id="btnAdminScrollBottom" title="التمرير إلى أسفل الصفحة" aria-label="أسفل الصفحة">
 <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
 </button>
</div>

<!-- Password Visibility Toggle Script -->
<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId) || (btn ? btn.closest('.password-input-wrap')?.querySelector('input') : null);
    if (!input) return;
    const isPassword = input.getAttribute('type') === 'password';
    input.setAttribute('type', isPassword ? 'text' : 'password');
    const icon = btn.querySelector('i') || btn.querySelector('svg');
    if (icon) {
        if (icon.tagName.toLowerCase() === 'i') {
            icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        }
    }
}
window.togglePasswordVisibility = togglePasswordVisibility;
</script>

</body>
</html>
