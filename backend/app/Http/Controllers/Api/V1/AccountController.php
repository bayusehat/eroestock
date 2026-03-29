<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAccountRequest;
use App\Http\Requests\Api\V1\UpdateAccountRequest;
use App\Http\Resources\V1\AccountResource;
use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class AccountController extends Controller
{
    public function index(): JsonResponse
    {
        $accounts = Account::with('children')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Accounts retrieved successfully',
            'data' => AccountResource::collection($accounts),
        ]);
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = Account::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => new AccountResource($account),
        ], 201);
    }

    public function show(Account $account): JsonResponse
    {
        $account->load('children', 'parent');

        return response()->json([
            'success' => true,
            'message' => 'Account retrieved successfully',
            'data' => new AccountResource($account),
        ]);
    }

    public function update(UpdateAccountRequest $request, Account $account): JsonResponse
    {
        $account->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Account updated successfully',
            'data' => new AccountResource($account),
        ]);
    }

    public function destroy(Account $account): JsonResponse
    {
        if ($account->transactions()->exists() || $account->contraTransactions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account with existing transactions',
            ], 422);
        }

        if ($account->journalEntryLines()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account with journal entry lines',
            ], 422);
        }

        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully',
        ]);
    }

    public function tree(): JsonResponse
    {
        $accounts = Account::with('children.children')
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        $balances = $this->computeAccountBalances();
        $this->mergeBalancesIntoTree($accounts, $balances);

        return response()->json([
            'success' => true,
            'message' => 'Account tree retrieved successfully',
            'data' => AccountResource::collection($accounts),
        ]);
    }

    /**
     * Compute current balance for each leaf account (opening + transactions + journal entries).
     */
    private function computeAccountBalances(): array
    {
        $accounts = Account::where('is_header', false)->where('is_active', true)->get();
        $balances = [];

        foreach ($accounts as $account) {
            $txnBalance = Transaction::where('account_id', $account->id)
                ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as balance')
                ->value('balance') ?? 0;

            $contraBalance = Transaction::where('contra_account_id', $account->id)
                ->selectRaw('SUM(CASE WHEN type = "expense" THEN amount ELSE -amount END) as balance')
                ->value('balance') ?? 0;

            $journalBalance = JournalEntryLine::where('account_id', $account->id)
                ->selectRaw('SUM(debit) - SUM(credit) as balance')
                ->value('balance') ?? 0;

            $balance = (float) $account->opening_balance + (float) $txnBalance + (float) $contraBalance + (float) $journalBalance;

            if (in_array($account->type, ['liability', 'equity', 'revenue'])) {
                $balance = -$balance;
            }

            $balances[$account->id] = (float) round($balance, 2);
        }

        return $balances;
    }

    /**
     * Merge computed balances into account tree. Header accounts get sum of children.
     */
    private function mergeBalancesIntoTree(Collection $accounts, array $balances): float
    {
        $total = 0.0;

        foreach ($accounts as $account) {
            if ($account->is_header && $account->relationLoaded('children')) {
                $childBalance = $this->mergeBalancesIntoTree($account->children, $balances);
                $account->balance = round($childBalance, 2);
                $total += $childBalance;
            } else {
                $balance = $balances[$account->id] ?? 0;
                $account->balance = $balance;
                $total += $balance;
            }
        }

        return $total;
    }
}
