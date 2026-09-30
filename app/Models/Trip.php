<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\TransportMode;
use App\Enums\TripStatus;
use App\Support\Campuses;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code', 'fulfiller_id', 'service_category_id', 'campus', 'destination', 'waypoints', 'departure_at', 'closes_at',
    'transport_mode', 'max_slots', 'service_fee', 'notes', 'status', 'closed_at', 'cancelled_at', 'cancel_reason',
])]
class Trip extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'departure_at' => 'datetime',
            'closes_at' => 'datetime',
            'closed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'status' => TripStatus::class,
            'transport_mode' => TransportMode::class,
            'max_slots' => 'integer',
            'service_fee' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function fulfiller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfiller_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function activeOrders(): HasMany
    {
        return $this->hasMany(Order::class)->whereIn(
            'status',
            array_map(fn (OrderStatus $s) => $s->value, OrderStatus::occupyingSlot())
        );
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', TripStatus::Open->value)->where('closes_at', '>', now());
    }

    public function scopeForCampus(Builder $query, ?string $campus): Builder
    {
        return $campus ? $query->where('campus', $campus) : $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('destination', 'like', "%{$term}%")
                ->orWhere('waypoints', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%");
        });
    }

    public function activeOrdersCount(): int
    {
        if (array_key_exists('active_orders_count', $this->attributes)) {
            return (int) $this->attributes['active_orders_count'];
        }

        return $this->activeOrders()->count();
    }

    public function remainingSlots(): int
    {
        return max(0, $this->max_slots - $this->activeOrdersCount());
    }

    public function isFull(): bool
    {
        return $this->remainingSlots() === 0;
    }

    public function isOpen(): bool
    {
        return $this->status === TripStatus::Open && $this->closes_at->isFuture();
    }

    public function isJoinable(): bool
    {
        return $this->isOpen() && ! $this->isFull();
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->fulfiller_id === $user->id;
    }

    public function campusName(): string
    {
        return Campuses::name($this->campus);
    }

    public function routeLabel(): string
    {
        return 'Menuju '.$this->destination;
    }
}
