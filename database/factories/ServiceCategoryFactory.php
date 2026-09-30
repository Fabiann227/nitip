<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceCategory>
 */
class ServiceCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(2),
            'name' => 'Layanan '.fake()->word(),
            'description' => fake()->sentence(),
            'fee_min' => 3000,
            'fee_default' => 3500,
            'fee_max' => 4000,
            'requires_document' => false,
            'has_item_cost' => true,
            'icon' => 'shopping_bag',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function foodInCampus(): static
    {
        return $this->state(fn () => [
            'code' => 'food_in_campus',
            'name' => 'Makanan Kantin Dalam',
            'icon' => 'restaurant',
            'fee_min' => 3000, 'fee_default' => 3500, 'fee_max' => 4000,
        ]);
    }

    public function print(): static
    {
        return $this->state(fn () => [
            'code' => ServiceCategory::PRINT_CODE,
            'name' => 'Print & Fotokopi Tugas',
            'icon' => 'print',
            'requires_document' => true,
            'fee_min' => 3000, 'fee_default' => 4000, 'fee_max' => 5000,
        ]);
    }
}
