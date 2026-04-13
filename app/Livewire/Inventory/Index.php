<?php

namespace App\Livewire\Inventory;

use App\Models\Item;
use App\Models\Inventory;
use Livewire\Component;
use Livewire\WithPagination;
use DB;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = ['search'];

    public function updatingSearch(): void { $this->resetPage(); }

    public bool $showModal = false;
    public ?Inventory $stockStatus = null;
    public int $stockNow = 0;
    public string $stockSide = '';
    public int $update_stock = 0;
    public bool $isSuccess = false;

    public function changeStock(int $id, $side){
        $this->showModal = true;
        $this->stockStatus = Inventory::find($id);
        if($side == 'store_stock'){
            $this->stockNow = $this->stockStatus->store_stock;
            $this->stockSide = 'store_stock';
            $this->update_stock = 0;
        }else{
            $this->stockNow = $this->stockStatus->warehouse_stock;
            $this->stockSide = 'warehouse_stock';
            $this->update_stock = 0;
        }
    }

    public function updateChangeStock(int $id, $side){
        $data = DB::transaction(function () use ($id,$side) {
            $inventory = Inventory::find($id);
            if($side == 'store_stock'){
                $inventory->store_stock = $this->update_stock;
            }else{
                $inventory->warehouse_stock = $this->update_stock;
            }
            if($inventory->save()){
                $this->isSuccess = true;
                $this->showModal = false;
            }
            $this->calTotalStock($id);
        });
    }

    public function calTotalStock($id){
        DB::transaction(function () use ($id) {
            $inventory = Inventory::find($id);
            $stockTotal= $inventory->store_stock + $inventory->warehouse_stock;
            $inventory->total_stock = $stockTotal;
            $inventory->save();
        });
    }

    public function delete(int $id): void
    {
        Item::findOrFail($id)->delete();
        Inventory::findOrFail($id)->delete();
        session()->flash('success', 'Item berhasil dihapus.');
    }

    public function render()
    {
        $items = Item::with(['inventory' => function($query){
            if ($this->search) {
                $s = $this->search;
                $query->where(fn ($q) => $q->where('size', 'like', "%{$s}%")->orWhere('sku', 'like', "%{$s}%"))->orderBy('sku');
            }
        }]);

        return view('livewire.inventory.index', ['items' => $items->paginate(25)]);
    }
}
