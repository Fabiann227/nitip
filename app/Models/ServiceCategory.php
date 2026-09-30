<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'description', 'fee_min', 'fee_default', 'fee_max', 'requires_document', 'has_item_cost', 'icon', 'sort_order', 'is_active'])]
class ServiceCategory extends Model
{
    use HasFactory;

    public const PRINT_CODE = 'print_copy';

    protected function casts(): array
    {
        return [
            'fee_min' => 'integer',
            'fee_default' => 'integer',
            'fee_max' => 'integer',
            'requires_document' => 'boolean',
            'has_item_cost' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function isPrint(): bool
    {
        return $this->code === self::PRINT_CODE || $this->requires_document;
    }

    public function feeRange(): string
    {
        return Money::rupiah($this->fee_min).' - '.Money::rupiah($this->fee_max);
    }
}
