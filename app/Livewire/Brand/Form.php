<?php

namespace App\Livewire\Brand;

use App\Models\Brand;
use Livewire\Component;

class Form extends Component
{
    public ?Brand $brand = null;
    public string $name = '';
    public string $code = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $notes = '';
    public bool $is_active = true;

    protected function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255']];
    }

    public function mount(?Brand $brand = null): void
    {
        $this->brand = $brand;
        if ($brand && $brand->exists) {
            $this->fill($brand->only(['name','code','email','phone','address','tax_id','contact_person','payment_terms','notes','is_active']));
        }
    }

    public function save(): void
    {
        $this->validate();
        $data = ['name' => $this->name, 'code' => $this->code ?: null, 'email' => $this->email ?: null,
                 'phone' => $this->phone ?: null, 'notes' => $this->notes ?: null,
                 'is_active' => $this->is_active];

        if ($this->brand && $this->brand->exists) {
            $this->brand->update($data);
        } else {
            $this->brand = Brand::create($data);
        }
        session()->flash('success', 'Brand berhasil disimpan.');
        $this->redirect(route('brands.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.brands.form', ['isEditing' => $this->brand && $this->brand->exists]);
    }
}
