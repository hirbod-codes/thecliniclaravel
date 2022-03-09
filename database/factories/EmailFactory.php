<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Email>
 */
class EmailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            strtolower(class_basename(Email::class)) => $this->faker->unique()->safeEmail(),
            strtolower(class_basename(Email::class)) . '_verified_at' => now(new \DateTimeZone('UTC')),
        ];
    }
}
