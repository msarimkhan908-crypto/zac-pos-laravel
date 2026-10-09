<?php

namespace Database\Factories;

use App\Models\AddonGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddonGroupFactory extends Factory
{
    protected $model = AddonGroup::class;

    public function definition(): array
    {
        return [
            'name'           => ucwords(fake()->words(2, true)),
            'min_selectable' => 0,
            'max_selectable' => fake()->numberBetween(1, 3),
            'is_required'    => false,
        ];
    }

    public function required(): static
    {
        return $this->state(['min_selectable' => 1, 'is_required' => true]);
    }
}
