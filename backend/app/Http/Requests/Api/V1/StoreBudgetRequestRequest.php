<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:ops_budget,expense_approval,other'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
