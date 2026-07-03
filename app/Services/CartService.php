<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * Assumption: each user has exactly one cart (no explicit DB unique
     * constraint on carts.user_id, so this is enforced here at the app layer).
     */
    public function getOrCreateCart(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Adding a variant already present in the cart merges into the existing
     * row instead of creating a duplicate line.
     */
    public function addItem(Cart $cart, ProductVariant $variant, int $quantity): CartItem
    {
        $item = CartItem::firstOrNew([
            'cart_id' => $cart->id,
            'product_variant_id' => $variant->id,
        ]);

        $newQuantity = ($item->exists ? $item->quantity : 0) + $quantity;
        $this->assertWithinStock($variant, $newQuantity);

        $item->quantity = $newQuantity;
        $item->save();

        return $item;
    }

    public function updateQuantity(CartItem $item, int $quantity): void
    {
        $this->assertWithinStock($item->variant, $quantity);
        $item->update(['quantity' => $quantity]);
    }

    private function assertWithinStock(ProductVariant $variant, int $quantity): void
    {
        if ($quantity > $variant->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Số lượng vượt quá tồn kho hiện có ({$variant->stock_quantity}).",
            ]);
        }
    }
}
