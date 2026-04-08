<?php

namespace App\Livewire\Invoices;

use App\Models\Account;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Show extends Component
{
    public Invoice $invoice;
    public bool $showPaymentModal = false;
    public float $paymentAmount = 0;
    public string $paymentDate = '';
    public string $paymentMethod = 'bank_transfer';
    public ?int $paymentAccountId = null;

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice->load(['client', 'items']);
        $this->paymentDate = now()->format('Y-m-d');
        $this->paymentAmount = $invoice->balance_due;
    }

    public function markAsSent(): void
    {
        $this->invoice->update(['status' => 'sent']);
        $this->invoice->refresh();
        session()->flash('success', 'Invoice ditandai sebagai terkirim.');
    }

    public function cancel(): void
    {
        $this->invoice->update(['status' => 'cancelled']);
        $this->invoice->refresh();
        session()->flash('success', 'Invoice dibatalkan.');
    }

    public function recordPayment(): void
    {
        $this->validate([
            'paymentAmount' => ['required', 'numeric', 'min:0.01'],
            'paymentDate' => ['required', 'date'],
        ]);

        DB::transaction(function () {
            $amountPaid = $this->invoice->amount_paid + $this->paymentAmount;
            $balanceDue = $this->invoice->grand_total - $amountPaid;
            $status = $balanceDue <= 0 ? 'paid' : 'partially_paid';

            $this->invoice->update([
                'amount_paid' => $amountPaid,
                'balance_due' => max(0, $balanceDue),
                'status' => $status,
            ]);
        });

        $this->invoice->refresh()->load(['client', 'items']);
        $this->showPaymentModal = false;
        session()->flash('success', 'Pembayaran berhasil dicatat.');
    }

    public function render()
    {
        return view('livewire.invoices.show', [
            'accounts' => Account::where('type', 'asset')->orderBy('name')->get(['id','name']),
        ]);
    }
}
