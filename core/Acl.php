<?php

/**
 * ACL registry (SH-11): canonical, pure permission matrix plus a deny-audit
 * helper. No DB/IO in the matrix so it is unit-testable and role resolution
 * stays a single source of truth for the rest of the app.
 *
 * Super-admin role always passes; every other role must appear in the matrix.
 * Unknown permissions fail closed (admin only), which surfaces typos early.
 */
class Acl
{
    const SUPER_ROLE = 'admin';

    /** Permission key => Arabic label (used by `craft acl:matrix`). */
    public static function permissions()
    {
        return array(
            'dashboard.view'    => 'لوحة التحكم',
            'content.manage'    => 'إدارة المحتوى (مقالات/تصنيفات)',
            'content.publish'   => 'نشر المحتوى',
            'media.manage'      => 'إدارة الملفات',
            'comments.moderate' => 'إدارة التعليقات',
            'users.manage'      => 'إدارة المستخدمين',
            'settings.manage'   => 'الإعدادات العامة',
            'system.cron'       => 'التحكم بالمهام المجدولة',
            'system.security'   => 'أمان النظام',
            'analytics.view'    => 'عرض التحليلات',
            'seo.manage'        => 'إدارة SEO',
            'backup.manage'     => 'النسخ الاحتياطي',
            'api_keys.manage'   => 'إدارة مفاتيح API'
        );
    }

    /** Roles granted a permission by default (admin is implicit). */
    public static function defaultRoles($permission)
    {
        $map = array(
            'dashboard.view'    => array('admin', 'editor'),
            'content.manage'    => array('admin', 'editor'),
            'content.publish'   => array('admin'),
            'media.manage'      => array('admin', 'editor'),
            'comments.moderate' => array('admin', 'moderator', 'editor'),
            'users.manage'      => array('admin'),
            'settings.manage'   => array('admin'),
            'system.cron'       => array('admin'),
            'system.security'   => array('admin'),
            'analytics.view'    => array('admin', 'editor'),
            'seo.manage'        => array('admin'),
            'backup.manage'     => array('admin'),
            'api_keys.manage'   => array('admin')
        );
        return array_values($map[$permission] ?? array());
    }

    /**
     * Pure check: is $role allowed $permission?
     * Single `===` against SUPER_ROLE short-circuits; any DB-side JSON grants
     * are layered on top by Auth::can() (admin override + per-user grants).
     */
    public static function allows($role, $permission)
    {
        if ($permission === null || $permission === '') {
            return false;
        }
        $role = is_string($role) ? strtolower(trim($role)) : '';
        if ($role === self::SUPER_ROLE) {
            return true;
        }
        return in_array($role, self::defaultRoles($permission), true);
    }
}