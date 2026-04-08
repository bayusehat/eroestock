<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use Livewire\Component;

class Index extends Component
{
    public string $search = '';

    public function render()
    {
        $accounts = Account::with('children')->whereNull('parent_id')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('code', 'like', "%{$this->search}%"))
            ->orderBy('code')
            ->get();

        return view('livewire.accounts.index', ['accounts' => $accounts]);
    }
}
