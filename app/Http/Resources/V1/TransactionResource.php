<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_no' => $this->transaction_no,
            'type' => $this->type,
            'date' => $this->date?->format('Y-m-d'),
            'amount' => (float) $this->amount,
            'account_id' => $this->account_id,
            'contra_account_id' => $this->contra_account_id,
            'client_id' => $this->client_id,
            'vendor_id' => $this->vendor_id,
            'work_order_id' => $this->work_order_id,
            'invoice_id' => $this->invoice_id,
            'category' => $this->category,
            'description' => $this->description,
            'reference_no' => $this->reference_no,
            'payment_method' => $this->payment_method,
            'is_reconciled' => $this->is_reconciled,
            'account' => new AccountResource($this->whenLoaded('account')),
            'contra_account' => new AccountResource($this->whenLoaded('contraAccount')),
            'client' => new ClientResource($this->whenLoaded('client')),
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
