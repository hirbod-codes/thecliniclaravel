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
            'phonenumber' => $this->faker->unique()->phoneNumber(),
            'phonenumber_verified_at' => now(new \DateTimeZone('UTC')),
        ];
    }
}
