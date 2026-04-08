<?php

namespace App\Livewire\WorkOrders;

use App\Models\Client;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Traits\GeneratesNumber;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Form extends Component
{
    public ?WorkOrder $workOrder = null;

    public ?int $client_id = null;
    public string $client_work_order_id = '';
    public string $title = '';
    public string $description = '';
    public string $category = '';
    public string $priority = 'medium';
    public string $order_date = '';
    public string $due_date = '';
    public array $items = [];

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'order_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['required', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['numeric', 'min:0'],
            'items.*.tax_rate' => ['numeric', 'min:0', 'max:100'],
        ];
    }

    public function mount(?WorkOrder $workOrder = null): void
    {
        $this->workOrder = $workOrder;
        $this->order_date = now()->format('Y-m-d');

        if ($workOrder && $workOrder->exists) {
            $this->client_id = $workOrder->client_id;
            $this->client_work_order_id = $workOrder->client_work_order_id ?? '';
            $this->title = $workOrder->title;
            $this->description = $workOrder->description ?? '';
            $this->category = $workOrder->category ?? '';
            $this->priority = $workOrder->priority ?? 'medium';
            $this->order_date = $workOrder->order_date?->format('Y-m-d') ?? now()->format('Y-m-d');
            $this->due_date = $workOrder->due_date?->format('Y-m-d') ?? '';
            $this->items = $workOrder->items->map(fn ($i) => [
                'description' => $i->description, 'quantity' => $i->quantity,
                'unit' => $i->unit, 'unit_price' => $i->unit_price,
                'discount' => $i->discount, 'tax_rate' => $i->tax_rate,
            ])->toArray();
        }

        if (empty($this->items)) {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = ['description' => '', 'quantity' => 1, 'unit' => 'pcs', 'unit_price' => 0, 'discount' => 0, 'tax_rate' => 0];
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

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $totals = $this->calcTotals();
            $data = [
                'client_id' => $this->client_id ?: null,
                'client_work_order_id' => $this->client_work_order_id ?: null,
                'title' => $this->title,
                'description' => $this->description ?: null,
                'category' => $this->category ?: null,
                'priority' => $this->priority,
                'order_date' => $this->order_date,
                'due_date' => $this->due_date ?: null,
                ...$totals,
            ];

            if ($this->workOrder && $this->workOrder->exists) {
                $this->workOrder->update($data);
                $this->workOrder->items()->delete();
            } else {
                $data['wo_number'] = GeneratesNumber::generateNumber('WO', 'work_orders', 'wo_number', 'Y');
                $data['status'] = 'draft';
                $data['created_by'] = auth()->id();
                $this->workOrder = WorkOrder::create($data);
            }

            foreach ($this->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $tax = $lineTotal * (($item['tax_rate'] ?? 0) / 100);
                $subtotal = $lineTotal - ($item['discount'] ?? 0) + $tax;
                $this->workOrder->items()->create([
                    'description' => $item['description'], 'quantity' => $item['quantity'],
                    'unit' => $item['unit'], 'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0, 'tax_rate' => $item['tax_rate'] ?? 0,
                    'subtotal' => $subtotal,
                ]);
            }
        });

        session()->flash('success', 'Work order berhasil disimpan.');
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
        return view('livewire.work-orders.form', [
            'clients' => Client::orderBy('name')->get(['id','name']),
            'isEditing' => $this->workOrder && $this->workOrder->exists,
        ]);
    }
}
