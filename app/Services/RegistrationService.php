<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Auth\Events\Registered;

class RegistrationService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function register(array $data): User
    {
        $user = User::query()->create([
            'role' => UserRole::Student,
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'nim' => $data['nim'],
            'campus' => $data['campus'],
            'whatsapp_number' => Phone::normalize($data['whatsapp_number']),
            'password' => $data['password'],
        ]);

        // Triggers the OTP email through User::sendEmailVerificationNotification().
        event(new Registered($user));

        return $user;
    }
}
