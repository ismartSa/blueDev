<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\Course;
use App\Models\Section;

class QuizSeeder extends Seeder
{
    public function run()
    {
        // Get all courses
        $courses = Course::all();

        foreach ($courses as $course) {
            // Get all sections for the course
            $sections = $course->sections;

            // Create 2-5 quizzes for each course
            $quizCount = rand(2, 5);
            for ($i = 0; $i < $quizCount; $i++) {
                $quiz = Quiz::create([
                    'title' => "Quiz " . ($i + 1) . " for " . $course->title,
                    'description' => "This is a sample quiz for " . $course->title,
                    'time_limit' => rand(10, 60), // 10 to 60 minutes
                    'passing_score' => rand(60, 80), // 60% to 80%
                    'is_active' => true,
                    'course_id' => $course->id,
                    'section_id' => $sections->isNotEmpty() ? $sections->random()->id : null,
                ]);

                // You can add logic here to create questions for each quiz if needed
            }
        }
    }
}
