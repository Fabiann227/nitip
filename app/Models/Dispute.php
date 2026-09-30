<?php

namespace App\Models;

use App\Enums\DisputeReason;
use App\Enums\DisputeResolution;
use App\Enums\DisputeStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'opened_by', 'reason', 'description', 'evidence_path', 'status', 'resolution', 'resolution_note', 'resolved_by', 'resolved_at'])]
class Dispute extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'reason' => DisputeReason::class,
            'status' => DisputeStatus::class,
            'resolution' => DisputeResolution::class,
            'resolved_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', DisputeStatus::Open->value);
    }

    public function isOpen(): bool
    {
        return $this->status === DisputeStatus::Open;
    }

    public function hasEvidence(): bool
    {
        return $this->evidence_path !== null;
    }
}
