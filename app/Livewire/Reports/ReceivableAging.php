<?php

namespace App\Livewire\Reports;

use App\Models\Invoice;
use Livewire\Component;

class ReceivableAging extends Component
{
    public function getReportData(): array
    {
        $invoices = Invoice::with('client:id,name')
            ->whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->get();

        $rows = $invoices->groupBy('client_id')->map(function ($group) {
            $client = $group->first()->client;
            $current = $days31 = $days61 = $over90 = 0;
            foreach ($group as $inv) {
                $days = now()->diffInDays($inv->due_date, false);
                if ($days >= 0) $current += $inv->balance_due;
                elseif ($days >= -30) $days31 += $inv->balance_due;
                elseif ($days >= -60) $days61 += $inv->balance_due;
                else $over90 += $inv->balance_due;
            }
            return ['name' => $client?->name ?? '-', 'current' => $current,
                    'days_31_60' => $days31, 'days_61_90' => $days61, 'over_90' => $over90,
                    'total' => $current + $days31 + $days61 + $over90];
        })->values();

        return ['rows' => $rows, 'totals' => [
            'current' => $rows->sum('current'), 'days_31_60' => $rows->sum('days_31_60'),
            'days_61_90' => $rows->sum('days_61_90'), 'over_90' => $rows->sum('over_90'),
            'total' => $rows->sum('total'),
        ]];
    }

    public function render()
    {
        return view('livewire.reports.receivable-aging', ['data' => $this->getReportData()]);
    }
}
