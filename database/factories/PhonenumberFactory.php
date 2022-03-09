<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Phonenumber>
 */
class PhonenumberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            strtolower(class_basename(Phonenumber::class)) => $this->faker->unique()->phoneNumber(),
            strtolower(class_basename(Phonenumber::class)) . '_verified_at' => now(new \DateTimeZone('UTC')),
        ];
    }
}
