<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Artisan command for backup database operations
 * Implements clean command structure with DRY principles
 */
class BackupCommand extends Command
{
    protected $signature = 'backup:db 
                           {action : Action to perform (test|sync|create-tables)}
                           {--table= : Specific table to sync}
                           {--model= : Specific model to sync}';

    protected $description = 'Manage backup database operations';

    private BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    /**
     * Execute the console command
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'test' => $this->testConnection(),
            'sync' => $this->syncData(),
            'create-tables' => $this->createBackupTables(),
            default => $this->handleInvalidAction($action)
        };
    }

    /**
     * Test backup database connection
     */
    private function testConnection(): int
    {
        $this->info('Testing backup database connection...');
        
        if ($this->backupService->testConnection()) {
            $this->info('✅ Backup database connection successful!');
            return Command::SUCCESS;
        }
        
        $this->error('❌ Backup database connection failed!');
        return Command::FAILURE;
    }

    /**
     * Sync data to backup database
     */
    private function syncData(): int
    {
        $table = $this->option('table');
        $model = $this->option('model');

        if ($model) {
            return $this->syncModel($model);
        }

        if ($table) {
            return $this->syncTable($table);
        }

        return $this->syncAllTables();
    }

    /**
     * Sync specific model to backup
     */
    private function syncModel(string $modelClass): int
    {
        try {
            if (!class_exists($modelClass)) {
                $this->error("Model {$modelClass} does not exist!");
                return Command::FAILURE;
            }

            $this->info("Syncing model {$modelClass} to backup...");
            
            $model = new $modelClass;
            $tableName = $model->getTable();
            
            if ($this->backupService->syncModelToBackup($modelClass, $tableName)) {
                $this->info("✅ Model {$modelClass} synced successfully!");
                return Command::SUCCESS;
            }
            
            $this->error("❌ Failed to sync model {$modelClass}!");
            return Command::FAILURE;
        } catch (Exception $e) {
            $this->error("Error syncing model: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Sync specific table to backup
     */
    private function syncTable(string $table): int
    {
        try {
            $this->info("Syncing table {$table} to backup...");
            
            $records = DB::table($table)->get()->toArray();
            $recordsArray = array_map('get_object_vars', $records);
            
            if ($this->backupService->bulkInsertToBackup($table, $recordsArray)) {
                $count = count($recordsArray);
                $this->info("✅ Table {$table} synced successfully! ({$count} records)");
                return Command::SUCCESS;
            }
            
            $this->error("❌ Failed to sync table {$table}!");
            return Command::FAILURE;
        } catch (Exception $e) {
            $this->error("Error syncing table: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Sync all tables to backup
     */
    private function syncAllTables(): int
    {
        $this->info('Syncing all tables to backup database...');
        
        $tables = ['users', 'courses', 'categories', 'quizzes', 'lectures'];
        $successCount = 0;
        
        foreach ($tables as $table) {
            if ($this->syncTable($table) === Command::SUCCESS) {
                $successCount++;
            }
        }
        
        $this->info("Sync completed: {$successCount}/" . count($tables) . " tables synced successfully.");
        return $successCount === count($tables) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Create backup tables with same structure as main database
     */
    private function createBackupTables(): int
    {
        try {
            $this->info('Creating backup database tables...');
            
            // Run migrations on backup database
            $this->call('migrate', [
                '--database' => $this->backupService->getConnectionName(),
                '--force' => true
            ]);
            
            $this->info('✅ Backup tables created successfully!');
            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("Error creating backup tables: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Handle invalid action
     */
    private function handleInvalidAction(string $action): int
    {
        $this->error("Invalid action: {$action}");
        $this->info('Available actions: test, sync, create-tables');
        return Command::FAILURE;
    }
}