<?php

namespace Database\Factories;

use App\Models\AddonOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddonOptionFactory extends Factory
{
    protected $model = AddonOption::class;

    public function definition(): array
    {
        return [
            'name'        => ucfirst(fake()->word()),
            'extra_price' => fake()->randomFloat(2, 0, 3),
            'sort_order'  => 0,
        ];
    }
}
