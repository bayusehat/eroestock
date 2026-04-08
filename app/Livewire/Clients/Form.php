<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Livewire\Component;

class Form extends Component
{
    public ?Client $client = null;
    public string $name = '';
    public string $code = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $tax_id = '';
    public string $contact_person = '';
    public string $payment_terms = '';
    public string $notes = '';
    public bool $is_active = true;

    protected function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255']];
    }

    public function mount(?Client $client = null): void
    {
        $this->client = $client;
        if ($client && $client->exists) {
            $this->fill($client->only(['name','code','email','phone','address','tax_id','contact_person','payment_terms','notes','is_active']));
        }
    }

    public function save(): void
    {
        $this->validate();
        $data = ['name' => $this->name, 'code' => $this->code ?: null, 'email' => $this->email ?: null,
                 'phone' => $this->phone ?: null, 'address' => $this->address ?: null,
                 'tax_id' => $this->tax_id ?: null, 'contact_person' => $this->contact_person ?: null,
                 'payment_terms' => $this->payment_terms ?: null, 'notes' => $this->notes ?: null,
                 'is_active' => $this->is_active];

        if ($this->client && $this->client->exists) {
            $this->client->update($data);
        } else {
            $this->client = Client::create($data);
        }
        session()->flash('success', 'Client berhasil disimpan.');
        $this->redirect(route('clients.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.clients.form', ['isEditing' => $this->client && $this->client->exists]);
    }
}
