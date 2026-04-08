<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreClientRequest;
use App\Http\Requests\Api\V1\UpdateClientRequest;
use App\Http\Resources\V1\ClientResource;
use App\Models\Client;
use App\Traits\GeneratesNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Client::query();

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

        $clients = $query->orderBy('name')->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'message' => 'Clients retrieved successfully',
            'data' => ClientResource::collection($clients),
            'meta' => [
                'current_page' => $clients->currentPage(),
                'per_page' => $clients->perPage(),
                'total' => $clients->total(),
                'last_page' => $clients->lastPage(),
            ],
        ]);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['code'] = GeneratesNumber::generateSimpleNumber('CLT', 'clients', 'code');
        $data['is_active'] = true;

        $client = Client::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Client created successfully',
            'data' => new ClientResource($client),
        ], 201);
    }

    public function show(Client $client): JsonResponse
    {
        $client->loadCount(['workOrders', 'invoices']);
        $client->total_revenue = $client->workOrders()->sum('grand_total');

        return response()->json([
            'success' => true,
            'message' => 'Client retrieved successfully',
            'data' => new ClientResource($client),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $client->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Client updated successfully',
            'data' => new ClientResource($client->fresh()),
        ]);
    }

    public function destroy(Client $client): JsonResponse
    {
        $unpaidInvoices = $client->invoices()->where('balance_due', '>', 0)->exists();

        if ($unpaidInvoices) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deactivate client with unpaid invoices',
            ], 422);
        }

        $client->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Client deactivated successfully',
        ]);
    }
}
