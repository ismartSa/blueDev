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
        $title = fake()->sentence;
        return [
            'user_id' => User::factory(), // Add this line to associate a user
            'title' => $title,
            'name' => fake()->name,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph,
            'body' => fake()->text,
            'duration' => fake()->numberBetween(1, 60),
            'image' => fake()->imageUrl(),
            'status' => fake()->boolean,
            'intro_video' => fake()->url,
            'price' => fake()->randomFloat(2, 0, 1000), // Add price field
        ];
    }
}
