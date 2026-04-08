<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public Account $account;
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(Account $account): void
    {
        $this->account = $account->load(['parent', 'children']);
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    protected function getAccountIds(): array
    {
        $ids = [$this->account->id];

        if ($this->account->is_header) {
            $ids = array_merge($ids, $this->collectDescendantIds($this->account->id));
        }

        return $ids;
    }

    protected function collectDescendantIds(int $parentId): array
    {
        $children = Account::where('parent_id', $parentId)->pluck('id');
        $ids = [];
        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, $this->collectDescendantIds($childId));
        }

        return $ids;
    }

    protected function buildQuery()
    {
        $accountIds = $this->getAccountIds();

        $query = JournalEntryLine::whereIn('account_id', $accountIds)
            ->with(['journalEntry', 'account'])
            ->whereHas('journalEntry')
            ->orderByDesc(
                JournalEntry::select('date')
                    ->whereColumn('journal_entries.id', 'journal_entry_lines.journal_entry_id')
                    ->limit(1)
            );

        if ($this->dateFrom) {
            $query->whereHas('journalEntry', fn ($q) => $q->where('date', '>=', $this->dateFrom));
        }
        if ($this->dateTo) {
            $query->whereHas('journalEntry', fn ($q) => $q->where('date', '<=', $this->dateTo));
        }

        return $query;
    }

    public function render()
    {
        $query = $this->buildQuery();
        $lines = $query->paginate(20);

        $accountIds = $this->getAccountIds();

        $totalsQuery = JournalEntryLine::whereIn('account_id', $accountIds)
            ->whereHas('journalEntry')
            ->when($this->dateFrom, fn ($q) => $q->whereHas('journalEntry', fn ($q2) => $q2->where('date', '>=', $this->dateFrom)))
            ->when($this->dateTo, fn ($q) => $q->whereHas('journalEntry', fn ($q2) => $q2->where('date', '<=', $this->dateTo)));

        $totalDebit = (clone $totalsQuery)->sum('debit');
        $totalCredit = (clone $totalsQuery)->sum('credit');

        return view('livewire.accounts.show', [
            'lines' => $lines,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'balance' => $totalDebit - $totalCredit,
            'isAggregate' => $this->account->is_header,
        ]);
    }
}
