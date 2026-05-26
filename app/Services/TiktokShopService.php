<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TiktokShopService
{
    protected string $appKey;
    protected string $appSecret;
    protected string $baseUrl;
    protected string $shopRegion;

    public function __construct()
    {
        $this->appKey    = config('tiktokshop.app_key');
        $this->appSecret = config('tiktokshop.app_secret');
        $this->baseUrl   = config('tiktokshop.base_url', 'https://open-api.tiktokglobalshop.com');
        $this->shopRegion = config('tiktokshop.shop_region', 'ID');
    }

    // ─────────────────────────────────────────────
    // 1. Generate Authorization URL (OAuth Step 1)
    // ─────────────────────────────────────────────

    /**
     * Generate the TikTok Shop OAuth authorization URL.
     * Redirect the user to this URL to start the OAuth flow.
     */
    public function getAuthorizationUrl(string $state = ''): string
    {
        $params = [
            'app_key'  => $this->appKey,
            'state'    => $state ?: csrf_token(),
        ];

        return 'https://services.tiktokshop.com/open/authorize?' . http_build_query($params);
    }

    // ─────────────────────────────────────────────
    // 2. Exchange Auth Code for Access Token (OAuth Step 2)
    // ─────────────────────────────────────────────

    /**
     * Exchange the authorization code (from OAuth callback) for an access token.
     *
     * @param  string $authCode  The `code` query parameter from the callback URL.
     * @return array{
     *   access_token: string,
     *   refresh_token: string,
     *   access_token_expire_in: int,
     *   refresh_token_expire_in: int,
     *   open_id: string,
     *   seller_name: string,
     *   seller_base_region: string,
     * }
     */
    public function getAccessToken(string $authCode): array
    {
        $path      = '/api/v2/token/get';
        $timestamp = $this->timestamp();
        $sign      = $this->signRequest($path, [], $timestamp);

        $response = Http::get($this->baseUrl . $path, [
            'app_key'    => $this->appKey,
            'app_secret' => $this->appSecret,
            'auth_code'  => $authCode,
            'grant_type' => 'authorized_code',
            'timestamp'  => $timestamp,
            'sign'       => $sign,
        ]);

        return $this->parseResponse($response);
    }

    // ─────────────────────────────────────────────
    // 3. Refresh Access Token
    // ─────────────────────────────────────────────

    /**
     * Refresh an expired access token using the refresh token.
     *
     * @param  string $refreshToken
     * @return array  Same shape as getAccessToken()
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $path      = '/api/v2/token/refresh';
        $timestamp = $this->timestamp();
        $sign      = $this->signRequest($path, [], $timestamp);

        $response = Http::get($this->baseUrl . $path, [
            'app_key'       => $this->appKey,
            'app_secret'    => $this->appSecret,
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
            'timestamp'     => $timestamp,
            'sign'          => $sign,
        ]);

        return $this->parseResponse($response);
    }

    // ─────────────────────────────────────────────
    // 4. Make Authenticated API Request
    // ─────────────────────────────────────────────

    /**
     * Make a signed GET request to the TikTok Shop API.
     *
     * @param  string $path         API endpoint path, e.g. '/api/orders/search'
     * @param  array  $queryParams  Additional query parameters
     * @param  string $accessToken  Seller's access token
     */
    public function get(string $path, array $queryParams, string $accessToken): array
    {
        $timestamp = $this->timestamp();
        $sign      = $this->signRequest($path, $queryParams, $timestamp);

        $response = Http::withHeaders($this->defaultHeaders())
            ->get($this->baseUrl . $path, array_merge($queryParams, [
                'app_key'      => $this->appKey,
                'access_token' => $accessToken,
                'timestamp'    => $timestamp,
                'sign'         => $sign,
                'shop_region'  => $this->shopRegion,
            ]));

        return $this->parseResponse($response);
    }

    /**
     * Make a signed POST request to the TikTok Shop API.
     *
     * @param  string $path         API endpoint path
     * @param  array  $queryParams  Query string parameters (for signing)
     * @param  array  $body         JSON body parameters
     * @param  string $accessToken  Seller's access token
     */
    public function post(string $path, array $queryParams, array $body, string $accessToken): array
    {
        $timestamp = $this->timestamp();
        $sign      = $this->signRequest($path, $queryParams, $timestamp);

        $response = Http::withHeaders($this->defaultHeaders())
            ->post($this->baseUrl . $path . '?' . http_build_query(array_merge($queryParams, [
                'app_key'      => $this->appKey,
                'access_token' => $accessToken,
                'timestamp'    => $timestamp,
                'sign'         => $sign,
                'shop_region'  => $this->shopRegion,
            ])), $body);

        return $this->parseResponse($response);
    }

    // ─────────────────────────────────────────────
    // 5. Token Management with Cache
    // ─────────────────────────────────────────────

    /**
     * Get a valid access token for a shop, auto-refreshing if needed.
     * Stores & retrieves token data from the Laravel cache.
     *
     * @param  string $shopId        A unique key for this shop/seller
     * @param  string $refreshToken  The refresh token to use if expired
     */
    public function getValidToken(string $shopId, string $refreshToken): string
    {
        $cacheKey = "tiktokshop_token_{$shopId}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        Log::info("TikTokShop: Refreshing token for shop {$shopId}");

        $tokenData   = $this->refreshAccessToken($refreshToken);
        $accessToken = $tokenData['data']['access_token'];
        $expiresIn   = $tokenData['data']['access_token_expire_in'] ?? 3600;

        // Cache the token with a small buffer before actual expiry
        Cache::put($cacheKey, $accessToken, now()->addSeconds($expiresIn - 60));

        return $accessToken;
    }

    // ─────────────────────────────────────────────
    // Internal Helpers
    // ─────────────────────────────────────────────

    /**
     * Generate HMAC-SHA256 signature required by TikTok Shop API.
     *
     * Signature formula:
     *   HMAC-SHA256( appSecret, appSecret + path + sorted_params_string + appSecret )
     */
    protected function signRequest(string $path, array $params, int $timestamp): string
    {
        // Merge common params used in signing
        $allParams = array_merge($params, [
            'app_key'   => $this->appKey,
            'timestamp' => $timestamp,
        ]);

        // Sort params alphabetically by key
        ksort($allParams);

        // Concatenate key-value pairs (no separators, no URL encoding)
        $paramString = '';
        foreach ($allParams as $key => $value) {
            $paramString .= $key . $value;
        }

        // Final string to sign: appSecret + path + params + appSecret
        $stringToSign = $this->appSecret . $path . $paramString . $this->appSecret;

        return hash_hmac('sha256', $stringToSign, $this->appSecret);
    }

    /**
     * Current Unix timestamp (seconds).
     */
    protected function timestamp(): int
    {
        return now()->timestamp;
    }

    /**
     * Default HTTP headers for TikTok Shop API requests.
     */
    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'x-tts-access-token' => '',  // will be overridden per request if needed
        ];
    }

    /**
     * Parse and validate the HTTP response from TikTok Shop API.
     *
     * @throws \RuntimeException on non-2xx responses or API errors
     */
    protected function parseResponse(\Illuminate\Http\Client\Response $response): array
    {
        if ($response->failed()) {
            Log::error('TikTokShop API HTTP error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new \RuntimeException(
                "TikTok Shop API request failed with HTTP {$response->status()}: {$response->body()}"
            );
        }

        $data = $response->json();

        // TikTok Shop uses `code` 0 for success
        if (isset($data['code']) && $data['code'] !== 0) {
            Log::error('TikTokShop API error response', $data);

            throw new \RuntimeException(
                "TikTok Shop API error [{$data['code']}]: " . ($data['message'] ?? 'Unknown error')
            );
        }

        return $data;
    }
}
