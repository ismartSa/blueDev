<?php

namespace Tests\Feature;

use App\Models\{User, Course};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class ValidationInsertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cannot_create_user_with_duplicate_email()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(QueryException::class);

        User::create([
            'name' => 'مستخدم آخر',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /** @test */
    public function cannot_create_course_without_required_fields()
    {
        $this->expectException(QueryException::class);

        Course::create([
            'description' => 'وصف بدون عنوان',
        ]);
    }

    /** @test */
    public function validates_data_types_and_constraints()
    {
        $course = Course::create([
            'title' => 'دورة تجريبية',
            'description' => 'وصف الدورة',
            'price' => 99.99,
        ]);

        $this->assertIsNumeric($course->price);
        $this->assertIsString($course->title);
        $this->assertNotNull($course->created_at);
    }
}
