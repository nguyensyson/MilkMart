<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users' => User::count(),
                'products' => Product::count(),
                'orders' => Invoice::count(),
            ],
            'bestSellers' => $this->bestSellingProducts(),
            'monthlyRevenue' => $this->monthlyRevenue(),
            'revenueByBrand' => $this->revenueByBrand(),
        ]);
    }

    private function bestSellingProducts()
    {
        return Product::query()
            ->select('products.id', 'products.name')
            ->selectRaw('SUM(invoice_details.quantity) as quantity_sold')
            ->join('product_variants', 'product_variants.product_id', '=', 'products.id')
            ->join('invoice_details', 'invoice_details.product_variant_id', '=', 'product_variants.id')
            ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->where('invoices.payment_status', 'paid')
            ->where('invoices.order_status', '!=', 'cancelled')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('quantity_sold')
            ->limit(5)
            ->get();
    }

    private function monthlyRevenue()
    {
        $year = Carbon::now()->year;
        $from = Carbon::create($year, 1, 1)->startOfYear();
        $to = Carbon::create($year, 12, 31)->endOfYear();

        $rows = Invoice::query()
            ->revenueEligible()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("TO_CHAR(created_at, 'MM') as month, COALESCE(SUM(total_amount), 0) as revenue")
            ->groupByRaw("TO_CHAR(created_at, 'MM')")
            ->get()
            ->keyBy('month');

        return collect(range(1, 12))->map(function (int $month) use ($rows) {
            $row = $rows->get(str_pad((string) $month, 2, '0', STR_PAD_LEFT));

            return [
                'month' => $month,
                'revenue' => $row ? (float) $row->revenue : 0.0,
            ];
        })->values();
    }

    private function revenueByBrand()
    {
        return Brand::query()
            ->select('brands.id', 'brands.name')
            ->selectRaw('COALESCE(SUM(invoice_details.total_price), 0) as revenue')
            ->join('products', 'products.brand_id', '=', 'brands.id')
            ->join('product_variants', 'product_variants.product_id', '=', 'products.id')
            ->join('invoice_details', 'invoice_details.product_variant_id', '=', 'product_variants.id')
            ->join('invoices', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->where('invoices.payment_status', 'paid')
            ->where('invoices.order_status', '!=', 'cancelled')
            ->groupBy('brands.id', 'brands.name')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'name' => $row->name,
                'revenue' => (float) $row->revenue,
            ]);
    }
}
