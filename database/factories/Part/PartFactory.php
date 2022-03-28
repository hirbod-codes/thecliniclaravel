<?php

namespace Database\Factories\Part;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Part\Part>
 */
class PartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->lexify(),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'price' => $this->faker->numberBetween(1000000, 3000000),
            'needed_time' => $this->faker->numberBetween(600, 2700),
        ];
    }
}
