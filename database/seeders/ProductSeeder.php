<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    /**
     * Products, variants and images crawled once from Vinamilk's public
     * product pages (vinamilk.com.vn/products/*) and frozen into
     * database/seeders/data/vinamilk_products.json. This seeder only reads
     * that fixture — it never hits the network, so it works the same way
     * on every fresh install.
     *
     * Image URLs point directly at Vinamilk's CDN (no local download/storage).
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            BrandSeeder::class,
        ]);

        $path = database_path('seeders/data/vinamilk_products.json');
        $products = json_decode(File::get($path), true);

        foreach ($products as $productData) {
            $category = Category::firstOrCreate(['name' => $productData['category']]);
            $brand = Brand::firstOrCreate(['name' => $productData['brand']]);

            $product = Product::firstOrCreate(
                ['name' => $productData['name']],
                [
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'description' => $productData['description'],
                ]
            );

            foreach ($productData['variants'] as $variantData) {
                $variant = ProductVariant::firstOrCreate(
                    ['sku' => $variantData['sku']],
                    [
                        'product_id' => $product->id,
                        'weight' => $variantData['weight'],
                        'price' => $variantData['price'],
                        'stock_quantity' => $variantData['stock_quantity'],
                        'image_url' => $variantData['image_url'],
                        'status' => $variantData['status'],
                    ]
                );

                if ($variantData['image_url']) {
                    ProductImage::firstOrCreate(
                        [
                            'product_variant_id' => $variant->id,
                            'image_url' => $variantData['image_url'],
                        ],
                        ['is_primary' => true]
                    );
                }
            }
        }
    }
}
