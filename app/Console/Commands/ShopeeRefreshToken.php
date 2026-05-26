<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schedule;

class ShopeeRefreshToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:shopee-refresh-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tokens = \App\Models\ShopeeToken::where('expires_at', '<', now()->addHours(1))
            ->where('expires_at', '>', now())
            ->get();

        $controller = new \App\Http\Controllers\ShopeeAuthController();

        foreach ($tokens as $token) {
            try {
                $controller->refreshToken($token->user_id, $token->shop_id);
            } catch (\Exception $e) {
                \Log::error('Gagal refresh token Shopee: ' . $e->getMessage());
            }
        }
    }
}
