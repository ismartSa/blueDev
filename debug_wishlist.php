<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test direct query
use App\Models\Course;
use Illuminate\Support\Facades\DB;

echo "Testing direct query...\n";

$query = Course::select(['id', 'title', 'slug', 'description', 'image', 'duration', 'price', 'status', 'category_id', 'user_id'])
    ->with(['category', 'instructor'])
    ->withCount(['lectures as lessons_count', 'enrollments'])
    ->where('status', 1);

// Add the same logic as in CourseController
$query->addSelect([
    'user_enrolled' => DB::raw('0'),
    'is_wishlisted' => DB::raw('0')
]);

$courses = $query->limit(3)->get();

echo "Found " . count($courses) . " courses:\n\n";

foreach ($courses as $index => $course) {
    echo "Course #" . ($index + 1) . ": " . $course->title . "\n";
    echo "  ID: " . $course->id . "\n";
    echo "  is_wishlisted: " . (isset($course->is_wishlisted) ? ($course->is_wishlisted ? 'true' : 'false') : 'NOT SET') . "\n";
    echo "  user_enrolled: " . (isset($course->user_enrolled) ? ($course->user_enrolled ? 'true' : 'false') : 'NOT SET') . "\n";
    echo "  Raw attributes: " . json_encode($course->getAttributes()) . "\n";
    echo "\n";
}

$kernel->terminate(null, null);