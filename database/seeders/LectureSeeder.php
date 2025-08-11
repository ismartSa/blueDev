<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lecture;
use App\Models\Course;
use App\Models\Section;

class LectureSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            $sections = $course->sections;

            foreach ($sections as $section) {
                $lectureCount = rand(3, 7); // Random number of lectures per section

                for ($i = 1; $i <= $lectureCount; $i++) {
                    Lecture::create([
                        'name' => "Lecture $i",
                        'title' => "Lecture $i for " . $section->title,
                        'description' => "Description for Lecture $i in " . $section->title,
                        'video_url' => 'https://example.com/video' . rand(1, 100) . '.mp4',
                        'duration' => rand(10, 60), // Duration in minutes
                        'order' => $i,
                        'slug' => "lecture-$i-" . \Illuminate\Support\Str::slug($section->title),
                        'course_id' => $course->id,
                        'section_id' => $section->id,
                    ]);
                }
            }
        }
    }
}
