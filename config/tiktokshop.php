<?php

return [

    /*
    |--------------------------------------------------------------------------
    | TikTok Shop API Credentials
    |--------------------------------------------------------------------------
    | Dapatkan App Key & App Secret dari TikTok Shop Partner Center:
    | https://partner.tiktokshop.com/
    */

    'app_key'     => env('TIKTOKSHOP_APP_KEY', ''),
    'app_secret'  => env('TIKTOKSHOP_APP_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    | Gunakan URL ini untuk semua request API TikTok Shop.
    */

    'base_url'    => env('TIKTOKSHOP_BASE_URL', 'https://open-api.tiktokglobalshop.com'),

    /*
    |--------------------------------------------------------------------------
    | Shop Region
    |--------------------------------------------------------------------------
    | Kode negara toko Anda (ISO 3166-1 alpha-2).
    | Indonesia: ID, Malaysia: MY, Thailand: TH, dsb.
    */

    'shop_region' => env('TIKTOKSHOP_SHOP_REGION', 'ID'),

];
