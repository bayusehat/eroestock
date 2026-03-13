<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RecordPaymentRequest;
use App\Http\Requests\Api\V1\StoreInvoiceRequest;
use App\Http\Requests\Api\V1\UpdateInvoiceRequest;
use App\Http\Resources\V1\InvoiceResource;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use App\Models\WorkOrder;
use App\Traits\GeneratesNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::query()->with('client:id,name,code');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('invoice_no', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('issue_date_from')) {
            $query->whereDate('issue_date', '>=', $request->issue_date_from);
        }

        if ($request->filled('issue_date_to')) {
            $query->whereDate('issue_date', '<=', $request->issue_date_to);
        }

        $invoices = $query->latest('issue_date')->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'message' => 'Invoices retrieved successfully',
            'data' => InvoiceResource::collection($invoices),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
                'last_page' => $invoices->lastPage(),
            ],
        ]);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = DB::transaction(function () use ($request) {
            $invoiceNo = GeneratesNumber::generateNumber('INV', 'invoices', 'invoice_no', 'Y');
            $totals = $this->calculateTotals($request->items);

            $invoice = Invoice::create([
                'invoice_no' => $invoiceNo,
                'client_id' => $request->client_id,
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'status' => 'draft',
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'discount_amount' => $totals['discount_amount'],
                'grand_total' => $totals['grand_total'],
                'amount_paid' => 0,
                'balance_due' => $totals['grand_total'],
                'notes' => $request->notes,
                'terms' => $request->terms,
                'created_by' => $request->user()->id,
            ]);

            foreach ($request->items as $item) {
                $subtotal = $this->calculateItemSubtotal(
                    $item['quantity'],
                    $item['unit_price'],
                    $item['discount'] ?? 0,
                    $item['tax_rate'] ?? 0
                );
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? null,
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'subtotal' => $subtotal,
                ]);
            }

            return $invoice;
        });

        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully',
            'data' => new InvoiceResource($invoice->load(['client', 'items'])),
        ], 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        $invoice->load(['client', 'items', 'workOrder', 'transactions']);

        return response()->json([
            'success' => true,
            'message' => 'Invoice retrieved successfully',
            'data' => new InvoiceResource($invoice),
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        if (!in_array($invoice->status, ['draft', 'sent'])) {
            return response()->json([
                'success' => false,
                'message' => 'Can only update invoices in draft or sent status',
            ], 422);
        }

        $invoice = DB::transaction(function () use ($request, $invoice) {
            $data = array_filter($request->only([
                'client_id', 'issue_date', 'due_date', 'notes', 'terms',
            ]), fn ($v) => $v !== null);

            if ($request->has('items')) {
                $invoice->items()->delete();
                $totals = $this->calculateTotals($request->items);

                foreach ($request->items as $item) {
                    $subtotal = $this->calculateItemSubtotal(
                        $item['quantity'],
                        $item['unit_price'],
                        $item['discount'] ?? 0,
                        $item['tax_rate'] ?? 0
                    );
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'] ?? null,
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'tax_rate' => $item['tax_rate'] ?? 0,
                        'subtotal' => $subtotal,
                    ]);
                }

                $data['subtotal'] = $totals['subtotal'];
                $data['tax_amount'] = $totals['tax_amount'];
                $data['discount_amount'] = $totals['discount_amount'];
                $data['grand_total'] = $totals['grand_total'];
                $data['balance_due'] = bcsub($totals['grand_total'], (string) $invoice->amount_paid, 2);
            }

            $invoice->update($data);

            return $invoice->fresh(['client', 'items']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully',
            'data' => new InvoiceResource($invoice),
        ]);
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only delete invoices in draft status',
            ], 422);
        }

        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully',
        ]);
    }

    public function createFromWorkOrder(Request $request, WorkOrder $work_order): JsonResponse
    {
        if ($work_order->invoice()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Work order already has an invoice',
            ], 422);
        }

        $invoice = DB::transaction(function () use ($work_order, $request) {
            $invoiceNo = GeneratesNumber::generateNumber('INV', 'invoices', 'invoice_no', 'Y');

            $invoice = Invoice::create([
                'invoice_no' => $invoiceNo,
                'client_id' => $work_order->client_id,
                'work_order_id' => $work_order->id,
                'issue_date' => now(),
                'due_date' => $work_order->due_date,
                'status' => 'draft',
                'subtotal' => $work_order->total_before_tax,
                'tax_amount' => $work_order->total_tax,
                'discount_amount' => $work_order->total_discount,
                'grand_total' => $work_order->grand_total,
                'amount_paid' => 0,
                'balance_due' => $work_order->grand_total,
                'notes' => $work_order->notes,
                'created_by' => $request->user()->id,
            ]);

            foreach ($work_order->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'tax_rate' => $item->tax_rate,
                    'subtotal' => $item->subtotal,
                ]);
            }

            $work_order->update(['status' => 'invoiced']);

            return $invoice->load(['client', 'items', 'workOrder']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Invoice created from work order successfully',
            'data' => new InvoiceResource($invoice),
        ], 201);
    }

    public function recordPayment(RecordPaymentRequest $request, Invoice $invoice): JsonResponse
    {
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot record payment for paid or cancelled invoice',
            ], 422);
        }

        $amount = (string) $request->amount;
        if (bccomp($amount, (string) $invoice->balance_due, 2) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Payment amount exceeds balance due',
            ], 422);
        }

        $receivableAccount = Account::where('code', '1-2000')->first();
        if (!$receivableAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Accounts Receivable account not found',
            ], 500);
        }

        $result = DB::transaction(function () use ($request, $invoice, $amount, $receivableAccount) {
            Transaction::create([
                'transaction_no' => GeneratesNumber::generateNumber('TXN', 'transactions', 'transaction_no', 'Y'),
                'type' => 'income',
                'date' => $request->payment_date,
                'amount' => $amount,
                'account_id' => $request->account_id,
                'contra_account_id' => $receivableAccount->id,
                'client_id' => $invoice->client_id,
                'invoice_id' => $invoice->id,
                'description' => $invoice->invoice_no . ' - Payment received',
                'reference_no' => $request->reference_no,
                'payment_method' => $request->payment_method,
                'created_by' => $request->user()->id,
            ]);

            $newAmountPaid = bcadd((string) $invoice->amount_paid, $amount, 2);
            $newBalanceDue = bcsub((string) $invoice->grand_total, $newAmountPaid, 2);

            $status = 'partially_paid';
            if (bccomp($newBalanceDue, '0', 2) <= 0) {
                $status = 'paid';
            }

            $invoice->update([
                'amount_paid' => $newAmountPaid,
                'balance_due' => $newBalanceDue,
                'status' => $status,
            ]);

            return $invoice->fresh(['client', 'items', 'workOrder', 'transactions']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'data' => new InvoiceResource($result),
        ]);
    }

    public function markAsSent(Invoice $invoice): JsonResponse
    {
        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only mark draft invoices as sent',
            ], 422);
        }

        $invoice->update(['status' => 'sent']);

        return response()->json([
            'success' => true,
            'message' => 'Invoice marked as sent successfully',
            'data' => new InvoiceResource($invoice->fresh(['client', 'items', 'workOrder'])),
        ]);
    }

    private function calculateItemSubtotal(float|string $quantity, float|string $unitPrice, float|string $discount, float|string $taxRate): string
    {
        $lineTotal = bcmul((string) $quantity, (string) $unitPrice, 2);
        $tax = bcmul($lineTotal, bcdiv((string) $taxRate, '100', 4), 2);

        return bcadd(bcsub($lineTotal, (string) $discount, 2), $tax, 2);
    }

    private function calculateTotals(array $items): array
    {
        $subtotal = '0';
        $taxAmount = '0';
        $discountAmount = '0';

        foreach ($items as $item) {
            $qty = (string) $item['quantity'];
            $unitPrice = (string) $item['unit_price'];
            $discount = (string) ($item['discount'] ?? 0);
            $taxRate = (string) ($item['tax_rate'] ?? 0);

            $lineTotal = bcmul($qty, $unitPrice, 2);
            $subtotal = bcadd($subtotal, $lineTotal, 2);
            $discountAmount = bcadd($discountAmount, $discount, 2);

            $tax = bcmul($lineTotal, bcdiv($taxRate, '100', 4), 2);
            $taxAmount = bcadd($taxAmount, $tax, 2);
        }

        $grandTotal = bcadd(bcsub($subtotal, $discountAmount, 2), $taxAmount, 2);

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'grand_total' => $grandTotal,
        ];
    }
}
