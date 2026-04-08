<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'overtime_hours' => ['nullable', 'numeric', 'min:0'],
            'overtime_rate' => ['nullable', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'array'],
            'allowances.*.name' => ['required_with:allowances', 'string'],
            'allowances.*.amount' => ['required_with:allowances', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'array'],
            'deductions.*.name' => ['required_with:deductions', 'string'],
            'deductions.*.amount' => ['required_with:deductions', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
