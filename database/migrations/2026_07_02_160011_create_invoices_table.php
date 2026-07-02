<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code', 50)->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('voucher_id')->nullable()->constrained('vouchers');
            $table->decimal('subtotal', 15, 2)->nullable();
            $table->decimal('discount_amount', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_status', 30)->nullable();
            $table->string('order_status', 30)->nullable();
            $table->text('shipping_address')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
