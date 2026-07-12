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
            $table->string('buyer_username')->after('flag');
            $table->string('package_number')->after('buyer_username');
            $table->string('shipping_carrier')->after('package_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shopee_orders', function (Blueprint $table) {
            $table->dropColumn('buyer_username');
            $table->dropColumn('package_number');
            $table->dropColumn('shipping_carrier');
        });
    }
};
