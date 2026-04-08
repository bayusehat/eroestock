<?php

namespace App\Livewire\Reports\Concerns;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntryLine;
use App\Models\PayrollRecord;
use App\Models\Transaction;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

/**
 * Query logic mirrored from App\Http\Controllers\Api\V1\ReportController.
 */
trait AppliesReportControllerQueries
{
    protected function profitLossReport(string $dateFrom, string $dateTo): array
    {
        $journalRevenue = JournalEntryLine::query()
            ->join('accounts', 'journal_entry_lines.account_id', '=', 'accounts.id')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('accounts.type', 'revenue')
            ->where('journal_entry_lines.credit', '>', 0)
            ->whereBetween('journal_entries.date', [$dateFrom, $dateTo])
            ->select('accounts.id', 'accounts.code', 'accounts.name', DB::raw('SUM(journal_entry_lines.credit) as total'))
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->get()
            ->map(fn ($r) => [
                'account_id' => $r->id,
                'account_code' => $r->code,
                'account_name' => $r->name,
                'amount' => (float) $r->total,
            ])
            ->values()
            ->toArray();

        $txnRevenue = Transaction::query()
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where('transactions.type', 'income')
            ->where('accounts.type', 'revenue')
            ->whereBetween('transactions.date', [$dateFrom, $dateTo])
            ->select('accounts.id', 'accounts.code', 'accounts.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->get()
            ->map(fn ($r) => [
                'account_id' => $r->id,
                'account_code' => $r->code,
                'account_name' => $r->name,
                'amount' => (float) $r->total,
            ])
            ->values()
            ->toArray();

        $revenueMap = [];
        foreach (array_merge($journalRevenue, $txnRevenue) as $item) {
            $key = $item['account_id'];
            if (! isset($revenueMap[$key])) {
                $revenueMap[$key] = $item;
            } else {
                $revenueMap[$key]['amount'] += $item['amount'];
            }
        }
        $revenueByAccount = array_values($revenueMap);

        $txnExpenses = Transaction::query()
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.date', [$dateFrom, $dateTo])
            ->select('accounts.id', 'accounts.code', 'accounts.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->get()
            ->map(fn ($r) => [
                'account_id' => $r->id,
                'account_code' => $r->code,
                'account_name' => $r->name,
                'amount' => (float) $r->total,
            ])
            ->values()
            ->toArray();

        $journalExpenses = JournalEntryLine::query()
            ->join('accounts', 'journal_entry_lines.account_id', '=', 'accounts.id')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('accounts.type', 'expense')
            ->where('journal_entry_lines.debit', '>', 0)
            ->whereBetween('journal_entries.date', [$dateFrom, $dateTo])
            ->select('accounts.id', 'accounts.code', 'accounts.name', DB::raw('SUM(journal_entry_lines.debit) as total'))
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->get()
            ->map(fn ($r) => [
                'account_id' => $r->id,
                'account_code' => $r->code,
                'account_name' => $r->name,
                'amount' => (float) $r->total,
            ])
            ->values()
            ->toArray();

        $expenseMap = [];
        foreach (array_merge($txnExpenses, $journalExpenses) as $item) {
            $key = $item['account_id'];
            if (! isset($expenseMap[$key])) {
                $expenseMap[$key] = $item;
            } else {
                $expenseMap[$key]['amount'] += $item['amount'];
            }
        }
        $expenseByAccount = array_values($expenseMap);

        $totalRevenue = array_sum(array_column($revenueByAccount, 'amount'));
        $totalExpenses = array_sum(array_column($expenseByAccount, 'amount'));
        $netProfit = $totalRevenue - $totalExpenses;

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_revenue' => (float) $totalRevenue,
            'total_expenses' => (float) $totalExpenses,
            'net_profit' => (float) $netProfit,
            'revenue' => $revenueByAccount,
            'expenses' => $expenseByAccount,
        ];
    }

    protected function balanceSheetReport(string $dateTo): array
    {
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

            $contraBalance = Transaction::where('contra_account_id', $account->id)
                ->whereDate('date', '<=', $dateTo)
                ->selectRaw('SUM(CASE WHEN type = "expense" THEN amount ELSE -amount END) as balance')
                ->value('balance') ?? 0;

            $journalDebits = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereDate('date', '<=', $dateTo))
                ->sum('debit');

            $journalCredits = JournalEntryLine::where('account_id', $account->id)
                ->whereHas('journalEntry', fn ($q) => $q->whereDate('date', '<=', $dateTo))
                ->sum('credit');

            $balance = (float) $account->opening_balance
                + (float) $txnBalance
                + (float) $contraBalance
                + (float) $journalDebits
                - (float) $journalCredits;

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
                'account_code' => $account->code,
                'account_name' => $account->name,
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

        return [
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
    }

    protected function cashFlowReport(string $dateFrom, string $dateTo): array
    {
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

        return [
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
    }

    protected function trialBalanceReport(string $dateFrom, string $dateTo): array
    {
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
                ->where('type', 'income')
                ->sum('amount');

            $txnCredits = Transaction::where('account_id', $account->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('type', 'expense')
                ->sum('amount');

            $contraDebits = Transaction::where('contra_account_id', $account->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('type', 'expense')
                ->sum('amount');

            $contraCredits = Transaction::where('contra_account_id', $account->id)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->where('type', 'income')
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

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'lines' => $lines,
            'total_debits' => (float) $totalDebits,
            'total_credits' => (float) $totalCredits,
            'balanced' => bccomp($totalDebits, $totalCredits, 2) === 0,
        ];
    }

    protected function collectDescendantAccountIds(int $parentId): array
    {
        $children = Account::where('parent_id', $parentId)->get(['id']);
        $ids = [];
        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->collectDescendantAccountIds($child->id));
        }

        return $ids;
    }

    protected function generalLedgerReport(int $accountId, string $dateFrom, string $dateTo): ?array
    {
        $account = Account::find($accountId);
        if (! $account) {
            return null;
        }

        $targetIds = [$account->id];
        if ($account->is_header) {
            $targetIds = array_merge($targetIds, $this->collectDescendantAccountIds($account->id));
        }

        $accountMap = Account::whereIn('id', $targetIds)->get()->keyBy('id');

        $openingBalance = 0.0;
        foreach ($targetIds as $tid) {
            $acc = $accountMap[$tid] ?? null;
            $openingBalance += (float) ($acc?->opening_balance ?? 0);
        }

        $beforeTxns = Transaction::whereIn('account_id', $targetIds)
            ->whereDate('date', '<', $dateFrom)
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as bal')
            ->value('bal');
        $openingBalance += (float) ($beforeTxns ?? 0);

        $beforeContra = Transaction::whereIn('contra_account_id', $targetIds)
            ->whereDate('date', '<', $dateFrom)
            ->selectRaw('SUM(CASE WHEN type = "expense" THEN amount ELSE -amount END) as bal')
            ->value('bal');
        $openingBalance += (float) ($beforeContra ?? 0);

        $journalBefore = JournalEntryLine::whereIn('account_id', $targetIds)
            ->whereHas('journalEntry', fn ($q) => $q->whereDate('date', '<', $dateFrom))
            ->selectRaw('SUM(debit) - SUM(credit) as bal')
            ->value('bal');
        $openingBalance += (float) ($journalBefore ?? 0);

        $movements = [];
        $isAggregate = count($targetIds) > 1;

        $txns = Transaction::where(function ($q) use ($targetIds) {
            $q->whereIn('account_id', $targetIds)->orWhereIn('contra_account_id', $targetIds);
        })
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        foreach ($txns as $t) {
            $debit = 0.0;
            $credit = 0.0;
            $desc = $t->description ?? $t->transaction_no;
            $matchedId = null;

            if (in_array($t->account_id, $targetIds)) {
                $matchedId = $t->account_id;
                if ($t->type === 'income') {
                    $debit = (float) $t->amount;
                } else {
                    $credit = (float) $t->amount;
                }
            } else {
                $matchedId = $t->contra_account_id;
                if ($t->type === 'expense') {
                    $debit = (float) $t->amount;
                } else {
                    $credit = (float) $t->amount;
                }
            }

            $entry = [
                'date' => $t->date?->format('Y-m-d'),
                'reference' => $t->transaction_no,
                'description' => $desc,
                'debit' => $debit,
                'credit' => $credit,
            ];

            if ($isAggregate && $matchedId) {
                $matchedAcc = $accountMap[$matchedId] ?? null;
                $entry['account_code'] = $matchedAcc?->code;
                $entry['account_name'] = $matchedAcc?->name;
            }

            $movements[] = $entry;
        }

        $journalLines = JournalEntryLine::whereIn('account_id', $targetIds)
            ->whereHas('journalEntry', fn ($q) => $q->whereBetween('date', [$dateFrom, $dateTo]))
            ->with('journalEntry')
            ->get();

        foreach ($journalLines as $line) {
            $entry = [
                'date' => $line->journalEntry?->date?->format('Y-m-d'),
                'reference' => $line->journalEntry?->journal_no ?? 'JE',
                'description' => $line->description ?? 'Journal entry',
                'debit' => (float) $line->debit,
                'credit' => (float) $line->credit,
                'journal_entry_id' => $line->journal_entry_id,
            ];

            if ($isAggregate) {
                $matchedAcc = $accountMap[$line->account_id] ?? null;
                $entry['account_code'] = $matchedAcc?->code;
                $entry['account_name'] = $matchedAcc?->name;
            }

            $movements[] = $entry;
        }

        usort($movements, fn ($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));

        $runningBalance = $openingBalance;
        $entries = [];
        foreach ($movements as $m) {
            $runningBalance += $m['debit'] - $m['credit'];
            $entry = [
                'date' => $m['date'],
                'description' => $m['description'],
                'reference' => $m['reference'] ?? null,
                'debit' => $m['debit'],
                'credit' => $m['credit'],
                'running_balance' => (float) round($runningBalance, 2),
                'journal_entry_id' => $m['journal_entry_id'] ?? null,
            ];
            if ($isAggregate) {
                $entry['account_code'] = $m['account_code'] ?? null;
                $entry['account_name'] = $m['account_name'] ?? null;
            }
            $entries[] = $entry;
        }

        return [
            'account' => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
            ],
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'opening_balance' => (float) round($openingBalance, 2),
            'entries' => $entries,
            'closing_balance' => (float) round($runningBalance, 2),
            'is_aggregate' => $isAggregate,
        ];
    }

    protected function receivableAgingReport(): array
    {
        $invoices = Invoice::whereNotIn('status', ['paid', 'cancelled'])
            ->where('balance_due', '>', 0)
            ->with('client:id,name,code')
            ->get();

        $buckets = ['current' => 0, '31_60' => 0, '61_90' => 0, '90_plus' => 0];
        $rows = [];
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
            if (! isset($rows[$clientId])) {
                $rows[$clientId] = [
                    'name' => $inv->client?->name ?? 'Unknown',
                    'current' => 0,
                    'days_31_60' => 0,
                    'days_61_90' => 0,
                    'over_90' => 0,
                    'total' => 0,
                ];
            }
            $rows[$clientId]['current'] += $bucket === 'current' ? $amount : 0;
            $rows[$clientId]['days_31_60'] += $bucket === '31_60' ? $amount : 0;
            $rows[$clientId]['days_61_90'] += $bucket === '61_90' ? $amount : 0;
            $rows[$clientId]['over_90'] += $bucket === '90_plus' ? $amount : 0;
            $rows[$clientId]['total'] += $amount;
        }

        return [
            'as_of_date' => $today->format('Y-m-d'),
            'rows' => array_values($rows),
            'totals' => [
                'current' => (float) $buckets['current'],
                'days_31_60' => (float) $buckets['31_60'],
                'days_61_90' => (float) $buckets['61_90'],
                'over_90' => (float) $buckets['90_plus'],
                'total' => (float) array_sum($buckets),
            ],
        ];
    }

    protected function payableAgingReport(): array
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
            if (! isset($byVendor[$vendorId])) {
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

        return [
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
    }

    protected function incomeByClientReport(string $dateFrom, string $dateTo): array
    {
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

        $rows = array_map(fn ($r) => [
            'client_name' => $r['client_name'],
            'amount' => $r['amount'],
            'percentage' => $r['percentage'],
        ], $data);

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_income' => (float) $grandTotal,
            'rows' => $rows,
        ];
    }

    protected function expenseByCategoryReport(string $dateFrom, string $dateTo): array
    {
        $totals = Transaction::where('type', 'expense')
            ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
            ->where('accounts.type', 'expense')
            ->whereBetween('transactions.date', [$dateFrom, $dateTo])
            ->select('accounts.id', 'accounts.code', 'accounts.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name')
            ->get();

        $grandTotal = $totals->sum('total');

        $rows = $totals->map(function ($row) use ($grandTotal) {
            $amount = (float) $row->total;
            $pct = $grandTotal > 0 ? ($amount / $grandTotal) * 100 : 0;

            return [
                'category' => $row->name,
                'amount' => $amount,
                'percentage' => (float) round($pct, 2),
            ];
        })->sortByDesc('amount')->values()->toArray();

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_expenses' => (float) $grandTotal,
            'rows' => $rows,
        ];
    }

    protected function workOrderSummaryReport(string $dateFrom, string $dateTo, ?int $clientId): array
    {
        $query = WorkOrder::query()
            ->whereBetween('order_date', [$dateFrom, $dateTo]);

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        $byStatusRaw = $query->get()
            ->groupBy('status')
            ->map(function ($items) {
                $count = $items->count();
                $totalValue = $items->sum('grand_total');

                return [
                    'count' => $count,
                    'total_value' => (float) $totalValue,
                    'average_value' => $count > 0 ? (float) round($totalValue / $count, 2) : 0,
                ];
            });

        $byStatus = [];
        foreach ($byStatusRaw as $status => $data) {
            $byStatus[] = array_merge(['status' => $status], $data);
        }

        $all = WorkOrder::when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->whereBetween('order_date', [$dateFrom, $dateTo])
            ->get();

        $totalWorkOrders = $all->count();
        $totalValue = $all->sum('grand_total');

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_work_orders' => $totalWorkOrders,
            'total_count' => $totalWorkOrders,
            'total_value' => (float) $totalValue,
            'average_value' => $totalWorkOrders > 0 ? (float) round($totalValue / $totalWorkOrders, 2) : 0,
            'by_status' => $byStatus,
        ];
    }

    protected function payrollSummaryReport(int $month, int $year): array
    {
        $dateFrom = sprintf('%04d-%02d-01', $year, $month);
        $dateTo = date('Y-m-t', strtotime($dateFrom));

        $query = PayrollRecord::with('employee:id,name,department')
            ->whereRaw('DATE(CONCAT(period_year, "-", LPAD(period_month, 2, "0"), "-01")) BETWEEN ? AND ?', [$dateFrom, $dateTo]);

        $records = $query->get();

        $byEmployee = $records->groupBy('employee_id')->map(function ($items, $empId) {
            $first = $items->first();
            $gross = (float) $items->sum('gross_pay');
            $deductions = (float) $items->sum('total_deductions');
            $tax = (float) $items->sum('tax_amount');
            $net = (float) $items->sum('net_pay');

            return [
                'employee_id' => $empId,
                'employee_name' => $first->employee?->name ?? 'Unknown',
                'department' => $first->employee?->department ?? null,
                'base_salary' => (float) $items->sum('base_salary'),
                'overtime' => (float) $items->sum('overtime_amount'),
                'allowances' => (float) $items->sum('total_allowances'),
                'deductions' => $deductions,
                'tax' => $tax,
                'net_pay' => $net,
            ];
        })->values()->toArray();

        $byDepartment = [];
        foreach ($records->groupBy(fn ($r) => $r->employee?->department ?? 'Unassigned') as $dept => $items) {
            $byDepartment[] = [
                'department' => $dept,
                'count' => $items->count(),
                'total' => (float) $items->sum('net_pay'),
            ];
        }

        return [
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
    }

    protected function taxSummaryReport(string $dateFrom, string $dateTo): array
    {
        $invoiceTax = (float) Invoice::whereBetween('issue_date', [$dateFrom, $dateTo])
            ->sum('tax_amount');

        $payrollTax = (float) PayrollRecord::whereRaw('DATE(CONCAT(period_year, "-", LPAD(period_month, 2, "0"), "-01")) BETWEEN ? AND ?', [$dateFrom, $dateTo])
            ->sum('tax_amount');

        $rows = [
            ['tax_type' => 'Sales', 'tax_name' => 'Invoice Tax Collected', 'amount' => $invoiceTax],
            ['tax_type' => 'Payroll', 'tax_name' => 'Income Tax Withheld', 'amount' => $payrollTax],
        ];

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'rows' => $rows,
            'total_collected' => $invoiceTax,
            'total_withheld' => $payrollTax,
            'net_liability' => $invoiceTax + $payrollTax,
        ];
    }
}
