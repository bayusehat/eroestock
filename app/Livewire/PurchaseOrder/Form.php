<?php

namespace App\Livewire\PurchaseOrder;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\Brand;
use App\Models\Client;
use App\Helpers\StockMovement;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Form extends Component
{
    public ?Item $item = null;
    public ?PurchaseOrder $purchaseOrder = null;
    public string $client_id = '';
    public string $description = '';
    public array $items = [];
    public array $inventoryItem = [];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'client_id' => ['required'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_id' => ['required'],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id' => 'Client/Supplier is required',
            'items.*.inventory_id' => 'Item is required',
            'items.*.quantity' => 'Quantity item is required'
        ];
    }

    public function mount(?PurchaseOrder $purchaseOrder = null): void
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->inventoryItem[] = Inventory::all();

        if ($purchaseOrder && $purchaseOrder->exists) {
            $this->client_id = $purchaseOrder->client_id;
            $this->description = $purchaseOrder->description;
            $this->items = $purchaseOrder->purchase_order_item->map(fn ($i) => [
                'id' => $i->id,
                'inventory_id' => $i->inventory_id,
                'quantity' => $i->quantity,
                'status' => $i->status,
            ])->toArray();
        }

        if (empty($this->purchaseOrder)) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = ['id' => '', 'inventory_id' => '', 'quantity' => 0];
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
                'client_id' => $this->client_id,
                'description' => $this->description,
            ];

            if ($this->purchaseOrder && $this->purchaseOrder->exists) {
                $this->purchaseOrder->update($data);
                $isUpdate = true;
            } else {
                $this->purchaseOrder = PurchaseOrder::create($data);
                $isUpdate = false;
            }

            foreach ($this->items as $item) {
                $this->purchaseOrder->purchase_order_item()->updateOrCreate(
                    ['id' => $item['id'] ?: null],
                    [
                        'inventory_id' => $item['inventory_id'],
                        'quantity' => $item['quantity'] ?? 0,
                        'status' => $item['status']
                    ]);
                }
        });

        session()->flash('success', 'Purchase Order berhasil disimpan.');
        $this->redirect(route('purchase-order.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.purchase-order.form', [
            'clients' => Client::orderBy('name')->get(['id','name']),
            'isEditing' => $this->item && $this->item->exists
        ]);
    }
}
