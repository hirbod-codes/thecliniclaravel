<?php

namespace Database\Factories;

use App\Models\Role;
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

        if ($this->faker->randomElement([0, 1]) === 1) {
            $email = $this->faker->unique()->phoneNumber();
            $email_verified_at = new \DateTime('now', new \DateTimeZone('UTC'));
        } else {
            $email = null;
            $email_verified_at = null;
        }

        return [
            'firstname' => $this->faker->firstname($gender),
            'lastname' => $this->faker->lastname($gender),
            'username' => $this->faker->unique()->username(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password

            'gender' => $gender,

            'email' => $email,
            'email_verified_at' => $email_verified_at,

            'phonenumber' => $this->faker->unique()->phoneNumber(),
            'phonenumber_verified_at' => new \DateTime('now', new \DateTimeZone('UTC')),

            'remember_token' => Str::random(10),
        ];
    }

    public function emailUnverified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    public function usersRolesForeignKey(string $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new Role)->getForeignKeyForName() => $value,
            ];
        });
    }
}
