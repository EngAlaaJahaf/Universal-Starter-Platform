<?php

class ActivityLogger
{
    public static function log($action, $entity, $entityId = null, $oldValues = null, $newValues = null)
    {
        try {
            $db = new Database();
            if (is_array($newValues)) {
                unset($newValues['_csrf'], $newValues['_csrf_token'], $newValues['password'], $newValues['password_hash'], $newValues['password_confirmation']);
            }
            if (is_array($oldValues)) {
                unset($oldValues['_csrf'], $oldValues['_csrf_token'], $oldValues['password'], $oldValues['password_hash'], $oldValues['password_confirmation']);
            }

            $db->query('INSERT INTO activity_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent) VALUES (:user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip_address, :user_agent)', array(
                ':user_id' => $user['id'] ?? null,
                ':action' => $action,
                ':entity_type' => $entity,
                ':entity_id' => $entityId,
                ':old_values' => $oldValues === null ? null : json_encode($oldValues, JSON_UNESCAPED_UNICODE),
                ':new_values' => $newValues === null ? null : json_encode($newValues, JSON_UNESCAPED_UNICODE),
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            ));
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }
    }
}
