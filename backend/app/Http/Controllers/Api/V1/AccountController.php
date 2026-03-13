<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAccountRequest;
use App\Http\Requests\Api\V1\UpdateAccountRequest;
use App\Http\Resources\V1\AccountResource;
use App\Models\Account;
use Illuminate\Http\JsonResponse;

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

        return response()->json([
            'success' => true,
            'message' => 'Account tree retrieved successfully',
            'data' => AccountResource::collection($accounts),
        ]);
    }
}
