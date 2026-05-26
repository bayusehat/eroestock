<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ShopeeService
{
    protected string $partnerId;
    protected string $partnerKey;
    protected string $baseUrl;
    protected string $redirectUrl;

    public function __construct()
    {
        $this->partnerId = config('shopee.partner_id');
        $this->partnerKey = config('shopee.partner_key');
        $this->baseUrl = config('shopee.base_url');
        $this->redirectUrl = config('shopee.redirect_url');
    }

    /**
     * Generate signature untuk Shopee API
     */
    public function generateSignature(string $path, int $timestamp, ?string $accessToken = null, ?int $shopId = null): string
    {
        $baseString = $this->partnerId . $path . $timestamp;

        if ($accessToken && $shopId) {
            $baseString .= $accessToken . $shopId;
        }

        return hash_hmac('sha256', $baseString, $this->partnerKey);
    }

    /**
     * Generate URL untuk OAuth authorization
     */
    public function getAuthUrl(): string
    {
        $path = '/api/v2/shop/auth_partner';
        $timestamp = time();
        $sign = $this->generateSignature($path, $timestamp);

        $params = http_build_query([
            'partner_id' => $this->partnerId,
            'timestamp' => $timestamp,
            'sign' => $sign,
            'redirect' => $this->redirectUrl,
        ]);

        return $this->baseUrl . $path . '?' . $params;
    }

    /**
     * Exchange authorization code untuk access token
     */
    public function getAccessToken(string $code, int $shopId): array
    {
        $path = '/api/v2/auth/token/get';
        $timestamp = time();
        $sign = $this->generateSignature($path, $timestamp);

        $response = Http::post($this->baseUrl . $path, [
            'partner_id' => (int) $this->partnerId,
            'timestamp' => $timestamp,
            'sign' => $sign,
            'code' => $code,
            'shop_id' => $shopId,
        ]);

        $data = $response->json();

        if (isset($data['access_token'])) {
            $this->storeTokens($shopId, $data);
        }

        return $data;
    }

    /**
     * Refresh access token
     */
    public function refreshAccessToken(int $shopId, string $refreshToken): array
    {
        $path = '/api/v2/auth/access_token/get';
        $timestamp = time();
        $sign = $this->generateSignature($path, $timestamp);

        $response = Http::post($this->baseUrl . $path, [
            'partner_id' => (int) $this->partnerId,
            'timestamp' => $timestamp,
            'sign' => $sign,
            'refresh_token' => $refreshToken,
            'shop_id' => $shopId,
        ]);

        $data = $response->json();

        if (isset($data['access_token'])) {
            $this->storeTokens($shopId, $data);
        }

        return $data;
    }

    /**
     * Simpan tokens ke cache/database
     */
    protected function storeTokens(int $shopId, array $data): void
    {
        $expireAt = now()->addSeconds($data['expire_in'] ?? 14400);

        Cache::put("shopee_access_token_{$shopId}", $data['access_token'], $expireAt);
        Cache::put("shopee_refresh_token_{$shopId}", $data['refresh_token'], now()->addDays(30));
    }

    /**
     * Ambil access token yang valid
     */
    public function getValidAccessToken(int $shopId): ?string
    {
        $accessToken = Cache::get("shopee_access_token_{$shopId}");

        if (!$accessToken) {
            $refreshToken = Cache::get("shopee_refresh_token_{$shopId}");

            if ($refreshToken) {
                $result = $this->refreshAccessToken($shopId, $refreshToken);
                return $result['access_token'] ?? null;
            }

            return null;
        }

        return $accessToken;
    }

    /**
     * API call dengan authentication
     */
    public function callApi(string $method, string $path, int $shopId, array $params = []): array
    {
        $accessToken = $this->getValidAccessToken($shopId);

        if (!$accessToken) {
            throw new \Exception('Access token tidak valid. Silakan re-authorize.');
        }

        $timestamp = time();
        $sign = $this->generateSignature($path, $timestamp, $accessToken, $shopId);

        $queryParams = [
            'partner_id' => $this->partnerId,
            'timestamp' => $timestamp,
            'sign' => $sign,
            'access_token' => $accessToken,
            'shop_id' => $shopId,
        ];

        $url = $this->baseUrl . $path . '?' . http_build_query($queryParams);

        $response = match (strtoupper($method)) {
            'GET' => Http::get($url, $params),
            'POST' => Http::post($url, $params),
            default => throw new \Exception("Method {$method} tidak didukung"),
        };

        return $response->json();
    }

    /**
     * Contoh: Get shop info
     */
    public function getShopInfo(int $shopId): array
    {
        return $this->callApi('GET', '/api/v2/shop/get_shop_info', $shopId);
    }

    /**
     * Contoh: Get product list
     */
    public function getProductList(int $shopId, int $offset = 0, int $pageSize = 20): array
    {
        return $this->callApi('GET', '/api/v2/product/get_item_list', $shopId, [
            'offset' => $offset,
            'page_size' => $pageSize,
            'item_status' => 'NORMAL',
        ]);
    }
}
