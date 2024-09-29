<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\User;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_displays_quizzes()
    {
        $quiz = Quiz::factory()->create();

        $response = $this->actingAs($this->user)->get(route('quizzes.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Quizzes/Index')
            ->has('quizzes.data', 1)
            ->where('quizzes.data.0.id', $quiz->id)
        );
    }

    public function test_create_displays_form()
    {
        $response = $this->actingAs($this->user)->get(route('quizzes.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Quizzes/Create')
            ->has('courses')
        );
    }

    public function test_store_creates_new_quiz()
    {
        $course = Course::factory()->create();
        $section = Section::factory()->create(['course_id' => $course->id]);

        $quizData = [
            'title' => 'Test Quiz',
            'description' => 'This is a test quiz',
            'time_limit' => 30,
            'passing_score' => 70,
            'is_active' => true,
            'course_id' => $course->id,
            'section_id' => $section->id,
        ];

        $response = $this->actingAs($this->user)->post(route('quizzes.store'), $quizData);

        $response->assertRedirect(route('quizzes.show', Quiz::first()));
        $this->assertDatabaseHas('quizzes', $quizData);
    }

    public function test_show_displays_quiz()
    {
        $quiz = Quiz::factory()->create();

        $response = $this->actingAs($this->user)->get(route('quizzes.show', $quiz));

        $response->assertStatus(200);
        $response->assertInertia(fn ($assert) => $assert
            ->component('Quizzes/Show')
            ->where('quiz.id', $quiz->id)
        );
    }

    public function test_destroy_deletes_quiz()
    {
        $quiz = Quiz::factory()->create();

        $response = $this->actingAs($this->user)->delete(route('quizzes.destroy', $quiz));

        $response->assertRedirect(route('quizzes.index'));
        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
    }
}
