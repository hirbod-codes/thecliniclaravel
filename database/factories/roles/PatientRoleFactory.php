<?php

namespace Database\Factories\roles;

use App\Models\roles\PatientRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientRoleFactory extends Factory
{
    public function definition()
    {
        return [];
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
