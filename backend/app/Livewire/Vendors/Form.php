<?php

namespace App\Livewire\Vendors;

use App\Models\Vendor;
use Livewire\Component;

class Form extends Component
{
    public ?Vendor $vendor = null;
    public string $name = '';
    public string $code = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $tax_id = '';
    public string $contact_person = '';
    public string $payment_terms = '';
    public string $bank_name = '';
    public string $bank_account = '';
    public string $bank_holder = '';
    public string $notes = '';
    public bool $is_active = true;

    protected function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255']];
    }

    public function mount(?Vendor $vendor = null): void
    {
        $this->vendor = $vendor;
        if ($vendor && $vendor->exists) {
            $this->fill($vendor->only(['name','code','email','phone','address','tax_id','contact_person','payment_terms','bank_name','bank_account','bank_holder','notes','is_active']));
        }
    }

    public function save(): void
    {
        $this->validate();
        $data = array_filter(['name' => $this->name, 'code' => $this->code ?: null, 'email' => $this->email ?: null,
                 'phone' => $this->phone ?: null, 'address' => $this->address ?: null,
                 'tax_id' => $this->tax_id ?: null, 'contact_person' => $this->contact_person ?: null,
                 'payment_terms' => $this->payment_terms ?: null, 'bank_name' => $this->bank_name ?: null,
                 'bank_account' => $this->bank_account ?: null, 'bank_holder' => $this->bank_holder ?: null,
                 'notes' => $this->notes ?: null], fn($v) => $v !== null);
        $data['is_active'] = $this->is_active;

        if ($this->vendor && $this->vendor->exists) {
            $this->vendor->update($data);
        } else {
            $this->vendor = Vendor::create($data);
        }
        session()->flash('success', 'Vendor berhasil disimpan.');
        $this->redirect(route('vendors.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.vendors.form', ['isEditing' => $this->vendor && $this->vendor->exists]);
    }
}
