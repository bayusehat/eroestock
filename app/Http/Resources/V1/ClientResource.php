<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'is_active' => $this->is_active,
            'work_orders_count' => $this->when(isset($this->work_orders_count), fn () => $this->work_orders_count),
            'invoices_count' => $this->when(isset($this->invoices_count), fn () => $this->invoices_count),
            'total_revenue' => $this->when(isset($this->total_revenue), fn () => (float) $this->total_revenue),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
