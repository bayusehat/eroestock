<?php

namespace App\Http\Controllers;

use App\Services\TiktokShopService;
use Illuminate\Http\Request;
use App\Model\TiktokToken;

class TiktokShopController extends Controller
{
    public function __construct(protected TiktokShopService $tiktok) {}

    // ─────────────────────────────────────────────
    // Step 1: Redirect seller ke halaman otorisasi TikTok
    // ─────────────────────────────────────────────

    public function showConnect()
    {
        return view('tiktok.connect');
    }

    public function redirectToAuthorize()
    {
        $authUrl = $this->tiktok->getAuthorizationUrl(state: session()->getId());
        return redirect()->away($authUrl);
    }

    // ─────────────────────────────────────────────────────
    // Step 2: TikTok redirect kembali ke sini dengan `code`
    // ─────────────────────────────────────────────────────

    public function handleCallback(Request $request)
    {
        $request->validate([
            'code'  => 'required|string',
            'state' => 'nullable|string',
        ]);

        // (Opsional) Validasi state untuk mencegah CSRF
        abort_if($request->state !== session()->getId(), 403, 'Invalid state');

        $authCode  = $request->query('code');
        $tokenData = $this->tiktok->getAccessToken($authCode);

        $data = $tokenData['data'];
        try {
            TiktokToken::updateOrCreate(
                [
                    'open_id'                  => $data['open_id']
                ],
                [
                    'seller_name'              => $data['seller_name'],
                    'access_token'             => $data['access_token'],
                    'refresh_token'            => $data['refresh_token'],
                    'access_token_expired_at'  => now()->addSeconds($data['access_token_expire_in']),
                    'refresh_token_expired_at' => now()->addSeconds($data['refresh_token_expire_in']),
                ]
            );


            return redirect()->route('dashboard')
                    ->with('success', 'Berhasil terhubung dengan TikTok!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                    ->with('error', 'Gagal terhubung dengan TikTok: '.$e->getMessage());
        }
    }

    public function refreshToken($userId, $openId){
        $token = TiktokToken::where('user_id', $userId)
            ->where('open_id', $openId)
            ->first();

        if (!$token || !$token->isExpired()) {
            return $token;
        }

        try {
            $this->tiktok->refreshAccessToken($token->refresh_token);
            return redirect()->back()->with('success','Token TikTok berhasil diperbarui');
        } catch (\Exception $e) {
            throw new \Exception('Gagal refresh token: ' . $e->getMessage());
        }
    }

    public function getOrders(Request $request)
    {
        $accessToken = TiktokToken::where('user_id',auth()->id())->first()->access_token;

        $orders = $this->tiktok->get('/api/orders/search', [
            'order_status' => 111,
            'page_size'    => 20,
        ], $accessToken);

        return response()->json($orders);
    }
}
