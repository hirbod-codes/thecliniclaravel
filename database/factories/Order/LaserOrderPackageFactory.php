<?php

namespace Database\Factories\Order;

use App\Models\Order\LaserOrder;
use App\Models\Package\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order\LaserOrderPackage>
 */
class LaserOrderPackageFactory extends Factory
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

    public function setLaserOrderFK(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [(new LaserOrder)->getForeignKey() => $value];
        });
    }

    public function setPackageFK(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [(new Package)->getForeignKey() => $value];
        });
    }
}
