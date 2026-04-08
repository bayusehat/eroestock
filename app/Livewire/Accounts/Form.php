<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
use Livewire\Component;

class Form extends Component
{
    public ?Account $account = null;
    public string $name = '';
    public string $code = '';
    public string $type = 'asset';
    public ?int $parent_id = null;
    public ?string $description = '';
    public float $opening_balance = 0;
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20'],
            'type' => ['required'],
        ];
    }

    public function mount(?Account $account = null): void
    {
        $this->account = $account;
        if ($account && $account->exists) {
            $this->fill($account->only(['name','code','type','parent_id','description','opening_balance','is_active']));
        }
    }

    public function save(): void
    {
        $this->validate();
        $data = ['name' => $this->name, 'code' => $this->code, 'type' => $this->type,
                 'parent_id' => $this->parent_id, 'description' => $this->description ?: null,
                 'opening_balance' => $this->opening_balance, 'is_active' => $this->is_active];

        if ($this->account && $this->account->exists) {
            $this->account->update($data);
        } else {
            Account::create($data);
        }

        session()->flash('success', 'Akun berhasil disimpan.');
        $this->redirect(route('accounts.index'), navigate: true);
    }

    public function render()
    {
        $parentAccounts = Account::whereNull('parent_id');
        if ($this->account && $this->account->exists) {
            $parentAccounts->where('id', '!=', $this->account->id);
        }
        return view('livewire.accounts.form', [
            'parentAccounts' => $parentAccounts->orderBy('code')->get(['id','code','name']),
            'isEditing' => $this->account && $this->account->exists,
        ]);
    }
}
