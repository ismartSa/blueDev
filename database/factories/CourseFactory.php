<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $title = $this->faker->sentence;
        return [
            'title' => $title,
            'name' => $this->faker->name,
            'slug' => Str::slug($title),
            'description' => $this->faker->paragraph,
            'body' => $this->faker->text,
            'duration' => $this->faker->numberBetween(1, 60),
            'image' => $this->faker->imageUrl(),
            'status' => $this->faker->boolean,
            'intro_video' => $this->faker->url,
            'price' => $this->faker->randomFloat(2, 0, 1000), // Add price field
        ];
    }
}
