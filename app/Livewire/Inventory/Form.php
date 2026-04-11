<?php

namespace App\Livewire\Inventory;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\Brand;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Form extends Component
{
    public ?Item $item = null;
    public string $name = '';
    public string $id_brand = '';
    public float $buy_price = 0;
    public float $sell_price = 0;
    public float $margin = 0;
    public array $items = [];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'id_brand' => ['required'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sku' => ['required', 'string'],
            'items.*.color' => ['required'],
            'items.*.size' => ['required', 'numeric', 'min:1'],
            'items.*.store_stock' => ['required', 'numeric', 'min:1'],
            'items.*.warehouse_stock' => ['required', 'numeric', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_brand' => 'Brand name is required',
            'items.*.sku' => 'SKU item is required',
            'items.*.color' => 'Color item is required',
            'items.*.size' => 'Size item is required',
            'items.*.store_stock' => 'Store Stock item is required',
            'items.*.warehouse_stock' => 'Warehouse stock item is required',
        ];
    }

    public function mount(?Item $item = null): void
    {
        $this->item = $item;

        if ($item && $item->exists) {
            $this->id_brand = $item->id_brand;
            $this->name = $item->name;
            $this->buy_price = $item->buy_price ?? 0;
            $this->sell_price = $item->sell_price ?? 0;
            $this->margin = $item->margin ?? 0;
            $this->items = $item->inventory->map(fn ($i) => [
                'sku' => $i->sku,
                'size' => $i->size,
                'color' => $i->color,
                'store_stock' => $i->store_stock,
                'warehouse_stock' => $i->warehouse_stock,
                'total_stock' => $i->total_stock,
            ])->toArray();
        }

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = ['sku' => '', 'size' => 38, 'color' => 'black', 'store_stock' => 0, 'warehouse_stock' => 0, 'total_stock' => 0];
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
            $isUpdate = false;
            $data = [
                'id_brand' => $this->id_brand,
                'name' => $this->name,
                'buy_price' => $this->buy_price,
                'sell_price' => $this->sell_price ?: 0,
                'margin' => $this->margin ?: 0
            ];

            if ($this->item && $this->item->exists) {
                $this->item->update($data);
                $isUpdate = true;
                $this->item->inventory()->delete();
            } else {
                $this->item = Item::create($data);
                $isUpdate = false;
            }

            foreach ($this->items as $item) {
                $total_stock = $item['store_stock'] + $item['warehouse_stock'];
                $this->item->inventory()->create([
                    'sku' => $item['sku'],
                    'color' => $item['color'],
                    'size' => $item['size'] ?? 0,
                    'store_stock' => $item['store_stock'],
                    'warehouse_stock' => $item['warehouse_stock'] ?? 0,
                    'total_stock' => $total_stock ?? 0,
                ]);
            }
        });

        session()->flash('success', 'Item berhasil disimpan.');
        $this->redirect(route('items.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.inventory.form', [
            'brands' => Brand::orderBy('name')->get(['id','name']),
            'isEditing' => $this->item && $this->item->exists
        ]);
    }
}
