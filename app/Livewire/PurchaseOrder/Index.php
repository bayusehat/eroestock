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

    public array $items = [];

    public function openListPo($id){
        $data = PurchaseOrderItem::with('inventory_item')->where('purchase_order_id', $id)->get();
        $this->items[] = $data;
    }

    public function render()
    {
        $purchaseOrders = PurchaseOrder::with(['client_id' => function($query){
            if ($this->search) {
                $s = $this->search;
                $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")
                ->orderBy('name'));
            }
        }]);

        return view('livewire.purchase-order.index', ['purchaseOrders' => $purchaseOrders->paginate(25)]);
    }
}
