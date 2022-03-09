<?php

namespace Database\Factories;

use App\Models\Email;
use App\Models\Phonenumber;
use App\Models\Rule;
use App\Models\Username;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $gender = $this->faker->randomElement(['Male', 'Female']);

        return [
            'firstname' => $this->faker->firstname($gender),
            'lastname' => $this->faker->lastname($gender),
            strtolower(class_basename(Username::class)) => $this->faker->unique()->username(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password

            'gender' => $gender,

            strtolower(class_basename(Email::class)) => $this->faker->unique()->safeEmail(),
            strtolower(class_basename(Email::class)) . '_verified_at' => now(new \DateTimeZone('UTC')),

            strtolower(class_basename(Phonenumber::class)) => $this->faker->unique()->phoneNumber(),
            strtolower(class_basename(Phonenumber::class)) . '_verified_at' => now(new \DateTimeZone('UTC')),

            'remember_token' => Str::random(10),
        ];
    }

    public function emailUnverified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                strtolower(class_basename(Email::class)) . '_verified_at' => null,
            ];
        });
    }
}
