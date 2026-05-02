<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryLog extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invetory_logs';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the inventory that owns the InvetoryLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public static function stockLog(array $data)
    {
        DB::transaction(function () use ($data) {
            self::create([
                'id_inventory' => $data['id'],
                'user_id' => auth()->id(),
                'movement_type' => $data['movement_type'],
                'quantity' => $data['quantity'],
                'quantity_before' => $data['quantity_before'],
                'quantity_after' => $data['quantity_after'],
                'reason' => $data['reason'],
                'note' => $data['note']
            ]);
        });
    }
}
