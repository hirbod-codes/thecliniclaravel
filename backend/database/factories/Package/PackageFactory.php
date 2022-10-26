<?php

namespace Database\Factories\Package;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package\Package>
 */
class PackageFactory extends Factory
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
        ];
    }

    public function setPrice(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return ['price' => $value];
        });
    }

    public function setGender(string $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return ['gender' => $value];
        });
    }
}
