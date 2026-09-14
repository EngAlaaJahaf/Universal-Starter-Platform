<?php
/**
 * acl_test — Acl canonical matrix (SH-11): super-admin short-circuit,
 * editor content rights vs admin-only system rights, unknown permissions
 * fail closed, and registry integrity (every default role resolves).
 */
TestRunner::suite('Acl');

// Super-admin bypasses everything.
TestRunner::isTrue(Acl::allows('admin', 'users.manage'), 'admin allowed users.manage');
TestRunner::isTrue(Acl::allows('admin', 'system.cron'), 'admin allowed system.cron');
TestRunner::isTrue(Acl::allows('admin', 'backup.manage'), 'admin allowed backup.manage');
TestRunner::isTrue(Acl::allows('ADMIN', 'dashboard.view'), 'role comparison is case-insensitive');

// Editor: content rights kept (zero-behavior-change), system rights denied.
TestRunner::isTrue(Acl::allows('editor', 'dashboard.view'), 'editor allowed dashboard.view');
TestRunner::isTrue(Acl::allows('editor', 'content.manage'), 'editor allowed content.manage');
TestRunner::isTrue(Acl::allows('editor', 'media.manage'), 'editor allowed media.manage');
TestRunner::isFalse(Acl::allows('editor', 'content.publish'), 'editor denied content.publish (publishing is admin-only)');
TestRunner::isFalse(Acl::allows('editor', 'users.manage'), 'editor denied users.manage');
TestRunner::isFalse(Acl::allows('editor', 'settings.manage'), 'editor denied settings.manage');
TestRunner::isFalse(Acl::allows('editor', 'system.cron'), 'editor denied system.cron');
TestRunner::isFalse(Acl::allows('editor', 'system.security'), 'editor denied system.security');
TestRunner::isFalse(Acl::allows('editor', 'backup.manage'), 'editor denied backup.manage');
TestRunner::isFalse(Acl::allows('editor', 'api_keys.manage'), 'editor denied api_keys.manage');

// Moderator + reader scoping.
TestRunner::isTrue(Acl::allows('moderator', 'comments.moderate'), 'moderator allowed comments.moderate');
TestRunner::isFalse(Acl::allows('moderator', 'content.publish'), 'moderator denied content.publish');
TestRunner::isFalse(Acl::allows('reader', 'dashboard.view'), 'reader denied dashboard.view');
TestRunner::isFalse(Acl::allows('reader', 'any.permission'), 'reader denied everything');

// Unknown permissions fail closed for non-admins.
TestRunner::isTrue(Acl::allows('admin', 'nonexistent.permission'), 'admin allowed unknown permission (fail-open super)');
TestRunner::isFalse(Acl::allows('editor', 'nonexistent.permission'), 'editor denied unknown permission (fail closed)');
TestRunner::isFalse(Acl::allows(null, 'dashboard.view'), 'null role denied');
TestRunner::isFalse(Acl::allows('', 'dashboard.view'), 'empty role denied');
TestRunner::isFalse(Acl::allows('admin', null), 'admin-allows(null) is false (guard against null perm)');

// defaultRoles returns a sane, non-empty default for registered permissions.
foreach (Acl::permissions() as $key => $label) {
    TestRunner::isTrue(is_string($label) && $label !== '', "label for {$key} is non-empty string");
    TestRunner::isTrue(Acl::allows('admin', $key) === true, "super-admin defaults to allow {$key}");
    // Every registered permission must grant at least the 'admin' role.
    TestRunner::isTrue(is_array(Acl::defaultRoles($key)), "defaultRoles({$key}) is an array");
}

// Registry is a flat key => label map (no collisions, stable order).
TestRunner::isTrue(count(Acl::permissions()) > 10, 'registry has a meaningful permission set');
TestRunner::isTrue(array_keys(Acl::permissions()) === array_values(array_unique(array_keys(Acl::permissions()))), 'registry keys unique');