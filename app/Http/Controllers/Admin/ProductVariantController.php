<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVariantRequest;
use App\Http\Requests\Admin\UpdateVariantRequest;
use App\Models\CartItem;
use App\Models\GoodsReceiptDetail;
use App\Models\InvoiceDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProductVariantController extends Controller
{
    public function store(StoreVariantRequest $request, Product $product): RedirectResponse
    {
        $product->variants()->create($request->validated());

        return back()->with('success', 'Đã thêm biến thể.');
    }

    public function update(UpdateVariantRequest $request, ProductVariant $variant): RedirectResponse
    {
        $variant->update($request->validated());

        return back()->with('success', 'Đã cập nhật biến thể.');
    }

    public function destroy(ProductVariant $variant, ProductImageService $imageService): RedirectResponse
    {
        $hasOrderHistory = InvoiceDetail::where('product_variant_id', $variant->id)->exists()
            || GoodsReceiptDetail::where('product_variant_id', $variant->id)->exists();

        if ($hasOrderHistory) {
            return back()->with('error', 'Không thể xóa biến thể vì đã phát sinh đơn hàng hoặc phiếu nhập.');
        }

        DB::transaction(function () use ($variant, $imageService) {
            $imageService->deleteFiles($variant->images);
            $variant->images()->delete();
            CartItem::where('product_variant_id', $variant->id)->delete();
            $variant->delete();
        });

        return back()->with('success', 'Đã xóa biến thể.');
    }
}
