<?php

namespace App\Livewire\Requests;

use App\Models\BudgetRequest;
use Livewire\Component;

class Show extends Component
{
    public BudgetRequest $budgetRequest;

    public function mount(BudgetRequest $budgetRequest): void
    {
        $this->budgetRequest = $budgetRequest->load(['createdByUser', 'reviewedBy', 'account']);
    }

    public function review(string $status): void
    {
        $this->budgetRequest->update(['status' => $status, 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        $this->budgetRequest->refresh();
        session()->flash('success', 'Request berhasil direview.');
    }

    public function render()
    {
        return view('livewire.requests.show');
    }
}
