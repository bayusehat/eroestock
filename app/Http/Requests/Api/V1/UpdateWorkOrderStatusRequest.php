<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $workOrder = $this->route('work_order');
        $currentStatus = $workOrder?->status ?? '';

        $allowed = match ($currentStatus) {
            'draft' => ['confirmed', 'cancelled'],
            'confirmed' => ['in_progress', 'cancelled'],
            'in_progress' => ['completed', 'cancelled'],
            'completed' => ['invoiced'],
            default => [],
        };

        return [
            'status' => ['required', 'in:' . implode(',', $allowed)],
        ];
    }
}
