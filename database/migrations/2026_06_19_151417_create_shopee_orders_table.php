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
        Schema::create('shopee_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_sn');
            $table->string('booking_sn')->nullable();
            $table->integer('create_time');
            $table->integer('day_to_ship');
            $table->string('order_status');
            $table->integer('ship_by_date');
            $table->boolean('cod')->default(false);
            $table->text('message_to_seller');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopee_orders');
    }
};
