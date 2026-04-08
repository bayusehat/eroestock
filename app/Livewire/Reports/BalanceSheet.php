<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use Livewire\Component;

class BalanceSheet extends Component
{
    public string $asOfDate = '';

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
    }

    public function getReportData(): array
    {
        $accounts = Account::with(['transactions' => fn($q) => $q->whereDate('date', '<=', $this->asOfDate)])
            ->where('is_active', true)->where('is_header', false)->orderBy('code')->get();

        $assets = $accounts->where('type', 'asset')->map(fn($a) => [
            'account_code' => $a->code, 'account_name' => $a->name,
            'balance' => $a->opening_balance + $a->transactions->sum(fn($t) => $t->type === 'income' ? $t->amount : -$t->amount),
        ]);
        $liabilities = $accounts->where('type', 'liability')->map(fn($a) => [
            'account_code' => $a->code, 'account_name' => $a->name,
            'balance' => $a->opening_balance + $a->transactions->sum(fn($t) => $t->amount),
        ]);
        $equity = $accounts->where('type', 'equity')->map(fn($a) => [
            'account_code' => $a->code, 'account_name' => $a->name,
            'balance' => $a->opening_balance,
        ]);

        return [
            'assets' => $assets, 'liabilities' => $liabilities, 'equity' => $equity,
            'total_assets' => $assets->sum('balance'),
            'total_liabilities' => $liabilities->sum('balance'),
            'total_equity' => $equity->sum('balance'),
        ];
    }

    public function render()
    {
        return view('livewire.reports.balance-sheet', ['data' => $this->getReportData()]);
    }
}
