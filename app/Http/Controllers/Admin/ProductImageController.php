<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreImageRequest;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Services\ProductImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(StoreImageRequest $request, ProductVariant $variant): RedirectResponse
    {
        $variantHasNoImages = ! $variant->images()->exists();

        foreach ($request->file('images') as $index => $file) {
            $path = $file->store('variants', 'public');

            $variant->images()->create([
                'image_url' => Storage::disk('public')->url($path),
                'is_primary' => $variantHasNoImages && $index === 0,
            ]);
        }

        return back()->with('success', 'Đã tải ảnh lên.');
    }

    public function destroy(ProductImage $image, ProductImageService $imageService): RedirectResponse
    {
        $wasPrimary = $image->is_primary;
        $variantId = $image->product_variant_id;

        $imageService->deleteFile($image->image_url);
        $image->delete();

        if ($wasPrimary) {
            $imageService->promoteNextPrimary($variantId);
        }

        return back()->with('success', 'Đã xóa hình ảnh.');
    }

    public function primary(ProductImage $image, ProductImageService $imageService): RedirectResponse
    {
        $imageService->setPrimary($image);

        return back()->with('success', 'Đã đặt ảnh đại diện.');
    }
}
