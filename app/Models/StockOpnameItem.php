<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOpnameItem extends Model
{
    use SoftDeletes, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stock_opname_items';

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
     * Get the so that owns the StockOpnameItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function so(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class, 'so_id');
    }

    /**
     * Get the user that owns the StockOpnameItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inventory_item(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
}
