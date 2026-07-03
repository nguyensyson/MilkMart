<?php

namespace App\Services;

use App\Models\Voucher;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class VoucherService
{
    /**
     * Resolve a voucher code for a cart/order of the given subtotal, or throw
     * a ValidationException with a message specific to the failure reason
     * (not found / locked / not-yet-started / expired / below minimum /
     * usage limit reached). Shared by the cart preview endpoint and the real
     * checkout so both apply the exact same rules.
     */
    public function findForApply(string $code, float $subtotal): Voucher
    {
        $voucher = Voucher::where('code', strtoupper(trim($code)))->first();

        if (! $voucher) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Mã voucher không tồn tại.',
            ]);
        }

        if ($voucher->status !== 'active') {
            throw ValidationException::withMessages([
                'voucher_code' => 'Mã voucher đang bị khóa.',
            ]);
        }

        $now = Carbon::now();

        if ($voucher->start_date && $now->lt($voucher->start_date)) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Mã voucher chưa đến thời gian áp dụng.',
            ]);
        }

        if ($voucher->end_date && $now->gt($voucher->end_date)) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Mã voucher đã hết hạn.',
            ]);
        }

        if ($voucher->min_order_value && $subtotal < (float) $voucher->min_order_value) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã voucher này.',
            ]);
        }

        if ($voucher->quantity !== null && $voucher->activeUsages()->count() >= $voucher->quantity) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Mã voucher đã hết lượt sử dụng.',
            ]);
        }

        return $voucher;
    }

    /**
     * Calculate the discount amount for a given subtotal, capped at
     * max_discount (for percent vouchers) and never exceeding the subtotal.
     */
    public function calculateDiscount(Voucher $voucher, float $subtotal): float
    {
        $discount = $voucher->discount_type === 'percent'
            ? $subtotal * ((float) $voucher->discount_value / 100)
            : (float) $voucher->discount_value;

        if ($voucher->max_discount) {
            $discount = min($discount, (float) $voucher->max_discount);
        }

        return round(min($discount, $subtotal), 2);
    }
}
