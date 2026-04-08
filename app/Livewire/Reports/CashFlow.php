<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use Livewire\Component;

class CashFlow extends Component
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
        $income = Transaction::where('type', 'income')->whereBetween('date', [$this->dateFrom, $this->dateTo])->sum('amount');
        $expense = Transaction::where('type', 'expense')->whereBetween('date', [$this->dateFrom, $this->dateTo])->sum('amount');

        return [
            'opening_balance' => 0,
            'closing_balance' => $income - $expense,
            'net_cash_flow' => $income - $expense,
            'operating' => ['inflows' => $income, 'outflows' => $expense],
            'investing' => ['inflows' => 0, 'outflows' => 0],
            'financing' => ['inflows' => 0, 'outflows' => 0],
        ];
    }

    public function render()
    {
        return view('livewire.reports.cash-flow', ['data' => $this->getReportData()]);
    }
}
