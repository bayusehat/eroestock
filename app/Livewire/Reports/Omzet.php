<?php

namespace App\Livewire\Reports;

use App\Models\WorkOrder;
use App\Models\ShopeeOrder;
use Livewire\Component;
use Carbon\Carbon;
use DB;

class Omzet extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(){
        if($this->dateFrom == '' && $this->dateTo == ''){
            $this->dateFrom = Carbon::now()->toDateString();
            $this->dateTo = Carbon::now()->toDateString();
        }
    }

    public function render()
    {
        $omzet = DB::select("select order_sn, buyer_username, create_time, escrow_amount, total_amount, gross_profit
            from(
            select *, case when total_amount <> 0 then total_amount - buy_price else 0 end as gross_profit from (
                select order_sn, buyer_username, create_time, escrow_amount, total_amount, b.model_discounted_price, b.model_sku from shopee_orders a
                    left join shopee_order_details b on a.id = b.shopee_order_id
                    where DATE(FROM_UNIXTIME(create_time)) between ? and ?
                ) a
            left join (
                select sku, buy_price from inventories a
                    left join items b on a.id_item = b.id
                ) b on a.model_sku = b.sku
            ) c
            group by order_sn, buyer_username, create_time, escrow_amount, total_amount, gross_profit
            order by order_sn desc
        ", [$this->dateFrom, $this->dateTo]);
        $total_omzet_by_date = collect($omzet)->sum('escrow_amount');
        $total_gross_profit = collect($omzet)->sum('gross_profit');
        return view('livewire.reports.omzet', [
            'omzet' => $omzet,
            'total_omzet' => $total_omzet_by_date,
            'gross_profit' => $total_gross_profit
        ]);
    }
}
