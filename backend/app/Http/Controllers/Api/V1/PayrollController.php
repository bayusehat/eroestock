<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GeneratePayrollRequest;
use App\Http\Requests\Api\V1\StorePayrollRequest;
use App\Http\Requests\Api\V1\UpdatePayrollRequest;
use App\Http\Resources\V1\PayrollRecordResource;
use App\Models\Account;
use App\Models\Employee;
use App\Models\PayrollRecord;
use App\Models\Transaction;
use App\Traits\GeneratesNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = PayrollRecord::query()->with('employee:id,employee_id,name');

        if ($request->filled('period_month')) {
            $query->where('period_month', $request->period_month);
        }

        if ($request->filled('period_year')) {
            $query->where('period_year', $request->period_year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $payrollRecords = $query->orderByDesc('period_year')->orderByDesc('period_month')->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'message' => 'Payroll records retrieved successfully',
            'data' => PayrollRecordResource::collection($payrollRecords),
            'meta' => [
                'current_page' => $payrollRecords->currentPage(),
                'per_page' => $payrollRecords->perPage(),
                'total' => $payrollRecords->total(),
                'last_page' => $payrollRecords->lastPage(),
            ],
        ]);
    }

    public function store(StorePayrollRequest $request): JsonResponse
    {
        $payrollRecord = DB::transaction(function () use ($request) {
            $payrollNo = GeneratesNumber::generateNumber('PAY', 'payroll_records', 'payroll_no', 'Y-m');
            $employee = Employee::findOrFail($request->employee_id);

            $overtimeAmount = bcmul(
                (string) ($request->overtime_hours ?? 0),
                (string) ($request->overtime_rate ?? 0),
                2
            );

            $totalAllowances = $this->sumJsonAmounts($request->allowances ?? []);
            $totalDeductions = $this->sumJsonAmounts($request->deductions ?? []);
            $taxAmount = (string) ($request->tax_amount ?? 0);

            $grossPay = bcadd(
                bcadd((string) $employee->base_salary, $overtimeAmount, 2),
                $totalAllowances,
                2
            );
            $netPay = bcsub(bcsub($grossPay, $totalDeductions, 2), $taxAmount, 2);

            return PayrollRecord::create([
                'payroll_no' => $payrollNo,
                'employee_id' => $request->employee_id,
                'period_month' => $request->period_month,
                'period_year' => $request->period_year,
                'base_salary' => $employee->base_salary,
                'overtime_hours' => $request->overtime_hours ?? 0,
                'overtime_rate' => $request->overtime_rate ?? 0,
                'overtime_amount' => $overtimeAmount,
                'allowances' => $request->allowances ?? [],
                'total_allowances' => $totalAllowances,
                'deductions' => $request->deductions ?? [],
                'total_deductions' => $totalDeductions,
                'gross_pay' => $grossPay,
                'tax_amount' => $taxAmount,
                'net_pay' => $netPay,
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by' => $request->user()->id,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Payroll record created successfully',
            'data' => new PayrollRecordResource($payrollRecord->load('employee')),
        ], 201);
    }

    public function show(PayrollRecord $payroll): JsonResponse
    {
        $payroll->load('employee');

        return response()->json([
            'success' => true,
            'message' => 'Payroll record retrieved successfully',
            'data' => new PayrollRecordResource($payroll),
        ]);
    }

    public function update(UpdatePayrollRequest $request, PayrollRecord $payroll): JsonResponse
    {
        if ($payroll->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only update payroll records in draft status',
            ], 422);
        }

        $payroll = DB::transaction(function () use ($request, $payroll) {
            $overtimeAmount = bcmul(
                (string) ($request->overtime_hours ?? $payroll->overtime_hours ?? 0),
                (string) ($request->overtime_rate ?? $payroll->overtime_rate ?? 0),
                2
            );

            $allowances = $request->allowances ?? $payroll->allowances ?? [];
            $deductions = $request->deductions ?? $payroll->deductions ?? [];
            $totalAllowances = $this->sumJsonAmounts($allowances);
            $totalDeductions = $this->sumJsonAmounts($deductions);
            $taxAmount = (string) ($request->tax_amount ?? $payroll->tax_amount ?? 0);

            $grossPay = bcadd(
                bcadd((string) $payroll->base_salary, $overtimeAmount, 2),
                $totalAllowances,
                2
            );
            $netPay = bcsub(bcsub($grossPay, $totalDeductions, 2), $taxAmount, 2);

            $payroll->update([
                'overtime_hours' => $request->overtime_hours ?? $payroll->overtime_hours,
                'overtime_rate' => $request->overtime_rate ?? $payroll->overtime_rate,
                'overtime_amount' => $overtimeAmount,
                'allowances' => $allowances,
                'total_allowances' => $totalAllowances,
                'deductions' => $deductions,
                'total_deductions' => $totalDeductions,
                'gross_pay' => $grossPay,
                'tax_amount' => $taxAmount,
                'net_pay' => $netPay,
                'notes' => $request->notes ?? $payroll->notes,
            ]);

            return $payroll->fresh('employee');
        });

        return response()->json([
            'success' => true,
            'message' => 'Payroll record updated successfully',
            'data' => new PayrollRecordResource($payroll),
        ]);
    }

    public function destroy(PayrollRecord $payroll): JsonResponse
    {
        if ($payroll->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only delete payroll records in draft status',
            ], 422);
        }

        $payroll->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payroll record deleted successfully',
        ]);
    }

    public function generate(GeneratePayrollRequest $request): JsonResponse
    {
        $count = DB::transaction(function () use ($request) {
            $activeEmployees = Employee::where('status', 'active')
                ->whereDoesntHave('payrollRecords', function ($q) use ($request) {
                    $q->where('period_month', $request->period_month)
                        ->where('period_year', $request->period_year);
                })
                ->get();

            $count = 0;
            foreach ($activeEmployees as $employee) {
                $payrollNo = GeneratesNumber::generateNumber('PAY', 'payroll_records', 'payroll_no', 'Y-m');

                PayrollRecord::create([
                    'payroll_no' => $payrollNo,
                    'employee_id' => $employee->id,
                    'period_month' => $request->period_month,
                    'period_year' => $request->period_year,
                    'base_salary' => $employee->base_salary,
                    'overtime_hours' => 0,
                    'overtime_rate' => 0,
                    'overtime_amount' => 0,
                    'allowances' => [],
                    'total_allowances' => 0,
                    'deductions' => [],
                    'total_deductions' => 0,
                    'gross_pay' => $employee->base_salary,
                    'tax_amount' => 0,
                    'net_pay' => $employee->base_salary,
                    'status' => 'draft',
                    'created_by' => $request->user()->id,
                ]);
                $count++;
            }

            return $count;
        });

        return response()->json([
            'success' => true,
            'message' => 'Payroll records generated successfully',
            'data' => ['count' => $count],
        ], 201);
    }

    public function approve(PayrollRecord $payroll_record): JsonResponse
    {
        if ($payroll_record->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Can only approve payroll records in draft status',
            ], 422);
        }

        $payroll_record->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => 'Payroll record approved successfully',
            'data' => new PayrollRecordResource($payroll_record->fresh('employee')),
        ]);
    }

    public function markAsPaid(PayrollRecord $payroll_record): JsonResponse
    {
        if ($payroll_record->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Can only mark approved payroll records as paid',
            ], 422);
        }

        $salaryAccount = Account::where('code', '5-1000')->first();
        $cashAccount = Account::where('code', '1-1002')->first();

        if (!$salaryAccount || !$cashAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Required accounts (Salary & Wages, Cash/Bank) not found',
            ], 500);
        }

        DB::transaction(function () use ($payroll_record, $salaryAccount, $cashAccount) {
            Transaction::create([
                'transaction_no' => GeneratesNumber::generateNumber('TXN', 'transactions', 'transaction_no', 'Y'),
                'type' => 'expense',
                'date' => now(),
                'amount' => $payroll_record->net_pay,
                'account_id' => $salaryAccount->id,
                'contra_account_id' => $cashAccount->id,
                'category' => 'Salary & Wages',
                'description' => $payroll_record->payroll_no . ' - ' . $payroll_record->employee->name,
                'payment_method' => 'bank_transfer',
                'created_by' => request()->user()->id,
            ]);

            $payroll_record->update([
                'status' => 'paid',
                'paid_date' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Payroll record marked as paid successfully',
            'data' => new PayrollRecordResource($payroll_record->fresh('employee')),
        ]);
    }

    private function sumJsonAmounts(array $items): string
    {
        $total = '0';
        foreach ($items as $item) {
            if (isset($item['amount'])) {
                $total = bcadd($total, (string) $item['amount'], 2);
            }
        }
        return $total;
    }
}
