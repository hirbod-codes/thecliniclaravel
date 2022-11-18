<?php

namespace Database\Factories\Order;

use App\Models\Order\LaserOrder;
use App\Models\Part\Part;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order\LaserOrderPart>
 */
class LaserOrderPartFactory extends Factory
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

    public function setPartFK(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [(new Part)->getForeignKey() => $value];
        });
    }
}
