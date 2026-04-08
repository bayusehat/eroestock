<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use App\Models\PayrollRecord;
use App\Traits\GeneratesNumber;
use Livewire\Component;

class Form extends Component
{
    public ?PayrollRecord $payrollRecord = null;
    public ?int $employee_id = null;
    public int $period_month;
    public int $period_year;
    public float $base_salary = 0;
    public float $overtime_hours = 0;
    public float $overtime_rate = 0;
    public string $allowances_json = '{}';
    public string $deductions_json = '{}';
    public float $tax_amount = 0;
    public string $payment_method = 'bank_transfer';
    public string $notes = '';

    protected function rules(): array
    {
        return [
            'employee_id' => ['required'],
            'period_month' => ['required', 'integer', 'between:1,12'],
            'period_year' => ['required', 'integer', 'min:2020'],
            'base_salary' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function mount(?PayrollRecord $payrollRecord = null): void
    {
        $this->payrollRecord = $payrollRecord;
        $this->period_month = now()->month;
        $this->period_year = now()->year;

        if ($payrollRecord && $payrollRecord->exists) {
            $this->employee_id = $payrollRecord->employee_id;
            $this->period_month = $payrollRecord->period_month;
            $this->period_year = $payrollRecord->period_year;
            $this->base_salary = $payrollRecord->base_salary;
            $this->overtime_hours = $payrollRecord->overtime_hours;
            $this->overtime_rate = $payrollRecord->overtime_rate;
            $this->allowances_json = json_encode($payrollRecord->allowances ?? []);
            $this->deductions_json = json_encode($payrollRecord->deductions ?? []);
            $this->tax_amount = $payrollRecord->tax_amount;
            $this->payment_method = $payrollRecord->payment_method ?? 'bank_transfer';
            $this->notes = $payrollRecord->notes ?? '';
        }
    }

    public function updatedEmployeeId(mixed $value): void
    {
        if ($value) {
            $employee = Employee::find($value);
            if ($employee) $this->base_salary = $employee->base_salary;
        }
    }

    public function getGrossPay(): float
    {
        $allowances = json_decode($this->allowances_json, true) ?? [];
        $overtimeAmount = $this->overtime_hours * $this->overtime_rate;
        return $this->base_salary + $overtimeAmount + array_sum($allowances);
    }

    public function save(): void
    {
        $this->validate();
        $allowances = json_decode($this->allowances_json, true) ?? [];
        $deductions = json_decode($this->deductions_json, true) ?? [];
        $overtimeAmount = $this->overtime_hours * $this->overtime_rate;
        $grossPay = $this->base_salary + $overtimeAmount + array_sum($allowances);
        $netPay = $grossPay - array_sum($deductions) - $this->tax_amount;

        $data = ['employee_id' => $this->employee_id, 'period_month' => $this->period_month,
                 'period_year' => $this->period_year, 'base_salary' => $this->base_salary,
                 'overtime_hours' => $this->overtime_hours, 'overtime_rate' => $this->overtime_rate,
                 'overtime_amount' => $overtimeAmount, 'allowances' => $allowances,
                 'total_allowances' => array_sum($allowances), 'deductions' => $deductions,
                 'total_deductions' => array_sum($deductions), 'gross_pay' => $grossPay,
                 'tax_amount' => $this->tax_amount, 'net_pay' => max(0, $netPay),
                 'payment_method' => $this->payment_method ?: null, 'notes' => $this->notes ?: null];

        if ($this->payrollRecord && $this->payrollRecord->exists) {
            $this->payrollRecord->update($data);
        } else {
            $data['payroll_no'] = GeneratesNumber::generateNumber('PAY', 'payroll_records', 'payroll_no', 'Y');
            $data['status'] = 'draft';
            PayrollRecord::create($data);
        }

        session()->flash('success', 'Payroll berhasil disimpan.');
        $this->redirect(route('payroll.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.payroll.form', [
            'employees' => Employee::where('status', 'active')->orderBy('name')->get(['id','name']),
            'isEditing' => $this->payrollRecord && $this->payrollRecord->exists,
        ]);
    }
}
