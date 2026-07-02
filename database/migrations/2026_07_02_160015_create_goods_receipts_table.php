<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_code', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('created_by')->constrained('users');
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};
