<?php

namespace App\Services;

use App\Models\Voucher;
use Illuminate\Support\Carbon;

class VoucherService
{
    /**
     * Validate that a voucher can be applied to an order of the given subtotal.
     */
    public function isApplicable(Voucher $voucher, float $subtotal): bool
    {
        if ($voucher->status !== 'active') {
            return false;
        }

        $now = Carbon::now();
        if ($voucher->start_date && $now->lt($voucher->start_date)) {
            return false;
        }
        if ($voucher->end_date && $now->gt($voucher->end_date)) {
            return false;
        }

        if ($voucher->min_order_value && $subtotal < $voucher->min_order_value) {
            return false;
        }

        return true;
    }

    /**
     * Calculate the discount amount for a given subtotal, capped at max_discount.
     */
    public function calculateDiscount(Voucher $voucher, float $subtotal): float
    {
        $discount = $voucher->discount_type === 'percent'
            ? $subtotal * ($voucher->discount_value / 100)
            : (float) $voucher->discount_value;

        if ($voucher->max_discount) {
            $discount = min($discount, (float) $voucher->max_discount);
        }

        return round($discount, 2);
    }
}
