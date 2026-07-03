<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSupplierRequest;
use App\Http\Requests\Admin\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));

        $suppliers = Supplier::query()
            ->withCount('goodsReceipts')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('Admin/Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());

        return back()->with('success', 'Đã thêm nhà cung cấp.');
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return back()->with('success', 'Đã cập nhật nhà cung cấp.');
    }

    /**
     * Suppliers referenced by goods receipts are permanent purchase history;
     * deleting them would orphan that history, so the delete is blocked instead.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->goodsReceipts()->exists()) {
            return back()->with('error', 'Không thể xóa nhà cung cấp vì đã có phiếu nhập hàng liên kết.');
        }

        $supplier->delete();

        return back()->with('success', 'Đã xóa nhà cung cấp.');
    }
}
