<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        $databaseConnections = $this->getDatabaseConnectionStatus();
        
        return Inertia::render('Settings', [
            'settings' => $settings,
            'groups' => [
                'branding' => 'Branding & Appearance',
                'contact' => 'Contact Information',
                'social' => 'Social Media',
                'system' => 'System Settings',
                'database' => 'Database Status'
            ],
            'databaseConnections' => $databaseConnections
        ]);
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.type' => 'required|in:string,boolean,integer,json,file'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        foreach ($request->settings as $settingData) {
            $setting = Setting::where('key', $settingData['key'])->first();
            
            if (!$setting) {
                continue;
            }

            $value = $settingData['value'];

            // Handle file uploads
            if ($setting->type === 'file' && $request->hasFile("file_{$setting->key}")) {
                $file = $request->file("file_{$setting->key}");
                
                // Delete old file if exists
                if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                    Storage::disk('public')->delete($setting->value);
                }
                
                // Store new file
                $path = $file->store('settings', 'public');
                $value = "/storage/{$path}";
            }

            Setting::set(
                $setting->key,
                $value,
                $setting->type,
                $setting->group,
                $setting->description
            );
        }

        Setting::clearCache();

        return back()->with('success', 'Settings updated successfully!');
    }

    /**
     * Upload file for setting
     */
    public function uploadFile(Request $request, string $key)
    {
        $request->validate([
            'file' => 'required|file|mimes:png,jpg,jpeg,svg,ico|max:2048'
        ]);

        $setting = Setting::where('key', $key)->first();
        
        if (!$setting || $setting->type !== 'file') {
            return response()->json(['error' => 'Invalid setting key'], 400);
        }

        // Delete old file
        if ($setting->value && Storage::disk('public')->exists(str_replace('/storage/', '', $setting->value))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $setting->value));
        }

        // Store new file
        $path = $request->file('file')->store('settings', 'public');
        $url = "/storage/{$path}";

        Setting::set($setting->key, $url, $setting->type, $setting->group, $setting->description);

        return response()->json([
            'success' => true,
            'url' => $url
        ]);
    }

    /**
     * Get setting value by key
     */
    public function getSetting(string $key)
    {
        $value = Setting::get($key);
        
        return response()->json([
            'key' => $key,
            'value' => $value
        ]);
    }

    /**
     * Reset settings to default
     */
    public function reset()
    {
        // Clear all settings
        Setting::truncate();
        
        // Run seeder
        Artisan::call('db:seed', ['--class' => 'SettingsSeeder']);
        
        Setting::clearCache();

        return back()->with('success', 'Settings reset to default values!');
    }

    /**
     * Get database connection status and all databases for admin users only
     */
    public function getDatabaseConnectionStatus()
    {
        $connections = [];
        $configConnections = Config::get('database.connections');
        
        foreach ($configConnections as $name => $config) {
            $status = $this->testDatabaseConnection($name);
            $databases = $status['active'] ? $this->getAllDatabases($name, $config['driver']) : [];
            
            $connections[] = [
                'name' => ucfirst($name),
                'driver' => $config['driver'] ?? 'unknown',
                'host' => $config['host'] ?? 'N/A',
                'database' => $config['database'] ?? 'N/A',
                'active' => $status['active'],
                'message' => $status['message'],
                'response_time' => $status['response_time'] ?? null,
                'databases' => $databases
            ];
        }
        
        return $connections;
    }

    /**
     * Get all databases from a connection
     */
    private function getAllDatabases($connectionName, $driver)
    {
        try {
            $connection = DB::connection($connectionName);
            $databases = [];
            
            switch ($driver) {
                case 'mysql':
                    $results = $connection->select('SHOW DATABASES');
                    foreach ($results as $result) {
                        $databases[] = $result->Database;
                    }
                    break;
                    
                case 'pgsql':
                    $results = $connection->select('SELECT datname FROM pg_database WHERE datistemplate = false');
                    foreach ($results as $result) {
                        $databases[] = $result->datname;
                    }
                    break;
                    
                case 'sqlite':
                    // For SQLite, return the database file path
                    $config = Config::get("database.connections.{$connectionName}");
                    $databases[] = basename($config['database'] ?? 'database.sqlite');
                    break;
                    
                case 'sqlsrv':
                    $results = $connection->select('SELECT name FROM sys.databases WHERE database_id > 4');
                    foreach ($results as $result) {
                        $databases[] = $result->name;
                    }
                    break;
                    
                default:
                    $databases[] = 'Database listing not supported for ' . $driver;
            }
            
            return $databases;
        } catch (\Exception $e) {
            return ['Error: ' . $e->getMessage()];
        }
    }

    /**
     * Test individual database connection
     */
    private function testDatabaseConnection($connectionName)
    {
        try {
            $startTime = microtime(true);
            DB::connection($connectionName)->getPdo();
            $endTime = microtime(true);
            
            return [
                'active' => true,
                'message' => 'Connected successfully',
                'response_time' => round(($endTime - $startTime) * 1000, 2) // milliseconds
            ];
        } catch (\Exception $e) {
            return [
                'active' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'response_time' => null
            ];
        }
    }

    /**
     * API endpoint to refresh database status
     */
    public function refreshDatabaseStatus()
    {
        return response()->json([
            'connections' => $this->getDatabaseConnectionStatus()
        ]);
    }
}