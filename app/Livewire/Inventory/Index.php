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

        if ($this->search) {
            $items->orWhere('name','like',"%{$this->search}%");
        }

        return view('livewire.inventory.index', ['items' => $items->paginate(25)]);
    }
}
