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
            $table->float('actual_shipping_fee')->after('escrow_amount');
            $table->float('buyer_transaction_fee')->after('actual_shipping_fee');
            $table->float('withholding_tax')->after('buyer_transaction_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopee_orders', function (Blueprint $table) {
            Schema::dropIfExists('actual_shipping_fee');
            Schema::dropIfExists('buyer_transaction_fee');
            Schema::dropIfExists('withholding_tax');
        });
    }
};
