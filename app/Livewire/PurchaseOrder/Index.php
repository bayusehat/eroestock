<?php

namespace App\Livewire\PurchaseOrder;

use App\Models\Item;
use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = ['search'];

    public function updatingSearch(): void { $this->resetPage(); }

    public bool $showModal = false;

    public function render()
    {
        $items = Item::with(['inventory' => function($query){
            if ($this->search) {
                $s = $this->search;
                $query->where(fn ($q) => $q->where('size', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%"))->orderBy('sku');
            }
        }]);

        return view('livewire.purchase-order.index', ['items' => $items->paginate(25)]);
    }
}
