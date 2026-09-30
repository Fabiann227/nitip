<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ServiceCategory;
use App\Models\Trip;
use App\Models\User;
use App\Support\Codes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => Codes::order(),
            'requester_id' => User::factory(),
            'fulfiller_id' => null,
            'trip_id' => null,
            'campus' => fn (array $attrs) => User::find($attrs['requester_id'])?->campus ?? 'UPH',
            'service_category_id' => fn () => ServiceCategory::query()->first()?->id ?? ServiceCategory::factory()->foodInCampus()->create()->id,
            'title' => fake()->randomElement(['Nasi Ayam Geprek + Es Teh', 'Kopi Susu Aren 2 cup', 'Roti Bakar Coklat', 'Print Laporan Praktikum']),
            'notes' => fake()->optional()->sentence(),
            'pickup_location' => 'Kantin Gedung '.fake()->randomLetter(),
            'dropoff_location' => 'Lobby Gedung '.fake()->randomLetter(),
            'needed_by' => now()->addHours(2),
            'items' => [['name' => 'Ayam Geprek', 'quantity' => 1, 'estimated_price' => 15000, 'note' => null]],
            'service_fee' => 3500,
            'estimated_item_cost' => 15000,
            'actual_item_cost' => null,
            'status' => OrderStatus::Open,
            'completion_pin' => Codes::pin(),
        ];
    }

    public function claimedBy(User $fulfiller): static
    {
        return $this->state(fn () => [
            'fulfiller_id' => $fulfiller->id,
            'status' => OrderStatus::AwaitingPayment,
            'matched_at' => now(),
        ]);
    }

    public function fromTrip(Trip $trip): static
    {
        return $this->state(fn () => [
            'trip_id' => $trip->id,
            'fulfiller_id' => $trip->fulfiller_id,
            'campus' => $trip->campus,
            'service_category_id' => $trip->service_category_id,
            'service_fee' => $trip->service_fee,
            'status' => OrderStatus::AwaitingPayment,
            'matched_at' => now(),
            'needed_by' => $trip->departure_at,
        ]);
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => OrderStatus::Completed,
            'matched_at' => now()->subHours(3),
            'paid_at' => now()->subHours(2),
            'started_at' => now()->subMinutes(90),
            'delivering_at' => now()->subMinutes(45),
            'delivered_at' => now()->subMinutes(20),
            'completed_at' => now()->subMinutes(10),
            'actual_item_cost' => $attrs['estimated_item_cost'] ?? 15000,
        ]);
    }
}
