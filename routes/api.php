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
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('api.auth.me');

        Route::middleware('role_or_permission:users-view|users-create|users-edit|users-delete')->group(function () {
            Route::apiResource('users', UserController::class)->names([
                'index' => 'api.users.index', 'store' => 'api.users.store',
                'show' => 'api.users.show', 'update' => 'api.users.update', 'destroy' => 'api.users.destroy',
            ]);
        });
        Route::middleware('role_or_permission:roles-view|roles-create|roles-edit|roles-delete')->group(function () {
            Route::get('/permissions', [RoleController::class, 'permissions'])->name('api.permissions.index');
            Route::get('/roles/permissions/list', [RoleController::class, 'permissions'])->name('api.roles.permissions');
            Route::apiResource('roles', RoleController::class)->names([
                'index' => 'api.roles.index', 'store' => 'api.roles.store',
                'show' => 'api.roles.show', 'update' => 'api.roles.update', 'destroy' => 'api.roles.destroy',
            ]);
        });

        Route::middleware('role_or_permission:accounts-view|accounts-create|accounts-edit|accounts-delete')->group(function () {
            Route::get('/accounts/tree', [AccountController::class, 'tree'])->name('api.accounts.tree');
            Route::apiResource('accounts', AccountController::class)->names([
                'index' => 'api.accounts.index', 'store' => 'api.accounts.store',
                'show' => 'api.accounts.show', 'update' => 'api.accounts.update', 'destroy' => 'api.accounts.destroy',
            ]);
        });

        Route::middleware('role_or_permission:clients-view|clients-create|clients-edit|clients-delete')->group(function () {
            Route::apiResource('clients', ClientController::class)->names([
                'index' => 'api.clients.index', 'store' => 'api.clients.store',
                'show' => 'api.clients.show', 'update' => 'api.clients.update', 'destroy' => 'api.clients.destroy',
            ]);
        });
        Route::middleware('role_or_permission:vendors-view|vendors-create|vendors-edit|vendors-delete')->group(function () {
            Route::apiResource('vendors', VendorController::class)->names([
                'index' => 'api.vendors.index', 'store' => 'api.vendors.store',
                'show' => 'api.vendors.show', 'update' => 'api.vendors.update', 'destroy' => 'api.vendors.destroy',
            ]);
        });
        Route::middleware('role_or_permission:work_orders-view|work_orders-create|work_orders-edit|work_orders-delete')->group(function () {
            Route::apiResource('work-orders', WorkOrderController::class)->names([
                'index' => 'api.work-orders.index', 'store' => 'api.work-orders.store',
                'show' => 'api.work-orders.show', 'update' => 'api.work-orders.update', 'destroy' => 'api.work-orders.destroy',
            ]);
            Route::patch('work-orders/{work_order}/status', [WorkOrderController::class, 'updateStatus'])->name('api.work-orders.status');
            Route::post('work-orders/{work_order}/duplicate', [WorkOrderController::class, 'duplicate'])->name('api.work-orders.duplicate');
        });
        Route::middleware('role_or_permission:budget_requests-view|budget_requests-create|budget_requests-edit|budget_requests-delete')->group(function () {
            Route::patch('budget-requests/{budget_request}/review', [BudgetRequestController::class, 'review'])->name('api.budget-requests.review');
            Route::apiResource('budget-requests', BudgetRequestController::class)->names([
                'index' => 'api.budget-requests.index', 'store' => 'api.budget-requests.store',
                'show' => 'api.budget-requests.show', 'update' => 'api.budget-requests.update', 'destroy' => 'api.budget-requests.destroy',
            ]);
        });

        Route::middleware('role_or_permission:invoices-view|invoices-create|invoices-edit|invoices-delete')->group(function () {
            Route::apiResource('invoices', InvoiceController::class)->names([
                'index' => 'api.invoices.index', 'store' => 'api.invoices.store',
                'show' => 'api.invoices.show', 'update' => 'api.invoices.update', 'destroy' => 'api.invoices.destroy',
            ]);
            Route::post('invoices/from-work-order/{work_order}', [InvoiceController::class, 'createFromWorkOrder'])->name('api.invoices.from-work-order');
            Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('api.invoices.payment');
            Route::patch('invoices/{invoice}/send', [InvoiceController::class, 'markAsSent'])->name('api.invoices.send');
            Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('api.invoices.cancel');
        });

        Route::middleware('role_or_permission:employees-view|employees-create|employees-edit|employees-delete')->group(function () {
            Route::apiResource('employees', EmployeeController::class)->names([
                'index' => 'api.employees.index', 'store' => 'api.employees.store',
                'show' => 'api.employees.show', 'update' => 'api.employees.update', 'destroy' => 'api.employees.destroy',
            ]);
        });

        Route::middleware('role_or_permission:payroll-view|payroll-create|payroll-edit|payroll-delete')->group(function () {
            Route::apiResource('payroll', PayrollController::class)->names([
                'index' => 'api.payroll.index', 'store' => 'api.payroll.store',
                'show' => 'api.payroll.show', 'update' => 'api.payroll.update', 'destroy' => 'api.payroll.destroy',
            ]);
            Route::post('payroll/generate', [PayrollController::class, 'generate'])->name('api.payroll.generate');
            Route::patch('payroll/{payroll_record}/approve', [PayrollController::class, 'approve'])->name('api.payroll.approve');
            Route::patch('payroll/{payroll_record}/pay', [PayrollController::class, 'markAsPaid'])->name('api.payroll.pay');
        });

        Route::middleware('role_or_permission:settings-view|settings-create|settings-edit|settings-delete')->group(function () {
            Route::apiResource('tax-rates', TaxRateController::class)->names([
                'index' => 'api.tax-rates.index', 'store' => 'api.tax-rates.store',
                'show' => 'api.tax-rates.show', 'update' => 'api.tax-rates.update', 'destroy' => 'api.tax-rates.destroy',
            ]);
            Route::get('/settings', [CompanySettingController::class, 'index'])->name('api.settings.index');
            Route::put('/settings', [CompanySettingController::class, 'update'])->name('api.settings.update');
            Route::get('/settings/{key}', [CompanySettingController::class, 'show'])->name('api.settings.show');
            Route::put('/settings/{key}', [CompanySettingController::class, 'set'])->name('api.settings.set');
        });

        Route::middleware('role_or_permission:transactions-view|transactions-create|transactions-edit|transactions-delete')->group(function () {
            Route::get('transactions-summary', [TransactionController::class, 'summary'])->name('api.transactions.summary');
            Route::apiResource('transactions', TransactionController::class)->names([
                'index' => 'api.transactions.index', 'store' => 'api.transactions.store',
                'show' => 'api.transactions.show', 'update' => 'api.transactions.update', 'destroy' => 'api.transactions.destroy',
            ]);
        });
        Route::middleware('role_or_permission:journal_entries-view|journal_entries-create|journal_entries-edit|journal_entries-delete')->group(function () {
            Route::apiResource('journal-entries', JournalEntryController::class)->names([
                'index' => 'api.journal-entries.index', 'store' => 'api.journal-entries.store',
                'show' => 'api.journal-entries.show', 'update' => 'api.journal-entries.update', 'destroy' => 'api.journal-entries.destroy',
            ]);
        });

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');

        Route::middleware('role_or_permission:reports-view')->group(function () {
            Route::prefix('reports')->name('api.reports.')->group(function () {
                Route::get('profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
                Route::get('balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
                Route::get('cash-flow', [ReportController::class, 'cashFlow'])->name('cash-flow');
                Route::get('trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
                Route::get('general-ledger', [ReportController::class, 'generalLedger'])->name('general-ledger');
                Route::get('receivable-aging', [ReportController::class, 'receivableAging'])->name('receivable-aging');
                Route::get('payable-aging', [ReportController::class, 'payableAging'])->name('payable-aging');
                Route::get('income-by-client', [ReportController::class, 'incomeByClient'])->name('income-by-client');
                Route::get('expense-by-category', [ReportController::class, 'expenseByCategory'])->name('expense-by-category');
                Route::get('work-order-summary', [ReportController::class, 'workOrderSummary'])->name('work-order-summary');
                Route::get('payroll-summary', [ReportController::class, 'payrollSummary'])->name('payroll-summary');
                Route::get('tax-summary', [ReportController::class, 'taxSummary'])->name('tax-summary');
            });
        });

        Route::middleware('role_or_permission:audit_logs-view')->group(function () {
            Route::get('audit-logs', [AuditLogController::class, 'index'])->name('api.audit-logs.index');
            Route::get('audit-logs-export', [AuditLogController::class, 'export'])->name('api.audit-logs.export');
            Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('api.audit-logs.show');
        });

        Route::middleware('role_or_permission:invoices-view')->get('exports/invoice/{invoice}/pdf', [ExportController::class, 'invoicePdf'])->name('api.exports.invoice-pdf');
        Route::middleware('role_or_permission:payroll-view')->get('exports/payroll/{payroll}/pdf', [ExportController::class, 'payslipPdf'])->name('api.exports.payslip-pdf');
        Route::middleware('role_or_permission:reports-view')->get('exports/report/{type}/pdf', [ExportController::class, 'reportPdf'])->name('api.exports.report-pdf');
    });
});
