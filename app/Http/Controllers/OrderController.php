<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\StoreOrderRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Invoice;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderService $orderService,
    ) {
    }

    public function create(Request $request): Response
    {
        $cart = $this->cartService->getOrCreateCart($request->user());

        return Inertia::render('Checkout/Create', [
            'cart' => $this->presentCart($cart),
            'shippingAddress' => $request->user()->address,
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $invoice = $this->orderService->checkout(
            $request->user(),
            $request->validated('shipping_address'),
            $request->validated('voucher_code') ?: null,
        );

        return redirect()->route('orders.show', $invoice)->with('success', 'Đặt hàng thành công.');
    }

    public function index(Request $request): Response
    {
        $status = $request->query('status') ?: null;

        $orders = Invoice::query()
            ->where('user_id', $request->user()->id)
            ->when($status, fn ($query) => $query->where('order_status', $status))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    public function show(Request $request, Invoice $order): Response
    {
        $this->authorizeOwnership($request, $order);

        $order->load(['details.variant.product', 'voucher']);

        return Inertia::render('Orders/Show', [
            'order' => $order,
            'cancellableStatuses' => Invoice::CANCELLABLE_STATUSES,
        ]);
    }

    public function cancel(Request $request, Invoice $order): RedirectResponse
    {
        $this->authorizeOwnership($request, $order);

        $this->orderService->cancel($order);

        return back()->with('success', 'Đã hủy đơn hàng.');
    }

    /**
     * Orders are only ever addressed by their own id, so ownership must be
     * checked explicitly to stop a user from viewing/cancelling someone
     * else's order (mirrors CartController::authorizeOwnership).
     */
    private function authorizeOwnership(Request $request, Invoice $order): void
    {
        abort_if((int) $order->user_id !== (int) $request->user()->id, 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function presentCart(Cart $cart): array
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
