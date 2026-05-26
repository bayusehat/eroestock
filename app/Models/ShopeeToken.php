<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopeeToken extends Model
{
    protected $fillable = [
        'user_id', 'shop_id', 'access_token', 'refresh_token',
        'expires_in', 'expires_at', 'shop_info'
    ];

    protected $casts = [
        'shop_info' => 'array',
        'expires_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired()
    {
        return now()->gte($this->expires_at);
    }
}
