<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const DISCOUNT_TYPES = ['percent', 'fixed'];

    public const STATUSES = ['active', 'inactive'];

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'max_discount',
        'min_order_value',
        'quantity',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'min_order_value' => 'decimal:2',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class);
    }

    /**
     * Usages tied to a cancelled invoice don't count against the redemption
     * limit — cancelling an order frees the voucher slot back up instead of
     * requiring a separate "restore usage" step.
     */
    public function activeUsages(): HasMany
    {
        return $this->usages()->whereHas(
            'invoice',
            fn ($query) => $query->where('order_status', '!=', 'cancelled'),
        );
    }
}
