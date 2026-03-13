<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wo_number' => $this->wo_number,
            'client_id' => $this->client_id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'priority' => $this->priority,
            'status' => $this->status,
            'order_date' => $this->order_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'completed_date' => $this->completed_date?->format('Y-m-d'),
            'assigned_to' => $this->assigned_to,
            'notes' => $this->notes,
            'total_before_tax' => (float) $this->total_before_tax,
            'total_tax' => (float) $this->total_tax,
            'total_discount' => (float) $this->total_discount,
            'grand_total' => (float) $this->grand_total,
            'client' => new ClientResource($this->whenLoaded('client')),
            'items' => WorkOrderItemResource::collection($this->whenLoaded('items')),
            'assigned_employee' => new EmployeeResource($this->whenLoaded('assignedTo')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
