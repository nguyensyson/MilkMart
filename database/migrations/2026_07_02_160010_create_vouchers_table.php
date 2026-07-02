<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('discount_type', 20)->nullable();
            $table->decimal('discount_value', 15, 2)->nullable();
            $table->decimal('max_discount', 15, 2)->nullable();
            $table->decimal('min_order_value', 15, 2)->nullable();
            $table->integer('quantity')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('status', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
