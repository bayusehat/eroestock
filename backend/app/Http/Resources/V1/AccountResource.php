<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'sub_type' => $this->sub_type,
            'parent_id' => $this->parent_id,
            'is_header' => $this->is_header,
            'description' => $this->description,
            'opening_balance' => (float) $this->opening_balance,
            'is_active' => $this->is_active,
            'is_system' => $this->is_system,
            'children' => AccountResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
