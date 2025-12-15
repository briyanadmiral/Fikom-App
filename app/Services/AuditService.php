<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * ✅ AuditService - Service untuk logging aktivitas user
 * 
 * Usage:
 * app(AuditService::class)->log('create', $model);
 * app(AuditService::class)->log('update', $model, $oldValues);
 * AuditService::record('approve', $tugas);
 */
class AuditService
{
    /**
     * Log aktivitas
     * 
     * @param string $action Action type (create, update, delete, approve, etc.)
     * @param Model|null $entity The entity being acted upon
     * @param array|null $oldValues Old values (untuk update/delete)
     * @param array|null $newValues New values (untuk create/update)
     * @return AuditLog
     */
    public function log(
        string $action,
        ?Model $entity = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        $user = Auth::user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->nama_lengkap ?? 'System',
            'action' => $action,
            'entity_type' => $entity ? class_basename($entity) : null,
            'entity_id' => $entity?->id,
            'entity_name' => $this->getEntityName($entity),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => $this->truncateUserAgent(request()?->userAgent()),
        ]);
    }

    /**
     * Static helper untuk quick logging
     */
    public static function record(string $action, ?Model $entity = null, ?array $old = null, ?array $new = null): AuditLog
    {
        return app(self::class)->log($action, $entity, $old, $new);
    }

    /**
     * Log create action
     */
    public function logCreate(Model $entity): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_CREATE,
            $entity,
            null,
            $this->getLoggableAttributes($entity)
        );
    }

    /**
     * Log update action
     */
    public function logUpdate(Model $entity, array $original): AuditLog
    {
        $changes = $entity->getChanges();
        
        // Only log if there are actual changes
        if (empty($changes)) {
            return new AuditLog(); // Return empty model
        }

        // Get only the changed fields from original
        $oldValues = array_intersect_key($original, $changes);

        return $this->log(
            AuditLog::ACTION_UPDATE,
            $entity,
            $oldValues,
            $changes
        );
    }

    /**
     * Log delete action
     */
    public function logDelete(Model $entity): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_DELETE,
            $entity,
            $this->getLoggableAttributes($entity),
            null
        );
    }

    /**
     * Log approval action
     */
    public function logApprove(Model $entity): AuditLog
    {
        return $this->log(AuditLog::ACTION_APPROVE, $entity);
    }

    /**
     * Log reject action
     */
    public function logReject(Model $entity, ?string $reason = null): AuditLog
    {
        return $this->log(
            AuditLog::ACTION_REJECT,
            $entity,
            null,
            $reason ? ['alasan_tolak' => $reason] : null
        );
    }

    /**
     * Log submit action
     */
    public function logSubmit(Model $entity): AuditLog
    {
        return $this->log(AuditLog::ACTION_SUBMIT, $entity);
    }

    /**
     * Log publish action
     */
    public function logPublish(Model $entity): AuditLog
    {
        return $this->log(AuditLog::ACTION_PUBLISH, $entity);
    }

    /**
     * Log archive action
     */
    public function logArchive(Model $entity): AuditLog
    {
        return $this->log(AuditLog::ACTION_ARCHIVE, $entity);
    }

    /**
     * Get entity name for display
     */
    protected function getEntityName(?Model $entity): ?string
    {
        if (!$entity) {
            return null;
        }

        // Try common name fields
        if (isset($entity->nomor)) {
            return $entity->nomor;
        }
        if (isset($entity->nama)) {
            return $entity->nama;
        }
        if (isset($entity->nama_lengkap)) {
            return $entity->nama_lengkap;
        }
        if (isset($entity->title)) {
            return $entity->title;
        }

        return "ID: {$entity->id}";
    }

    /**
     * Get loggable attributes (exclude sensitive/large fields)
     */
    protected function getLoggableAttributes(Model $entity): array
    {
        $attributes = $entity->toArray();

        // Fields to exclude from logging
        $exclude = [
            'password',
            'sandi_hash',
            'remember_token',
            'signature_path',
            'detail_tugas', // Too large
            'menetapkan', // Too large
            'old_values',
            'new_values',
        ];

        foreach ($exclude as $field) {
            unset($attributes[$field]);
        }

        // Truncate long text fields
        foreach ($attributes as $key => $value) {
            if (is_string($value) && strlen($value) > 200) {
                $attributes[$key] = substr($value, 0, 200) . '...';
            }
        }

        return $attributes;
    }

    /**
     * Truncate user agent to fit database column
     */
    protected function truncateUserAgent(?string $userAgent): ?string
    {
        if (!$userAgent) {
            return null;
        }

        return substr($userAgent, 0, 255);
    }
}
