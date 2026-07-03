<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Http\Requests\Admin\UpdatePaymentStatusRequest;
use App\Models\Invoice;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    private const PER_PAGE = 15;

    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'order_status' => ['sometimes', 'nullable', Rule::in(Invoice::ORDER_STATUSES)],
            'payment_status' => ['sometimes', 'nullable', Rule::in(Invoice::PAYMENT_STATUSES)],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $orderStatus = $validated['order_status'] ?? null;
        $paymentStatus = $validated['payment_status'] ?? null;

        $orders = Invoice::query()
            ->with('user:id,fullname,email')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('invoice_code', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($query) => $query->where('fullname', 'like', "%{$search}%"));
                });
            })
            ->when($orderStatus, fn ($query) => $query->where('order_status', $orderStatus))
            ->when($paymentStatus, fn ($query) => $query->where('payment_status', $paymentStatus))
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $search,
                'order_status' => $orderStatus,
                'payment_status' => $paymentStatus,
            ],
            'orderStatuses' => Invoice::ORDER_STATUSES,
            'paymentStatuses' => Invoice::PAYMENT_STATUSES,
        ]);
    }

    public function show(Invoice $order): Response
    {
        $order->load(['user:id,fullname,email,phone,address', 'details.variant.product', 'voucher']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
            'orderTransitions' => Invoice::STATUS_TRANSITIONS,
            'paymentStatuses' => Invoice::PAYMENT_STATUSES,
        ]);
    }

    public function updatePaymentStatus(UpdatePaymentStatusRequest $request, Invoice $order): RedirectResponse
    {
        $this->orderService->updatePaymentStatus($order, $request->validated('payment_status'));

        return back()->with('success', 'Đã cập nhật trạng thái thanh toán.');
    }

    public function updateOrderStatus(UpdateOrderStatusRequest $request, Invoice $order): RedirectResponse
    {
        $this->orderService->updateOrderStatus($order, $request->validated('order_status'));

        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng.');
    }
}
