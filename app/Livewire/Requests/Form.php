<?php

namespace App\Livewire\Requests;

use App\Models\Account;
use App\Models\BudgetRequest;
use App\Traits\GeneratesNumber;
use Livewire\Component;

class Form extends Component
{
    public ?BudgetRequest $budgetRequest = null;
    public string $type = 'purchase';
    public string $title = '';
    public string $description = '';
    public ?float $amount = null;
    public ?int $account_id = null;

    protected function rules(): array
    {
        return [
            'type' => ['required'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function mount(?BudgetRequest $budgetRequest = null): void
    {
        $this->budgetRequest = $budgetRequest;
        if ($budgetRequest && $budgetRequest->exists) {
            $this->fill($budgetRequest->only(['type','title','description','amount','account_id']));
        }
    }

    public function save(): void
    {
        $this->validate();
        $data = ['type' => $this->type, 'title' => $this->title,
                 'description' => $this->description ?: null,
                 'amount' => $this->amount, 'account_id' => $this->account_id];

        if ($this->budgetRequest && $this->budgetRequest->exists) {
            $this->budgetRequest->update($data);
        } else {
            $data['request_no'] = GeneratesNumber::generateNumber('REQ', 'budget_requests', 'request_no', 'Y');
            $data['status'] = 'pending';
            $data['created_by'] = auth()->id();
            BudgetRequest::create($data);
        }
        session()->flash('success', 'Request berhasil disimpan.');
        $this->redirect(route('requests.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.requests.form', [
            'accounts' => Account::where('type', 'expense')->orderBy('name')->get(['id','name']),
            'isEditing' => $this->budgetRequest && $this->budgetRequest->exists,
        ]);
    }
}
