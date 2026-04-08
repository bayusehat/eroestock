<?php

namespace App\Livewire\Requests;

use App\Models\BudgetRequest;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    protected $queryString = ['search', 'statusFilter'];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function review(int $id, string $status): void
    {
        BudgetRequest::findOrFail($id)->update([
            'status' => $status,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        session()->flash('success', 'Request berhasil diupdate.');
    }

    public function render()
    {
        $query = BudgetRequest::query()->with('createdByUser:id,name');
        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('title', 'like', "%{$s}%")->orWhere('request_no', 'like', "%{$s}%"));
        }
        if ($this->statusFilter) $query->where('status', $this->statusFilter);

        return view('livewire.requests.index', ['requests' => $query->latest()->paginate(25)]);
    }
}
