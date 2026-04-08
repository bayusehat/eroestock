<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_no')->unique();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->tinyInteger('period_month');
            $table->smallInteger('period_year');
            $table->decimal('base_salary', 15, 2);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_rate', 15, 2)->default(0);
            $table->decimal('overtime_amount', 15, 2)->default(0);
            $table->json('allowances')->nullable();
            $table->decimal('total_allowances', 15, 2)->default(0);
            $table->json('deductions')->nullable();
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('gross_pay', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('net_pay', 15, 2);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->date('paid_date')->nullable();
            $table->enum('payment_method', ['bank_transfer', 'cash', 'check'])->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
