<?php
declare(strict_types=1);

final class Activity
{
    public static function log(string $action, string $entity = '', int $entityId = 0, string $meta = ''): void
    {
        try {
            Database::insert('activity_log', [
                'user_id' => Auth::id(),
                'action' => mb_substr($action, 0, 80),
                'entity' => mb_substr($entity, 0, 40),
                'entity_id' => $entityId,
                'meta' => mb_substr($meta, 0, 250),
                'ip' => mb_substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable) {
            // журнал не должен ломать основной сценарий
        }
    }
}
