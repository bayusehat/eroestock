<?php

namespace App\Livewire\Invoices;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\TaxRate;
use App\Traits\GeneratesNumber;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Form extends Component
{
    public ?Invoice $invoice = null;
    public ?int $client_id = null;
    public string $issue_date = '';
    public string $due_date = '';
    public string $notes = '';
    public string $terms = '';
    public array $items = [];

    protected function rules(): array
    {
        return [
            'client_id' => ['required'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function mount(?Invoice $invoice = null): void
    {
        $this->invoice = $invoice;
        $this->issue_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');

        if ($invoice && $invoice->exists) {
            $this->client_id = $invoice->client_id;
            $this->issue_date = $invoice->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d');
            $this->due_date = $invoice->due_date?->format('Y-m-d') ?? now()->addDays(30)->format('Y-m-d');
            $this->notes = $invoice->notes ?? '';
            $this->terms = $invoice->terms ?? '';
            $this->items = $invoice->items->map(fn ($i) => [
                'description' => $i->description, 'quantity' => $i->quantity,
                'unit' => $i->unit ?? 'pcs', 'unit_price' => $i->unit_price,
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

    public function getTaxAmountProperty(): float
    {
        return collect($this->items)->sum(fn ($i) => (($i['quantity'] * $i['unit_price']) - ($i['discount'] ?? 0)) * (($i['tax_rate'] ?? 0) / 100));
    }

    public function getDiscountAmountProperty(): float
    {
        return collect($this->items)->sum(fn ($i) => $i['discount'] ?? 0);
    }

    public function getGrandTotalProperty(): float
    {
        return $this->subtotal + $this->taxAmount;
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $subtotal = collect($this->items)->sum(fn ($i) => $i['quantity'] * $i['unit_price']);
            $discount = collect($this->items)->sum(fn ($i) => $i['discount'] ?? 0);
            $tax = collect($this->items)->sum(fn ($i) => (($i['quantity'] * $i['unit_price']) - ($i['discount'] ?? 0)) * (($i['tax_rate'] ?? 0) / 100));
            $grandTotal = $subtotal - $discount + $tax;

            $data = [
                'client_id' => $this->client_id, 'issue_date' => $this->issue_date,
                'due_date' => $this->due_date, 'notes' => $this->notes ?: null,
                'terms' => $this->terms ?: null, 'subtotal' => $subtotal,
                'discount_amount' => $discount, 'tax_amount' => $tax,
                'grand_total' => $grandTotal, 'balance_due' => $grandTotal,
            ];

            if ($this->invoice && $this->invoice->exists) {
                $this->invoice->update($data);
                $this->invoice->items()->delete();
            } else {
                $data['invoice_no'] = GeneratesNumber::generateNumber('INV', 'invoices', 'invoice_no', 'Y');
                $data['status'] = 'draft';
                $data['amount_paid'] = 0;
                $this->invoice = Invoice::create($data);
            }

            foreach ($this->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $itemTax = ($lineTotal - ($item['discount'] ?? 0)) * (($item['tax_rate'] ?? 0) / 100);
                $this->invoice->items()->create([
                    'description' => $item['description'], 'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? 'pcs', 'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0, 'tax_rate' => $item['tax_rate'] ?? 0,
                    'subtotal' => $lineTotal - ($item['discount'] ?? 0) + $itemTax,
                ]);
            }
        });

        session()->flash('success', 'Invoice berhasil disimpan.');
        $this->redirect(route('invoices.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.invoices.form', [
            'clients' => Client::orderBy('name')->get(['id','name']),
            'isEditing' => $this->invoice && $this->invoice->exists,
        ]);
    }
}
