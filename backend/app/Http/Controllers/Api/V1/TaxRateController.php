<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreTaxRateRequest;
use App\Http\Requests\Api\V1\UpdateTaxRateRequest;
use App\Http\Resources\V1\TaxRateResource;
use App\Models\TaxRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TaxRate::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $taxRates = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Tax rates retrieved successfully',
            'data' => TaxRateResource::collection($taxRates),
        ]);
    }

    public function store(StoreTaxRateRequest $request): JsonResponse
    {
        if ($request->boolean('is_default')) {
            TaxRate::where('type', $request->type)->update(['is_default' => false]);
        }

        $taxRate = TaxRate::create(array_merge($request->validated(), [
            'is_default' => $request->boolean('is_default', false),
            'is_active' => $request->boolean('is_active', true),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Tax rate created successfully',
            'data' => new TaxRateResource($taxRate),
        ], 201);
    }

    public function show(TaxRate $tax_rate): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Tax rate retrieved successfully',
            'data' => new TaxRateResource($tax_rate),
        ]);
    }

    public function update(UpdateTaxRateRequest $request, TaxRate $tax_rate): JsonResponse
    {
        $data = array_filter($request->validated(), fn ($v) => $v !== null);

        if (isset($data['is_default']) && $data['is_default']) {
            TaxRate::where('type', $tax_rate->type)->where('id', '!=', $tax_rate->id)->update(['is_default' => false]);
        }

        $tax_rate->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Tax rate updated successfully',
            'data' => new TaxRateResource($tax_rate->fresh()),
        ]);
    }

    public function destroy(TaxRate $tax_rate): JsonResponse
    {
        $tax_rate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tax rate deleted successfully',
        ]);
    }
}
