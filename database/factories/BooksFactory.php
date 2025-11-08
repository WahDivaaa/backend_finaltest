<?php

namespace Database\Factories;

use App\Models\Authors;
use App\Models\Categories;
use Illuminate\Database\Eloquent\Factories\Factory;
use PharIo\Manifest\Author;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\books>
 */
class BooksFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'author_id' => Authors::inRandomOrder()->value('id'),
            'category_id' => Categories::inRandomOrder()->value('id'),
            'isbn' => $this->faker->unique()->isbn13(),
            'publisher' => $this->faker->company(),
            'year' => $this->faker->numberBetween(1950, 2025),
            'status' => $this->faker->randomElement(['available', 'rented', 'reserved']),
            'location' => $this->faker->randomElement([
                'Gianyar', 'Denpasar', 'Badung', 'Bangli', 'Buleleng', 'Jembrana', 'Tabanan'
            ]),
        ];
    }
}
