<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\BudgetRequestController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\CompanySettingController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\ExportController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\JournalEntryController;
use App\Http\Controllers\Api\V1\PayrollController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\TaxRateController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\VendorController;
use App\Http\Controllers\Api\V1\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::middleware('role_or_permission:users-view|users-create|users-edit|users-delete')->group(function () {
            Route::apiResource('users', UserController::class);
        });
        Route::middleware('role_or_permission:roles-view|roles-create|roles-edit|roles-delete')->group(function () {
            Route::get('/permissions', [RoleController::class, 'permissions']);
            Route::get('/roles/permissions/list', [RoleController::class, 'permissions']);
            Route::apiResource('roles', RoleController::class);
        });

        Route::middleware('role_or_permission:accounts-view|accounts-create|accounts-edit|accounts-delete')->group(function () {
            Route::get('/accounts/tree', [AccountController::class, 'tree']);
            Route::apiResource('accounts', AccountController::class);
        });

        Route::middleware('role_or_permission:clients-view|clients-create|clients-edit|clients-delete')->group(function () {
            Route::apiResource('clients', ClientController::class);
        });
        Route::middleware('role_or_permission:vendors-view|vendors-create|vendors-edit|vendors-delete')->group(function () {
            Route::apiResource('vendors', VendorController::class);
        });
        Route::middleware('role_or_permission:work_orders-view|work_orders-create|work_orders-edit|work_orders-delete')->group(function () {
            Route::apiResource('work-orders', WorkOrderController::class);
            Route::patch('work-orders/{work_order}/status', [WorkOrderController::class, 'updateStatus']);
            Route::post('work-orders/{work_order}/duplicate', [WorkOrderController::class, 'duplicate']);
        });
        Route::middleware('role_or_permission:budget_requests-view|budget_requests-create|budget_requests-edit|budget_requests-delete')->group(function () {
            Route::patch('budget-requests/{budget_request}/review', [BudgetRequestController::class, 'review']);
            Route::apiResource('budget-requests', BudgetRequestController::class);
        });

        Route::middleware('role_or_permission:invoices-view|invoices-create|invoices-edit|invoices-delete')->group(function () {
            Route::apiResource('invoices', InvoiceController::class);
            Route::post('invoices/from-work-order/{work_order}', [InvoiceController::class, 'createFromWorkOrder']);
            Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment']);
            Route::patch('invoices/{invoice}/send', [InvoiceController::class, 'markAsSent']);
        });

        Route::middleware('role_or_permission:employees-view|employees-create|employees-edit|employees-delete')->group(function () {
            Route::apiResource('employees', EmployeeController::class);
        });

        Route::middleware('role_or_permission:payroll-view|payroll-create|payroll-edit|payroll-delete')->group(function () {
            Route::apiResource('payroll', PayrollController::class);
            Route::post('payroll/generate', [PayrollController::class, 'generate']);
            Route::patch('payroll/{payroll_record}/approve', [PayrollController::class, 'approve']);
            Route::patch('payroll/{payroll_record}/pay', [PayrollController::class, 'markAsPaid']);
        });

        Route::middleware('role_or_permission:settings-view|settings-create|settings-edit|settings-delete')->group(function () {
            Route::apiResource('tax-rates', TaxRateController::class);
            Route::get('/settings', [CompanySettingController::class, 'index']);
            Route::put('/settings', [CompanySettingController::class, 'update']);
            Route::get('/settings/{key}', [CompanySettingController::class, 'show']);
            Route::put('/settings/{key}', [CompanySettingController::class, 'set']);
        });

        Route::middleware('role_or_permission:transactions-view|transactions-create|transactions-edit|transactions-delete')->group(function () {
            Route::get('transactions-summary', [TransactionController::class, 'summary']);
            Route::apiResource('transactions', TransactionController::class);
        });
        Route::middleware('role_or_permission:journal_entries-view|journal_entries-create|journal_entries-edit|journal_entries-delete')->group(function () {
            Route::apiResource('journal-entries', JournalEntryController::class);
        });

        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::middleware('role_or_permission:reports-view')->group(function () {
            Route::prefix('reports')->group(function () {
                Route::get('profit-loss', [ReportController::class, 'profitLoss']);
                Route::get('balance-sheet', [ReportController::class, 'balanceSheet']);
                Route::get('cash-flow', [ReportController::class, 'cashFlow']);
                Route::get('trial-balance', [ReportController::class, 'trialBalance']);
                Route::get('general-ledger', [ReportController::class, 'generalLedger']);
                Route::get('receivable-aging', [ReportController::class, 'receivableAging']);
                Route::get('payable-aging', [ReportController::class, 'payableAging']);
                Route::get('income-by-client', [ReportController::class, 'incomeByClient']);
                Route::get('expense-by-category', [ReportController::class, 'expenseByCategory']);
                Route::get('work-order-summary', [ReportController::class, 'workOrderSummary']);
                Route::get('payroll-summary', [ReportController::class, 'payrollSummary']);
                Route::get('tax-summary', [ReportController::class, 'taxSummary']);
            });
        });

        Route::middleware('role_or_permission:audit_logs-view')->group(function () {
            Route::get('audit-logs', [AuditLogController::class, 'index']);
            Route::get('audit-logs-export', [AuditLogController::class, 'export']);
            Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show']);
        });

        Route::middleware('role_or_permission:invoices-view')->get('exports/invoice/{invoice}/pdf', [ExportController::class, 'invoicePdf']);
        Route::middleware('role_or_permission:payroll-view')->get('exports/payroll/{payroll}/pdf', [ExportController::class, 'payslipPdf']);
        Route::middleware('role_or_permission:reports-view')->get('exports/report/{type}/pdf', [ExportController::class, 'reportPdf']);
    });
});
