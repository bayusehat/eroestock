<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_no',
        'type',
        'title',
        'description',
        'amount',
        'status',
        'created_by',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'account_id',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
