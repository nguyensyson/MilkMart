<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptDetail;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ReportsFlowTest extends TestCase
{
    /**
     * This suite runs against a real Oracle DB with no RefreshDatabase/
     * transaction rollback, so leftover rows from a previous run would
     * otherwise inflate the aggregate counts these tests assert on.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $invoiceIds = Invoice::where('invoice_code', 'like', 'TEMPREPORT%')->pluck('id');
        VoucherUsage::whereIn('invoice_id', $invoiceIds)->delete();
        InvoiceDetail::whereIn('invoice_id', $invoiceIds)->delete();
        Invoice::whereIn('id', $invoiceIds)->delete();

        $receiptIds = GoodsReceipt::where('receipt_code', 'like', 'TEMPREPORTGR%')->pluck('id');
        GoodsReceiptDetail::whereIn('receipt_id', $receiptIds)->delete();
        GoodsReceipt::whereIn('id', $receiptIds)->delete();
    }

    private function makeUser(string $roleName, string $email): User
    {
        $role = Role::where('name', $roleName)->firstOrFail();

        return User::updateOrCreate(
            ['email' => $email],
            [
                'role_id' => $role->id,
                'fullname' => "Temp {$roleName}",
                'password_hash' => Hash::make('password'),
                'address' => '1 Temp St',
                'status' => 'active',
            ],
        );
    }

    public function test_only_admin_can_access_reports(): void
    {
        $customer = $this->makeUser('Customer', 'temp-report-guard-customer@example.com');
        $admin = $this->makeUser('Admin', 'temp-report-guard-admin@example.com');

        foreach ([
            '/admin/reports',
            '/admin/reports/revenue',
            '/admin/reports/inventory',
            '/admin/reports/best-sellers',
            '/admin/reports/vouchers',
            '/admin/reports/goods-receipts',
        ] as $path) {
            $this->actingAs($customer)->get($path)->assertForbidden();
        }

        $this->actingAs($admin)
            ->get('/admin/reports/revenue')
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Reports/Revenue'));
    }

    public function test_revenue_and_best_sellers_only_count_paid_non_cancelled_invoices_in_range(): void
    {
        $admin = $this->makeUser('Admin', 'temp-report-admin@example.com');
        $customer = $this->makeUser('Customer', 'temp-report-customer@example.com');

        $variant = ProductVariant::where('status', 'active')->where('price', '>', 0)->firstOrFail();

        $makeInvoice = function (string $suffix, string $paymentStatus, string $orderStatus, $createdAt) use ($customer) {
            return Invoice::create([
                'invoice_code' => 'TEMPREPORT'.$suffix.uniqid(),
                'user_id' => $customer->id,
                'subtotal' => 100000,
                'discount_amount' => 0,
                'total_amount' => 100000,
                'payment_method' => 'cod',
                'payment_status' => $paymentStatus,
                'order_status' => $orderStatus,
                'shipping_address' => 'x',
                'created_at' => $createdAt,
            ]);
        };

        $counted = $makeInvoice('COUNTED', 'paid', 'completed', now());
        InvoiceDetail::create([
            'invoice_id' => $counted->id,
            'product_variant_id' => $variant->id,
            'quantity' => 3,
            'unit_price' => $variant->price,
            'total_price' => $variant->price * 3,
        ]);

        // excluded: unpaid
        $makeInvoice('UNPAID', 'unpaid', 'pending', now());
        // excluded: paid but cancelled
        $makeInvoice('CANCELLED', 'paid', 'cancelled', now());
        // excluded: outside the requested date range
        $makeInvoice('OLD', 'paid', 'completed', now()->subDays(45));

        $from = now()->subDays(2)->toDateString();
        $to = now()->toDateString();

        $this->actingAs($admin)
            ->get("/admin/reports/revenue?from={$from}&to={$to}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Reports/Revenue')
                ->where('summary.total_orders', 1)
                ->where('summary.total_revenue', fn ($value) => (float) $value === 100000.0)
            );

        $this->actingAs($admin)
            ->get("/admin/reports/best-sellers?from={$from}&to={$to}&limit=5")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Reports/BestSellers')
                ->has('bestSellers', 1)
                ->where('bestSellers.0.product_variant_id', $variant->id)
                ->where('bestSellers.0.quantity_sold', 3)
            );
    }

    public function test_inventory_report_filters_by_category_and_low_stock(): void
    {
        $admin = $this->makeUser('Admin', 'temp-report-inventory-admin@example.com');

        // Unique names per run: this test isn't wrapped in a DB transaction
        // (Oracle test DB, no RefreshDatabase), so a fixed name would let
        // variants pile up across repeated runs and inflate total_variants.
        $category = Category::create(['name' => 'Temp Report Category '.uniqid(), 'description' => 'temp']);
        $brand = Brand::firstOrCreate(['name' => 'Temp Report Brand'], ['description' => 'temp']);
        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Temp Report Product',
            'description' => 'temp',
        ]);
        $lowStockVariant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEMPREPORTLOW'.uniqid(),
            'weight' => 1,
            'price' => 10000,
            'stock_quantity' => 2,
            'status' => 'active',
        ]);
        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'TEMPREPORTHIGH'.uniqid(),
            'weight' => 1,
            'price' => 10000,
            'stock_quantity' => 500,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get("/admin/reports/inventory?category_id={$category->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Reports/Inventory')
                ->where('summary.total_variants', 2)
                ->where('summary.low_stock_count', 1)
            );

        $this->actingAs($admin)
            ->get("/admin/reports/inventory?category_id={$category->id}&low_stock=1")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Reports/Inventory')
                ->has('details.data', 1)
                ->where('details.data.0.id', $lowStockVariant->id)
            );
    }

    public function test_voucher_report_excludes_usage_from_cancelled_orders(): void
    {
        $admin = $this->makeUser('Admin', 'temp-report-voucher-admin@example.com');
        $customer = $this->makeUser('Customer', 'temp-report-voucher-customer@example.com');

        $voucher = Voucher::updateOrCreate(['code' => 'TEMPREPORTVOUCHER'], [
            'discount_type' => 'fixed',
            'discount_value' => 20000,
            'max_discount' => null,
            'min_order_value' => null,
            'quantity' => null,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'status' => 'active',
        ]);

        $activeInvoice = Invoice::create([
            'invoice_code' => 'TEMPREPORTVUACTIVE'.uniqid(),
            'user_id' => $customer->id,
            'voucher_id' => $voucher->id,
            'subtotal' => 200000,
            'discount_amount' => 20000,
            'total_amount' => 180000,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'order_status' => 'completed',
            'shipping_address' => 'x',
            'created_at' => now(),
        ]);
        VoucherUsage::create([
            'voucher_id' => $voucher->id,
            'user_id' => $customer->id,
            'invoice_id' => $activeInvoice->id,
            'used_at' => now(),
        ]);

        $cancelledInvoice = Invoice::create([
            'invoice_code' => 'TEMPREPORTVUCANCEL'.uniqid(),
            'user_id' => $customer->id,
            'voucher_id' => $voucher->id,
            'subtotal' => 500000,
            'discount_amount' => 20000,
            'total_amount' => 480000,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'order_status' => 'cancelled',
            'shipping_address' => 'x',
            'created_at' => now(),
        ]);
        VoucherUsage::create([
            'voucher_id' => $voucher->id,
            'user_id' => $customer->id,
            'invoice_id' => $cancelledInvoice->id,
            'used_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/admin/reports/vouchers')
            ->assertInertia(function (Assert $page) use ($voucher) {
                $page->component('Admin/Reports/Vouchers')
                    ->where('vouchers', function ($vouchers) use ($voucher) {
                        $row = collect($vouchers)->firstWhere('id', $voucher->id);
                        $this->assertNotNull($row);
                        $this->assertSame(1, $row['usage_count']);
                        $this->assertEqualsWithDelta(20000, (float) $row['total_discount'], 0.01);

                        return true;
                    });
            });
    }

    public function test_goods_receipts_report_groups_by_supplier_and_filters(): void
    {
        $admin = $this->makeUser('Admin', 'temp-report-gr-admin@example.com');

        $supplier = Supplier::create([
            'name' => 'Temp Report Supplier '.uniqid(),
            'phone' => '0123456789',
            'email' => 'temp-supplier@example.com',
            'address' => 'x',
        ]);
        $variant = ProductVariant::first();

        $receipt = GoodsReceipt::create([
            'receipt_code' => 'TEMPREPORTGR'.uniqid(),
            'supplier_id' => $supplier->id,
            'created_by' => $admin->id,
            'total_amount' => 50000,
            'created_at' => now(),
        ]);
        GoodsReceiptDetail::create([
            'receipt_id' => $receipt->id,
            'product_variant_id' => $variant->id,
            'quantity' => 5,
            'import_price' => 10000,
            'total_price' => 50000,
        ]);

        $from = now()->subDays(2)->toDateString();
        $to = now()->toDateString();

        $this->actingAs($admin)
            ->get("/admin/reports/goods-receipts?from={$from}&to={$to}&supplier_id={$supplier->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Reports/GoodsReceipts')
                ->where('summary.total_receipts', 1)
                ->where('summary.total_value', fn ($value) => (float) $value === 50000.0)
                ->has('bySupplier', 1)
                ->where('bySupplier.0.supplier_id', $supplier->id)
            );
    }
}
