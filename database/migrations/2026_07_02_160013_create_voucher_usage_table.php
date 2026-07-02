<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->dateTime('used_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_usage');
    }
};
