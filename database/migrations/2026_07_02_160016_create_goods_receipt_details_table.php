<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receipt_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained('goods_receipts');
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->integer('quantity')->nullable();
            $table->decimal('import_price', 15, 2)->nullable();
            $table->decimal('total_price', 15, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_details');
    }
};
