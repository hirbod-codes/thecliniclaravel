<?php

namespace Database\Factories\Auth;

use App\Models\User;
use App\Models\Roles\SecretaryRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class SecretaryFactory extends Factory
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
                (new SecretaryRole)->getForeignKey() => $value,
            ];
        });
    }
}
