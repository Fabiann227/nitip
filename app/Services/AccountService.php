<?php

namespace App\Services;

use App\Enums\PaymentMethodType;
use App\Models\User;
use App\Notifications\AppNotification;
use App\Support\Phone;
use Illuminate\Http\UploadedFile;

/**
 * Profile, avatar, payment destination and admin moderation of accounts.
 */
class AccountService
{
    public function __construct(private readonly FileStorageService $files) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null, bool $removeAvatar = false): User
    {
        $user->fill([
            'name' => $data['name'],
            'whatsapp_number' => Phone::normalize($data['whatsapp_number'] ?? null),
            'bio' => $data['bio'] ?? null,
        ]);

        if ($removeAvatar && $user->avatar_path) {
            $this->files->deletePublic($user->avatar_path);
            $user->avatar_path = null;
        }

        if ($avatar) {
            $this->files->deletePublic($user->avatar_path);
            $user->avatar_path = $this->files->storePublic($avatar, 'avatars');
        }

        $user->save();

        return $user;
    }

    /**
     * One payment destination per user (GoPay / OVO / DANA / ShopeePay / bank / QRIS).
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePaymentMethod(User $user, array $data, ?UploadedFile $qris = null): User
    {
        $type = PaymentMethodType::from($data['type']);

        $user->fill([
            'payment_type' => $type,
            'payment_provider' => $type->requiresProviderName() ? ($data['provider_name'] ?? null) : null,
            'payment_account_number' => $type->requiresAccountNumber() ? ($data['account_number'] ?? null) : null,
            'payment_account_name' => $data['account_name'],
        ]);

        if ($qris) {
            $user->payment_qris_path = $this->files->replacePrivate($user->payment_qris_path, $qris, "qris/{$user->id}");
        } elseif ($type !== PaymentMethodType::Qris && $user->payment_qris_path) {
            $this->files->deletePrivate($user->payment_qris_path);
            $user->payment_qris_path = null;
        }

        $user->save();

        return $user;
    }

    public function clearPaymentMethod(User $user): User
    {
        $this->files->deletePrivate($user->payment_qris_path);

        $user->forceFill([
            'payment_type' => null,
            'payment_provider' => null,
            'payment_account_number' => null,
            'payment_account_name' => null,
            'payment_qris_path' => null,
        ])->save();

        return $user;
    }

    public function suspend(User $user, User $admin, string $reason): User
    {
        $user->forceFill([
            'is_suspended' => true,
            'suspended_at' => now(),
            'suspension_reason' => $reason,
        ])->save();

        $user->notify(new AppNotification(
            'Akun ditangguhkan',
            "Akunmu ditangguhkan oleh admin Nitip. Alasan: {$reason}. Hubungi admin untuk banding.",
            null,
            'block',
            'danger',
        ));

        return $user;
    }

    public function unsuspend(User $user, User $admin): User
    {
        $user->forceFill([
            'is_suspended' => false,
            'suspended_at' => null,
            'suspension_reason' => null,
        ])->save();

        $user->notify(new AppNotification(
            'Akun aktif kembali',
            'Penangguhan akunmu sudah dicabut. Selamat kembali ke Nitip!',
            route('dashboard'),
            'check_circle',
            'success',
        ));

        return $user;
    }

    public function verifyManually(User $user, User $admin): User
    {
        if (! $user->hasVerifiedEmail()) {
            $user->forceFill([
                'email_verified_at' => now(),
                'otp_code_hash' => null,
                'otp_expires_at' => null,
                'otp_attempts' => 0,
            ])->save();
        }

        return $user;
    }
}
