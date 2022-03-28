<?php

namespace Database\Factories\Package;

use App\Models\Package\Package;
use App\Models\Part\Part;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package\PartPackage>
 */
class PartPackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [];
    }

    public function setPartFK(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new Part)->getForeignKey() => $value
            ];
        });
    }

    public function setPackageFK(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [
                (new Package)->getForeignKey() => $value
            ];
        });
    }
}
