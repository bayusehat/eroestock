<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'quantity' => (float) $this->quantity,
            'unit' => $this->unit,
            'unit_price' => (float) $this->unit_price,
            'discount' => (float) $this->discount,
            'tax_rate' => (float) $this->tax_rate,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}
