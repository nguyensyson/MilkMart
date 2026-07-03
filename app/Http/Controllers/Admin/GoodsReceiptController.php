<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGoodsReceiptRequest;
use App\Models\GoodsReceipt;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GoodsReceiptController extends Controller
{
    private const PER_PAGE = 15;

    public function __construct(private readonly InventoryService $inventoryService)
    {
    }

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'search' => ['sometimes', 'nullable', 'string', 'max:50'],
            'supplier_id' => ['sometimes', 'nullable', 'integer', Rule::exists('suppliers', 'id')],
            'from_date' => ['sometimes', 'nullable', 'date'],
            'to_date' => ['sometimes', 'nullable', 'date'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $supplierId = $validated['supplier_id'] ?? null;
        $fromDate = $validated['from_date'] ?? null;
        $toDate = $validated['to_date'] ?? null;

        $goodsReceipts = GoodsReceipt::query()
            ->with(['supplier:id,name', 'creator:id,fullname'])
            ->when($search !== '', fn ($query) => $query->where('receipt_code', 'like', "%{$search}%"))
            ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId))
            ->when($fromDate, fn ($query) => $query->whereDate('created_at', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('created_at', '<=', $toDate))
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('Admin/GoodsReceipts/Index', [
            'goodsReceipts' => $goodsReceipts,
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => $search,
                'supplier_id' => $supplierId,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
        ]);
    }

    public function show(GoodsReceipt $goodsReceipt): Response
    {
        $goodsReceipt->load(['supplier', 'creator:id,fullname', 'details.variant.product:id,name']);

        return Inertia::render('Admin/GoodsReceipts/Show', [
            'goodsReceipt' => $goodsReceipt,
        ]);
    }

    public function create(): Response
    {
        $variants = ProductVariant::query()
            ->with('product:id,name')
            ->orderBy('product_id')
            ->get(['id', 'product_id', 'sku', 'weight', 'stock_quantity']);

        return Inertia::render('Admin/GoodsReceipts/Create', [
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'variants' => $variants,
        ]);
    }

    public function store(StoreGoodsReceiptRequest $request): RedirectResponse
    {
        $items = $request->validated('items');

        $goodsReceipt = DB::transaction(function () use ($request, $items) {
            $goodsReceipt = GoodsReceipt::create([
                'receipt_code' => $this->generateReceiptCode(),
                'supplier_id' => $request->validated('supplier_id'),
                'created_by' => $request->user()->id,
                'total_amount' => $this->money($this->itemsTotal($items)),
                // GoodsReceipt::$timestamps is false and the migration's
                // useCurrent() default doesn't translate to an actual Oracle
                // column default (yajra/laravel-oci8), so it must be set
                // explicitly here or every receipt ends up with a NULL created_at.
                'created_at' => now(),
            ]);

            foreach ($items as $item) {
                $goodsReceipt->details()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                    'import_price' => $item['import_price'],
                    'total_price' => $this->money($item['quantity'] * $item['import_price']),
                ]);
            }

            $goodsReceipt->load('details');
            $this->inventoryService->applyGoodsReceipt($goodsReceipt);

            return $goodsReceipt;
        });

        return redirect()->route('admin.goods-receipts.show', $goodsReceipt)
            ->with('success', 'Đã tạo phiếu nhập hàng và cập nhật tồn kho.');
    }

    /**
     * @param  array<int, array{quantity: int, import_price: float|string}>  $items
     */
    private function itemsTotal(array $items): float
    {
        return array_sum(array_map(fn ($item) => $item['quantity'] * $item['import_price'], $items));
    }

    /**
     * Decimal-cast columns reject raw PHP floats (brick/math deprecation),
     * so monetary values are formatted to fixed-precision strings first.
     */
    private function money(float $value): string
    {
        return number_format($value, 2, '.', '');
    }

    private function generateReceiptCode(): string
    {
        do {
            $code = 'PN'.now()->format('ymd').Str::upper(Str::random(5));
        } while (GoodsReceipt::where('receipt_code', $code)->exists());

        return $code;
    }
}
