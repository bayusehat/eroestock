<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiktokToken extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tiktok_tokens';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = ['*'];

    protected $casts = [
        'access_token_expired_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired()
    {
        return now()->gte($this->access_token_expired_at);
    }
}
