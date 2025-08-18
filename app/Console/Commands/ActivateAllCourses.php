<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;

class ActivateAllCourses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courses:activate-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate all courses by setting their status to published';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to activate all courses...');
        
        // Count total courses before update
        $totalCourses = Course::count();
        
        if ($totalCourses === 0) {
            $this->warn('No courses found in the database.');
            return;
        }
        
        // Update all courses to published status
        $updatedCount = Course::query()->update(['status' => 'published']);
        
        $this->info("Successfully activated {$updatedCount} out of {$totalCourses} courses.");
        
        // Display summary
        $activeCourses = Course::where('status', 'published')->count();
        $this->line("Total active courses: {$activeCourses}");
        
        $this->info('All courses have been activated successfully!');
    }
}