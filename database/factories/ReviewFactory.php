<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'reviewer_id' => User::factory(),
            'reviewee_id' => User::factory(),
            'rating' => fake()->numberBetween(4, 5),
            'comment' => fake()->optional()->randomElement(['Cepat dan ramah!', 'Pesanan sesuai, makasih ya.', 'Top, recommended.']),
        ];
    }
}
