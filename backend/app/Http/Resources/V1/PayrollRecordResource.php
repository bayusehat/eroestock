<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payroll_no' => $this->payroll_no,
            'employee_id' => $this->employee_id,
            'period_month' => $this->period_month,
            'period_year' => $this->period_year,
            'base_salary' => (float) $this->base_salary,
            'overtime_hours' => (float) $this->overtime_hours,
            'overtime_rate' => (float) $this->overtime_rate,
            'overtime_amount' => (float) $this->overtime_amount,
            'allowances' => $this->allowances,
            'total_allowances' => (float) $this->total_allowances,
            'deductions' => $this->deductions,
            'total_deductions' => (float) $this->total_deductions,
            'gross_pay' => (float) $this->gross_pay,
            'tax_amount' => (float) $this->tax_amount,
            'net_pay' => (float) $this->net_pay,
            'status' => $this->status,
            'paid_date' => $this->paid_date?->format('Y-m-d'),
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
