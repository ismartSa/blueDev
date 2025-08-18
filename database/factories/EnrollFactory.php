<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enrollment>
 */
class EnrollFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'confirmed', 'cancelled'];

        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'enrollment_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'enrollment_status' => fake()->randomElement($statuses),
            'completion_date' => fake()->optional(0.3)->dateTimeBetween('-6 months', 'now'),
            'progress_percentage' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * تعيين حالة التسجيل إلى مؤكد
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'enrollment_status' => 'confirmed',
        ]);
    }

    /**
     * تعيين حالة التسجيل إلى مكتمل
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'enrollment_status' => 'confirmed',
            'progress_percentage' => 100,
            'completion_date' => fake()->dateTimeBetween('-3 months', 'now'),
        ]);
    }
}
