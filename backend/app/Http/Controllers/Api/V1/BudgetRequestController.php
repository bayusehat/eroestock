<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ReviewBudgetRequestRequest;
use App\Http\Requests\Api\V1\StoreBudgetRequestRequest;
use App\Http\Requests\Api\V1\UpdateBudgetRequestRequest;
use App\Http\Resources\V1\BudgetRequestResource;
use App\Models\BudgetRequest;
use App\Traits\GeneratesNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BudgetRequest::query()->with(['createdBy:id,name,email', 'reviewedBy:id,name,email', 'account:id,code,name']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_no', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        $requests = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'message' => 'Budget requests retrieved successfully',
            'data' => BudgetRequestResource::collection($requests),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
                'last_page' => $requests->lastPage(),
            ],
        ]);
    }

    public function store(StoreBudgetRequestRequest $request): JsonResponse
    {
        $requestNo = GeneratesNumber::generateNumber('REQ', 'budget_requests', 'request_no', 'Y');

        $budgetRequest = BudgetRequest::create([
            'request_no' => $requestNo,
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'status' => 'pending',
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Budget request created successfully',
            'data' => new BudgetRequestResource($budgetRequest->load('createdBy')),
        ], 201);
    }

    public function show(BudgetRequest $budgetRequest): JsonResponse
    {
        $budgetRequest->load(['createdBy', 'reviewedBy', 'account']);

        return response()->json([
            'success' => true,
            'message' => 'Budget request retrieved successfully',
            'data' => new BudgetRequestResource($budgetRequest),
        ]);
    }

    public function update(UpdateBudgetRequestRequest $request, BudgetRequest $budgetRequest): JsonResponse
    {
        if ($budgetRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Can only update pending requests',
            ], 422);
        }

        $budgetRequest->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Budget request updated successfully',
            'data' => new BudgetRequestResource($budgetRequest->load(['createdBy', 'reviewedBy'])),
        ]);
    }

    public function destroy(BudgetRequest $budgetRequest): JsonResponse
    {
        if ($budgetRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Can only delete pending requests',
            ], 422);
        }

        $budgetRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Budget request deleted successfully',
        ]);
    }

    public function review(ReviewBudgetRequestRequest $request, BudgetRequest $budgetRequest): JsonResponse
    {
        if ($budgetRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Request has already been reviewed',
            ], 422);
        }

        $budgetRequest->update([
            'status' => $request->status,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $request->review_notes,
            'account_id' => $request->status === 'approved' ? $request->account_id : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Budget request reviewed successfully',
            'data' => new BudgetRequestResource($budgetRequest->load(['createdBy', 'reviewedBy'])),
        ]);
    }
}
