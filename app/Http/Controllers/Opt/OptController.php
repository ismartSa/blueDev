<?php

namespace App\Http\Controllers\Opt;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class OptController extends Controller
{
    // Show upload page
    public function index()
    {
        return Inertia::render('Opt/Upload');
    }

    // Convert JSON to PostgreSQL
    public function convertToSql(Request $request)
    {
        $request->validate([
            'json_content' => 'required|string',
            'table_name' => 'required|string'
        ]);

        try {
            $jsonContent = $request->json_content;
            $tableName = $request->table_name;

            // Parse JSON
            $data = json_decode($jsonContent, true);

            if (!$data) {
                return back()->with('error', 'Invalid JSON format');
            }

            $sqlQueries = $this->generatePostgreSQLQueries($data, $tableName);

            return back()->with([
                'success' => 'SQL queries generated successfully',
                'sql_output' => $sqlQueries
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function generatePostgreSQLQueries($data, $tableName)
    {
        $sql = '';
        $insertValues = [];

        // Handle nested JSON structure
        foreach ($data as $item) {
            if (isset($item['content']) && is_array($item['content'])) {
                // Process nested content
                foreach ($item['content'] as $record) {
                    $values = [];
                    $values[] = $record['id'] ?? 'NULL';
                    $values[] = "'" . ($record['key'] ?? '') . "'";
                    $values[] = "'" . ($record['name_en'] ?? '') . "'";
                    $values[] = "'" . ($record['name'] ?? '') . "'";
                    $values[] = $record['questions_number'] ?? 0;
                    $values[] = $record['completedQuizNumber'] ?? 0;
                    $values[] = $record['savedQuizNumber'] ?? 0;

                    $insertValues[] = '(' . implode(', ', $values) . ')';
                }
            } else {
                // Handle direct records
                $values = [];
                foreach ($item as $key => $value) {
                    if (is_string($value)) {
                        $values[] = "'" . $value . "'";
                    } else {
                        $values[] = $value ?? 'NULL';
                    }
                }
                $insertValues[] = '(' . implode(', ', $values) . ')';
            }
        }

        // Generate CREATE TABLE
        $sql .= "-- Create table\n";
        $sql .= "CREATE TABLE IF NOT EXISTS {$tableName} (\n";
        $sql .= "    id INTEGER PRIMARY KEY,\n";
        $sql .= "    key VARCHAR(50),\n";
        $sql .= "    name_en VARCHAR(255),\n";
        $sql .= "    name VARCHAR(255),\n";
        $sql .= "    questions_number INTEGER,\n";
        $sql .= "    completed_quiz_number INTEGER DEFAULT 0,\n";
        $sql .= "    saved_quiz_number INTEGER DEFAULT 0,\n";
        $sql .= "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n";
        $sql .= ");\n\n";

        // Generate INSERT statements
        $sql .= "-- Insert data\n";
        $sql .= "INSERT INTO {$tableName} (id, key, name_en, name, questions_number, completed_quiz_number, saved_quiz_number) VALUES\n";
        $sql .= implode(",\n", $insertValues) . ";\n\n";

        // Add SELECT query
        $sql .= "-- Check inserted data\n";
        $sql .= "SELECT * FROM {$tableName} ORDER BY id;";

        return $sql;
    }
}
