<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\ApplyVoucherRequest;
use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\VoucherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly VoucherService $voucherService,
    ) {
    }

    public function show(Request $request): Response
    {
        $cart = $this->cartService->getOrCreateCart($request->user());

        return Inertia::render('Cart/Show', [
            'cart' => $this->present($cart),
        ]);
    }

    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $variant = ProductVariant::findOrFail($request->validated('variant_id'));
        $cart = $this->cartService->getOrCreateCart($request->user());

        $this->cartService->addItem($cart, $variant, (int) $request->validated('quantity'));

        return back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $item): RedirectResponse
    {
        $this->authorizeOwnership($request, $item);

        $this->cartService->updateQuantity($item, (int) $request->validated('quantity'));

        return back()->with('success', 'Đã cập nhật số lượng.');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeOwnership($request, $item);

        $item->delete();

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    /**
     * Preview-only: validates the voucher against the cart's current
     * subtotal and returns the discount, but does not persist anything
     * (usage is only recorded once the order is actually placed in
     * OrderService::checkout, which re-validates the voucher itself).
     */
    public function applyVoucher(ApplyVoucherRequest $request): RedirectResponse
    {
        $cart = $this->cartService->getOrCreateCart($request->user());
        $subtotal = $this->subtotal($cart);

        if ($subtotal <= 0) {
            return back()->withErrors(['voucher_code' => 'Giỏ hàng của bạn đang trống.']);
        }

        $voucher = $this->voucherService->findForApply($request->validated('voucher_code'), $subtotal);
        $discount = $this->voucherService->calculateDiscount($voucher, $subtotal);

        return back()->with('voucher', [
            'code' => $voucher->code,
            'discount_type' => $voucher->discount_type,
            'discount_value' => (float) $voucher->discount_value,
            'discount_amount' => $discount,
            'subtotal' => $subtotal,
            'total' => round($subtotal - $discount, 2),
        ]);
    }

    private function subtotal(Cart $cart): float
    {
        return (float) $cart->items()->with('variant')->get()
            ->sum(fn (CartItem $item) => $item->quantity * (float) $item->variant->price);
    }

    /**
     * Cart items are only ever addressed by their own id, never a cart id
     * from the client, so ownership must be checked explicitly here to stop
     * a user from mutating someone else's cart.
     */
    private function authorizeOwnership(Request $request, CartItem $item): void
    {
        // Loose-typed compare: the Oracle driver returns foreign-key columns
        // like cart.user_id as numeric strings, while Auth::user()->id is int.
        abort_if((int) $item->cart->user_id !== (int) $request->user()->id, 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Cart $cart): array
    {
        $cart->load(['items' => fn ($query) => $query->orderBy('id'), 'items.variant.product']);

        $items = $cart->items->map(fn (CartItem $item) => [
            'id' => $item->id,
            'quantity' => $item->quantity,
            'unit_price' => (float) $item->variant->price,
            'subtotal' => (float) $item->variant->price * $item->quantity,
            'variant' => [
                'id' => $item->variant->id,
                'sku' => $item->variant->sku,
                'weight' => $item->variant->weight,
                'stock_quantity' => $item->variant->stock_quantity,
                'image_url' => $item->variant->image_url,
                'status' => $item->variant->status,
                'product' => [
                    'id' => $item->variant->product->id,
                    'name' => $item->variant->product->name,
                ],
            ],
        ])->values();

        return [
            'id' => $cart->id,
            'items' => $items,
            'total' => (float) $items->sum('subtotal'),
        ];
    }
}
