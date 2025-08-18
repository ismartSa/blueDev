<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Hash;

echo "=== PMP Course Enrollment Test ===\n\n";

// Check existing courses first
echo "Checking existing courses...\n";
$allCourses = Course::all(['id', 'title', 'name']);
echo "Found {$allCourses->count()} courses in database:\n";
foreach ($allCourses as $course) {
    echo "  - ID: {$course->id}, Title: {$course->title}, Name: " . ($course->name ?? 'N/A') . "\n";
}

// Create admin user first if needed
$adminUser = User::where('email', 'admin@laravel-brive.com')->first();
if (!$adminUser) {
    $adminUser = User::create([
        'name' => 'Admin User',
        'email' => 'admin@laravel-brive.com',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
    ]);
    echo "✅ Created admin user: {$adminUser->name}\n";
}

// Check if PMP course exists
$pmpCourse = Course::where('name', 'PMP')->orWhere('title', 'LIKE', '%PMP%')->first();
if (!$pmpCourse) {
    echo "\n❌ PMP Course not found, creating one...\n";
    $pmpCourse = Course::create([
        'title' => 'Project Management Professional (PMP)®',
        'name' => 'PMP',
        'slug' => 'pmp-certification',
        'description' => 'Comprehensive project management certification course based on PMBOK Guide',
        'body' => 'This comprehensive PMP certification course covers all aspects of project management according to the PMBOK Guide.',
        'duration' => 60,
        'status' => true,
        'price' => 0,
        'user_id' => $adminUser->id,
    ]);
    echo "✅ Created PMP course with ID: {$pmpCourse->id}\n";
}

echo "✅ PMP Course found: {$pmpCourse->title}\n";
echo "   - Duration: {$pmpCourse->duration} hours\n";
echo "   - Price: $" . ($pmpCourse->price ?? 'Free') . "\n";
echo "   - Status: " . ($pmpCourse->status ? 'Active' : 'Inactive') . "\n\n";

// Create or find test user
$testUser = User::where('email', 'test@pmp.com')->first();
if (!$testUser) {
    $testUser = User::create([
        'name' => 'PMP Test User',
        'email' => 'test@pmp.com',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
    ]);
    echo "✅ Created test user: {$testUser->name}\n";
} else {
    echo "✅ Using existing test user: {$testUser->name}\n";
}

// Check if user is already enrolled
$existingEnrollment = Enrollment::where('user_id', $testUser->id)
    ->where('course_id', $pmpCourse->id)
    ->first();

if ($existingEnrollment) {
    echo "ℹ️  User already enrolled on: {$existingEnrollment->enrolled_at}\n";
    echo "   - Progress: {$existingEnrollment->progress}%\n";
    echo "   - Status: " . ($existingEnrollment->completed ? 'Completed' : 'In Progress') . "\n";
} else {
    // Create new enrollment
    try {
        $enrollment = Enrollment::create([
            'user_id' => $testUser->id,
            'course_id' => $pmpCourse->id,
            'enrolled_at' => now(),
            'progress' => 0,
            'completed' => false,
        ]);
        
        echo "✅ Successfully enrolled user in PMP course\n";
        echo "   - Enrollment ID: {$enrollment->id}\n";
        echo "   - Enrolled at: {$enrollment->enrolled_at}\n";
    } catch (Exception $e) {
        echo "❌ Enrollment failed: {$e->getMessage()}\n";
        exit(1);
    }
}

// Test course access
echo "\n=== Testing Course Access ===\n";
$userEnrollments = $testUser->enrollments()->with('course')->get();
echo "User has {$userEnrollments->count()} enrollment(s):\n";

foreach ($userEnrollments as $enrollment) {
    echo "  - {$enrollment->course->title} ({$enrollment->progress}% complete)\n";
}

// Test sections and lectures
echo "\n=== Course Structure ===\n";
$sections = $pmpCourse->sections()->with('lectures')->get();
echo "PMP Course has {$sections->count()} section(s):\n";

foreach ($sections as $section) {
    echo "  📁 {$section->title} ({$section->lectures->count()} lectures)\n";
    foreach ($section->lectures as $lecture) {
        echo "    📹 {$lecture->title} ({$lecture->duration} min)\n";
    }
}

echo "\n✅ PMP Course enrollment test completed successfully!\n";