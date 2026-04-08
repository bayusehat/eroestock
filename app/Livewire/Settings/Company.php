<?php

namespace App\Livewire\Settings;

use App\Models\CompanySetting;
use Livewire\Component;
use Livewire\WithFileUploads;

class Company extends Component
{
    use WithFileUploads;

    public string $company_name = '';
    public string $company_address = '';
    public string $company_phone = '';
    public string $company_email = '';
    public string $company_tax_id = '';
    public string $default_currency = 'IDR';
    public string $default_payment_terms = '30';
    public ?string $company_logo = null;
    public mixed $company_logo_upload = null;

    public function mount(): void
    {
        $records = CompanySetting::all()->pluck('value', 'key')->toArray();
        $this->company_name = $records['company_name'] ?? '';
        $this->company_address = $records['company_address'] ?? '';
        $this->company_phone = $records['company_phone'] ?? '';
        $this->company_email = $records['company_email'] ?? '';
        $this->company_tax_id = $records['company_tax_id'] ?? '';
        $this->default_currency = $records['currency'] ?? 'IDR';
        $this->default_payment_terms = $records['default_payment_terms'] ?? '30';
        $this->company_logo = $records['company_logo'] ?? null;
    }

    public function save(): void
    {
        $this->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_email' => ['nullable', 'email'],
            'company_logo_upload' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($this->company_logo_upload) {
            $path = $this->company_logo_upload->store('company', 'public');
            $this->company_logo = $path;
        }

        $settings = [
            'company_name' => $this->company_name,
            'company_address' => $this->company_address,
            'company_phone' => $this->company_phone,
            'company_email' => $this->company_email,
            'company_tax_id' => $this->company_tax_id,
            'currency' => $this->default_currency,
            'default_payment_terms' => $this->default_payment_terms,
            'company_logo' => $this->company_logo ?? '',
        ];

        foreach ($settings as $key => $value) {
            CompanySetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->company_logo_upload = null;
        session()->flash('success', 'Pengaturan perusahaan berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.settings.company')
            ->layout('components.layouts.app', ['title' => 'Company settings']);
    }
}
