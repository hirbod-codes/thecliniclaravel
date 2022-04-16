<?php

namespace Database\Factories\roles;

use App\Models\roles\CustomRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomRole>
 */
class CustomRoleFactory extends Factory
{
    public function definition()
    {
        return [];
    }

    public function usersForeignKey(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new CustomRole)->getKeyName() => $value,
            ];
        });
    }

    public function usersRoleNameForeignKey(string $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new CustomRole)->getUserRoleNameFKColumnName() => $value,
            ];
        });
    }
}
