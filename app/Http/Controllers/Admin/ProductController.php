<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Brand;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\GoodsReceiptDetail;
use App\Models\InvoiceDetail;
use App\Models\Product;
use App\Services\ProductImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer', Rule::exists('categories', 'id')],
            'brand_id' => ['sometimes', 'integer', Rule::exists('brands', 'id')],
            // "visible"/"hidden" is derived from whether the product still
            // has any active variant — products have no standalone status
            // column (see Product::activeVariants()).
            'status' => ['sometimes', Rule::in(['visible', 'hidden'])],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $categoryId = $validated['category_id'] ?? null;
        $brandId = $validated['brand_id'] ?? null;
        $status = $validated['status'] ?? null;

        $products = Product::query()
            ->with(['category:id,name', 'brand:id,name', 'primaryImage'])
            ->withCount(['variants', 'activeVariants'])
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($brandId, fn ($query) => $query->where('brand_id', $brandId))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($status === 'visible', fn ($query) => $query->whereHas('activeVariants'))
            ->when($status === 'hidden', fn ($query) => $query->whereDoesntHave('activeVariants'))
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => $search,
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'status' => $status,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Products/Create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->validated());

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Đã tạo sản phẩm. Thêm biến thể để sản phẩm hiển thị trên cửa hàng.');
    }

    public function edit(Product $product): Response
    {
        $product->load([
            'category:id,name',
            'brand:id,name',
            'variants' => fn ($query) => $query->orderBy('id'),
            'variants.images' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('id'),
        ]);

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'brands' => Brand::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return back()->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Product $product, ProductImageService $imageService): RedirectResponse
    {
        $variantIds = $product->variants()->pluck('id');

        if ($this->hasOrderHistory($variantIds)) {
            return back()->with('error', 'Không thể xóa sản phẩm vì đã có biến thể phát sinh đơn hàng hoặc phiếu nhập.');
        }

        DB::transaction(function () use ($product, $imageService, $variantIds) {
            foreach ($product->variants as $variant) {
                $imageService->deleteFiles($variant->images);
                $variant->images()->delete();
            }
            // Variants can't be hard-deleted while a foreign key still points
            // at them from someone's cart; the cart entry is just ephemeral
            // state, unlike invoice/goods-receipt history checked above.
            CartItem::whereIn('product_variant_id', $variantIds)->delete();
            $product->variants()->delete();
            $product->delete();
        });

        return redirect()->route('admin.products.index')->with('success', 'Đã xóa sản phẩm.');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>  $variantIds
     */
    private function hasOrderHistory($variantIds): bool
    {
        if ($variantIds->isEmpty()) {
            return false;
        }

        return InvoiceDetail::whereIn('product_variant_id', $variantIds)->exists()
            || GoodsReceiptDetail::whereIn('product_variant_id', $variantIds)->exists();
    }
}
