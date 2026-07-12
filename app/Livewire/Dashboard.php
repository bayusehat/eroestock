<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\WorkOrder;
use App\Models\ShopeeOrder;
use App\Models\ShopeeOrderDetail;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfYear = $now->copy()->startOfYear();

        $revenueMtd = Transaction::where('type', 'income')->whereBetween('date', [$startOfMonth, $now])->sum('amount');
        $expensesMtd = Transaction::where('type', 'expense')->whereBetween('date', [$startOfMonth, $now])->sum('amount');
        $netProfitMtd = $revenueMtd - $expensesMtd;

        $cashAccounts = Account::where('type', 'asset')
            ->where(fn ($q) => $q->where('code', 'like', '1-100%')->orWhere('name', 'like', '%cash%')->orWhere('name', 'like', '%bank%'))
            ->pluck('id');

        $cashBalance = Transaction::whereIn('account_id', $cashAccounts)
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as balance')
            ->value('balance') ?? 0;
        $cashBalance += Account::whereIn('id', $cashAccounts)->sum('opening_balance');

        $outstandingReceivables = Invoice::whereIn('status', ['sent', 'partially_paid', 'overdue'])->sum('balance_due');

        $recentTransactions = Transaction::with(['account'])
            ->latest('date')
            ->limit(10)
            ->get();

        $workOrderPipeline = WorkOrder::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $potentialPl = ShopeeOrderDetail::whereHas('order', function($q){
            $q->where('order_status', '<>', 'CANCELLED');
        })->sum('model_original_price');

        return view('livewire.dashboard', [
            'revenueMtd' => $revenueMtd,
            'expensesMtd' => $expensesMtd,
            'netProfitMtd' => $netProfitMtd,
            'cashBalance' => $cashBalance,
            'outstandingReceivables' => $outstandingReceivables,
            'outstandingPayables' => 0,
            'potentialPl' => $potentialPl,
            'recentTransactions' => $recentTransactions,
            'workOrderPipeline' => $workOrderPipeline,
        ]);
    }
}
