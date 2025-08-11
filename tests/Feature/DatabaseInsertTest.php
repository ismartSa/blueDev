<?php

namespace Tests\Feature;

use App\Models\{User, Course, Section, Quiz, Question, Answer, Lecture};
use Tests\TestCase;

class DatabaseInsertTest extends TestCase
{
    /** @test */
    public function can_insert_user_data()
    {
        $user = User::create([
            'name' => 'أحمد محمد',
            'email' => 'ahmed' . time() . '@test.com',
            'password' => bcrypt('password')
        ]);

        $this->assertDatabaseHas('users', ['name' => 'أحمد محمد']);
    }

    /** @test */
    public function can_insert_course_with_sections()
    {
        $user = User::factory()->create();

        $course = Course::create([
            'title' => 'دورة تجريبية ' . time(),
            'name' => 'course-test-' . time(), // Add missing name field
            'description' => 'وصف الدورة',
            'body ' => 'course-test-'. time(), // Add missing name field
            'price' => 199.99,
            'user_id' => $user->id
        ]);

        $section = Section::create([
            'title' => 'القسم الأول',
            'course_id' => $course->id,
            'order' => 1
        ]);

        $this->assertDatabaseHas('courses', ['id' => $course->id]);
        $this->assertDatabaseHas('sections', ['id' => $section->id]);
    }

    /** @test */
    public function can_insert_quiz_with_questions()
    {
        $course = Course::factory()->create();
        $section = Section::factory()->create(['course_id' => $course->id]);

        $quiz = Quiz::create([
            'title' => 'اختبار تجريبي',
            'description' => 'وصف الاختبار',
            'time_limit' => 60,
            'passing_score' => 70,
            'course_id' => $course->id,
            'section_id' => $section->id
        ]);

        $question = Question::create([
            'question_title' => 'ما هي عاصمة مصر؟',
            'question_type_id' => 1,
            'correct_answers_required' => 1,
            'quiz_id' => $quiz->id
        ]);

        Answer::create([
            'answer' => 'القاهرة',
            'is_correct' => true,
            'question_id' => $question->id
        ]);

        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id]);
        $this->assertDatabaseHas('questions', ['id' => $question->id]);
    }

    /** @test */
    public function can_insert_lecture_data()
    {
        $course = Course::factory()->create();
        $section = Section::factory()->create(['course_id' => $course->id]);

        $lecture = Lecture::create([
            'name' => 'محاضرة-تجريبية',
            'title' => 'محاضرة تجريبية',
            'description' => 'وصف المحاضرة',
            'course_id' => $course->id,
            'section_id' => $section->id,
            'order' => 1
        ]);

        $this->assertDatabaseHas('lectures', ['id' => $lecture->id]);
    }

    /** @test */
    public function can_bulk_insert_data()
    {
        $user = User::factory()->create();
        $courses = [];

        for ($i = 1; $i <= 3; $i++) {
            $courses[] = Course::create([
                'title' => "دورة رقم {$i}",
                'name' => "course-{$i}-" . time(), // Add missing name field
                'description' => "وصف الدورة {$i}",
                'price' => 100 * $i,
                'user_id' => $user->id
            ]);
        }

        $this->assertEquals(3, count($courses));
        $this->assertDatabaseHas('courses', ['title' => 'دورة رقم 1']);
    }

    /** @test */
    public function validates_unique_constraints()
    {
        $email = 'unique' . time() . '@test.com';

        $user1 = User::create([
            'name' => 'مستخدم أول',
            'email' => $email,
            'password' => bcrypt('password')
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::create([
            'name' => 'مستخدم ثاني',
            'email' => $email,
            'password' => bcrypt('password')
        ]);
    }

    /** @test */
    public function preserves_existing_data()
    {
        $initialCount = User::count();

        User::create([
            'name' => 'مستخدم جديد',
            'email' => 'new' . time() . '@test.com',
            'password' => bcrypt('password')
        ]);

        $this->assertEquals($initialCount + 1, User::count());
    }
}
