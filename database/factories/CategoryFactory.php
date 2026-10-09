<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        return [
            'name'        => ucwords($name),
            'slug'        => Str::slug($name),
            'description' => fake()->optional(0.6)->sentence(),
            'color'       => fake()->optional(0.5)->hexColor(),
            'sort_order'  => fake()->numberBetween(0, 100),
            'is_active'   => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
