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

                'uuid' => $this->faker->uuid,
                'name' => $this->faker->sentence,
                'title' => $this->faker->sentence,
                'course_id' => \App\Models\Course::inRandomOrder()->first()->id,
                'section_id' => \App\Models\Section::inRandomOrder()->first()->id,
                'description' => $this->faker->paragraph,
                'video_url' => $this->faker->url,
                'duration' => $this->faker->numberBetween(10, 60),
                'order' => $this->faker->numberBetween(1, 10),

        ];
    }
}
