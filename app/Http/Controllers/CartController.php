<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function show(Request $request): Response
    {
        $cart = Cart::with('items.variant.product')
            ->where('user_id', $request->user()?->id)
            ->first();

        return Inertia::render('Cart/Show', [
            'cart' => $cart,
        ]);
    }
}
