<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Section;
use App\Models\Lecture;

echo "=== Creating PMP Course Content ===\n\n";

// Find PMP course
$pmpCourse = Course::where('name', 'PMP')->first();
if (!$pmpCourse) {
    echo "❌ PMP Course not found\n";
    exit(1);
}

echo "✅ Found PMP Course: {$pmpCourse->title}\n\n";

// Create sections for PMP course
$sectionsData = [
    [
        'title' => 'Project Management Fundamentals',
        'description' => 'Introduction to project management concepts and PMBOK Guide',
        'order' => 1,
        'lectures' => [
            ['title' => 'What is Project Management?', 'duration' => 15],
            ['title' => 'PMBOK Guide Overview', 'duration' => 20],
            ['title' => 'Project Life Cycle', 'duration' => 18],
        ]
    ],
    [
        'title' => 'Project Integration Management',
        'description' => 'Managing project integration processes',
        'order' => 2,
        'lectures' => [
            ['title' => 'Develop Project Charter', 'duration' => 25],
            ['title' => 'Develop Project Management Plan', 'duration' => 30],
            ['title' => 'Direct and Manage Project Work', 'duration' => 22],
        ]
    ],
    [
        'title' => 'Project Scope Management',
        'description' => 'Planning and controlling project scope',
        'order' => 3,
        'lectures' => [
            ['title' => 'Plan Scope Management', 'duration' => 20],
            ['title' => 'Collect Requirements', 'duration' => 28],
            ['title' => 'Define Scope', 'duration' => 25],
            ['title' => 'Create WBS', 'duration' => 30],
        ]
    ],
    [
        'title' => 'Project Schedule Management',
        'description' => 'Planning and controlling project schedules',
        'order' => 4,
        'lectures' => [
            ['title' => 'Plan Schedule Management', 'duration' => 18],
            ['title' => 'Define Activities', 'duration' => 22],
            ['title' => 'Sequence Activities', 'duration' => 25],
            ['title' => 'Estimate Activity Durations', 'duration' => 28],
        ]
    ]
];

foreach ($sectionsData as $sectionData) {
    // Check if section already exists
    $existingSection = Section::where('course_id', $pmpCourse->id)
        ->where('title', $sectionData['title'])
        ->first();
    
    if ($existingSection) {
        echo "ℹ️  Section already exists: {$sectionData['title']}\n";
        $section = $existingSection;
    } else {
        $section = Section::create([
            'course_id' => $pmpCourse->id,
            'title' => $sectionData['title'],
            'description' => $sectionData['description'],
            'order' => $sectionData['order'],
        ]);
        echo "✅ Created section: {$section->title}\n";
    }
    
    // Create lectures for this section
    foreach ($sectionData['lectures'] as $index => $lectureData) {
        $existingLecture = Lecture::where('section_id', $section->id)
            ->where('title', $lectureData['title'])
            ->first();
        
        if ($existingLecture) {
            echo "   ℹ️  Lecture already exists: {$lectureData['title']}\n";
        } else {
            $lecture = Lecture::create([
                'course_id' => $pmpCourse->id,
                'section_id' => $section->id,
                'title' => $lectureData['title'],
                'name' => strtolower(str_replace(' ', '-', $lectureData['title'])),
                'uuid' => \Illuminate\Support\Str::uuid(),
                'description' => 'Detailed explanation of ' . $lectureData['title'],
                'video_url' => 'https://example.com/videos/' . strtolower(str_replace(' ', '-', $lectureData['title'])) . '.mp4',
                'duration' => $lectureData['duration'],
                'order' => $index + 1,
                'status' => true,
            ]);
            echo "   ✅ Created lecture: {$lecture->title} ({$lecture->duration} min)\n";
        }
    }
    echo "\n";
}

// Summary
$totalSections = $pmpCourse->sections()->count();
$totalLectures = Lecture::where('course_id', $pmpCourse->id)->count();
$totalDuration = Lecture::where('course_id', $pmpCourse->id)->sum('duration');

echo "=== PMP Course Summary ===\n";
echo "📚 Total Sections: {$totalSections}\n";
echo "🎥 Total Lectures: {$totalLectures}\n";
echo "⏱️  Total Duration: {$totalDuration} minutes\n";
echo "\n✅ PMP Course content creation completed!\n";