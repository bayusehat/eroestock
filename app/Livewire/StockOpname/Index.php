<?php

namespace App\Livewire\StockOpname;

use App\Models\Item;
use App\Models\Inventory;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
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

    public ?StockOpname $changingStatusSo = null;
    public string $newStatus = '';

    public function openListSo($id){
        $data = StockOpnameItem::with('inventory_item')->where('so_id', $id)->get();
        $this->items[] = $data;
    }

    public function delete($id){
        StockOpname::findOrFail($id)->delete();
        StockOpnameItem::findOrFail($id)->delete();
        session()->flash('success', 'Stock Opname berhasil dihapus.');
    }

    public function openStatusModal(int $id): void
    {
        $this->changingStatusSo = StockOpname::find($id);
        $this->newStatus = '';
    }

    public function closeStatusModal(): void
    {
        $this->changingStatusSo = null;
        $this->newStatus = '';
    }

    public function updateStatus(): void
    {
        if (! $this->changingStatusSo || ! $this->newStatus) return;

        $data = ['status' => $this->newStatus];
        if ($this->newStatus === 'approved') {
            $data['status'] = $this->newStatus;
            //calculate stock

        }

        $this->changingStatusSo->update($data);
        session()->flash('success', "Status diperbarui ke {$this->newStatus}");
        $this->closeStatusModal();
    }

    public function calculateStock($id){
        $data = StockOpnameItem::where(['so_id', $id])->get();
        foreach ($data as $i => $item) {
            //
        }
    }

    public function render()
    {
        $stockOpnames = StockOpname::with(['user' => function($query){
            if ($this->search) {
                $s = $this->search;
                $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")
                ->orderBy('name'));
            }
        }]);

        return view('livewire.stock-opname.index', [
            'stockOpnames' => $stockOpnames->paginate(25),
            'transitions' => [
                'draft' => ['on_check', 'approved'],
                'on_check' => ['draft', 'approved'],
            ],
        ]);
    }
}
