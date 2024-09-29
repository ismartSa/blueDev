<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $courses = [
            [
                'title' => 'Introduction to Web Development',
                'description' => 'Learn the basics of web development including HTML, CSS, and JavaScript.',
                'body' => 'This course covers the fundamental concepts of web development...',
                'duration' => 30,
                'status' => true,
                'intro_video' => 'https://example.com/intro-video.mp4',
            ],
            [
                'title' => 'Advanced JavaScript Programming',
                'description' => 'Dive deep into JavaScript with advanced concepts and techniques.',
                'body' => 'Explore advanced JavaScript topics such as closures, prototypes, and async programming...',
                'duration' => 45,
                'status' => true,
                'intro_video' => 'https://example.com/advanced-js-intro.mp4',
            ],
            [
                'title' => 'PHP for Beginners',
                'description' => 'Start your journey with PHP, a popular server-side scripting language.',
                'body' => 'Learn PHP basics, syntax, and how to interact with databases...',
                'duration' => 35,
                'status' => true,
                'intro_video' => 'https://example.com/php-intro.mp4',
            ],
            [
                'title' => 'Laravel Framework Essentials',
                'description' => 'Master Laravel, the PHP framework for web artisans.',
                'body' => 'Discover Laravel\'s powerful features including Eloquent ORM, routing, and Blade templating...',
                'duration' => 50,
                'status' => true,
                'intro_video' => 'https://example.com/laravel-intro.mp4',
            ],
            [
                'title' => 'Vue.js Fundamentals',
                'description' => 'Build dynamic user interfaces with Vue.js.',
                'body' => 'Learn component-based architecture, state management, and more with Vue.js...',
                'duration' => 40,
                'status' => true,
                'intro_video' => 'https://example.com/vuejs-intro.mp4',
            ],
        ];

        foreach ($courses as $courseData) {
            $course = Course::create([
                'title' => $courseData['title'],
                'name' => $courseData['title'],
                'slug' => Str::slug($courseData['title']),
                'description' => $courseData['description'],
                'body' => $courseData['body'],
                'duration' => $courseData['duration'],
                'image' => 'https://via.placeholder.com/640x480.png/005588?text=' . urlencode($courseData['title']),
                'status' => $courseData['status'],
                'intro_video' => $courseData['intro_video'],
            ]);

            // Create sections for each course
            for ($i = 1; $i <= 3; $i++) {
                Section::create([
                    'course_id' => $course->id,
                    'title' => "Section $i for " . $course->title,
                    'order' => $i,
                ]);
            }
        }
    }
}
