<?php

namespace Database\Factories\Auth;

use App\Models\Roles\OperatorRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperatorFactory extends Factory
{
    public function definition()
    {
        return [];
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
                (new OperatorRole)->getForeignKey() => $value,
            ];
        });
    }
}
