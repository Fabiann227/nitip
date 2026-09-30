<?php

namespace Database\Factories;

use App\Enums\TransportMode;
use App\Enums\TripStatus;
use App\Models\ServiceCategory;
use App\Models\Trip;
use App\Models\User;
use App\Support\Codes;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    public function definition(): array
    {
        $departure = now()->addHours(fake()->numberBetween(1, 6));

        return [
            'code' => Codes::trip(),
            'fulfiller_id' => User::factory()->withPaymentMethod(),
            'service_category_id' => fn () => ServiceCategory::query()->first()?->id ?? ServiceCategory::factory()->foodInCampus()->create()->id,
            'campus' => fn (array $attrs) => User::find($attrs['fulfiller_id'])?->campus ?? 'UPH',
            'destination' => 'Kantin '.fake()->randomLetter(),
            'waypoints' => null,
            'departure_at' => $departure,
            'closes_at' => $departure->copy()->subMinutes(15),
            'transport_mode' => TransportMode::Walk,
            'max_slots' => 3,
            'service_fee' => 3500,
            'notes' => fake()->optional()->sentence(),
            'status' => TripStatus::Open,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => TripStatus::Closed, 'closed_at' => now()]);
    }
}
