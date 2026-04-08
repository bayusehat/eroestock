<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number')->unique();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['draft', 'confirmed', 'in_progress', 'completed', 'invoiced', 'cancelled'])->default('draft');
            $table->date('order_date');
            $table->date('due_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->decimal('total_before_tax', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);
            $table->decimal('total_discount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
