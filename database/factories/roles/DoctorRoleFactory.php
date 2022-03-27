<?php

namespace Database\Factories\roles;

use App\Models\roles\DoctorRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorRoleFactory extends Factory
{
    public function definition()
    {
        return [];
    }

    public function usersForeignKey(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new DoctorRole)->getKeyName() => $value,
            ];
        });
    }

    public function usersRoleNameForeignKey(string $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new DoctorRole)->getUserRoleNameFKColumnName() => $value,
            ];
        });
    }
}
