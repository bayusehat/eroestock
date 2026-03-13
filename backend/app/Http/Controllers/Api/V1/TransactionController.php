<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTransactionRequest;
use App\Http\Requests\Api\V1\UpdateTransactionRequest;
use App\Http\Resources\V1\TransactionResource;
use App\Models\Transaction;
use App\Traits\GeneratesNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::query()->with(['account:id,code,name', 'contraAccount:id,code,name']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_no', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('is_reconciled')) {
            $query->where('is_reconciled', filter_var($request->is_reconciled, FILTER_VALIDATE_BOOLEAN));
        }

        $transactions = $query->latest('date')->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'message' => 'Transactions retrieved successfully',
            'data' => TransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'last_page' => $transactions->lastPage(),
            ],
        ]);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['transaction_no'] = GeneratesNumber::generateNumber('TXN', 'transactions', 'transaction_no', 'Y');
        $data['created_by'] = $request->user()->id;

        $transaction = Transaction::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully',
            'data' => new TransactionResource($transaction->load(['account', 'contraAccount', 'client', 'vendor'])),
        ], 201);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load(['account', 'contraAccount', 'client', 'vendor']);

        return response()->json([
            'success' => true,
            'message' => 'Transaction retrieved successfully',
            'data' => new TransactionResource($transaction),
        ]);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        if ($transaction->is_reconciled) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update reconciled transaction',
            ], 422);
        }

        $transaction->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully',
            'data' => new TransactionResource($transaction->fresh(['account', 'contraAccount', 'client', 'vendor'])),
        ]);
    }

    public function destroy(Transaction $transaction): JsonResponse
    {
        if ($transaction->is_reconciled) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot void reconciled transaction',
            ], 422);
        }

        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction voided successfully',
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $query = Transaction::query();

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $totals = $query->select('type', DB::raw('SUM(amount) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->map(fn ($v) => (float) $v);

        $data = [
            'income' => $totals->get('income', 0),
            'expense' => $totals->get('expense', 0),
            'transfer' => $totals->get('transfer', 0),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Transaction summary retrieved successfully',
            'data' => $data,
        ]);
    }
}
