<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ReviewBudgetRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:approved,rejected'],
            'review_notes' => ['nullable', 'string'],
            'account_id' => ['required_if:status,approved', 'nullable', 'exists:accounts,id'],
        ];
    }
}
