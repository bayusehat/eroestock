<?php

namespace App\Livewire\Transactions;

use App\Models\Account;
use App\Models\Transaction;
use App\Traits\GeneratesNumber;
use Livewire\Component;

class Form extends Component
{
    public string $type = 'income';
    public string $date = '';
    public float $amount = 0;
    public ?int $account_id = null;
    public ?int $contra_account_id = null;
    public string $description = '';
    public string $reference_no = '';
    public string $payment_method = 'bank_transfer';
    public string $category = '';

    protected function rules(): array
    {
        return [
            'type' => ['required'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'account_id' => ['required'],
        ];
    }

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $this->validate();
        Transaction::create([
            'transaction_no' => GeneratesNumber::generateNumber('TXN', 'transactions', 'transaction_no', 'Y'),
            'type' => $this->type, 'date' => $this->date, 'amount' => $this->amount,
            'account_id' => $this->account_id, 'contra_account_id' => $this->contra_account_id,
            'description' => $this->description ?: null, 'reference_no' => $this->reference_no ?: null,
            'payment_method' => $this->payment_method ?: null, 'category' => $this->category ?: null,
        ]);
        session()->flash('success', 'Transaksi berhasil disimpan.');
        $this->redirect(route('transactions.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.transactions.form', [
            'accounts' => Account::where('is_active', true)->orderBy('code')->get(['id','code','name']),
        ]);
    }
}
