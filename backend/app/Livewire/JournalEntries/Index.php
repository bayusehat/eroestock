<?php

namespace App\Livewire\JournalEntries;

use App\Models\JournalEntry;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = ['search'];

    public function updatingSearch(): void { $this->resetPage(); }

    public function render()
    {
        $query = JournalEntry::query()->withCount('lines');
        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('journal_no', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%"));
        }
        return view('livewire.journal-entries.index', ['entries' => $query->latest('date')->paginate(25)]);
    }
}
