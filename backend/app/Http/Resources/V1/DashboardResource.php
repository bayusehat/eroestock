<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'revenue_mtd' => $this->resource['revenue_mtd'] ?? 0,
            'revenue_ytd' => $this->resource['revenue_ytd'] ?? 0,
            'expenses_mtd' => $this->resource['expenses_mtd'] ?? 0,
            'expenses_ytd' => $this->resource['expenses_ytd'] ?? 0,
            'net_profit_mtd' => $this->resource['net_profit_mtd'] ?? 0,
            'net_profit_ytd' => $this->resource['net_profit_ytd'] ?? 0,
            'cash_balance' => $this->resource['cash_balance'] ?? 0,
            'outstanding_receivables' => $this->resource['outstanding_receivables'] ?? 0,
            'outstanding_payables' => $this->resource['outstanding_payables'] ?? 0,
            'recent_transactions' => $this->resource['recent_transactions'] ?? [],
            'work_order_pipeline' => $this->resource['work_order_pipeline'] ?? [],
        ];
    }
}
