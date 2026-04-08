<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $typeFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    protected $queryString = ['search', 'typeFilter', 'dateFrom', 'dateTo'];

    public function updatingSearch(): void { $this->resetPage(); }

    public function render()
    {
        $query = Transaction::query()->with('account:id,name');
        if ($this->search) {
            $s = $this->search;
            $query->where(fn ($q) => $q->where('transaction_no', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%"));
        }
        if ($this->typeFilter) $query->where('type', $this->typeFilter);
        if ($this->dateFrom) $query->whereDate('date', '>=', $this->dateFrom);
        if ($this->dateTo) $query->whereDate('date', '<=', $this->dateTo);

        return view('livewire.transactions.index', ['transactions' => $query->latest('date')->paginate(25)]);
    }
}
