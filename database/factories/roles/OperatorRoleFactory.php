<?php

namespace Database\Factories\roles;

use App\Models\roles\OperatorRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class OperatorRoleFactory extends Factory
{
    public function definition()
    {
        return [];
    }

    public function usersForeignKey(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new OperatorRole)->getKeyName() => $value,
            ];
        });
    }

    public function usersRoleNameForeignKey(string $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new OperatorRole)->getUserRoleNameFKColumnName() => $value,
            ];
        });
    }
}
