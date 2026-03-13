<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:income,expense,transfer'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'account_id' => ['required', 'exists:accounts,id'],
            'contra_account_id' => ['required', 'exists:accounts,id', 'different:account_id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'work_order_id' => ['nullable', 'exists:work_orders,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'in:cash,bank_transfer,check,other'],
        ];
    }
}
