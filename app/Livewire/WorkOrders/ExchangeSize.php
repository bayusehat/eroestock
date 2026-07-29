<?php

namespace App\Livewire\WorkOrders;

use App\Models\Client;
use App\Models\ShopeeOrder;
use App\Models\ShopeeOrderDetail;
use App\Models\Inventory;
use App\Traits\GeneratesNumber;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Transaction;
use Carbon\Carbon;

class ExchangeSize extends Component
{
    public ?ShopeeOrder $shopeeOrder = null;

    public ?int $client_id = null;
    public string $buyer_name = '';
    public string $order_sn = '';
    public string $title = '';
    public string $description = '';
    public string $category = '';
    public string $priority = 'medium';
    public string $order_date = '';
    public string $due_date = '';
    public int $shopeeId = 0;
    public array $items = [];
    public array $inventoryItem = [];

    protected function rules(): array
    {
        return [
            'order_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['numeric', 'min:0'],
            'items.*.tax_rate' => ['numeric', 'min:0', 'max:100'],
        ];
    }

    public function mount(?ShopeeOrder $shopeeOrder = null): void
    {
        $this->shopeeOrder = $shopeeOrder;
        $this->order_date = now()->format('Y-m-d');
        $this->inventoryItem[] = Inventory::with('item')->get();

        if ($shopeeOrder && $shopeeOrder->exists) {
            $this->buyer_name = $shopeeOrder->buyer_username;
            $this->order_sn = $shopeeOrder->order_sn;
            $this->shopeeId = $shopeeOrder->id;
            if (!empty($shopeeOrder->exchange_size)) {
                $this->items = $shopeeOrder->exchange_size->map(fn ($i) => [
                    'id' => $i->id,
                    'inventory_id' => $i->inventory_id, 'quantity' => $i->quantity,
                    'unit' => $i->unit, 'unit_price' => $i->unit_price,
                    'discount' => $i->discount, 'tax_rate' => $i->tax_rate,
                ])->toArray();
            }
        }

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = ['id' => '', 'inventory_id' => '', 'quantity' => 1, 'unit' => 'pcs', 'unit_price' => 0, 'discount' => 0, 'tax_rate' => 0];
    }

    public function updatedItems($value, $key)
    {
        // $key will look like "0.product_id"
        // We can check if the changed key ends with product_id
        if (str_ends_with($key, '.inventory_id')) {
            $index = explode('.', $key)[0]; // Extract the index (0)

            // Fetch the related value and update the price within the same index
            $product = Inventory::with('item')->find($value);
            $this->items[$index]['unit_price'] = $product ? $product->item->sell_price : 0;
        }
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            array_splice($this->items, $index, 1);
            $this->items = array_values($this->items);
        }
    }

    public function getSubtotalProperty(): float
    {
        return collect($this->items)->sum(fn ($i) => ($i['quantity'] * $i['unit_price']) - ($i['discount'] ?? 0));
    }

    public function getTotalTaxProperty(): float
    {
        return collect($this->items)->sum(fn ($i) => (($i['quantity'] * $i['unit_price']) - ($i['discount'] ?? 0)) * (($i['tax_rate'] ?? 0) / 100));
    }

    public function getTotalDiscountProperty(): float
    {
        return collect($this->items)->sum(fn ($i) => $i['discount'] ?? 0);
    }

    public function getGrandTotalProperty(): float
    {
        return $this->subtotal + $this->totalTax;
    }

    public function addToTransactions($order_id){
        Transaction::create([
            'transaction_no' => GeneratesNumber::generateNumber('TXN', 'transactions', 'transaction_no', 'Y'),
            'type' => 'income', 'date' => Carbon::now()->toDateString(), 'amount' => $order_id->grand_total,
            'account_id' => 4, 'contra_account_id' => 22,
            'description' => 'Offline Order '.$order_id->wo_number ?: null, 'reference_no' => $order_id->wo_number ?: null,
            'payment_method' => 'bank_transfer' ?: null, 'category' => '-' ?: null, 'created_by' => auth()->id()
        ]);
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $totals = $this->calcTotals();

            // if ($this->shopeeOrder && $this->shopeeOrder->exists) {
            //     $this->shopeeOrder->update($data);
            // } else {
            //     $data['wo_number'] = GeneratesNumber::generateNumber('ES', 'work_orders', 'wo_number', 'Y');
            //     $data['status'] = 'draft';
            //     $data['created_by'] = auth()->id();
            //     $this->shopeeOrder = ShopeeOrder::create($data);
            // }

            foreach ($this->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $tax = $lineTotal * (($item['tax_rate'] ?? 0) / 100);
                $subtotal = $lineTotal - ($item['discount'] ?? 0) + $tax;
                $this->shopeeOrder->items()->updateOrCreate(
                ['id' => $item['id'] ?: null],
                [
                    'inventory_id' => $item['inventory_id'], 'quantity' => $item['quantity'],
                    'unit' => $item['unit'], 'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0, 'tax_rate' => $item['tax_rate'] ?? 0,
                    'subtotal' => $subtotal
                ]);
                Inventory::where([
                    'id' => $item['inventory_id']
                ])->decrement('store_stock', (int) $item['quantity']);
            }
        });

        $this->addToTransactions($this->shopeeOrder);
        session()->flash('success', 'Tukar size berhasil disimpan.');
        $this->redirect(route('work-orders.index'), navigate: true);
    }

    private function calcTotals(): array
    {
        $totalBeforeTax = $totalTax = $totalDiscount = 0;
        foreach ($this->items as $item) {
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $discount = $item['discount'] ?? 0;
            $tax = $lineTotal * (($item['tax_rate'] ?? 0) / 100);
            $totalBeforeTax += $lineTotal;
            $totalDiscount += $discount;
            $totalTax += $tax;
        }
        return [
            'total_before_tax' => $totalBeforeTax,
            'total_tax' => $totalTax,
            'total_discount' => $totalDiscount,
            'grand_total' => $totalBeforeTax - $totalDiscount + $totalTax,
        ];
    }

    public function render()
    {
        return view('livewire.work-orders.exchange-size', [
            'isEditing' => $this->shopeeOrder && $this->shopeeOrder->exists,
            'shopeeItems' => ShopeeOrderDetail::where('shopee_order_id',$this->shopeeId)->get() ?? []
        ]);
    }
}
