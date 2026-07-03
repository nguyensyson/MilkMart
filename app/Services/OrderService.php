<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function checkout(User $user, string $shippingAddress, ?string $voucherCode): Invoice
    {
        return DB::transaction(function () use ($user, $shippingAddress, $voucherCode) {
            $cart = $this->cartService->getOrCreateCart($user);
            $items = $cart->items()->with('variant')->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Giỏ hàng của bạn đang trống.',
                ]);
            }

            foreach ($items as $item) {
                if ($item->quantity > $item->variant->stock_quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Sản phẩm \"{$item->variant->sku}\" không đủ tồn kho (còn {$item->variant->stock_quantity}).",
                    ]);
                }
            }

            $subtotal = (float) $items->sum(fn (\App\Models\CartItem $item) => $item->quantity * (float) $item->variant->price);

            [$voucher, $discount] = $voucherCode
                ? $this->resolveVoucher($voucherCode, $subtotal)
                : [null, 0.0];

            $invoice = Invoice::create([
                'invoice_code' => $this->generateInvoiceCode(),
                'user_id' => $user->id,
                'voucher_id' => $voucher?->id,
                'subtotal' => $this->money($subtotal),
                'discount_amount' => $this->money($discount),
                'total_amount' => $this->money($subtotal - $discount),
                'payment_method' => 'cod',
                'payment_status' => 'unpaid',
                'order_status' => 'pending',
                'shipping_address' => $shippingAddress,
            ]);

            foreach ($items as $item) {
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->variant->price,
                    'total_price' => $this->money($item->quantity * (float) $item->variant->price),
                ]);

                $item->variant->decrement('stock_quantity', $item->quantity);
            }

            if ($voucher) {
                VoucherUsage::create([
                    'voucher_id' => $voucher->id,
                    'user_id' => $user->id,
                    'invoice_id' => $invoice->id,
                    'used_at' => now(),
                ]);
            }

            $cart->items()->delete();

            return $invoice;
        });
    }

    public function cancel(Invoice $invoice): void
    {
        if (! in_array($invoice->order_status, Invoice::CANCELLABLE_STATUSES, true)) {
            throw ValidationException::withMessages([
                'order_status' => 'Đơn hàng không thể hủy ở trạng thái hiện tại.',
            ]);
        }

        DB::transaction(function () use ($invoice) {
            $this->restock($invoice);
            $invoice->update(['order_status' => 'cancelled']);
        });
    }

    public function updateOrderStatus(Invoice $invoice, string $status): void
    {
        $allowed = Invoice::STATUS_TRANSITIONS[$invoice->order_status] ?? [];

        if (! in_array($status, $allowed, true)) {
            throw ValidationException::withMessages([
                'order_status' => "Không thể chuyển trạng thái đơn hàng từ \"{$invoice->order_status}\" sang \"{$status}\".",
            ]);
        }

        DB::transaction(function () use ($invoice, $status) {
            if ($status === 'cancelled') {
                $this->restock($invoice);
            }

            $invoice->update(['order_status' => $status]);
        });
    }

    public function updatePaymentStatus(Invoice $invoice, string $status): void
    {
        $invoice->update(['payment_status' => $status]);
    }

    private function restock(Invoice $invoice): void
    {
        foreach ($invoice->details as $detail) {
            $detail->variant()->increment('stock_quantity', $detail->quantity);
        }
    }

    /**
     * @return array{0: ?Voucher, 1: float}
     */
    private function resolveVoucher(string $code, float $subtotal): array
    {
        $voucher = Voucher::where('code', strtoupper(trim($code)))->first();

        if (! $voucher || $voucher->status !== 'active') {
            throw ValidationException::withMessages([
                'voucher_code' => 'Mã voucher không hợp lệ.',
            ]);
        }

        $now = now();
        if (($voucher->start_date && $now->lt($voucher->start_date)) || ($voucher->end_date && $now->gt($voucher->end_date))) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Mã voucher đã hết hạn hoặc chưa có hiệu lực.',
            ]);
        }

        if ($voucher->min_order_value && $subtotal < (float) $voucher->min_order_value) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã voucher này.',
            ]);
        }

        if ($voucher->quantity !== null && VoucherUsage::where('voucher_id', $voucher->id)->count() >= $voucher->quantity) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Mã voucher đã hết lượt sử dụng.',
            ]);
        }

        $discount = $voucher->discount_type === 'percent'
            ? $subtotal * ((float) $voucher->discount_value / 100)
            : (float) $voucher->discount_value;

        if ($voucher->max_discount) {
            $discount = min($discount, (float) $voucher->max_discount);
        }

        return [$voucher, round(min($discount, $subtotal), 2)];
    }

    /**
     * Decimal-cast columns reject raw PHP floats (brick/math deprecation),
     * so monetary values are formatted to fixed-precision strings first.
     */
    private function money(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private function generateInvoiceCode(): string
    {
        do {
            $code = 'DH'.now()->format('ymd').Str::upper(Str::random(5));
        } while (Invoice::where('invoice_code', $code)->exists());

        return $code;
    }
}
