<?php

namespace App\Livewire\Reports;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ExpenseByCategory extends Component
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
        $rows = Transaction::join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$this->dateFrom, $this->dateTo])
            ->select('accounts.name as category', DB::raw('SUM(transactions.amount) as amount'))
            ->groupBy('accounts.id', 'accounts.name')
            ->orderByDesc('amount')
            ->get()
            ->collect();

        $total = $rows->sum('amount');
        $rows = $rows->map(fn($r) => ['category' => $r->category, 'amount' => (float)$r->amount, 'percentage' => $total > 0 ? round(($r->amount / $total) * 100, 1) : 0]);

        return ['rows' => $rows];
    }

    public function render()
    {
        return view('livewire.reports.expense-by-category', ['data' => $this->getReportData()]);
    }
}
