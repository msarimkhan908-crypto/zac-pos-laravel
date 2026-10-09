<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'name'       => fake()->randomElement(['Small', 'Medium', 'Large', 'Regular', 'Double']),
            'price'      => fake()->randomFloat(2, 2, 20),
            'is_default' => false,
            'sort_order' => 0,
        ];
    }

    public function default(): static
    {
        return $this->state(['is_default' => true]);
    }
}
