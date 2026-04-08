<?php

namespace App\Livewire\Reports;

use App\Models\PayrollRecord;
use Livewire\Component;

class PayrollSummary extends Component
{
    public int $period_month;
    public int $period_year;

    public function mount(): void
    {
        $this->period_month = now()->month;
        $this->period_year = now()->year;
    }

    public function getReportData(): array
    {
        $records = PayrollRecord::with('employee:id,name')
            ->where('period_month', $this->period_month)
            ->where('period_year', $this->period_year)
            ->get();

        return [
            'total_gross' => $records->sum('gross_pay'),
            'total_deductions' => $records->sum('total_deductions'),
            'total_tax' => $records->sum('tax_amount'),
            'total_net' => $records->sum('net_pay'),
            'by_employee' => $records->map(fn($r) => [
                'employee_name' => $r->employee?->name ?? '-',
                'base_salary' => $r->base_salary,
                'overtime' => $r->overtime_amount,
                'allowances' => $r->total_allowances,
                'deductions' => $r->total_deductions,
                'tax' => $r->tax_amount,
                'net_pay' => $r->net_pay,
            ]),
        ];
    }

    public function render()
    {
        return view('livewire.reports.payroll-summary', ['data' => $this->getReportData()]);
    }
}
