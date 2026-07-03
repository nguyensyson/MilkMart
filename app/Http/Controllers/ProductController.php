<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', 'between:1,60'],
            'category_id' => ['sometimes', 'integer', Rule::exists('categories', 'id')],
            'brand_id' => ['sometimes', 'integer', Rule::exists('brands', 'id')],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'keyword' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort' => ['sometimes', Rule::in(['newest', 'price_asc', 'price_desc', 'name'])],
        ]);

        // Docs list both `search` and `keyword` as acceptable aliases for the
        // same free-text filter; accept either.
        $search = trim((string) ($validated['search'] ?? $validated['keyword'] ?? ''));
        $categoryId = $validated['category_id'] ?? null;
        $brandId = $validated['brand_id'] ?? null;
        $sort = $validated['sort'] ?? 'newest';

        $products = Product::query()
            ->whereHas('activeVariants')
            ->with(['category:id,name', 'brand:id,name', 'cheapestVariant'])
            ->withMin('activeVariants as min_price', 'price')
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($brandId, fn ($query) => $query->where('brand_id', $brandId))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($sort === 'price_asc', fn ($query) => $query->orderBy('min_price'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderByDesc('min_price'))
            ->when($sort === 'name', fn ($query) => $query->orderBy('name'))
            ->when($sort === 'newest', fn ($query) => $query->orderByDesc('created_at'))
            ->paginate($validated['limit'] ?? self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'sort' => $sort,
            ],
        ]);
    }

    public function show(Product $product): Response
    {
        $product->load(['category:id,name', 'brand:id,name']);

        $variants = $product->activeVariants()
            ->with(['images' => fn ($query) => $query->orderByDesc('is_primary')])
            ->orderBy('price')
            ->get();

        // No active variants left means the product is effectively hidden
        // from the storefront (visibility is modeled at the variant level,
        // there is no standalone "is_active" flag on products).
        abort_if($variants->isEmpty(), 404);

        return Inertia::render('Products/Show', [
            'product' => $product,
            'variants' => $variants,
        ]);
    }
}
