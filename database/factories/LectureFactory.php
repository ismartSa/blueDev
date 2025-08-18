<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lecture>
 */
class LectureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

                'uuid' => fake()->uuid,
                'name' => fake()->sentence,
                'title' => fake()->sentence,
                'course_id' => \App\Models\Course::inRandomOrder()->first()->id,
                'section_id' => \App\Models\Section::inRandomOrder()->first()->id,
                'description' => fake()->paragraph,
                'video_url' => fake()->url,
                'duration' => fake()->numberBetween(10, 60),
                'order' => fake()->numberBetween(1, 10),

        ];
    }
}
