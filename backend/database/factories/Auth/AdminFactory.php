<?php

namespace Database\Factories\Auth;

use App\Models\Auth\Admin;
use App\Models\Roles\AdminRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminFactory extends Factory
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
                (new AdminRole)->getForeignKey() => $value,
            ];
        });
    }
}
