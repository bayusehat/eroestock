<?php

namespace App\Livewire\Reports;

use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class WorkOrderSummary extends Component
{
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function getReportData(): array
    {
        $workOrders = WorkOrder::whereBetween('order_date', [$this->dateFrom, $this->dateTo])->get();
        $byStatus = $workOrders->groupBy('status')->map(fn($g, $status) => [
            'status' => $status, 'count' => $g->count(), 'total_value' => $g->sum('grand_total'),
        ])->values();

        return [
            'total_work_orders' => $workOrders->count(),
            'total_value' => $workOrders->sum('grand_total'),
            'average_value' => $workOrders->count() > 0 ? $workOrders->avg('grand_total') : 0,
            'by_status' => $byStatus,
        ];
    }

    public function render()
    {
        return view('livewire.reports.work-order-summary', ['data' => $this->getReportData()]);
    }
}
