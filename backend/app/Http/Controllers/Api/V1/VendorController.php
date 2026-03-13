<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreVendorRequest;
use App\Http\Requests\Api\V1\UpdateVendorRequest;
use App\Http\Resources\V1\VendorResource;
use App\Models\Vendor;
use App\Traits\GeneratesNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Vendor::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $vendors = $query->orderBy('name')->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'message' => 'Vendors retrieved successfully',
            'data' => VendorResource::collection($vendors),
            'meta' => [
                'current_page' => $vendors->currentPage(),
                'per_page' => $vendors->perPage(),
                'total' => $vendors->total(),
                'last_page' => $vendors->lastPage(),
            ],
        ]);
    }

    public function store(StoreVendorRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['code'] = GeneratesNumber::generateSimpleNumber('VND', 'vendors', 'code');
        $data['is_active'] = true;

        $vendor = Vendor::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Vendor created successfully',
            'data' => new VendorResource($vendor),
        ], 201);
    }

    public function show(Vendor $vendor): JsonResponse
    {
        $vendor->loadCount('transactions');
        $vendor->total_expenses = $vendor->transactions()->where('type', 'expense')->sum('amount');

        return response()->json([
            'success' => true,
            'message' => 'Vendor retrieved successfully',
            'data' => new VendorResource($vendor),
        ]);
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): JsonResponse
    {
        $vendor->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Vendor updated successfully',
            'data' => new VendorResource($vendor->fresh()),
        ]);
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        $vendor->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Vendor deactivated successfully',
        ]);
    }
}
