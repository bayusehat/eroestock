<?php

namespace App\Livewire\Reports;

use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TrialBalance extends Component
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
        $accounts = JournalEntryLine::join('accounts', 'journal_entry_lines.account_id', '=', 'accounts.id')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->whereBetween('journal_entries.date', [$this->dateFrom, $this->dateTo])
            ->select('accounts.code', 'accounts.name',
                DB::raw('SUM(journal_entry_lines.debit) as total_debit'),
                DB::raw('SUM(journal_entry_lines.credit) as total_credit'))
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->orderBy('accounts.code')
            ->get()->map(fn($r) => [
                'account_code' => $r->code, 'account_name' => $r->name,
                'debit' => (float)$r->total_debit, 'credit' => (float)$r->total_credit,
            ]);

        return [
            'accounts' => $accounts,
            'total_debits' => $accounts->sum('debit'),
            'total_credits' => $accounts->sum('credit'),
        ];
    }

    public function render()
    {
        return view('livewire.reports.trial-balance', ['data' => $this->getReportData()]);
    }
}
