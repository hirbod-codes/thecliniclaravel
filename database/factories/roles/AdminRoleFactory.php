<?php

namespace Database\Factories\roles;

use App\Models\roles\AdminRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminRoleFactory extends Factory
{
    public function definition()
    {
        return [];
    }

    public function usersForeignKey(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new AdminRole)->getKeyName() => $value,
            ];
        });
    }

    public function usersRoleNameForeignKey(string $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new AdminRole)->getUserRoleNameFKColumnName() => $value,
            ];
        });
    }
}
