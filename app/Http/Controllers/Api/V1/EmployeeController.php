<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreEmployeeRequest;
use App\Http\Requests\Api\V1\UpdateEmployeeRequest;
use App\Http\Resources\V1\EmployeeResource;
use App\Models\Employee;
use App\Traits\GeneratesNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Employee::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        $employees = $query->orderBy('name')->paginate($request->get('per_page', 25));

        return response()->json([
            'success' => true,
            'message' => 'Employees retrieved successfully',
            'data' => EmployeeResource::collection($employees),
            'meta' => [
                'current_page' => $employees->currentPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
                'last_page' => $employees->lastPage(),
            ],
        ]);
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employeeId = GeneratesNumber::generateSimpleNumber('EMP', 'employees', 'employee_id', 3);

        $employee = Employee::create(array_merge($request->validated(), [
            'employee_id' => $employeeId,
            'status' => 'active',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully',
            'data' => new EmployeeResource($employee),
        ], 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        $ytdEarnings = $employee->payrollRecords()
            ->where('period_year', now()->year)
            ->where('status', 'paid')
            ->sum('net_pay');

        $employee->ytd_earnings = $ytdEarnings;
        $employee->payroll_records_count = $employee->payrollRecords()->count();

        return response()->json([
            'success' => true,
            'message' => 'Employee retrieved successfully',
            'data' => new EmployeeResource($employee),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $employee->update(array_filter($request->validated(), fn ($v) => $v !== null));

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
            'data' => new EmployeeResource($employee->fresh()),
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->update([
            'status' => 'terminated',
            'end_date' => now(),
        ]);
        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully',
        ]);
    }
}
