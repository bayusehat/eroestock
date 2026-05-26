<?php

namespace App\Http\Controllers;

use App\Services\TiktokShopService;
use Illuminate\Http\Request;

class TiktokShopController extends Controller
{
    public function __construct(protected TiktokShopService $tiktok) {}

    // ─────────────────────────────────────────────
    // Step 1: Redirect seller ke halaman otorisasi TikTok
    // ─────────────────────────────────────────────

    public function redirectToAuthorize()
    {
        $authUrl = $this->tiktok->getAuthorizationUrl(state: session()->getId());

        return redirect()->away($authUrl);
    }

    // ─────────────────────────────────────────────
    // Step 2: TikTok redirect kembali ke sini dengan `code`
    // ─────────────────────────────────────────────

    public function handleCallback(Request $request)
    {
        $request->validate([
            'code'  => 'required|string',
            'state' => 'nullable|string',
        ]);

        // (Opsional) Validasi state untuk mencegah CSRF
        // abort_if($request->state !== session()->getId(), 403, 'Invalid state');

        $authCode  = $request->query('code');
        $tokenData = $this->tiktok->getAccessToken($authCode);

        $data = $tokenData['data'];

        // Simpan token ke database (sesuaikan dengan model Anda)
        // Shop::updateOrCreate(
        //     ['open_id' => $data['open_id']],
        //     [
        //         'seller_name'              => $data['seller_name'],
        //         'access_token'             => $data['access_token'],
        //         'refresh_token'            => $data['refresh_token'],
        //         'access_token_expired_at'  => now()->addSeconds($data['access_token_expire_in']),
        //         'refresh_token_expired_at' => now()->addSeconds($data['refresh_token_expire_in']),
        //     ]
        // );

        return response()->json([
            'message'       => 'Toko berhasil terhubung!',
            'open_id'       => $data['open_id'],
            'seller_name'   => $data['seller_name'],
            'access_token'  => $data['access_token'],   // simpan ke DB, jangan expose ke user!
            'refresh_token' => $data['refresh_token'],  // sama
        ]);
    }

    // ─────────────────────────────────────────────
    // Contoh: Panggil API lain setelah auth
    // ─────────────────────────────────────────────

    public function getOrders(Request $request)
    {
        // Ambil token dari DB / session (contoh hardcode)
        $accessToken = 'ACCESS_TOKEN_DARI_DATABASE';

        $orders = $this->tiktok->get('/api/orders/search', [
            'order_status' => 111,
            'page_size'    => 20,
        ], $accessToken);

        return response()->json($orders);
    }
}
