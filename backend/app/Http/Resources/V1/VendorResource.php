<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'tax_id' => $this->tax_id,
            'contact_person' => $this->contact_person,
            'payment_terms' => $this->payment_terms,
            'notes' => $this->notes,
            'bank_name' => $this->bank_name,
            'bank_account' => $this->bank_account,
            'bank_holder' => $this->bank_holder,
            'is_active' => $this->is_active,
            'transactions_count' => $this->when(isset($this->transactions_count), fn () => $this->transactions_count),
            'total_expenses' => $this->when(isset($this->total_expenses), fn () => (float) $this->total_expenses),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
