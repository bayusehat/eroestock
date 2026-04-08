<?php

namespace App\Livewire\Reports;

use App\Models\Invoice;
use Livewire\Component;

class IncomeByClient extends Component
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
        $rows = Invoice::with('client:id,name')
            ->whereIn('status', ['paid', 'partially_paid', 'sent'])
            ->whereBetween('issue_date', [$this->dateFrom, $this->dateTo])
            ->get()
            ->groupBy('client_id')
            ->map(fn($g) => ['client_name' => $g->first()->client?->name ?? '-', 'amount' => $g->sum('grand_total')])
            ->sortByDesc('amount')
            ->values();

        $total = $rows->sum('amount');
        $rows = $rows->map(fn($r) => [...$r, 'percentage' => $total > 0 ? round(($r['amount'] / $total) * 100, 1) : 0]);

        return ['rows' => $rows];
    }

    public function render()
    {
        return view('livewire.reports.income-by-client', ['data' => $this->getReportData()]);
    }
}
