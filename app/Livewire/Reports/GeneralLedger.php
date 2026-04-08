<?php

namespace App\Livewire\Reports;

use App\Livewire\Reports\Concerns\AppliesReportControllerQueries;
use App\Models\Account;
use App\Models\JournalEntry;
use Livewire\Component;

class GeneralLedger extends Component
{
    use AppliesReportControllerQueries;

    public string $dateFrom = '';
    public string $dateTo = '';
    public ?int $accountId = null;
    public ?int $expandedEntryId = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfYear()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function toggleEntry(int $entryId): void
    {
        $this->expandedEntryId = $this->expandedEntryId === $entryId ? null : $entryId;
    }

    public function getReportData(): array
    {
        if (! $this->accountId) {
            return [];
        }

        return $this->generalLedgerReport($this->accountId, $this->dateFrom, $this->dateTo) ?? [];
    }

    public function getExpandedJournalProperty(): ?JournalEntry
    {
        if (! $this->expandedEntryId) {
            return null;
        }

        return JournalEntry::with('lines.account')->find($this->expandedEntryId);
    }

    public function render()
    {
        return view('livewire.reports.general-ledger', [
            'accounts' => Account::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'is_header']),
            'data' => $this->getReportData(),
            'expandedJournal' => $this->getExpandedJournalProperty(),
        ]);
    }
}
