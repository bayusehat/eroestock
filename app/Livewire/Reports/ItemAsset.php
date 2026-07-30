<?php

namespace App\Livewire\Reports;

use App\Models\WorkOrder;
use App\Models\ShopeeOrder;
use Livewire\Component;
use DB;

class ItemAsset extends Component
{
    public function render()
    {
        $items = DB::select("select name, sum(store_stock) total_stock, sum(buy_price * store_stock) total_hpp, sum(sell_price * store_stock) total_asset, sum((sell_price - buy_price) * store_stock) nett_worth from (
                            select name, buy_price, sell_price, sku, size, store_stock from items a
                                left join inventories b on a.id = b.id_item
                            ) a
                            group by name
                order by name");
        $totalAssets = DB::select("SELECT SUM(total_assets) total FROM (
            SELECT id_item, (grand_stock * margin) profit, (grand_stock * buy_price) total_assets from (
                SELECT a.id_item, sum(store_stock) grand_stock,SUM(b.sell_price - b.buy_price) margin, buy_price FROM inventories a left join items b on a.id_item = b.id
                GROUP BY a.id_item, buy_price
                ORDER BY a.id_item
            ) a
            ) b");
        return view('livewire.reports.item-assets', [
            'items' => $items,
            'total_asset' => $totalAssets[0]->total
        ]);
    }
}
