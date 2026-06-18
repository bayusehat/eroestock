<?php

namespace App\Livewire\StockOpname;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\Brand;
use App\Models\Client;
use App\Helpers\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\GeneratesNumber;

class Form extends Component
{
    public ?Item $item = null;
    public ?StockOpname $stockOpname = null;
    public string $so_date = '';
    public string $status = '';
    public string $description = '';
    public array $items = [];
    public array $inventoryItem = [];
    public string $search = '';

    protected function rules(): array
    {
        return [
            'so_date' => ['required'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_id' => ['required'],
            'items.*.stock_inhouse' => ['required', 'numeric'],
            'items.*.stock_system' => ['required', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'so_date' => 'Tanggal Stock Opname is required',
            'items.*.inventory_id' => 'Item is required',
            'items.*.stock_inhouse' => 'Stock Inhouse is required',
            'items.*.stock_system' => 'Stock System cannot be 0'
        ];
    }

    public function mount(?StockOpname $stockOpname = null): void
    {
        $this->stockOpname = $stockOpname;
        $this->inventoryItem[] = Inventory::with('item')->get();
        $this->so_date = Carbon::now()->format('d-m-Y H:i');
        if ($stockOpname && $stockOpname->exists) {
            $this->so_date = $stockOpname->so_date;
            $this->status = $stockOpname->status;
            $this->so_by = $stockOpname->so_by;
            $this->description = $stockOpname->description;
            if($stockOpname->items()->exists()){
                $this->items = $stockOpname->items->map(fn ($i) => [
                    'id' => $i->id,
                    'inventory_id' => $i->inventory_id,
                    'stock_system' => $i->stock_system,
                    'stock_inhouse' => $i->stock_inhouse,
                    'stock_remaining' => $i->stock_remaining,
                    'notes' => $i->notes
                ])->toArray();
            }
        }else{
            $this->items = $this->inventoryItem[0]->map(fn ($i) => [
                'id' => null,
                'inventory_id' => $i->id,
                'stock_system' => $i->store_stock,
                'stock_inhouse' => $i->stock_inhouse ?? 0,
                'stock_remaining' => $i->stock_remaining ?? 0,
                'notes' => $i->notes ?? 0
            ])->toArray();
        }

        // if (empty($this->stockOpname)) {
        //     $this->addItem();
        // }
    }

    public function addItem(): void
    {
        $this->items[] = ['id' => '', 'inventory_id' => '', 'stock_system' => 0, 'stock_inhouse' => 0, 'stock_remaining' => 0, 'notes' => ''];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            array_splice($this->items, $index, 1);
            $this->items = array_values($this->items);
        }
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $data = [
                'so_date' => $this->so_date,
                'status' => $this->status ?? 'draft',
                'description' => $this->description,
                'so_by' => auth()->id()
            ];

            if ($this->stockOpname && $this->stockOpname->exists) {
                $this->stockOpname->update($data);
            } else {
                $data['so_number'] = GeneratesNumber::generateNumber('SO', 'stock_opnames', 'so_number', 'Y');
                $data['status'] = 'draft';
                $this->stockOpname = StockOpname::create($data);
            }

            foreach ($this->items as $item) {
                $stock_remaining = ($item['stock_inhouse'] - $item['stock_system']);
                $this->stockOpname->items()->updateOrCreate(
                    ['id' => $item['id'] ?: null],
                    [
                        'inventory_id' => $item['inventory_id'],
                        'stock_system' => $item['stock_system'] ?? 0,
                        'stock_inhouse' => $item['stock_inhouse'] ?? 0,
                        'stock_remaining' => $stock_remaining,
                        'notes' => $item['notes']
                    ]);
                }
        });

        session()->flash('success', 'Stock Opname berhasil disimpan.');
        $this->redirect(route('stock-opname.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.stock-opname.form',[
            'list' => Inventory::with('item')->get(),
            'isEditing' => $this->stockOpname && $this->stockOpname->exists,
        ]);
    }
}
