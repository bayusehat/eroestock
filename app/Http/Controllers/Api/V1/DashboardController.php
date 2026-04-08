<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\DashboardResource;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfYear = $now->copy()->startOfYear();

        $revenueAccountTypes = ['revenue'];
        $expenseAccountTypes = ['expense'];

        $revenueMtd = Transaction::where('type', 'income')
            ->whereBetween('date', [$startOfMonth, $now])
            ->sum('amount');

        $revenueYtd = Transaction::where('type', 'income')
            ->whereBetween('date', [$startOfYear, $now])
            ->sum('amount');

        $expensesMtd = Transaction::where('type', 'expense')
            ->whereBetween('date', [$startOfMonth, $now])
            ->sum('amount');

        $expensesYtd = Transaction::where('type', 'expense')
            ->whereBetween('date', [$startOfYear, $now])
            ->sum('amount');

        $cashAccounts = Account::where('type', 'asset')
            ->where(function ($q) {
                $q->where('code', 'like', '1-100%')
                    ->orWhere('name', 'like', '%cash%')
                    ->orWhere('name', 'like', '%bank%');
            })
            ->pluck('id');

        $cashBalance = Transaction::whereIn('account_id', $cashAccounts)
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as balance')
            ->value('balance') ?? 0;

        $cashBalance += Account::whereIn('id', $cashAccounts)->sum('opening_balance');

        $outstandingReceivables = Invoice::whereIn('status', ['sent', 'partially_paid', 'overdue'])
            ->sum('balance_due');

        $outstandingPayables = 0;

        $recentTransactions = Transaction::with(['account', 'client', 'vendor'])
            ->latest('date')
            ->limit(10)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'transaction_no' => $t->transaction_no,
                'type' => $t->type,
                'date' => $t->date?->format('Y-m-d'),
                'amount' => (float) $t->amount,
                'description' => $t->description,
                'account' => $t->account?->name,
            ]);

        $workOrderPipeline = WorkOrder::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $data = [
            'revenue_mtd' => (float) $revenueMtd,
            'revenue_ytd' => (float) $revenueYtd,
            'expenses_mtd' => (float) $expensesMtd,
            'expenses_ytd' => (float) $expensesYtd,
            'net_profit_mtd' => (float) ($revenueMtd - $expensesMtd),
            'net_profit_ytd' => (float) ($revenueYtd - $expensesYtd),
            'cash_balance' => (float) $cashBalance,
            'outstanding_receivables' => (float) $outstandingReceivables,
            'outstanding_payables' => (float) $outstandingPayables,
            'recent_transactions' => $recentTransactions,
            'work_order_pipeline' => $workOrderPipeline,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data retrieved successfully',
            'data' => $data,
        ]);
    }
}
