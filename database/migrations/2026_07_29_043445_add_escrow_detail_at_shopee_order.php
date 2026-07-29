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
            $table->float('order_discounted_price')->after('withholding_tax')->default(0);
            $table->string('tracking_number')->after('shipping_carrier')->nullable();
            $table->float('voucher_from_shopee')->after('order_discounted_price')->default(0);
            $table->float('voucher_from_seller')->after('voucher_from_shopee')->default(0);
            $table->float('service_fee')->after('voucher_from_seller')->default(0);
            $table->float('original_price')->after('total_amount')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopee_orders', function (Blueprint $table) {
            Schema::dropIfExists('order_discounted_price');
            Schema::dropIfExists('tracking_number');
            Schema::dropIfExists('voucher_from_shopee');
            Schema::dropIfExists('voucher_from_seller');
            Schema::dropIfExists('service_fee');
            Schema::dropIfExists('original_price');
        });
    }
};
