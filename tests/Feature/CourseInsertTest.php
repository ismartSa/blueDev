<?php

namespace Tests\Feature;

use App\Models\{Course, Section, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseInsertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_course_with_sections()
    {
        $user = User::factory()->create();

        $courseData = [
            'title' => 'دورة البرمجة المتقدمة',
            'description' => 'دورة شاملة في البرمجة',
            'price' => 299.99,
        ];

        $course = Course::create($courseData);

        $sectionData = [
            'title' => 'القسم الأول',
            'description' => 'مقدمة في البرمجة',
            'course_id' => $course->id,
            'order' => 1,
        ];

        $section = Section::create($sectionData);

        $this->assertDatabaseHas('courses', $courseData);
        $this->assertDatabaseHas('sections', $sectionData);
        $this->assertEquals($course->id, $section->course_id);
    }

    /** @test */
    public function can_bulk_insert_courses()
    {
        $coursesData = [
            ['title' => 'دورة 1', 'description' => 'وصف 1', 'price' => 100],
            ['title' => 'دورة 2', 'description' => 'وصف 2', 'price' => 200],
            ['title' => 'دورة 3', 'description' => 'وصف 3', 'price' => 300],
        ];

        foreach ($coursesData as $data) {
            Course::create($data);
        }

        $this->assertEquals(3, Course::count());
        $this->assertDatabaseHas('courses', ['title' => 'دورة 1']);
        $this->assertDatabaseHas('courses', ['title' => 'دورة 2']);
        $this->assertDatabaseHas('courses', ['title' => 'دورة 3']);
    }
}
