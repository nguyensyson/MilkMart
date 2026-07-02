<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        $products = Product::with(['brand', 'category'])
            ->orderBy('name')
            ->get(['id', 'category_id', 'brand_id', 'name', 'description']);

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }
}
