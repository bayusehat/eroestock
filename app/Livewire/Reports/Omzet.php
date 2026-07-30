<?php

namespace App\Livewire\Reports;

use App\Models\WorkOrder;
use App\Models\ShopeeOrder;
use Livewire\Component;
use DB;

class Omzet extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';

    public function render()
    {
        $omzet = ShopeeOrder::query();
        if($this->dateFrom && $this->dateTo){
            $omzet->whereBetween(DB::raw('DATE(FROM_UNIXTIME(create_time))'),
                    [$this->dateFrom, $this->dateTo]);
        }
        $total_omzet_by_date = $omzet->sum('escrow_amount');
        return view('livewire.reports.omzet', [
            'omzet' => $omzet->paginate(100),
            'total_omzet' => $total_omzet_by_date
        ]);
    }
}
