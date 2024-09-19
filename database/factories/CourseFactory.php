<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'name' => $this->faker->name,
            'slug' => $this->faker->slug,
            'description' => $this->faker->paragraph,
            'body' => $this->faker->text,
            'duration' => $this->faker->numberBetween(1, 60), // Adjust range as needed
            'image' => $this->faker->imageUrl(), // Generates a placeholder image URL
            'status' => $this->faker->boolean,
            'intro_video' => $this->faker->url, // Generates a placeholder video URL
        ];
    }
}
