<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name'          => ucwords(fake()->words(3, true)),
            'sku'           => 'SKU-' . strtoupper(Str::random(6)),
            'price'         => fake()->randomFloat(2, 2, 30),
            'is_active'     => true,
            'is_available'  => true,
            'prints_on_kot' => true,
            'sort_order'    => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function unavailable(): static
    {
        return $this->state(['is_available' => false]);
    }
}
