<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Models\Section;
use Tests\TestCase;

class CourseLectureTest extends TestCase
{
    /** @test */
    public function an_authorized_user_can_create_a_lecture_for_a_course()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $section = Section::factory()->create(['course_id' => $course->id]);

        $lectureData = [
            'name' => 'new-test-lecture',
            'title' => 'New Test Lecture',
            'description' => 'This is a test lecture description.',
            'video_url' => 'https://example.com/video.mp4',
            'duration' => 1800,
            'order' => 1,
            'section_id' => $section->id,
        ];

        $response = $this->actingAs($user)
                         ->post(route('courses.lecture.store', ['course' => $course->id]), $lectureData);

        $this->assertDatabaseHas('lectures', [
            'name' => 'new-test-lecture',
            'title' => 'New Test Lecture',
            'description' => 'This is a test lecture description.',
            'video_url' => 'https://example.com/video.mp4',
            'duration' => 1800,
            'order' => 1,
            'course_id' => $course->id,
            'section_id' => $section->id,
        ]);

        $response->assertStatus(302);
    }
}
