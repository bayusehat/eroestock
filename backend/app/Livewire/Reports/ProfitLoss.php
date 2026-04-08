<?php

namespace App\Livewire\Reports;

use App\Models\JournalEntryLine;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProfitLoss extends Component
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
        $revenue = Transaction::join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where('transactions.type', 'income')
            ->whereBetween('transactions.date', [$this->dateFrom, $this->dateTo])
            ->select('accounts.code', 'accounts.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->get()->map(fn($r) => ['account_code' => $r->code, 'account_name' => $r->name, 'amount' => (float) $r->total]);

        $expenses = Transaction::join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$this->dateFrom, $this->dateTo])
            ->select('accounts.code', 'accounts.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->get()->map(fn($r) => ['account_code' => $r->code, 'account_name' => $r->name, 'amount' => (float) $r->total]);

        $totalRevenue = $revenue->sum('amount');
        $totalExpenses = $expenses->sum('amount');

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_profit' => $totalRevenue - $totalExpenses,
        ];
    }

    public function render()
    {
        return view('livewire.reports.profit-loss', ['data' => $this->getReportData()]);
    }
}
