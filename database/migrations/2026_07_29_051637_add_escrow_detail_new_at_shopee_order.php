<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shopee_orders', function (Blueprint $table) {
            $table->string('buyer_payment_method')->nullable();
            $table->float('order_seller_discount')->default(0);
            $table->float('shopee_voucher')->default(0);
            $table->float('buyer_service_fee')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopee_orders', function (Blueprint $table) {
            Schema::dropIfExists('buyer_payment_method');
            Schema::dropIfExists('order_seller_discount');
            Schema::dropIfExists('shopee_voucher');
            Schema::dropIfExists('buyer_service_fee');
        });
    }
};
