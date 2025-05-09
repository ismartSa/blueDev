<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'title' => 'Introduction to Web Development',
                'description' => 'Learn the basics of web development including HTML, CSS, and JavaScript.',
                'body' => 'This course covers the fundamental concepts of web development...',
                'duration' => 30,
                'status' => true,
                'intro_video' => 'https://example.com/intro-video.mp4',
                'price' => 0, // دورة مجانية
            ],
            [
                'title' => 'Advanced JavaScript Programming',
                'description' => 'Dive deep into JavaScript with advanced concepts and techniques.',
                'body' => 'Explore advanced JavaScript topics such as closures, prototypes, and async programming...',
                'duration' => 45,
                'status' => true,
                'intro_video' => 'https://example.com/advanced-js-intro.mp4',
                'price' => 49.99,
            ],
            [
                'title' => 'PHP for Beginners',
                'description' => 'Start your journey with PHP, a popular server-side scripting language.',
                'body' => 'Learn PHP basics, syntax, and how to interact with databases...',
                'duration' => 35,
                'status' => true,
                'intro_video' => 'https://example.com/php-intro.mp4',
                'price' => 29.99,
            ],
        ];

        $this->createCoursesWithSections($courses);

        // إنشاء دورات إضافية باستخدام المصنع
        Course::factory(3)->create()->each(function ($course) {
            // إنشاء 2-4 أقسام لكل دورة
            $sectionCount = rand(2, 4);
            for ($i = 1; $i <= $sectionCount; $i++) {
                Section::create([
                    'course_id' => $course->id,
                    'title' => "Section $i for " . $course->title,
                    'description' => "This section covers important topics related to " . $course->title,
                    'order' => $i,
                ]);
            }
        });
    }

    /**
     * إنشاء الدورات مع الأقسام الخاصة بها
     */
    private function createCoursesWithSections(array $coursesData): void
    {
        foreach ($coursesData as $courseData) {
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
                'price' => $courseData['price'] ?? 0,
            ]);

            // إنشاء أقسام لكل دورة
            $sectionCount = rand(2, 4);
            for ($i = 1; $i <= $sectionCount; $i++) {
                Section::create([
                    'course_id' => $course->id,
                    'title' => "Section $i for " . $course->title,
                    'description' => "This section covers important topics related to " . $course->title,
                    'order' => $i,
                ]);
            }
        }
    }
}
