<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Backup Service for insert-only operations to secondary database
 * Implements DRY principle with reusable backup methods
 */
class BackupService
{
    private string $backupConnection = 'backup_pgsql';

    /**
     * Insert data into backup database table
     * 
     * @param string $table Table name
     * @param array $data Data to insert
     * @return bool Success status
     */
    public function insertToBackup(string $table, array $data): bool
    {
        try {
            DB::connection($this->backupConnection)->table($table)->insert($data);
            Log::info("Backup insert successful for table: {$table}");
            return true;
        } catch (Exception $e) {
            Log::error("Backup insert failed for table {$table}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk insert data into backup database
     * 
     * @param string $table Table name
     * @param array $records Array of records to insert
     * @return bool Success status
     */
    public function bulkInsertToBackup(string $table, array $records): bool
    {
        if (empty($records)) {
            return true;
        }

        try {
            DB::connection($this->backupConnection)->table($table)->insert($records);
            $count = count($records);
            Log::info("Backup bulk insert successful: {$count} records to {$table}");
            return true;
        } catch (Exception $e) {
            Log::error("Backup bulk insert failed for table {$table}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sync specific model data to backup database
     * 
     * @param string $model Model class name
     * @param string $table Target backup table
     * @param array $conditions Optional where conditions
     * @return bool Success status
     */
    public function syncModelToBackup(string $model, string $table, array $conditions = []): bool
    {
        try {
            $query = $model::query();
            
            // Apply conditions if provided
            foreach ($conditions as $field => $value) {
                $query->where($field, $value);
            }
            
            $records = $query->get()->toArray();
            
            if (empty($records)) {
                return true;
            }
            
            return $this->bulkInsertToBackup($table, $records);
        } catch (Exception $e) {
            Log::error("Model sync to backup failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Test backup database connection
     * 
     * @return bool Connection status
     */
    public function testConnection(): bool
    {
        try {
            DB::connection($this->backupConnection)->getPdo();
            return true;
        } catch (Exception $e) {
            Log::error("Backup database connection failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get backup database connection name
     * 
     * @return string Connection name
     */
    public function getConnectionName(): string
    {
        return $this->backupConnection;
    }
}