<?php

namespace Tests;

use App\Enums\UserRole;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\ServiceCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->seed(ServiceCategorySeeder::class);
    }

    protected function category(string $code = 'food_in_campus'): ServiceCategory
    {
        return ServiceCategory::query()->where('code', $code)->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function student(array $attributes = [], bool $withPaymentMethod = true): User
    {
        $factory = User::factory();

        if ($withPaymentMethod) {
            $factory = $factory->withPaymentMethod();
        }

        return $factory->create($attributes);
    }

    protected function admin(): User
    {
        return User::factory()->admin()->create(['role' => UserRole::Admin]);
    }

    /**
     * Valid payload for posting a food request.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function requestPayload(array $overrides = []): array
    {
        return array_replace([
            'service_category_id' => $this->category()->id,
            'title' => 'Ayam Geprek + Es Teh',
            'notes' => 'Pedas sedang',
            'pickup_location' => 'Food Court UPH (Gedung B Lt. 1)',
            'dropoff_location' => 'Gedung D Lt. 5',
            'needed_by' => now()->addHours(2)->format('Y-m-d H:i'),
            'service_fee' => 3500,
            'items' => [
                ['name' => 'Ayam Geprek', 'quantity' => 1, 'estimated_price' => 18000, 'note' => null],
                ['name' => 'Es Teh', 'quantity' => 2, 'estimated_price' => 5000, 'note' => 'Less sugar'],
            ],
        ], $overrides);
    }

    /**
     * Valid payload for posting a print request (document must be added by the caller).
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function printPayload(array $overrides = []): array
    {
        return $this->requestPayload(array_replace([
            'service_category_id' => $this->category('print_copy')->id,
            'title' => 'Print Makalah Etika',
            'service_fee' => 4000,
            'estimated_item_cost' => 20000,
            'print' => ['pages' => 20, 'copies' => 1, 'paper_size' => 'A4', 'binding' => 'staple'],
            'items' => [],
        ], $overrides));
    }
}
