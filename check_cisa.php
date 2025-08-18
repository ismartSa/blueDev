<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$course = App\Models\Course::where('title', 'like', '%CISA%')->first();

if ($course) {
    echo "CISA Course found:\n";
    echo "ID: {$course->id}\n";
    echo "Title: {$course->title}\n";
    echo "Price: $" . $course->price . "\n";
    echo "Duration: {$course->duration} hours\n";
    echo "Status: " . ($course->status ? 'Active' : 'Inactive') . "\n";
    
    // Check sections
    $sections = $course->sections;
    echo "Sections: {$sections->count()}\n";
    foreach ($sections as $section) {
        echo "  - {$section->title}\n";
    }
} else {
    echo "CISA Course not found in database\n";
}