<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $course = Course::inRandomOrder()->first() ?? Course::factory()->create();
        $section = Section::where('course_id', $course->id)->inRandomOrder()->first()
                  ?? Section::factory()->create(['course_id' => $course->id]);

        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'time_limit' => $this->faker->numberBetween(10, 60),
            'passing_score' => $this->faker->numberBetween(60, 80),
            'is_active' => $this->faker->boolean(80),
            'course_id' => $course->id,
            'section_id' => $section->id,
        ];
    }

    /**
     * تعيين الاختبار كنشط
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}
