<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class GeneratePayrollRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'period_year' => ['required', 'integer'],
        ];
    }
}
