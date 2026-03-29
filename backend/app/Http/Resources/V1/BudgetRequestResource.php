<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_no' => $this->request_no,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount !== null ? (float) $this->amount : null,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'review_notes' => $this->review_notes,
            'account_id' => $this->account_id,
            'account' => $this->whenLoaded('account', fn () => [
                'id' => $this->account->id,
                'code' => $this->account->code,
                'name' => $this->account->name,
            ]),
            'created_by_user' => new UserResource($this->whenLoaded('createdBy')),
            'reviewed_by_user' => new UserResource($this->whenLoaded('reviewedBy')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
