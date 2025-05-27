<?php

namespace App\Http\Controllers\Opt;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class OptController extends Controller
{
    /**
     * Display the main dashboard
     */
    public function index()
    {
        try {
            // Get available database connections
            $connections = config('database.connections');
            $availableConnections = array_keys($connections);

            // Test PostgreSQL connections specifically
            $postgresqlConnections = [];
            $connectionStatuses = [];

            foreach ($availableConnections as $connection) {
                $connectionConfig = config("database.connections.{$connection}");

                // Check if it's a PostgreSQL connection
                if (isset($connectionConfig['driver']) && $connectionConfig['driver'] === 'pgsql') {
                    $postgresqlConnections[] = $connection;

                    // Test the connection
                    try {
                        DB::connection($connection)->getPdo();
                        $connectionStatuses[$connection] = [
                            'status' => 'connected',
                            'message' => 'Connection successful',
                            'details' => [
                                'host' => $connectionConfig['host'] ?? 'N/A',
                                'database' => $connectionConfig['database'] ?? 'N/A',
                                'port' => $connectionConfig['port'] ?? 'N/A'
                            ]
                        ];
                    }

    /**
     * Download exported file
     */


    /**
     * Show JSON upload form
     */
    public function showUploadForm()
    {
        $connections = config('database.connections');
        $availableConnections = array_keys($connections);

        return view('opt.upload', compact('availableConnections'));
    }

    /**
     * Upload and process JSON file
     */
    public function uploadJson(Request $request)
    {
        try {
            // Validate uploaded file
            $validator = Validator::make($request->all(), [
                'json_file' => 'required|file|mimes:json,txt|max:10240', // 10MB max
                'database_connection' => 'required|string',
                'table_name' => 'required|string|max:64'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Validation error');
            }

            $file = $request->file('json_file');
            $connection = $request->input('database_connection');
            $tableName = $request->input('table_name');

            // Read JSON file content
            $jsonContent = file_get_contents($file->getRealPath());
            $data = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()
                    ->with('error', 'Invalid JSON file: ' . json_last_error_msg())
                    ->withInput();
            }

            // Process and insert data into database
            $result = $this->processJsonData($data, $connection, $tableName);

            return redirect()->back()->with([
                'success' => 'File uploaded and processed successfully',
                'result' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Error uploading JSON file: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'An error occurred while processing the file: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Process JSON data and insert into database
     */
    private function processJsonData(array $data, string $connection, string $tableName): array
    {
        $insertedCount = 0;
        $errorCount = 0;

        DB::connection($connection)->beginTransaction();

        try {
            foreach ($data as $record) {
                try {
                    // Ensure data is in array format
                    if (!is_array($record)) {
                        $errorCount++;
                        continue;
                    }

                    // Insert data
                    DB::connection($connection)
                        ->table($tableName)
                        ->insert($record);

                    $insertedCount++;

                } catch (Exception $e) {
                    Log::warning("Error inserting record: " . $e->getMessage());
                    $errorCount++;
                }
            }

            DB::connection($connection)->commit();

            return [
                'total_records' => count($data),
                'inserted_count' => $insertedCount,
                'error_count' => $errorCount
            ];

        } catch (Exception $e) {
            DB::connection($connection)->rollBack();
            throw $e;
        }
    }

    /**
     * Show data viewer form
     */
    public function showDataViewer()
    {
        $connections = config('database.connections');
        $availableConnections = array_keys($connections);

        return view('opt.data-viewer', compact('availableConnections'));
    }

    /**
     * Get data from specific database
     */
    public function getData(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'database_connection' => 'required|string',
                'table_name' => 'required|string|max:64',
                'limit' => 'nullable|integer|min:1|max:1000',
                'offset' => 'nullable|integer|min:0'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Validation error');
            }

            $connection = $request->input('database_connection');
            $tableName = $request->input('table_name');
            $limit = $request->input('limit', 100);
            $offset = $request->input('offset', 0);

            $data = DB::connection($connection)
                ->table($tableName)
                ->limit($limit)
                ->offset($offset)
                ->get();

            return view('opt.data-viewer', [
                'data' => $data,
                'tableName' => $tableName,
                'connection' => $connection,
                'availableConnections' => array_keys(config('database.connections'))
            ]);

        } catch (Exception $e) {
            Log::error('Error retrieving data: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'An error occurred while retrieving data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show query executor form
     */
    public function showQueryExecutor()
    {
        $connections = config('database.connections');
        $availableConnections = array_keys($connections);

        return view('opt.query-executor', compact('availableConnections'));
    }

    /**
     * Execute custom query on specific database
     */
    public function executeQuery(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'database_connection' => 'required|string',
                'query' => 'required|string',
                'query_type' => 'required|string|in:select,insert,update,delete'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Validation error');
            }

            $connection = $request->input('database_connection');
            $query = $request->input('query');
            $queryType = $request->input('query_type');

            // Execute query based on type
            $result = $this->executeCustomQuery($connection, $query, $queryType);

            return view('opt.query-executor', [
                'result' => $result,
                'queryType' => $queryType,
                'executedQuery' => $query,
                'availableConnections' => array_keys(config('database.connections'))
            ]);

        } catch (Exception $e) {
            Log::error('Error executing query: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'An error occurred while executing query: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Execute custom query
     */
    private function executeCustomQuery(string $connection, string $query, string $queryType)
    {
        switch ($queryType) {
            case 'select':
                return DB::connection($connection)->select($query);

            case 'insert':
                return DB::connection($connection)->insert($query);

            case 'update':
                return DB::connection($connection)->update($query);

            case 'delete':
                return DB::connection($connection)->delete($query);

            default:
                throw new Exception('Invalid query type');
        }
    }

    /**
     * Test database connection
     */
    public function testConnection(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'database_connection' => 'required|string'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->with('error', 'Validation error');
            }

            $connection = $request->input('database_connection');

            // Test connection
            DB::connection($connection)->getPdo();

            return redirect()->back()->with([
                'success' => 'Database connection successful',
                'connection' => $connection
            ]);

        } catch (Exception $e) {
            Log::error('Database connection failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Get database tables list
     */
    public function getTables(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'database_connection' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $connection = $request->input('database_connection');

            // Get tables based on database type
            $tables = $this->getTablesList($connection);

            return response()->json([
                'success' => true,
                'tables' => $tables
            ]);

        } catch (Exception $e) {
            Log::error('Error getting tables: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tables list based on database type
     */
    private function getTablesList(string $connection): array
    {
        $dbType = config("database.connections.{$connection}.driver");

        switch ($dbType) {
            case 'mysql':
                $query = "SHOW TABLES";
                break;

            case 'pgsql':
                $query = "SELECT tablename FROM pg_tables WHERE schemaname = 'public'";
                break;

            case 'sqlsrv':
                $query = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
                break;

            case 'sqlite':
                $query = "SELECT name FROM sqlite_master WHERE type='table'";
                break;

            default:
                throw new Exception('Unsupported database type');
        }

        $results = DB::connection($connection)->select($query);

        // Extract table names from results
        $tables = [];
        foreach ($results as $result) {
            $result = (array) $result;
            $tables[] = reset($result);
        }

        return $tables;
    }

    /**
     * Show export form
     */
    public function showExportForm()
    {
        $connections = config('database.connections');
        $availableConnections = array_keys($connections);

        return view('opt.export', compact('availableConnections'));
    }

    /**
     * Export data to JSON file
     */
    public function exportToJson(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'database_connection' => 'required|string',
                'table_name' => 'required|string|max:64',
                'limit' => 'nullable|integer|min:1|max:10000'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Validation error');
            }

            $connection = $request->input('database_connection');
            $tableName = $request->input('table_name');
            $limit = $request->input('limit', 1000);

            // Get data from table
            $data = DB::connection($connection)
                ->table($tableName)
                ->limit($limit)
                ->get()
                ->toArray();

            // Generate filename
            $filename = $tableName . '_export_' . date('Y-m-d_H-i-s') . '.json';

            // Store JSON file
            $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            Storage::disk('public')->put('exports/' . $filename, $jsonContent);

            return redirect()->back()->with([
                'success' => 'Data exported successfully',
                'filename' => $filename,
                'download_url' => Storage::disk('public')->url('exports/' . $filename),
                'record_count' => count($data)
            ]);

        } catch (Exception $e) {
            Log::error('Error exporting data: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'An error occurred while exporting data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get PostgreSQL database information
     */
    public function getPostgresqlInfo(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'database_connection' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $connection = $request->input('database_connection');

            // Verify it's a PostgreSQL connection
            $connectionConfig = config("database.connections.{$connection}");
            if (!isset($connectionConfig['driver']) || $connectionConfig['driver'] !== 'pgsql') {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection is not a PostgreSQL database'
                ], 400);
            }

            // Get PostgreSQL version
            $version = DB::connection($connection)->select('SELECT version()')[0]->version;

            // Get database size
            $dbName = $connectionConfig['database'];
            $dbSize = DB::connection($connection)->select("
                SELECT pg_size_pretty(pg_database_size(?)) as size
            ", [$dbName])[0]->size;

            // Get table count
            $tableCount = DB::connection($connection)->select("
                SELECT COUNT(*) as count
                FROM information_schema.tables
                WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
            ")[0]->count;

            // Get connection info
            $connInfo = DB::connection($connection)->select("
                SELECT
                    COUNT(*) as active_connections,
                    current_database() as current_db,
                    current_user as current_user
                FROM pg_stat_activity
                WHERE datname = current_database()
            ")[0];

            return response()->json([
                'success' => true,
                'data' => [
                    'version' => $version,
                    'database_size' => $dbSize,
                    'table_count' => $tableCount,
                    'active_connections' => $connInfo->active_connections,
                    'current_database' => $connInfo->current_db,
                    'current_user' => $connInfo->current_user,
                    'host' => $connectionConfig['host'],
                    'port' => $connectionConfig['port']
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error getting PostgreSQL info: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error getting PostgreSQL information',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

