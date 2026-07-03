<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVoucherRequest;
use App\Http\Requests\Admin\UpdateVoucherRequest;
use App\Models\Voucher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class VoucherController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:50'],
            'status' => ['sometimes', 'nullable', Rule::in(['active', 'inactive', 'expired', 'used_up'])],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $status = $validated['status'] ?? null;
        $now = Carbon::now();

        $vouchers = Voucher::query()
            ->withCount(['activeUsages as used_count'])
            ->when($search !== '', fn ($query) => $query->where('code', 'like', '%'.strtoupper($search).'%'))
            ->when($status === 'inactive', fn ($query) => $query->where('status', 'inactive'))
            ->when($status === 'expired', function ($query) use ($now) {
                $query->where('status', 'active')->whereNotNull('end_date')->where('end_date', '<', $now);
            })
            ->when($status === 'used_up', function ($query) {
                $query->where('status', 'active')->whereNotNull('quantity')->havingRaw('used_count >= quantity');
            })
            ->when($status === 'active', function ($query) use ($now) {
                $query->where('status', 'active')
                    ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $now))
                    ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $now))
                    ->havingRaw('quantity is null or used_count < quantity');
            })
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('Admin/Vouchers/Index', [
            'vouchers' => $vouchers,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'discountTypes' => Voucher::DISCOUNT_TYPES,
        ]);
    }

    public function store(StoreVoucherRequest $request): RedirectResponse
    {
        Voucher::create($request->validated());

        return back()->with('success', 'Đã tạo voucher.');
    }

    public function update(UpdateVoucherRequest $request, Voucher $voucher): RedirectResponse
    {
        $voucher->update($request->validated());

        return back()->with('success', 'Đã cập nhật voucher.');
    }

    /**
     * Vouchers are locked (status -> inactive) instead of hard-deleted: past
     * invoices and voucher_usage rows still reference this id for reporting,
     * and hard-deleting would break that history.
     */
    public function destroy(Voucher $voucher): RedirectResponse
    {
        $voucher->update(['status' => 'inactive']);

        return back()->with('success', 'Đã khóa voucher.');
    }

    public function usage(Voucher $voucher): Response
    {
        $usages = $voucher->usages()
            ->with(['user:id,fullname,email', 'invoice:id,invoice_code,order_status,discount_amount'])
            ->orderByDesc('used_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('Admin/Vouchers/Usage', [
            'voucher' => $voucher,
            'usages' => $usages,
        ]);
    }
}
