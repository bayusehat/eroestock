<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:accounts,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['asset', 'liability', 'equity', 'revenue', 'expense'])],
            'sub_type' => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'is_header' => ['boolean'],
            'description' => ['nullable', 'string'],
            'opening_balance' => ['numeric'],
            'is_active' => ['boolean'],
        ];
    }
}
