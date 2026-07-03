<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    private const DEFAULT_RANGE_DAYS = 30;

    private const PER_PAGE = 20;

    /**
     * Variants at or below this stock level are flagged as "sắp hết hàng".
     * The schema has no per-variant reorder-threshold column, so a single
     * store-wide threshold stands in for it.
     */
    private const LOW_STOCK_THRESHOLD = 10;

    public function revenue(Request $request): Response
    {
        [$from, $to] = $this->resolveDateRange($request);
        $granularity = $this->granularityFor($from, $to);
        $periodExpr = $this->periodExpression('created_at', $granularity);

        $base = Invoice::query()->revenueEligible()->whereBetween('created_at', [$from, $to]);

        $summaryRow = (clone $base)
            ->selectRaw('COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_revenue')
            ->first();

        $totalOrders = (int) $summaryRow->total_orders;
        $totalRevenue = (float) $summaryRow->total_revenue;

        $chartData = (clone $base)
            ->selectRaw("{$periodExpr} as period, COALESCE(SUM(total_amount), 0) as revenue, COUNT(*) as orders_count")
            ->groupByRaw($periodExpr)
            ->orderByRaw($periodExpr)
            ->get()
            ->map(fn ($row) => [
                'period' => $row->period,
                'revenue' => (float) $row->revenue,
                'orders_count' => (int) $row->orders_count,
            ]);

        return Inertia::render('Admin/Reports/Revenue', [
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'average_order_value' => $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0,
            ],
            'chartData' => $chartData,
            'groupBy' => $granularity,
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
        ]);
    }

    public function inventory(Request $request): Response
    {
        $validated = $request->validate([
            'category_id' => ['sometimes', 'nullable', 'integer', Rule::exists('categories', 'id')],
            'brand_id' => ['sometimes', 'nullable', 'integer', Rule::exists('brands', 'id')],
            'low_stock' => ['sometimes', 'boolean'],
        ]);

        $categoryId = $validated['category_id'] ?? null;
        $brandId = $validated['brand_id'] ?? null;
        $lowStockOnly = (bool) ($validated['low_stock'] ?? false);

        $scopeFilters = function ($query) use ($categoryId, $brandId) {
            $query->when($categoryId, fn ($q) => $q->whereHas('product', fn ($q) => $q->where('category_id', $categoryId)))
                ->when($brandId, fn ($q) => $q->whereHas('product', fn ($q) => $q->where('brand_id', $brandId)));
        };

        $variants = ProductVariant::query()
            ->tap($scopeFilters)
            ->with(['product:id,name,category_id,brand_id', 'product.category:id,name', 'product.brand:id,name'])
            ->when($lowStockOnly, fn ($q) => $q->where('stock_quantity', '<=', self::LOW_STOCK_THRESHOLD))
            ->orderBy('stock_quantity')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $totals = ProductVariant::query()
            ->tap($scopeFilters)
            ->selectRaw('COUNT(*) as total_variants, COALESCE(SUM(stock_quantity), 0) as total_stock_quantity, COALESCE(SUM(stock_quantity * price), 0) as total_stock_value')
            ->first();

        $lowStockCount = ProductVariant::query()
            ->tap($scopeFilters)
            ->where('stock_quantity', '<=', self::LOW_STOCK_THRESHOLD)
            ->count();

        return Inertia::render('Admin/Reports/Inventory', [
            'summary' => [
                'total_variants' => (int) $totals->total_variants,
                'total_stock_quantity' => (int) $totals->total_stock_quantity,
                'total_stock_value' => (float) $totals->total_stock_value,
                'low_stock_count' => $lowStockCount,
            ],
            'details' => $variants,
            'lowStockThreshold' => self::LOW_STOCK_THRESHOLD,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'low_stock' => $lowStockOnly,
            ],
        ]);
    }

    public function bestSellers(Request $request): Response
    {
        [$from, $to] = $this->resolveDateRange($request);

        $validated = $request->validate([
            'limit' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['sometimes', 'nullable', Rule::in(['quantity', 'revenue'])],
        ]);

        $limit = (int) ($validated['limit'] ?? 10);
        $sortBy = $validated['sort_by'] ?? 'quantity';
        $sortColumn = $sortBy === 'revenue' ? 'revenue' : 'quantity_sold';

        $rows = InvoiceDetail::query()
            ->select('product_variant_id')
            ->selectRaw('SUM(quantity) as quantity_sold, SUM(total_price) as revenue')
            ->whereHas('invoice', function ($query) use ($from, $to) {
                $query->revenueEligible()->whereBetween('created_at', [$from, $to]);
            })
            ->groupBy('product_variant_id')
            ->orderByDesc($sortColumn)
            ->limit($limit)
            ->get();

        $variants = ProductVariant::query()
            ->with('product:id,name')
            ->whereIn('id', $rows->pluck('product_variant_id'))
            ->get()
            ->keyBy('id');

        $bestSellers = $rows->map(function ($row) use ($variants) {
            $variant = $variants->get($row->product_variant_id);

            return [
                'product_variant_id' => (int) $row->product_variant_id,
                'product_name' => $variant?->product?->name,
                'sku' => $variant?->sku,
                'quantity_sold' => (int) $row->quantity_sold,
                'revenue' => (float) $row->revenue,
            ];
        })->values();

        return Inertia::render('Admin/Reports/BestSellers', [
            'bestSellers' => $bestSellers,
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'limit' => $limit,
                'sort_by' => $sortBy,
            ],
        ]);
    }

    public function vouchers(Request $request): Response
    {
        $validated = $request->validate([
            'from' => ['sometimes', 'nullable', 'date'],
            'to' => ['sometimes', 'nullable', 'date'],
        ]);

        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;

        if ($from && $to && Carbon::parse($from)->gt(Carbon::parse($to))) {
            throw ValidationException::withMessages(['to' => 'Ngày kết thúc phải sau ngày bắt đầu.']);
        }

        // Usage/discount figures come straight off invoices (rather than
        // joining through voucher_usage) since voucher_usage records only the
        // usage event — the order value and discount amount live on the
        // invoice row it points to, one-to-one.
        $usageRows = Invoice::query()
            ->whereNotNull('voucher_id')
            ->where('order_status', '!=', 'cancelled')
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->selectRaw('voucher_id, COUNT(*) as usage_count, COALESCE(SUM(subtotal), 0) as total_order_value, COALESCE(SUM(discount_amount), 0) as total_discount')
            ->groupBy('voucher_id')
            ->get()
            ->keyBy('voucher_id');

        $vouchers = Voucher::query()
            ->orderBy('code')
            ->get()
            ->map(function ($voucher) use ($usageRows) {
                $row = $usageRows->get($voucher->id);

                return [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'status' => $voucher->status,
                    'discount_type' => $voucher->discount_type,
                    'discount_value' => $voucher->discount_value,
                    'usage_count' => (int) ($row->usage_count ?? 0),
                    'total_order_value' => (float) ($row->total_order_value ?? 0),
                    'total_discount' => (float) ($row->total_discount ?? 0),
                ];
            })
            ->sortByDesc('usage_count')
            ->values();

        return Inertia::render('Admin/Reports/Vouchers', [
            'vouchers' => $vouchers,
            'summary' => [
                'total_vouchers' => $vouchers->count(),
                'total_usage_count' => $vouchers->sum('usage_count'),
                'total_discount_given' => $vouchers->sum('total_discount'),
            ],
            'filters' => [
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }

    public function goodsReceipts(Request $request): Response
    {
        [$from, $to] = $this->resolveDateRange($request);
        $granularity = $this->granularityFor($from, $to);
        $periodExpr = $this->periodExpression('created_at', $granularity);

        $validated = $request->validate([
            'supplier_id' => ['sometimes', 'nullable', 'integer', Rule::exists('suppliers', 'id')],
        ]);
        $supplierId = $validated['supplier_id'] ?? null;

        $base = GoodsReceipt::query()
            ->whereBetween('created_at', [$from, $to])
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId));

        $summaryRow = (clone $base)
            ->selectRaw('COUNT(*) as total_receipts, COALESCE(SUM(total_amount), 0) as total_value')
            ->first();

        $bySupplier = (clone $base)
            ->join('suppliers', 'suppliers.id', '=', 'goods_receipts.supplier_id')
            ->selectRaw('suppliers.id as supplier_id, suppliers.name as supplier_name, COUNT(*) as receipts_count, COALESCE(SUM(goods_receipts.total_amount), 0) as total_value')
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderByDesc('total_value')
            ->get()
            ->map(fn ($row) => [
                'supplier_id' => (int) $row->supplier_id,
                'supplier_name' => $row->supplier_name,
                'receipts_count' => (int) $row->receipts_count,
                'total_value' => (float) $row->total_value,
            ]);

        $chartData = (clone $base)
            ->selectRaw("{$periodExpr} as period, COUNT(*) as receipts_count, COALESCE(SUM(total_amount), 0) as total_value")
            ->groupByRaw($periodExpr)
            ->orderByRaw($periodExpr)
            ->get()
            ->map(fn ($row) => [
                'period' => $row->period,
                'receipts_count' => (int) $row->receipts_count,
                'total_value' => (float) $row->total_value,
            ]);

        return Inertia::render('Admin/Reports/GoodsReceipts', [
            'summary' => [
                'total_receipts' => (int) $summaryRow->total_receipts,
                'total_value' => (float) $summaryRow->total_value,
            ],
            'bySupplier' => $bySupplier,
            'chartData' => $chartData,
            'groupBy' => $granularity,
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
                'supplier_id' => $supplierId,
            ],
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveDateRange(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['sometimes', 'nullable', 'date'],
            'to' => ['sometimes', 'nullable', 'date'],
        ]);

        $to = ! empty($validated['to']) ? Carbon::parse($validated['to'])->endOfDay() : Carbon::now()->endOfDay();
        $from = ! empty($validated['from'])
            ? Carbon::parse($validated['from'])->startOfDay()
            : (clone $to)->subDays(self::DEFAULT_RANGE_DAYS - 1)->startOfDay();

        if ($from->gt($to)) {
            throw ValidationException::withMessages(['to' => 'Ngày kết thúc phải sau ngày bắt đầu.']);
        }

        return [$from, $to];
    }

    /**
     * Buckets the chart data by day/week/month depending on how wide the
     * selected range is, so a one-year revenue query doesn't return 365
     * daily points.
     */
    private function granularityFor(Carbon $from, Carbon $to): string
    {
        $days = $from->diffInDays($to) + 1;

        return match (true) {
            $days <= 31 => 'day',
            $days <= 180 => 'week',
            default => 'month',
        };
    }

    private function periodExpression(string $column, string $granularity): string
    {
        return match ($granularity) {
            'day' => "TO_CHAR({$column}, 'YYYY-MM-DD')",
            'week' => "TO_CHAR(TRUNC({$column}, 'IW'), 'YYYY-MM-DD')",
            'month' => "TO_CHAR({$column}, 'YYYY-MM')",
        };
    }
}
