<?php

namespace Tests\Feature;

use App\Models\{Quiz, Question, Answer, Course, Section, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizInsertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_quiz_with_questions_and_answers()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $section = Section::factory()->create(['course_id' => $course->id]);

        $quizData = [
            'title' => 'اختبار البرمجة',
            'description' => 'اختبار شامل في البرمجة',
            'time_limit' => 60,
            'passing_score' => 70,
            'course_id' => $course->id,
            'section_id' => $section->id,
            'is_active' => true,
        ];

        $quiz = Quiz::create($quizData);

        $questionData = [
            'question_title' => 'ما هي لغة البرمجة الأفضل؟',
            'question_type_id' => 1,
            'correct_answers_required' => 1,
            'quiz_id' => $quiz->id,
        ];

        $question = Question::create($questionData);

        $answersData = [
            ['answer' => 'PHP', 'is_correct' => true, 'question_id' => $question->id],
            ['answer' => 'Java', 'is_correct' => false, 'question_id' => $question->id],
            ['answer' => 'Python', 'is_correct' => false, 'question_id' => $question->id],
        ];

        foreach ($answersData as $answerData) {
            Answer::create($answerData);
        }

        $this->assertDatabaseHas('quizzes', $quizData);
        $this->assertDatabaseHas('questions', $questionData);
        $this->assertEquals(3, Answer::where('question_id', $question->id)->count());
        $this->assertEquals(1, Answer::where('question_id', $question->id)->where('is_correct', true)->count());
    }

    /** @test */
    public function can_import_multiple_questions_with_answers()
    {
        $quiz = Quiz::factory()->create();

        $questionsData = [
            [
                'question_title' => 'السؤال الأول',
                'question_type_id' => 1,
                'correct_answers_required' => 1,
                'quiz_id' => $quiz->id,
                'answers' => [
                    ['answer' => 'الإجابة الصحيحة', 'is_correct' => true],
                    ['answer' => 'إجابة خاطئة 1', 'is_correct' => false],
                    ['answer' => 'إجابة خاطئة 2', 'is_correct' => false],
                ]
            ],
            [
                'question_title' => 'السؤال الثاني',
                'question_type_id' => 2,
                'correct_answers_required' => 2,
                'quiz_id' => $quiz->id,
                'answers' => [
                    ['answer' => 'إجابة صحيحة 1', 'is_correct' => true],
                    ['answer' => 'إجابة صحيحة 2', 'is_correct' => true],
                    ['answer' => 'إجابة خاطئة', 'is_correct' => false],
                ]
            ]
        ];

        foreach ($questionsData as $questionData) {
            $answers = $questionData['answers'];
            unset($questionData['answers']);

            $question = Question::create($questionData);

            foreach ($answers as $answerData) {
                $answerData['question_id'] = $question->id;
                Answer::create($answerData);
            }
        }

        $this->assertEquals(2, Question::where('quiz_id', $quiz->id)->count());
        $this->assertEquals(6, Answer::count());
    }
}
