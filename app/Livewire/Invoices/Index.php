<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Invoice;
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

    public function markAsSent(int $id): void
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update(['status' => 'sent']);
        session()->flash('success', 'Invoice ditandai sebagai terkirim.');
    }

    public function cancel(int $id): void
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->update(['status' => 'cancelled']);
        session()->flash('success', 'Invoice dibatalkan.');
    }

    public function render()
    {
        $query = Invoice::query()->with('client:id,name');
        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('invoice_no', 'like', "%{$s}%")->orWhereHas('client', fn($q2) => $q2->where('name', 'like', "%{$s}%")));
        }
        if ($this->statusFilter) $query->where('status', $this->statusFilter);

        return view('livewire.invoices.index', ['invoices' => $query->latest('issue_date')->paginate(25)]);
    }
}
