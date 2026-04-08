<?php

namespace App\Livewire\JournalEntries;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Traits\GeneratesNumber;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Form extends Component
{
    public string $date = '';
    public string $description = '';
    public array $lines = [];

    protected function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required'],
            'lines.*.debit' => ['numeric', 'min:0'],
            'lines.*.credit' => ['numeric', 'min:0'],
        ];
    }

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
        $this->addLine();
        $this->addLine();
    }

    public function addLine(): void
    {
        $this->lines[] = ['account_id' => null, 'debit' => 0, 'credit' => 0, 'description' => ''];
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) > 2) {
            array_splice($this->lines, $index, 1);
            $this->lines = array_values($this->lines);
        }
    }

    public function getTotalDebitProperty(): float
    {
        return collect($this->lines)->sum('debit');
    }

    public function getTotalCreditProperty(): float
    {
        return collect($this->lines)->sum('credit');
    }

    public function getIsBalancedProperty(): bool
    {
        return abs($this->totalDebit - $this->totalCredit) < 0.01;
    }

    public function save(): void
    {
        $this->validate();

        if (! $this->isBalanced) {
            $this->addError('lines', 'Total debit harus sama dengan total kredit.');
            return;
        }

        DB::transaction(function () {
            $entry = JournalEntry::create([
                'journal_no' => GeneratesNumber::generateNumber('JE', 'journal_entries', 'journal_no', 'Y'),
                'date' => $this->date,
                'description' => $this->description ?: null,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->lines as $line) {
                $entry->lines()->create([
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'description' => $line['description'] ?? null,
                ]);
            }
        });

        session()->flash('success', 'Journal entry berhasil disimpan.');
        $this->redirect(route('journal-entries.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.journal-entries.form', [
            'accounts' => Account::where('is_active', true)->orderBy('code')->get(['id','code','name']),
        ]);
    }
}
