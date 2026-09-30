<?php

namespace Database\Factories;

use App\Enums\DisputeReason;
use App\Enums\DisputeStatus;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dispute>
 */
class DisputeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'opened_by' => User::factory(),
            'reason' => DisputeReason::ReceiptMismatch,
            'description' => fake()->paragraph(),
            'evidence_path' => null,
            'status' => DisputeStatus::Open,
        ];
    }
}
