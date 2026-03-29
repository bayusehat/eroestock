<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_requests', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->after('review_notes')->constrained('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('budget_requests', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
        });
    }
};
