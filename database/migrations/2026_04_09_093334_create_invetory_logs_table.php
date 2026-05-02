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
        Schema::create('invetory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_inventory');
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('movement_type'); // IN, OUT, RETURN, ADJUSTMENT
            $table->string('reference_type')->nullable(); // PURCHASE_ORDER, SALES_ORDER, RETURN
            $table->string('reference_number')->nullable(); // PO-12345, SO-67890
            $table->integer('quantity');
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('movement_type');
            $table->index('reference_number');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invetory_logs');
    }
};
