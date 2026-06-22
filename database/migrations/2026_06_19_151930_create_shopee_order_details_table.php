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
        Schema::create('shopee_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopee_order_id');
            $table->integer('item_id');
            $table->string('item_name');
            $table->string('item_sku'); //retation to inventory
            $table->integer('order_item_id');
            $table->integer('weight');
            $table->integer('active_qty');
            $table->string('image_info');
            $table->float('model_original_price');
            $table->bigInteger('model_id');
            $table->float('model_discounted_price');
            $table->integer('model_quantity_purchased');
            $table->string('model_sku');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopee_order_details');
    }
};
