<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Wishlist Table Data:\n";
echo "==================\n";

$wishlists = App\Models\Wishlist::all();

if ($wishlists->count() > 0) {
    foreach ($wishlists as $wishlist) {
        echo "ID: {$wishlist->id}\n";
        echo "User ID: {$wishlist->user_id}\n";
        echo "Course ID: {$wishlist->course_id}\n";
        echo "Created: {$wishlist->created_at}\n";
        echo "Updated: {$wishlist->updated_at}\n";
        echo "-------------------\n";
    }
} else {
    echo "No wishlist records found.\n";
}

echo "\nTotal wishlist records: " . $wishlists->count() . "\n";

// Check if table exists
echo "\nTable exists: " . (Schema::hasTable('wishlists') ? 'Yes' : 'No') . "\n";

// Check a sample course with is_wishlisted
echo "\nSample course with wishlist status:\n";
$user = App\Models\User::first();
if ($user) {
    $course = App\Models\Course::select(['id', 'title'])
        ->addSelect([
            'is_wishlisted' => DB::raw('EXISTS(SELECT 1 FROM wishlists WHERE wishlists.course_id = courses.id AND wishlists.user_id = ' . $user->id . ')')
        ])
        ->first();
    
    if ($course) {
        echo "Course: {$course->title}\n";
        echo "Is Wishlisted: " . ($course->is_wishlisted ? 'Yes' : 'No') . "\n";
    }
}