<?php

namespace App\Http\Controllers;

use App\Models\ShopeeToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Muhanz\Shoapi\Facades\Shoapi;

class ShopeeAuthController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Tampilkan halaman koneksi Shopee
     */
    public function showConnect()
    {
        return view('shopee.connect');
    }

    /**
     * Redirect ke halaman otorisasi Shopee
     */
    public function redirectToShopee()
    {
        try {
            return Shoapi::call('shop')
                ->access('auth_partner')
                ->redirect();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghubungkan ke Shopee: ' . $e->getMessage());
        }
    }

    /**
     * Callback setelah otorisasi dari Shopee
     */
    public function handleShopeeCallback(Request $request)
    {
        $code = $request->get('code');
        $shopId = $request->get('shop_id');

        if (!$code || !$shopId) {
            return redirect()->route('shopee.connect')
                ->with('error', 'Parameter tidak lengkap dari Shopee');
        }

        try {
            // Dapatkan access token dari Shopee [citation:2]
            $params = [
                'code' => $code,
                'shop_id' => (int) $shopId,
            ];

            $response = Shoapi::call('auth')
                ->access('get_access_token')
                ->shop((int) $shopId)
                ->request($params)
                ->response();

            $response = json_decode(json_encode($response, true), true);

            if ($response['api_status'] !== 'success') {
                throw new \Exception('Gagal mendapatkan access token');
            }

            // Simpan token ke database
            ShopeeToken::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'shop_id' => $response['shop_id_list'][0]
                ],
                [
                    'access_token' => $response['access_token'],
                    'refresh_token' => $response['refresh_token'],
                    'expires_in' => $response['expire_in'],
                    'expires_at' => now()->addSeconds($response['expire_in']),
                ]
            );

            // Dapatkan informasi toko
            $this->getShopInfo(Auth::id(), $response['shop_id_list'][0]);

            return redirect()->route('dashboard')
                ->with('success', 'Berhasil terhubung dengan Shopee!');

        } catch (\Exception $e) {
            return redirect()->route('shopee.connect')
                ->with('error', 'Gagal autentikasi Shopee: ' . $e->getMessage());
        }
    }

    /**
     * Refresh token ketika expired
     */
    public function refreshToken($userId, $shopId)
    {
        $token = ShopeeToken::where('user_id', $userId)
            ->where('shop_id', $shopId)
            ->first();

        if (!$token || !$token->isExpired()) {
            return $token;
        }

        try {
            $params = [
                'refresh_token' => $token->refresh_token,
                'shop_id' => (int) $shopId,
            ];

            $response = Shoapi::call('auth')
                ->access('refresh_access_token')
                ->shop((int) $shopId)
                ->request($params)
                ->response();

            if ($response['api_status'] === 'success') {
                $token->update([
                    'access_token' => $response['access_token'],
                    'refresh_token' => $response['refresh_token'],
                    'expires_in' => $response['expire_in'],
                    'expires_at' => now()->addSeconds($response['expire_in']),
                ]);
            }

            return $token;
        } catch (\Exception $e) {
            throw new \Exception('Gagal refresh token: ' . $e->getMessage());
        }
    }

    /**
     * Ambil informasi toko dari Shopee
     */
    private function getShopInfo($userId, $shopId)
    {
        $token = ShopeeToken::where('user_id', $userId)
            ->where('shop_id', $shopId)
            ->first();

        if (!$token) {
            return null;
        }

        try {
            $response = Shoapi::call('shop')
                ->access('get_shop_info', $token->access_token)
                ->shop((int) $shopId)
                ->response();
            $response = json_decode(json_encode($response, true), true);

            if ($response['api_status'] === 'success') {
                $token->update([
                    'shop_info' => $response
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            return null;
        }
    }
}
