<?php

namespace App\Services;

use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageService
{
    /**
     * Remove the local file backing an image, if any. Seed data points
     * image_url at an external CDN, so URLs without "/storage/" are left
     * alone — there is nothing local to clean up.
     */
    public function deleteFile(?string $imageUrl): void
    {
        if (! $imageUrl || ! str_contains($imageUrl, '/storage/')) {
            return;
        }

        Storage::disk('public')->delete(Str::after($imageUrl, '/storage/'));
    }

    /**
     * @param  iterable<ProductImage>  $images
     */
    public function deleteFiles(iterable $images): void
    {
        foreach ($images as $image) {
            $this->deleteFile($image->image_url);
        }
    }

    /**
     * Promote another image of the same variant to primary. Called after the
     * previous primary image was deleted, so at most one image ends up
     * primary again.
     */
    public function promoteNextPrimary(int $variantId): void
    {
        ProductImage::where('product_variant_id', $variantId)
            ->oldest('id')
            ->first()
            ?->update(['is_primary' => true]);
    }

    public function setPrimary(ProductImage $image): void
    {
        ProductImage::where('product_variant_id', $image->product_variant_id)
            ->where('id', '!=', $image->id)
            ->update(['is_primary' => false]);

        $image->update(['is_primary' => true]);
    }
}
