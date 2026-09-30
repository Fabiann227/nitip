<?php

namespace App\Models;

use App\Enums\PaymentMethodType;
use App\Enums\UserRole;
use App\Notifications\ResetPasswordNotification;
use App\Services\EmailVerificationService;
use App\Support\Campuses;
use App\Support\Phone;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'role', 'name', 'email', 'nim', 'campus', 'whatsapp_number', 'password', 'avatar_path', 'bio',
    'payment_type', 'payment_provider', 'payment_account_number', 'payment_account_name', 'payment_qris_path',
])]
#[Hidden(['password', 'remember_token', 'otp_code_hash'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'payment_type' => PaymentMethodType::class,
            'otp_expires_at' => 'datetime',
            'otp_sent_at' => 'datetime',
            'otp_attempts' => 'integer',
            'is_suspended' => 'boolean',
            'suspended_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'fulfiller_id');
    }

    public function requestedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'requester_id');
    }

    public function fulfilledOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'fulfiller_id');
    }

    public function reviewsReceived(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function reviewsGiven(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeStudents(Builder $query): Builder
    {
        return $query->where('role', UserRole::Student->value);
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', UserRole::Admin->value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_suspended', false);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('nim', 'like', "%{$term}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function isSuspended(): bool
    {
        return (bool) $this->is_suspended;
    }

    public function campusName(): string
    {
        return Campuses::name($this->campus);
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->name)) ?: [];
        $letters = array_map(fn (string $w) => Str::upper(Str::substr($w, 0, 1)), array_slice($words, 0, 2));

        return implode('', $letters) ?: 'N';
    }

    /**
     * "Sarah Tanaka" => "Sarah T."
     */
    public function shortName(): string
    {
        $words = preg_split('/\s+/', trim($this->name)) ?: [];

        if (count($words) <= 1) {
            return $this->name;
        }

        return $words[0].' '.Str::upper(Str::substr(end($words), 0, 1)).'.';
    }

    public function avatarUrl(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        return Storage::disk(config('nitip.uploads.public_disk'))->url($this->avatar_path);
    }

    public function whatsappUrl(?string $text = null): ?string
    {
        return Phone::whatsappUrl($this->whatsapp_number, $text);
    }

    public function whatsappPretty(): string
    {
        return Phone::pretty($this->whatsapp_number);
    }

    /*
    |--------------------------------------------------------------------------
    | Payment destination (direct P2P)
    |--------------------------------------------------------------------------
    */

    public function hasPaymentMethod(): bool
    {
        return $this->payment_type !== null && $this->payment_account_name !== null;
    }

    public function paymentTitle(): string
    {
        if (! $this->payment_type) {
            return '-';
        }

        return $this->payment_type === PaymentMethodType::BankTransfer && $this->payment_provider
            ? "Bank {$this->payment_provider}"
            : $this->payment_type->label();
    }

    /**
     * "GoPay · 0812... · a.n. Nama"
     */
    public function paymentSummary(): string
    {
        if (! $this->hasPaymentMethod()) {
            return 'Belum diatur';
        }

        $parts = [$this->paymentTitle()];

        if ($this->payment_account_number) {
            $parts[] = $this->payment_account_number;
        }

        $parts[] = "a.n. {$this->payment_account_name}";

        return implode(' · ', $parts);
    }

    public function hasQris(): bool
    {
        return $this->payment_qris_path !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | Verification & ratings
    |--------------------------------------------------------------------------
    */

    /**
     * Send OTP email instead of Laravel's signed verification link.
     */
    public function sendEmailVerificationNotification(): void
    {
        app(EmailVerificationService::class)->send($this);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function ratingAverage(): ?float
    {
        if (array_key_exists('reviews_received_avg_rating', $this->attributes)) {
            $avg = $this->attributes['reviews_received_avg_rating'];

            return $avg === null ? null : round((float) $avg, 2);
        }

        $avg = $this->reviewsReceived()->avg('rating');

        return $avg === null ? null : round((float) $avg, 2);
    }

    public function ratingCount(): int
    {
        if (array_key_exists('reviews_received_count', $this->attributes)) {
            return (int) $this->attributes['reviews_received_count'];
        }

        return $this->reviewsReceived()->count();
    }

    public function completedDeliveriesCount(): int
    {
        if (array_key_exists('completed_deliveries_count', $this->attributes)) {
            return (int) $this->attributes['completed_deliveries_count'];
        }

        return $this->fulfilledOrders()->where('status', 'completed')->count();
    }
}
