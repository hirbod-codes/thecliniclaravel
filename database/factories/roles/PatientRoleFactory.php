<?php

namespace Database\Factories\roles;

use App\Models\roles\PatientRole;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientRoleFactory extends Factory
{
    public function definition()
    {
        return [
            'age' => $this->faker->numberBetween(16, 70),
            'state' => $this->faker->state(),
            'city' => $this->faker->city(),
            'address' => $this->faker->address(),
            'laser_grade' => $this->faker->numerify() . '/' . $this->faker->numerify(),
        ];
    }

    public function usersForeignKey(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new PatientRole)->getKeyName() => $value,
            ];
        });
    }

    public function usersRoleNameForeignKey(string $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new PatientRole)->getUserRoleNameFKColumnName() => $value,
            ];
        });
    }
}
