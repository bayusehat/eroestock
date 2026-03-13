<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntryLine;
use App\Models\PayrollRecord;
use App\Models\Transaction;
use App\Models\Vendor;
use App\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function dateRange(Request $request): array
    {
        $year = now()->year;
        $dateFrom = $request->filled('date_from')
            ? $request->date_from
            : "{$year}-01-01";
        $dateTo = $request->filled('date_to')
            ? $request->date_to
            : "{$year}-12-31";

        return [$dateFrom, $dateTo];
    }

    public function profitLoss(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);

        $revenueByAccount = Transaction::query()
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where('transactions.type', 'income')
            ->whereBetween('transactions.date', [$dateFrom, $dateTo])
            ->select('accounts.id', 'accounts.code', 'accounts.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->get()
            ->map(fn ($r) => [
                'account_id' => $r->id,
                'code' => $r->code,
                'name' => $r->name,
                'amount' => (float) $r->total,
            ])
            ->values()
            ->toArray();

        $expenseByAccount = Transaction::query()
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$dateFrom, $dateTo])
            ->select('accounts.id', 'accounts.code', 'accounts.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->get()
            ->map(fn ($r) => [
                'account_id' => $r->id,
                'code' => $r->code,
                'name' => $r->name,
                'amount' => (float) $r->total,
            ])
            ->values()
            ->toArray();

        $totalRevenue = array_sum(array_column($revenueByAccount, 'amount'));
        $totalExpenses = array_sum(array_column($expenseByAccount, 'amount'));
        $netProfit = $totalRevenue - $totalExpenses;

        $data = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_revenue' => (float) $totalRevenue,
            'total_expenses' => (float) $totalExpenses,
            'net_profit' => (float) $netProfit,
            'revenue_accounts' => $revenueByAccount,
            'expense_accounts' => $expenseByAccount,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Profit & Loss report retrieved successfully',
            'data' => $data,
        ]);
    }

    public function balanceSheet(Request $request): JsonResponse
    {
        $dateTo = $request->filled('date_to') ? $request->date_to : now()->format('Y-m-d');

        $accounts = Account::where('is_header', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $balances = [];
        foreach ($accounts as $account) {
            $txnBalance = Transaction::where('account_id', $account->id)
                ->whereDate('date', '<=', $dateTo)
                ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as balance')
                ->value('balance') ?? 0;

            $journalDebits = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereDate('date', '<=', $dateTo))
                ->sum('debit');

            $journalCredits = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereDate('date', '<=', $dateTo))
                ->sum('credit');

            $balance = (float) $account->opening_balance + (float) $txnBalance + (float) $journalDebits - (float) $journalCredits;

            if ($account->type === 'liability' || $account->type === 'equity' || $account->type === 'revenue') {
                $balance = -$balance;
            }

            $balances[$account->id] = $balance;
        }

        $assets = [];
        $liabilities = [];
        $equity = [];

        foreach ($accounts as $account) {
            $balance = $balances[$account->id] ?? 0;
            if (abs($balance) < 0.01) {
                continue;
            }

            $item = [
                'account_id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'balance' => (float) $balance,
            ];

            if ($account->type === 'asset') {
                $assets[] = $item;
            } elseif ($account->type === 'liability') {
                $liabilities[] = $item;
            } elseif ($account->type === 'equity') {
                $equity[] = $item;
            }
        }

        $totalAssets = array_sum(array_column($assets, 'balance'));
        $totalLiabilities = array_sum(array_column($liabilities, 'balance'));
        $totalEquity = array_sum(array_column($equity, 'balance'));
        $liabilitiesPlusEquity = $totalLiabilities + $totalEquity;

        $data = [
            'as_of_date' => $dateTo,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => (float) $totalAssets,
            'total_liabilities' => (float) $totalLiabilities,
            'total_equity' => (float) $totalEquity,
            'total_liabilities_equity' => (float) $liabilitiesPlusEquity,
            'balanced' => abs($totalAssets - $liabilitiesPlusEquity) < 0.01,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Balance Sheet report retrieved successfully',
            'data' => $data,
        ]);
    }

    public function cashFlow(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);

        $cashAccountIds = Account::where('type', 'asset')
            ->where('code', 'like', '1-1%')
            ->where('is_header', false)
            ->pluck('id');

        $openingBalance = '0';
        foreach ($cashAccountIds as $accountId) {
            $account = Account::find($accountId);
            $openingBalance = bcadd($openingBalance, (string) ($account->opening_balance ?? 0), 2);

            $inflow = Transaction::where('account_id', $accountId)
                ->whereDate('date', '<', $dateFrom)
                ->whereIn('type', ['income', 'transfer'])
                ->sum('amount');
            $outflow = Transaction::where('contra_account_id', $accountId)
                ->whereDate('date', '<', $dateFrom)
                ->whereIn('type', ['expense', 'transfer'])
                ->sum('amount');
            $openingBalance = bcadd($openingBalance, (string) $inflow, 2);
            $openingBalance = bcsub($openingBalance, (string) $outflow, 2);
        }

        $periodTxns = Transaction::where(function ($q) use ($cashAccountIds) {
            $q->whereIn('account_id', $cashAccountIds)
                ->orWhereIn('contra_account_id', $cashAccountIds);
        })
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->get();

        $operatingInflow = '0';
        $operatingOutflow = '0';
        $investingInflow = '0';
        $investingOutflow = '0';
        $financingInflow = '0';
        $financingOutflow = '0';

        foreach ($periodTxns as $t) {
            $amount = (string) $t->amount;
            $cashIds = $cashAccountIds->toArray();
            $cashIsAccount = in_array($t->account_id, $cashIds);
            $isInflow = $cashIsAccount && in_array($t->type, ['income', 'transfer']);

            if (in_array($t->type, ['income', 'expense'])) {
                if ($isInflow) {
                    $operatingInflow = bcadd($operatingInflow, $amount, 2);
                } else {
                    $operatingOutflow = bcadd($operatingOutflow, $amount, 2);
                }
            } elseif ($t->type === 'transfer') {
                if ($isInflow) {
                    $financingInflow = bcadd($financingInflow, $amount, 2);
                } else {
                    $financingOutflow = bcadd($financingOutflow, $amount, 2);
                }
            } else {
                if ($isInflow) {
                    $investingInflow = bcadd($investingInflow, $amount, 2);
                } else {
                    $investingOutflow = bcadd($investingOutflow, $amount, 2);
                }
            }
        }

        $totalInflow = bcadd(bcadd($operatingInflow, $investingInflow, 2), $financingInflow, 2);
        $totalOutflow = bcadd(bcadd($operatingOutflow, $investingOutflow, 2), $financingOutflow, 2);
        $closingBalance = bcadd($openingBalance, bcsub($totalInflow, $totalOutflow, 2), 2);

        $data = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'opening_balance' => (float) $openingBalance,
            'operating_activities' => [
                'inflow' => (float) $operatingInflow,
                'outflow' => (float) $operatingOutflow,
                'net' => (float) bcsub($operatingInflow, $operatingOutflow, 2),
            ],
            'investing_activities' => [
                'inflow' => (float) $investingInflow,
                'outflow' => (float) $investingOutflow,
                'net' => (float) bcsub($investingInflow, $investingOutflow, 2),
            ],
            'financing_activities' => [
                'inflow' => (float) $financingInflow,
                'outflow' => (float) $financingOutflow,
                'net' => (float) bcsub($financingInflow, $financingOutflow, 2),
            ],
            'total_inflow' => (float) $totalInflow,
            'total_outflow' => (float) $totalOutflow,
            'closing_balance' => (float) $closingBalance,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Cash Flow report retrieved successfully',
            'data' => $data,
        ]);
    }

    public function trialBalance(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);

        $accounts = Account::where('is_header', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $lines = [];
        $totalDebits = '0';
        $totalCredits = '0';

        foreach ($accounts as $account) {
            $txnDebits = Transaction::where('account_id', $account->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('type', 'expense')
                ->sum('amount');

            $txnCredits = Transaction::where('account_id', $account->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('type', 'income')
                ->sum('amount');

            $contraDebits = Transaction::where('contra_account_id', $account->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('type', 'income')
                ->sum('amount');

            $contraCredits = Transaction::where('contra_account_id', $account->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('type', 'expense')
                ->sum('amount');

            $journalDebits = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('date', [$dateFrom, $dateTo]))
                ->sum('debit');

            $journalCredits = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereBetween('date', [$dateFrom, $dateTo]))
                ->sum('credit');

            $debits = (float) $txnDebits + (float) $contraDebits + (float) $journalDebits;
            $credits = (float) $txnCredits + (float) $contraCredits + (float) $journalCredits;

            if (abs($debits - $credits) < 0.01) {
                continue;
            }

            if ($debits > $credits) {
                $debits = $debits - $credits;
                $credits = 0;
            } else {
                $credits = $credits - $debits;
                $debits = 0;
            }

            $lines[] = [
                'account_id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'debits' => (float) $debits,
                'credits' => (float) $credits,
            ];

            $totalDebits = bcadd($totalDebits, (string) $debits, 2);
            $totalCredits = bcadd($totalCredits, (string) $credits, 2);
        }

        $data = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'lines' => $lines,
            'total_debits' => (float) $totalDebits,
            'total_credits' => (float) $totalCredits,
            'balanced' => bccomp($totalDebits, $totalCredits, 2) === 0,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Trial Balance report retrieved successfully',
            'data' => $data,
        ]);
    }

    public function generalLedger(Request $request): JsonResponse
    {
        $accountId = $request->get('account_id');
        if (!$accountId) {
            return response()->json([
                'success' => false,
                'message' => 'account_id is required',
            ], 422);
        }

        $account = Account::find($accountId);
        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Account not found',
            ], 404);
        }

        [$dateFrom, $dateTo] = $this->dateRange($request);

        $openingBalance = (float) ($account->opening_balance ?? 0);
        $beforeTxns = Transaction::where('account_id', $accountId)
            ->whereDate('date', '<', $dateFrom)
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as bal')
            ->value('bal');
        $openingBalance += (float) ($beforeTxns ?? 0);

        $beforeContra = Transaction::where('contra_account_id', $accountId)
            ->whereDate('date', '<', $dateFrom)
            ->selectRaw('SUM(CASE WHEN type = "expense" THEN amount ELSE -amount END) as bal')
            ->value('bal');
        $openingBalance += (float) ($beforeContra ?? 0);

        $journalBefore = JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', fn ($q) => $q->whereDate('date', '<', $dateFrom))
            ->selectRaw('SUM(debit) - SUM(credit) as bal')
            ->value('bal');
        $openingBalance += (float) ($journalBefore ?? 0);

        $movements = [];

        $txns = Transaction::where(function ($q) use ($accountId) {
            $q->where('account_id', $accountId)->orWhere('contra_account_id', $accountId);
        })
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        foreach ($txns as $t) {
            $debit = 0.0;
            $credit = 0.0;
            $desc = $t->description ?? $t->transaction_no;

            if ($t->account_id == $accountId) {
                if ($t->type === 'income') {
                    $debit = (float) $t->amount;
                } else {
                    $credit = (float) $t->amount;
                }
            } else {
                if ($t->type === 'expense') {
                    $debit = (float) $t->amount;
                } else {
                    $credit = (float) $t->amount;
                }
            }

            $movements[] = [
                'date' => $t->date?->format('Y-m-d'),
                'reference' => $t->transaction_no,
                'description' => $desc,
                'debit' => $debit,
                'credit' => $credit,
            ];
        }

        $journalLines = JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', fn ($q) => $q->whereBetween('date', [$dateFrom, $dateTo]))
            ->with('journalEntry')
            ->get();

        foreach ($journalLines as $line) {
            $movements[] = [
                'date' => $line->journalEntry?->date?->format('Y-m-d'),
                'reference' => $line->journalEntry?->journal_no ?? 'JE',
                'description' => $line->description ?? 'Journal entry',
                'debit' => (float) $line->debit,
                'credit' => (float) $line->credit,
            ];
        }

        usort($movements, fn ($a, $b) => strcmp($a['date'], $b['date']));

        $runningBalance = $openingBalance;
        foreach ($movements as &$m) {
            $runningBalance += $m['debit'] - $m['credit'];
            $m['balance'] = (float) round($runningBalance, 2);
        }

        $data = [
            'account' => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
            ],
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'opening_balance' => (float) round($openingBalance, 2),
            'movements' => $movements,
            'closing_balance' => (float) round($runningBalance, 2),
        ];

        return response()->json([
            'success' => true,
            'message' => 'General Ledger retrieved successfully',
            'data' => $data,
        ]);
    }

    public function receivableAging(Request $request): JsonResponse
    {
        $invoices = Invoice::whereNotIn('status', ['paid', 'cancelled'])
            ->where('balance_due', '>', 0)
            ->with('client:id,name,code')
            ->get();

        $buckets = ['current' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0];
        $byClient = [];
        $today = now();

        foreach ($invoices as $inv) {
            $daysOverdue = $inv->due_date ? $today->diffInDays($inv->due_date, false) : 0;
            if ($inv->due_date && $inv->due_date->isFuture()) {
                $daysOverdue = -$daysOverdue;
            }

            $amount = (float) $inv->balance_due;

            if ($daysOverdue <= 30) {
                $bucket = 'current';
            } elseif ($daysOverdue <= 60) {
                $bucket = '31_60';
            } elseif ($daysOverdue <= 90) {
                $bucket = '61_90';
            } else {
                $bucket = '90_plus';
            }

            $buckets[$bucket] += $amount;

            $clientId = $inv->client_id;
            if (!isset($byClient[$clientId])) {
                $byClient[$clientId] = [
                    'client_id' => $clientId,
                    'client_name' => $inv->client?->name ?? 'Unknown',
                    'client_code' => $inv->client?->code ?? null,
                    'current' => 0,
                    '31_60' => 0,
                    '61_90' => 0,
                    '90_plus' => 0,
                    'total' => 0,
                ];
            }
            $byClient[$clientId][$bucket] += $amount;
            $byClient[$clientId]['total'] += $amount;
        }

        $data = [
            'as_of_date' => $today->format('Y-m-d'),
            'by_client' => array_values($byClient),
            'totals' => [
                'current' => (float) $buckets['current'],
                '31_60' => (float) $buckets['31_60'],
                '61_90' => (float) $buckets['61_90'],
                '90_plus' => (float) $buckets['90_plus'],
                'grand_total' => (float) array_sum($buckets),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Receivable Aging report retrieved successfully',
            'data' => $data,
        ]);
    }

    public function payableAging(Request $request): JsonResponse
    {
        $transactions = Transaction::where('type', 'expense')
            ->whereNotNull('vendor_id')
            ->with('vendor:id,name,code')
            ->get();

        $buckets = ['current' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0];
        $byVendor = [];
        $today = now();

        foreach ($transactions as $t) {
            $dueDate = $t->date;
            $daysOverdue = $dueDate ? $today->diffInDays($dueDate, false) : 0;
            if ($dueDate && $dueDate->isFuture()) {
                $daysOverdue = -$daysOverdue;
            }

            $amount = (float) $t->amount;

            if ($daysOverdue <= 30) {
                $bucket = 'current';
            } elseif ($daysOverdue <= 60) {
                $bucket = '31_60';
            } elseif ($daysOverdue <= 90) {
                $bucket = '61_90';
            } else {
                $bucket = '90_plus';
            }

            $buckets[$bucket] += $amount;

            $vendorId = $t->vendor_id;
            if (!isset($byVendor[$vendorId])) {
                $byVendor[$vendorId] = [
                    'vendor_id' => $vendorId,
                    'vendor_name' => $t->vendor?->name ?? 'Unknown',
                    'vendor_code' => $t->vendor?->code ?? null,
                    'current' => 0,
                    '31_60' => 0,
                    '61_90' => 0,
                    '90_plus' => 0,
                    'total' => 0,
                ];
            }
            $byVendor[$vendorId][$bucket] += $amount;
            $byVendor[$vendorId]['total'] += $amount;
        }

        $data = [
            'as_of_date' => $today->format('Y-m-d'),
            'by_vendor' => array_values($byVendor),
            'totals' => [
                'current' => (float) $buckets['current'],
                '31_60' => (float) $buckets['31_60'],
                '61_90' => (float) $buckets['61_90'],
                '90_plus' => (float) $buckets['90_plus'],
                'grand_total' => (float) array_sum($buckets),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Payable Aging report retrieved successfully',
            'data' => $data,
        ]);
    }

    public function incomeByClient(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);

        $totals = Transaction::where('type', 'income')
            ->whereNotNull('client_id')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->select('client_id', DB::raw('SUM(amount) as total'))
            ->groupBy('client_id')
            ->with('client:id,name,code')
            ->get();

        $grandTotal = $totals->sum('total');

        $data = $totals->map(function ($row) use ($grandTotal) {
            $amount = (float) $row->total;
            $pct = $grandTotal > 0 ? ($amount / $grandTotal) * 100 : 0;

            return [
                'client_id' => $row->client_id,
                'client_name' => $row->client?->name ?? 'Unknown',
                'client_code' => $row->client?->code ?? null,
                'amount' => $amount,
                'percentage' => (float) round($pct, 2),
            ];
        })->sortByDesc('amount')->values()->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Income by Client report retrieved successfully',
            'data' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'total_income' => (float) $grandTotal,
                'by_client' => $data,
            ],
        ]);
    }

    public function expenseByCategory(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);

        $totals = Transaction::where('type', 'expense')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where('accounts.type', 'expense')
            ->whereBetween('transactions.date', [$dateFrom, $dateTo])
            ->select('accounts.id', 'accounts.code', 'accounts.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->get();

        $grandTotal = $totals->sum('total');

        $data = $totals->map(function ($row) use ($grandTotal) {
            $amount = (float) $row->total;
            $pct = $grandTotal > 0 ? ($amount / $grandTotal) * 100 : 0;

            return [
                'account_id' => $row->id,
                'code' => $row->code,
                'name' => $row->name,
                'amount' => $amount,
                'percentage' => (float) round($pct, 2),
            ];
        })->sortByDesc('amount')->values()->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Expense by Category report retrieved successfully',
            'data' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'total_expenses' => (float) $grandTotal,
                'by_category' => $data,
            ],
        ]);
    }

    public function workOrderSummary(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);

        $query = WorkOrder::query()
            ->whereBetween('order_date', [$dateFrom, $dateTo]);

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $byStatus = $query->get()
            ->groupBy('status')
            ->map(function ($items) {
                $count = $items->count();
                $totalValue = $items->sum('grand_total');
                return [
                    'count' => $count,
                    'total_value' => (float) $totalValue,
                    'average_value' => $count > 0 ? (float) round($totalValue / $count, 2) : 0,
                ];
            })
            ->toArray();

        $all = WorkOrder::when($request->filled('client_id'), fn ($q) => $q->where('client_id', $request->client_id))
            ->whereBetween('order_date', [$dateFrom, $dateTo])
            ->get();

        $totalWorkOrders = $all->count();
        $totalValue = $all->sum('grand_total');

        $data = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_work_orders' => $totalWorkOrders,
            'total_value' => (float) $totalValue,
            'average_value' => $totalWorkOrders > 0 ? (float) round($totalValue / $totalWorkOrders, 2) : 0,
            'by_status' => $byStatus,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Work Order Summary report retrieved successfully',
            'data' => $data,
        ]);
    }

    public function payrollSummary(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);

        $query = PayrollRecord::with('employee:id,name,department')
            ->whereRaw('DATE(CONCAT(period_year, "-", LPAD(period_month, 2, "0"), "-01")) BETWEEN ? AND ?', [$dateFrom, $dateTo]);

        $records = $query->get();

        $byEmployee = $records->groupBy('employee_id')->map(function ($items, $empId) {
            $first = $items->first();
            return [
                'employee_id' => $empId,
                'employee_name' => $first->employee?->name ?? 'Unknown',
                'department' => $first->employee?->department ?? null,
                'count' => $items->count(),
                'gross_pay' => (float) $items->sum('gross_pay'),
                'total_deductions' => (float) $items->sum('total_deductions'),
                'tax_amount' => (float) $items->sum('tax_amount'),
                'net_pay' => (float) $items->sum('net_pay'),
            ];
        })->values()->toArray();

        $byDepartment = $records->groupBy(fn ($r) => $r->employee?->department ?? 'Unassigned')->map(function ($items) {
            return [
                'count' => $items->count(),
                'total_gross' => (float) $items->sum('gross_pay'),
                'total_deductions' => (float) $items->sum('total_deductions'),
                'total_tax' => (float) $items->sum('tax_amount'),
                'total_net' => (float) $items->sum('net_pay'),
            ];
        })->toArray();

        $data = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_gross' => (float) $records->sum('gross_pay'),
            'total_deductions' => (float) $records->sum('total_deductions'),
            'total_tax' => (float) $records->sum('tax_amount'),
            'total_net' => (float) $records->sum('net_pay'),
            'record_count' => $records->count(),
            'by_employee' => $byEmployee,
            'by_department' => $byDepartment,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Payroll Summary report retrieved successfully',
            'data' => $data,
        ]);
    }

    public function taxSummary(Request $request): JsonResponse
    {
        [$dateFrom, $dateTo] = $this->dateRange($request);

        $invoiceTax = Invoice::whereBetween('issue_date', [$dateFrom, $dateTo])
            ->sum('tax_amount');

        $payrollTax = PayrollRecord::whereRaw('DATE(CONCAT(period_year, "-", LPAD(period_month, 2, "0"), "-01")) BETWEEN ? AND ?', [$dateFrom, $dateTo])
            ->sum('tax_amount');

        $data = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'by_type' => [
                'invoice_tax_collected' => (float) $invoiceTax,
                'payroll_income_tax_withheld' => (float) $payrollTax,
            ],
            'total' => (float) ($invoiceTax + $payrollTax),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Tax Summary report retrieved successfully',
            'data' => $data,
        ]);
    }
}
