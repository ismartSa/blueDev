<?php

namespace Database\Factories;

use App\Models\User; // Add this line
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
            'user_id' => User::factory(), // Add this line to associate a user
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
