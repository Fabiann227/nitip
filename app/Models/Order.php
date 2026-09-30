<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PrintBinding;
use App\Support\Campuses;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'code', 'requester_id', 'fulfiller_id', 'trip_id', 'service_category_id', 'campus', 'title', 'notes',
    'pickup_location', 'dropoff_location', 'needed_by', 'items', 'print_spec', 'document_path', 'document_name',
    'service_fee', 'estimated_item_cost', 'actual_item_cost', 'receipt_path',
    'payment_method', 'payment_proof_path', 'payment_note', 'payment_submitted_at', 'payment_verified_at', 'payment_rejection_reason',
    'status', 'completion_pin', 'needs_refund', 'matched_at', 'paid_at', 'started_at', 'delivering_at',
    'delivered_at', 'completed_at', 'cancelled_at', 'cancelled_by', 'cancel_reason',
])]
class Order extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'items' => 'array',
            'print_spec' => 'array',
            'needed_by' => 'datetime',
            'payment_submitted_at' => 'datetime',
            'payment_verified_at' => 'datetime',
            'matched_at' => 'datetime',
            'paid_at' => 'datetime',
            'started_at' => 'datetime',
            'delivering_at' => 'datetime',
            'delivered_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'needs_refund' => 'boolean',
            'service_fee' => 'integer',
            'estimated_item_cost' => 'integer',
            'actual_item_cost' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function fulfiller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fulfiller_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->orderBy('created_at')->orderBy('id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOpenRequests(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Open->value)
            ->whereNull('fulfiller_id')
            ->where(function (Builder $q) {
                $q->whereNull('needed_by')->orWhere('needed_by', '>', now());
            });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value]);
    }

    public function scopeForCampus(Builder $query, ?string $campus): Builder
    {
        return $campus ? $query->where('campus', $campus) : $query;
    }

    public function scopeInvolving(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('requester_id', $user->id)->orWhere('fulfiller_id', $user->id);
        });
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('pickup_location', 'like', "%{$term}%")
                ->orWhere('dropoff_location', 'like', "%{$term}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | State helpers
    |--------------------------------------------------------------------------
    */

    public function isRequest(): bool
    {
        return $this->trip_id === null;
    }

    public function isFromTrip(): bool
    {
        return $this->trip_id !== null;
    }

    public function isOpen(): bool
    {
        return $this->status === OrderStatus::Open;
    }

    public function isExpired(): bool
    {
        return $this->isOpen() && $this->needed_by !== null && $this->needed_by->isPast();
    }

    public function isRequester(User $user): bool
    {
        return $this->requester_id === $user->id;
    }

    public function isFulfiller(User $user): bool
    {
        return $this->fulfiller_id !== null && $this->fulfiller_id === $user->id;
    }

    public function isParticipant(User $user): bool
    {
        return $this->isRequester($user) || $this->isFulfiller($user);
    }

    public function roleFor(User $user): ?string
    {
        if ($this->isRequester($user)) {
            return 'requester';
        }

        if ($this->isFulfiller($user)) {
            return 'fulfiller';
        }

        return null;
    }

    public function counterpartFor(User $user): ?User
    {
        if ($this->isRequester($user)) {
            return $this->fulfiller;
        }

        if ($this->isFulfiller($user)) {
            return $this->requester;
        }

        return null;
    }

    public function campusName(): string
    {
        return Campuses::name($this->campus);
    }

    /*
    |--------------------------------------------------------------------------
    | Items & print spec (JSON)
    |--------------------------------------------------------------------------
    */

    /**
     * @return list<array{name: string, quantity: int, estimated_price: ?int, note: ?string}>
     */
    public function itemList(): array
    {
        return array_values($this->items ?? []);
    }

    public function hasItems(): bool
    {
        return count($this->itemList()) > 0;
    }

    public function itemsSummary(): string
    {
        return implode(', ', array_map(fn (array $i) => "{$i['quantity']}x {$i['name']}", $this->itemList()));
    }

    public function itemsSubtotal(): int
    {
        return (int) array_sum(array_map(fn (array $i) => (int) $i['quantity'] * (int) ($i['estimated_price'] ?? 0), $this->itemList()));
    }

    public function hasPrintSpec(): bool
    {
        return ! empty($this->print_spec);
    }

    public function printBinding(): ?PrintBinding
    {
        return $this->hasPrintSpec() ? PrintBinding::tryFrom((string) ($this->print_spec['binding'] ?? 'none')) : null;
    }

    public function printSummary(): string
    {
        if (! $this->hasPrintSpec()) {
            return '-';
        }

        $spec = $this->print_spec;

        return implode(' · ', [
            ($spec['pages'] ?? 1).' halaman',
            ($spec['copies'] ?? 1).' rangkap',
            ! empty($spec['is_color']) ? 'Berwarna' : 'Hitam putih',
            $spec['paper_size'] ?? 'A4',
            $this->printBinding()?->label() ?? 'Tanpa jilid',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Money
    |--------------------------------------------------------------------------
    */

    public function estimatedTotal(): int
    {
        return $this->service_fee + $this->estimated_item_cost;
    }

    public function finalTotal(): int
    {
        return $this->service_fee + ($this->actual_item_cost ?? $this->estimated_item_cost);
    }

    /**
     * Positive = requester owes extra, negative = fulfiller returns change.
     */
    public function settlementDifference(): ?int
    {
        if ($this->actual_item_cost === null) {
            return null;
        }

        return $this->actual_item_cost - $this->estimated_item_cost;
    }

    public function hasPaymentProof(): bool
    {
        return $this->payment_proof_path !== null;
    }

    public function hasReceipt(): bool
    {
        return $this->receipt_path !== null;
    }

    public function hasDocument(): bool
    {
        return $this->document_path !== null;
    }

    public function fileUrl(string $kind): string
    {
        return route('orders.files', [$this, $kind]);
    }

    public function hasReviewFrom(User $user): bool
    {
        return $this->reviews->contains('reviewer_id', $user->id);
    }

    public function whatsappText(User $sender): string
    {
        $counterpart = $this->counterpartFor($sender);

        return __(config('nitip.whatsapp_template'), [
            'name' => $counterpart?->shortName() ?? '',
            'sender' => $sender->shortName(),
            'code' => $this->code,
            'title' => $this->title,
        ]);
    }
}
