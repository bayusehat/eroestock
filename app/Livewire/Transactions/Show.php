<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use Livewire\Component;

class Show extends Component
{
    public Transaction $transaction;

    public function mount(Transaction $transaction): void
    {
        $this->transaction = $transaction->load(['account', 'contraAccount', 'client', 'vendor']);
    }

    public function render()
    {
        return view('livewire.transactions.show');
    }
}
