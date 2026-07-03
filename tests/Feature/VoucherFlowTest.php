<?php

namespace Tests\Feature;

use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Models\Voucher;
use App\Services\CartService;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VoucherFlowTest extends TestCase
{
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

    private function makeCartWithVariant(User $user, int $quantity): ProductVariant
    {
        $variant = ProductVariant::where('status', 'active')->where('stock_quantity', '>=', $quantity)->firstOrFail();

        $cart = app(CartService::class)->getOrCreateCart($user);
        $cart->items()->delete();
        app(CartService::class)->addItem($cart, $variant, $quantity);

        return $variant;
    }

    public function test_customer_can_preview_and_checkout_with_a_valid_voucher(): void
    {
        $customer = $this->makeUser('Customer', 'temp-voucher-customer@example.com');
        $admin = $this->makeUser('Admin', 'temp-voucher-admin@example.com');

        $variant = $this->makeCartWithVariant($customer, 2);
        $subtotal = (float) $variant->price * 2;

        $voucher = Voucher::updateOrCreate(['code' => 'TEMPTEST10'], [
            'discount_type' => 'percent',
            'discount_value' => 10,
            'max_discount' => null,
            'min_order_value' => 1,
            'quantity' => 1,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'status' => 'active',
        ]);

        // invalid code is rejected with a clear message
        $this->actingAs($customer)
            ->post('/cart/apply-voucher', ['voucher_code' => 'NOPE-DOES-NOT-EXIST'])
            ->assertSessionHasErrors('voucher_code');

        // valid code returns a discount preview without consuming a usage slot
        $this->actingAs($customer)
            ->post('/cart/apply-voucher', ['voucher_code' => 'temptest10'])
            ->assertSessionDoesntHaveErrors()
            ->assertSessionHas('voucher.code', 'TEMPTEST10');
        $this->assertSame(0, $voucher->activeUsages()->count());

        // place the order with the voucher applied
        $this->actingAs($customer)
            ->post('/checkout', ['shipping_address' => '456 Ship St', 'voucher_code' => 'TEMPTEST10'])
            ->assertRedirect();

        $invoice = $customer->fresh()->invoices()->latest('id')->firstOrFail();
        $this->assertSame($voucher->id, $invoice->voucher_id);
        $this->assertEqualsWithDelta($subtotal * 0.1, (float) $invoice->discount_amount, 0.01);
        $this->assertSame(1, $voucher->activeUsages()->count());

        // quantity is now exhausted: a second customer is rejected
        $secondCustomer = $this->makeUser('Customer', 'temp-voucher-customer-2@example.com');
        $this->makeCartWithVariant($secondCustomer, 1);
        $this->actingAs($secondCustomer)
            ->post('/cart/apply-voucher', ['voucher_code' => 'TEMPTEST10'])
            ->assertSessionHasErrors('voucher_code');

        // cancelling the order frees the usage slot back up
        $this->actingAs($customer)->post("/orders/{$invoice->id}/cancel")->assertRedirect();
        $this->assertSame(0, $voucher->activeUsages()->count());
        $this->actingAs($secondCustomer)
            ->post('/cart/apply-voucher', ['voucher_code' => 'TEMPTEST10'])
            ->assertSessionDoesntHaveErrors();

        // admin can see the (cancelled) usage in the voucher's history
        $this->actingAs($admin)
            ->get("/admin/vouchers/{$voucher->id}/usage")
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Vouchers/Usage')->has('usages.data', 1));
    }

    public function test_only_admin_can_manage_vouchers(): void
    {
        $customer = $this->makeUser('Customer', 'temp-voucher-guard-customer@example.com');
        $admin = $this->makeUser('Admin', 'temp-voucher-guard-admin@example.com');

        $this->actingAs($customer)->get('/admin/vouchers')->assertForbidden();

        $this->actingAs($admin)
            ->get('/admin/vouchers')
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Vouchers/Index'));

        $this->actingAs($admin)->post('/admin/vouchers', [
            'code' => 'TEMPGUARD',
            'discount_type' => 'fixed',
            'discount_value' => 5000,
            'min_order_value' => null,
            'quantity' => null,
            'start_date' => now()->subDay()->toDateTimeString(),
            'end_date' => now()->addDay()->toDateTimeString(),
            'status' => 'active',
        ])->assertSessionDoesntHaveErrors();

        $voucher = Voucher::where('code', 'TEMPGUARD')->firstOrFail();

        // customer cannot lock/unlock vouchers
        $this->actingAs($customer)->delete("/admin/vouchers/{$voucher->id}")->assertForbidden();

        // admin locks it via soft status change, not a hard delete
        $this->actingAs($admin)->delete("/admin/vouchers/{$voucher->id}")->assertRedirect();
        $this->assertSame('inactive', $voucher->fresh()->status);
        $this->assertNotNull(Voucher::find($voucher->id));
    }
}
