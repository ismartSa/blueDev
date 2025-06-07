<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Models\Section; // Assuming you might need a section to associate the lecture
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseLectureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_authorized_user_can_create_a_lecture_for_a_course()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $section = Section::factory()->create(['course_id' => $course->id]);

        $lectureData = [
            'title' => 'New Test Lecture',
            'description' => 'This is a test lecture description.',
            'section_id' => $section->id,
        ];

        // Fix: Use correct route name (plural 'lectures')
        $response = $this->actingAs($user)
                         ->post(route('courses.lecture.store', ['course' => $course->id]), $lectureData);

        // Assert: Check for expected outcomes
        // 1. Check if the lecture was created in the database
        $this->assertDatabaseHas('lectures', [
            'title' => 'New Test Lecture',
            'course_id' => $course->id, // Or section_id if that's how it's structured
        ]);

        // 2. Check for a successful redirect (adjust if your controller returns something else)
        // For example, if it redirects back to the course edit page or a success page.
        // $response->assertRedirect(route('courses.edit', ['course' => $course->id]));
        // Or, if it returns a JSON response with the new lecture:
        // $response->assertStatus(201); // HTTP 201 Created
        // $response->assertJsonFragment(['title' => 'New Test Lecture']);

        // For this example, let's assume a redirect to the course details page
        // You'll need to adjust this to match your actual application flow.
        // If it redirects, it's often a 302 status.
        $response->assertStatus(302); // Or whatever your successful response status is

        // You can also assert that a session flash message is set, if applicable
        // $response->assertSessionHas('success', 'Lecture created successfully.');
    }
}
