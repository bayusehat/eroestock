<?php

namespace App\Livewire\Settings;

use App\Models\TaxRate;
use Livewire\Component;

class TaxRates extends Component
{
    public ?int $editingId = null;
    public string $name = '';
    public float $rate = 0;
    public string $type = 'sales';
    public bool $is_default = false;
    public bool $is_active = true;

    public function edit(int $id): void
    {
        $this->editingId = $id;
        $taxRate = TaxRate::findOrFail($id);
        $this->name = $taxRate->name;
        $this->rate = $taxRate->rate;
        $this->type = $taxRate->type;
        $this->is_default = (bool) $taxRate->is_default;
        $this->is_active = (bool) $taxRate->is_active;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'name', 'rate', 'type', 'is_default']);
        $this->is_active = true;
    }

    public function create(): void
    {
        $this->validate(['name' => ['required', 'string', 'max:255'], 'rate' => ['required', 'numeric', 'min:0', 'max:100']]);
        TaxRate::create(['name' => $this->name, 'rate' => $this->rate, 'type' => $this->type, 'is_default' => $this->is_default, 'is_active' => $this->is_active]);
        $this->reset(['name', 'rate', 'type', 'is_default']);
        $this->is_active = true;
        session()->flash('success', 'Tax rate berhasil ditambahkan.');
    }

    public function update(): void
    {
        $this->validate(['name' => ['required', 'string', 'max:255'], 'rate' => ['required', 'numeric', 'min:0', 'max:100']]);
        TaxRate::findOrFail($this->editingId)->update(['name' => $this->name, 'rate' => $this->rate, 'type' => $this->type, 'is_default' => $this->is_default, 'is_active' => $this->is_active]);
        $this->cancelEdit();
        session()->flash('success', 'Tax rate berhasil diperbarui.');
    }

    public function delete(int $id): void
    {
        TaxRate::findOrFail($id)->delete();
        session()->flash('success', 'Tax rate berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.settings.tax-rates', ['taxRates' => TaxRate::orderBy('type')->orderBy('name')->get()])
            ->layout('components.layouts.app', ['title' => 'Tax rates']);
    }
}
