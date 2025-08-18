<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$course = App\Models\Course::where('title', 'like', '%CISA%')->first();

if ($course) {
    echo "CISA Course: {$course->title}\n";
    echo "Course ID: {$course->id}\n\n";
    
    $sections = $course->sections()->orderBy('order')->get();
    $totalLectures = 0;
    
    foreach ($sections as $index => $section) {
        $lectures = $section->lectures()->orderBy('order')->get();
        $lectureCount = $lectures->count();
        $totalLectures += $lectureCount;
        
        echo "Section " . ($index + 1) . ": {$section->title}\n";
        echo "Lectures: {$lectureCount}\n";
        
        foreach ($lectures as $lecture) {
            echo "  - {$lecture->name}: {$lecture->title} ({$lecture->duration} min)\n";
        }
        echo "\n";
    }
    
    echo "Total Lectures: {$totalLectures}\n";
} else {
    echo "CISA Course not found\n";
}