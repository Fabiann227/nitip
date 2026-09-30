<?php

namespace Database\Factories;

use App\Enums\PaymentMethodType;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $name = fake()->name();

        return [
            'role' => UserRole::Student,
            'name' => $name,
            'email' => Str::slug($name, '.').fake()->unique()->numberBetween(1, 99999).'@student.uph.edu',
            'nim' => (string) fake()->unique()->numberBetween(10000000, 99999999),
            'campus' => 'UPH',
            'whatsapp_number' => '628'.fake()->numerify('##########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'bio' => fake()->optional(0.5)->sentence(8),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::Admin,
            'campus' => null,
            'nim' => null,
            'email' => 'admin'.fake()->unique()->numberBetween(1, 9999).'@nitip.test',
        ]);
    }

    public function suspended(string $reason = 'Pelanggaran ketentuan layanan'): static
    {
        return $this->state(fn () => [
            'is_suspended' => true,
            'suspended_at' => now(),
            'suspension_reason' => $reason,
        ]);
    }

    public function withPaymentMethod(PaymentMethodType $type = PaymentMethodType::Gopay): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_type' => $type,
            'payment_provider' => $type === PaymentMethodType::BankTransfer ? 'BCA' : null,
            'payment_account_number' => $type === PaymentMethodType::Qris ? null : '08'.fake()->numerify('##########'),
            'payment_account_name' => $attributes['name'] ?? fake()->name(),
        ]);
    }
}
