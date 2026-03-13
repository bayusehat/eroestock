<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWorkOrderRequest;
use App\Http\Requests\Api\V1\UpdateWorkOrderRequest;
use App\Http\Requests\Api\V1\UpdateWorkOrderStatusRequest;
use App\Http\Resources\V1\WorkOrderResource;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Traits\GeneratesNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = WorkOrder::query()->with('client:id,name,code');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('wo_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('order_date_from')) {
            $query->whereDate('order_date', '>=', $request->order_date_from);
        }

        if ($request->filled('order_date_to')) {
            $query->whereDate('order_date', '<=', $request->order_date_to);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $workOrders = $query->latest('order_date')->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'message' => 'Work orders retrieved successfully',
            'data' => WorkOrderResource::collection($workOrders),
            'meta' => [
                'current_page' => $workOrders->currentPage(),
                'per_page' => $workOrders->perPage(),
                'total' => $workOrders->total(),
                'last_page' => $workOrders->lastPage(),
            ],
        ]);
    }

    public function store(StoreWorkOrderRequest $request): JsonResponse
    {
        $workOrder = DB::transaction(function () use ($request) {
            $woNumber = GeneratesNumber::generateNumber('WO', 'work_orders', 'wo_number', 'Y');

            $totals = $this->calculateTotals($request->items);

            $workOrder = WorkOrder::create([
                'wo_number' => $woNumber,
                'client_id' => $request->client_id,
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'priority' => $request->priority ?? 'medium',
                'status' => 'draft',
                'order_date' => $request->order_date,
                'due_date' => $request->due_date,
                'assigned_to' => $request->assigned_to,
                'notes' => $request->notes,
                'total_before_tax' => $totals['total_before_tax'],
                'total_tax' => $totals['total_tax'],
                'total_discount' => $totals['total_discount'],
                'grand_total' => $totals['grand_total'],
                'created_by' => $request->user()->id,
            ]);

            foreach ($request->items as $item) {
                $subtotal = $this->calculateItemSubtotal(
                    $item['quantity'],
                    $item['unit_price'],
                    $item['discount'] ?? 0,
                    $item['tax_rate'] ?? 0
                );
                WorkOrderItem::create([
                    'work_order_id' => $workOrder->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? null,
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'subtotal' => $subtotal,
                ]);
            }

            return $workOrder;
        });

        return response()->json([
            'success' => true,
            'message' => 'Work order created successfully',
            'data' => new WorkOrderResource($workOrder->load(['client', 'items', 'assignedTo'])),
        ], 201);
    }

    public function show(WorkOrder $workOrder): JsonResponse
    {
        $workOrder->load(['client', 'items', 'assignedTo']);

        return response()->json([
            'success' => true,
            'message' => 'Work order retrieved successfully',
            'data' => new WorkOrderResource($workOrder),
        ]);
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder): JsonResponse
    {
        if (!in_array($workOrder->status, ['draft', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Can only update work orders in draft or confirmed status',
            ], 422);
        }

        $workOrder = DB::transaction(function () use ($request, $workOrder) {
            $data = array_filter($request->only([
                'client_id', 'title', 'description', 'category', 'priority',
                'order_date', 'due_date', 'assigned_to', 'notes',
            ]), fn ($v) => $v !== null);

            if ($request->has('items')) {
                $workOrder->items()->delete();
                $totals = $this->calculateTotals($request->items);

                foreach ($request->items as $item) {
                    $subtotal = $this->calculateItemSubtotal(
                        $item['quantity'],
                        $item['unit_price'],
                        $item['discount'] ?? 0,
                        $item['tax_rate'] ?? 0
                    );
                    WorkOrderItem::create([
                        'work_order_id' => $workOrder->id,
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'] ?? null,
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'tax_rate' => $item['tax_rate'] ?? 0,
                        'subtotal' => $subtotal,
                    ]);
                }

                $data['total_before_tax'] = $totals['total_before_tax'];
                $data['total_tax'] = $totals['total_tax'];
                $data['total_discount'] = $totals['total_discount'];
                $data['grand_total'] = $totals['grand_total'];
            }

            $workOrder->update($data);

            return $workOrder->fresh(['client', 'items', 'assignedTo']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Work order updated successfully',
            'data' => new WorkOrderResource($workOrder),
        ]);
    }

    public function destroy(WorkOrder $workOrder): JsonResponse
    {
        if ($workOrder->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only delete work orders in draft status',
            ], 422);
        }

        $workOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Work order deleted successfully',
        ]);
    }

    public function updateStatus(UpdateWorkOrderStatusRequest $request, WorkOrder $workOrder): JsonResponse
    {
        $data = ['status' => $request->status];

        if ($request->status === 'completed') {
            $data['completed_date'] = now();
        }

        $workOrder->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Work order status updated successfully',
            'data' => new WorkOrderResource($workOrder->fresh(['client', 'items', 'assignedTo'])),
        ]);
    }

    public function duplicate(WorkOrder $workOrder): JsonResponse
    {
        $newWorkOrder = DB::transaction(function () use ($workOrder) {
            $woNumber = GeneratesNumber::generateNumber('WO', 'work_orders', 'wo_number', 'Y');

            $newWorkOrder = WorkOrder::create([
                'wo_number' => $woNumber,
                'client_id' => $workOrder->client_id,
                'title' => $workOrder->title,
                'description' => $workOrder->description,
                'category' => $workOrder->category,
                'priority' => $workOrder->priority,
                'status' => 'draft',
                'order_date' => $workOrder->order_date,
                'due_date' => $workOrder->due_date,
                'assigned_to' => $workOrder->assigned_to,
                'notes' => $workOrder->notes,
                'total_before_tax' => $workOrder->total_before_tax,
                'total_tax' => $workOrder->total_tax,
                'total_discount' => $workOrder->total_discount,
                'grand_total' => $workOrder->grand_total,
                'created_by' => request()->user()->id,
            ]);

            foreach ($workOrder->items as $item) {
                WorkOrderItem::create([
                    'work_order_id' => $newWorkOrder->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'tax_rate' => $item->tax_rate,
                    'subtotal' => $item->subtotal,
                ]);
            }

            return $newWorkOrder->load(['client', 'items', 'assignedTo']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Work order duplicated successfully',
            'data' => new WorkOrderResource($newWorkOrder),
        ], 201);
    }

    private function calculateItemSubtotal(float|string $quantity, float|string $unitPrice, float|string $discount, float|string $taxRate): string
    {
        $lineTotal = bcmul((string) $quantity, (string) $unitPrice, 2);
        $tax = bcmul($lineTotal, bcdiv((string) $taxRate, '100', 4), 2);

        return bcadd(bcsub($lineTotal, (string) $discount, 2), $tax, 2);
    }

    private function calculateTotals(array $items): array
    {
        $totalBeforeTax = '0';
        $totalTax = '0';
        $totalDiscount = '0';

        foreach ($items as $item) {
            $qty = (string) $item['quantity'];
            $unitPrice = (string) $item['unit_price'];
            $discount = (string) ($item['discount'] ?? 0);
            $taxRate = (string) ($item['tax_rate'] ?? 0);

            $lineTotal = bcmul($qty, $unitPrice, 2);
            $totalBeforeTax = bcadd($totalBeforeTax, $lineTotal, 2);
            $totalDiscount = bcadd($totalDiscount, $discount, 2);

            $tax = bcmul($lineTotal, bcdiv($taxRate, '100', 4), 2);
            $totalTax = bcadd($totalTax, $tax, 2);
        }

        $grandTotal = bcadd(bcsub($totalBeforeTax, $totalDiscount, 2), $totalTax, 2);

        return [
            'total_before_tax' => $totalBeforeTax,
            'total_tax' => $totalTax,
            'total_discount' => $totalDiscount,
            'grand_total' => $grandTotal,
        ];
    }
}
