<?php

namespace Database\Factories\Auth;

use App\Models\Auth\Operator;
use App\Models\User;
use App\Models\Roles\PatientRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
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

    public function userFK(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new User)->getForeignKey() => $value,
            ];
        });
    }

    public function roleFK(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new PatientRole)->getForeignKey() => $value,
            ];
        });
    }

    public function operatorFK(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new Operator)->getKeyName() => $value,
            ];
        });
    }
}
