<?php

namespace App\Traits;

use App\Services\BackupService;
use Illuminate\Support\Facades\Log;

/**
 * Trait for models to enable automatic backup functionality
 * Implements DRY principle for backup operations
 */
trait HasBackup
{
    /**
     * Boot the trait and register model events
     */
    protected static function bootHasBackup(): void
    {
        // Auto-backup on model creation
        static::created(function ($model) {
            $model->backupToSecondaryDb();
        });

        // Auto-backup on model update (optional)
        static::updated(function ($model) {
            if ($model->shouldBackupOnUpdate()) {
                $model->backupToSecondaryDb();
            }
        });
    }

    /**
     * Backup current model instance to secondary database
     * 
     * @return bool Success status
     */
    public function backupToSecondaryDb(): bool
    {
        $backupService = app(BackupService::class);
        $tableName = $this->getBackupTableName();
        $data = $this->getBackupData();

        return $backupService->insertToBackup($tableName, $data);
    }

    /**
     * Get the backup table name (can be overridden in models)
     * 
     * @return string Table name for backup
     */
    protected function getBackupTableName(): string
    {
        return $this->getTable();
    }

    /**
     * Get data to backup (can be overridden in models)
     * 
     * @return array Data array for backup
     */
    protected function getBackupData(): array
    {
        $data = $this->toArray();
        
        // Add backup timestamp
        $data['backed_up_at'] = now();
        
        return $data;
    }

    /**
     * Determine if model should backup on update (can be overridden)
     * 
     * @return bool Whether to backup on update
     */
    protected function shouldBackupOnUpdate(): bool
    {
        return false; // Default: only backup on create
    }

    /**
     * Manually trigger backup for this model
     * 
     * @return bool Success status
     */
    public function triggerBackup(): bool
    {
        return $this->backupToSecondaryDb();
    }

    /**
     * Bulk backup multiple models
     * 
     * @param \Illuminate\Database\Eloquent\Collection $models
     * @return bool Success status
     */
    public static function bulkBackup($models): bool
    {
        if ($models->isEmpty()) {
            return true;
        }

        $backupService = app(BackupService::class);
        $firstModel = $models->first();
        $tableName = $firstModel->getBackupTableName();
        
        $records = $models->map(function ($model) {
            return $model->getBackupData();
        })->toArray();

        return $backupService->bulkInsertToBackup($tableName, $records);
    }
}