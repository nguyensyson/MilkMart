<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\ProductVariant;

class InventoryService
{
    /**
     * Apply a goods receipt's line items onto product_variants.stock_quantity.
     */
    public function applyGoodsReceipt(GoodsReceipt $goodsReceipt): void
    {
        foreach ($goodsReceipt->details as $detail) {
            ProductVariant::whereKey($detail->product_variant_id)
                ->increment('stock_quantity', $detail->quantity);
        }
    }
}
