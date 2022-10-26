<?php

namespace Database\Factories\Order;

use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order\RegularOrder>
 */
class RegularOrderFactory extends Factory
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

    public function setPrice(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return ['price' => $value];
        });
    }

    public function setNeedeTime(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return ['needed_time' => $value];
        });
    }

    public function setOrderFK(int $value): static
    {
        return $this->state(function (array $attributes) use ($value) {
            return [(new Order)->getForeignKey() => $value];
        });
    }
}
