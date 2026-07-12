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
        // 'seller_name'              => $data['seller_name'],
        //             'access_token'             => $data['access_token'],
        //             'refresh_token'            => $data['refresh_token'],
        //             'access_token_expired_at'  => now()->addSeconds($data['access_token_expire_in']),
        //             'refresh_token_expired_at' => now()->addSeconds($data['refresh_token_expire_in']),
        Schema::create('tiktok_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('open_id');
            $table->string('seller_name');
            $table->string('access_token');
            $table->string('refresh_token');
            $table->bigInteger('access_token_expired_at');
            $table->bigInteger('refresh_token_expired_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiktok_tokens');
    }
};
