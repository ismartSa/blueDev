<?php

namespace Tests\Feature;

use App\Models\{User, Course, Enrollment, LectureUserProgress};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentInsertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_enroll_user_in_course()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $enrollmentData = [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ];

        $enrollment = Enrollment::create($enrollmentData);

        $this->assertDatabaseHas('enrollments', $enrollmentData);
        $this->assertEquals($user->id, $enrollment->user_id);
        $this->assertEquals($course->id, $enrollment->course_id);
    }

    /** @test */
    public function can_track_lecture_progress()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lecture = \App\Models\Lecture::factory()->create(['course_id' => $course->id]);

        $progressData = [
            'user_id' => $user->id,
            'lecture_id' => $lecture->id,
            'completed' => true,
            'completed_at' => now(),
        ];

        $progress = LectureUserProgress::create($progressData);

        $this->assertDatabaseHas('lecture_user_progress', $progressData);
        $this->assertTrue($progress->completed);
    }

    /** @test */
    public function can_bulk_enroll_users()
    {
        $users = User::factory()->count(10)->create();
        $course = Course::factory()->create();

        foreach ($users as $user) {
            Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'enrolled_at' => now(),
            ]);
        }

        $this->assertEquals(10, Enrollment::where('course_id', $course->id)->count());
    }
}
