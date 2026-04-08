<?php

namespace App\Livewire\Reports;

use Livewire\Component;

class PayableAging extends Component
{
    public function render()
    {
        return view('livewire.reports.payable-aging', ['data' => ['rows' => collect(), 'totals' => ['current'=>0,'days_31_60'=>0,'days_61_90'=>0,'over_90'=>0,'total'=>0]]]);
    }
}
