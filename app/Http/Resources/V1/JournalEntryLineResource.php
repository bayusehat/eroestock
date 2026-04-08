<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'debit' => (float) $this->debit,
            'credit' => (float) $this->credit,
            'description' => $this->description,
            'account' => new AccountResource($this->whenLoaded('account')),
        ];
    }
}
