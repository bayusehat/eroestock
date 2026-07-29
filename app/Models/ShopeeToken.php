<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShopeeToken extends Model
{
    use SoftDeletes, HasFactory;

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
