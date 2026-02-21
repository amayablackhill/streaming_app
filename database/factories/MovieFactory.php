<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(2),
            'release_year' => $this->faker->year(),  
            'director' => $this->faker->name(),  
            'genre' => $this->faker->word(), 
            'rating' => $this->faker->randomFloat(1, 1, 10),
            'picture' => $this->faker->imageUrl(),
        ];
    }
}
