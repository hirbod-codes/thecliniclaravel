<?php

namespace Database\Factories;

use App\Models\Rule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class SecretaryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            //
        ];
    }

    public function fk(int $fk): static
    {
        $fkName = strtolower(class_basename(Rule::class)) . '_' . (new Rule)->getKey();

        return $this->state([$fkName => $fk]);
    }
}
