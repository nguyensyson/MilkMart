<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Invoice;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class OrderFlowTempTest extends TestCase
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

    public function test_full_order_flow(): void
    {
        $customer = $this->makeUser('Customer', 'temp-order-customer@example.com');
        $otherCustomer = $this->makeUser('Customer', 'temp-order-other@example.com');
        $admin = $this->makeUser('Admin', 'temp-order-admin@example.com');

        // guest is redirected to login
        $this->get('/checkout')->assertRedirect('/login');

        $variant = ProductVariant::where('status', 'active')->where('stock_quantity', '>=', 2)->firstOrFail();

        $cart = app(CartService::class)->getOrCreateCart($customer);
        $cart->items()->delete();
        app(CartService::class)->addItem($cart, $variant, 2);

        $this->actingAs($customer)
            ->get('/checkout')
            ->assertInertia(fn (Assert $page) => $page->component('Checkout/Create')->has('cart.items', 1));

        $this->actingAs($customer)
            ->post('/checkout', ['shipping_address' => '456 Ship St'])
            ->assertRedirect();

        $invoice = Invoice::where('user_id', $customer->id)->latest('id')->firstOrFail();
        $this->assertSame('pending', $invoice->order_status);
        $this->assertSame('unpaid', $invoice->payment_status);
        $this->assertSame(0, $cart->items()->count());

        $this->actingAs($customer)
            ->get('/orders')
            ->assertInertia(fn (Assert $page) => $page->component('Orders/Index'));

        $this->actingAs($customer)
            ->get("/orders/{$invoice->id}")
            ->assertInertia(fn (Assert $page) => $page->component('Orders/Show')->where('order.id', $invoice->id));

        // another customer cannot view/cancel this order
        $this->actingAs($otherCustomer)->get("/orders/{$invoice->id}")->assertForbidden();
        $this->actingAs($otherCustomer)->post("/orders/{$invoice->id}/cancel")->assertForbidden();

        // non-backoffice user cannot reach /admin/orders
        $this->actingAs($customer)->get('/admin/orders')->assertForbidden();

        $this->actingAs($admin)
            ->get('/admin/orders')
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Orders/Index'));

        $this->actingAs($admin)
            ->get("/admin/orders/{$invoice->id}")
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Orders/Show')->where('order.id', $invoice->id));

        // invalid direct jump pending -> completed is rejected
        $this->actingAs($admin)
            ->put("/admin/orders/{$invoice->id}/order-status", ['order_status' => 'completed'])
            ->assertSessionHasErrors('order_status');
        $this->assertSame('pending', $invoice->fresh()->order_status);

        // valid transition
        $this->actingAs($admin)
            ->put("/admin/orders/{$invoice->id}/order-status", ['order_status' => 'confirmed'])
            ->assertSessionDoesntHaveErrors();
        $this->assertSame('confirmed', $invoice->fresh()->order_status);

        $this->actingAs($admin)
            ->put("/admin/orders/{$invoice->id}/payment-status", ['payment_status' => 'paid'])
            ->assertSessionDoesntHaveErrors();
        $this->assertSame('paid', $invoice->fresh()->payment_status);

        // customer can still cancel while confirmed
        $stockBefore = $variant->fresh()->stock_quantity;
        $this->actingAs($customer)
            ->post("/orders/{$invoice->id}/cancel")
            ->assertRedirect();
        $this->assertSame('cancelled', $invoice->fresh()->order_status);
        $this->assertSame($stockBefore + 2, $variant->fresh()->stock_quantity);
    }
}
