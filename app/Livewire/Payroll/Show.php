<?php

namespace App\Livewire\Payroll;

use App\Models\PayrollRecord;
use Livewire\Component;

class Show extends Component
{
    public PayrollRecord $payrollRecord;

    public function mount(PayrollRecord $payrollRecord): void
    {
        $this->payrollRecord = $payrollRecord->load('employee');
    }

    public function approve(): void
    {
        $this->payrollRecord->update(['status' => 'approved']);
        $this->payrollRecord->refresh();
        session()->flash('success', 'Payroll disetujui.');
    }

    public function markAsPaid(): void
    {
        $this->payrollRecord->update(['status' => 'paid', 'paid_date' => now()]);
        $this->payrollRecord->refresh();
        session()->flash('success', 'Payroll ditandai sebagai dibayar.');
    }

    public function render()
    {
        return view('livewire.payroll.show');
    }
}
