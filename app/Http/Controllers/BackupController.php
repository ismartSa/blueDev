<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Controller for backup operations
 * Implements clean API structure with DRY principles
 */
class BackupController extends Controller
{
    private BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Test backup database connection
     */
    public function testConnection(): JsonResponse
    {
        $isConnected = $this->backupService->testConnection();
        
        return response()->json([
            'success' => $isConnected,
            'message' => $isConnected 
                ? 'Backup database connection successful' 
                : 'Backup database connection failed',
            'timestamp' => now()
        ]);
    }

    /**
     * Manually backup a specific user
     */
    public function backupUser(Request $request): JsonResponse
    {
        $userId = $request->input('user_id');
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required'
            ], 400);
        }

        $user = User::find($userId);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $success = $user->triggerBackup();
        
        return response()->json([
            'success' => $success,
            'message' => $success 
                ? "User {$user->name} backed up successfully" 
                : 'Backup failed',
            'user_id' => $userId
        ]);
    }

    /**
     * Bulk backup multiple users
     */
    public function bulkBackupUsers(Request $request): JsonResponse
    {
        $userIds = $request->input('user_ids', []);
        
        if (empty($userIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No user IDs provided'
            ], 400);
        }

        $users = User::whereIn('id', $userIds)->get();
        
        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No users found'
            ], 404);
        }

        $success = User::bulkBackup($users);
        
        return response()->json([
            'success' => $success,
            'message' => $success 
                ? "Bulk backup completed for {$users->count()} users" 
                : 'Bulk backup failed',
            'users_count' => $users->count()
        ]);
    }

    /**
     * Sync all data to backup database
     */
    public function syncAllData(): JsonResponse
    {
        try {
            $results = [];
            $tables = ['users', 'courses', 'categories', 'quizzes', 'lectures'];
            
            foreach ($tables as $table) {
                $records = DB::table($table)->get()->toArray();
                $recordsArray = array_map('get_object_vars', $records);
                
                $success = $this->backupService->bulkInsertToBackup($table, $recordsArray);
                $results[$table] = [
                    'success' => $success,
                    'records_count' => count($recordsArray)
                ];
            }
            
            $successCount = collect($results)->where('success', true)->count();
            $totalTables = count($tables);
            
            return response()->json([
                'success' => $successCount === $totalTables,
                'message' => "Sync completed: {$successCount}/{$totalTables} tables synced",
                'results' => $results,
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Sync all data failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get backup database status
     */
    public function getStatus(): JsonResponse
    {
        $isConnected = $this->backupService->testConnection();
        
        $status = [
            'connection' => $isConnected ? 'active' : 'failed',
            'connection_name' => $this->backupService->getConnectionName(),
            'timestamp' => now()
        ];
        
        if ($isConnected) {
            try {
                // Get table counts from backup database
                $tables = ['users', 'courses', 'categories', 'quizzes', 'lectures'];
                $tableCounts = [];
                
                foreach ($tables as $table) {
                    try {
                        $count = DB::connection($this->backupService->getConnectionName())
                                   ->table($table)
                                   ->count();
                        $tableCounts[$table] = $count;
                    } catch (\Exception $e) {
                        $tableCounts[$table] = 'table_not_exists';
                    }
                }
                
                $status['table_counts'] = $tableCounts;
            } catch (\Exception $e) {
                $status['error'] = 'Could not retrieve table information';
            }
        }
        
        return response()->json($status);
    }
}