<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Inventory;
use App\Models\InventoryLog;
use DB;

class StockMovement
{
    public static function stockLog(array $data)
    {
        DB::transaction(function () use ($data) {
            InventoryLog::create($data);
        });
    }
}
