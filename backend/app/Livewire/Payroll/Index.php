<?php

namespace App\Livewire\Payroll;

use App\Models\PayrollRecord;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    protected $queryString = ['search', 'statusFilter'];

    public function updatingSearch(): void { $this->resetPage(); }

    public function approve(int $id): void
    {
        PayrollRecord::findOrFail($id)->update(['status' => 'approved']);
        session()->flash('success', 'Payroll disetujui.');
    }

    public function markAsPaid(int $id): void
    {
        PayrollRecord::findOrFail($id)->update(['status' => 'paid', 'paid_date' => now()]);
        session()->flash('success', 'Payroll ditandai sebagai dibayar.');
    }

    public function render()
    {
        $query = PayrollRecord::query()->with('employee:id,name');
        if ($this->search) {
            $s = $this->search;
            $query->whereHas('employee', fn ($q) => $q->where('name', 'like', "%{$s}%"));
        }
        if ($this->statusFilter) $query->where('status', $this->statusFilter);

        return view('livewire.payroll.index', ['records' => $query->latest()->paginate(25)]);
    }
}
