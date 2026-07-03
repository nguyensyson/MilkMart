<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use HasFactory;

    public $timestamps = false;

    public const ORDER_STATUSES = ['pending', 'confirmed', 'shipping', 'completed', 'cancelled'];

    public const PAYMENT_STATUSES = ['unpaid', 'paid', 'refunded'];

    /**
     * Customer self-service cancellation is only allowed before the order
     * has been handed off for shipping.
     */
    public const CANCELLABLE_STATUSES = ['pending', 'confirmed'];

    /**
     * Admin/staff order_status changes must follow this forward-only flow;
     * completed/cancelled are terminal.
     *
     * @var array<string, array<int, string>>
     */
    public const STATUS_TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['shipping', 'cancelled'],
        'shipping' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    protected $fillable = [
        'invoice_code',
        'user_id',
        'voucher_id',
        'subtotal',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'order_status',
        'shipping_address',
        // $timestamps is false (no updated_at column) so this must be mass
        // assignable — it's set explicitly on create() instead of relying on
        // Eloquent's automatic timestamp management.
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function voucherUsage(): HasOne
    {
        return $this->hasOne(VoucherUsage::class);
    }

    /**
     * Reports only count orders that actually represent realized revenue:
     * paid, and not cancelled (a cancelled order may still be marked paid
     * pending a refund, but shouldn't count as revenue).
     */
    public function scopeRevenueEligible(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid')->where('order_status', '!=', 'cancelled');
    }
}
