<?php

namespace App\Livewire\JournalEntries;

use App\Models\JournalEntry;
use Livewire\Component;

class Show extends Component
{
    public JournalEntry $journalEntry;

    public function mount(JournalEntry $journalEntry): void
    {
        $this->journalEntry = $journalEntry->load(['lines.account', 'createdBy']);
    }

    public function render()
    {
        return view('livewire.journal-entries.show');
    }
}
