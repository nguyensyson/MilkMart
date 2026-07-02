<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->string('sku', 50)->unique()->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('image_url', 500)->nullable();
            $table->string('status', 20)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
