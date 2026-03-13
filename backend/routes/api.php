<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\CompanySettingController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\JournalEntryController;
use App\Http\Controllers\Api\V1\PayrollController;
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

        Route::apiResource('users', UserController::class);
        Route::get('/roles/permissions/list', [RoleController::class, 'permissions']);
        Route::apiResource('roles', RoleController::class);

        Route::get('/accounts/tree', [AccountController::class, 'tree']);
        Route::apiResource('accounts', AccountController::class);

        Route::apiResource('clients', ClientController::class);
        Route::apiResource('vendors', VendorController::class);
        Route::apiResource('work-orders', WorkOrderController::class);
        Route::patch('work-orders/{work_order}/status', [WorkOrderController::class, 'updateStatus']);
        Route::post('work-orders/{work_order}/duplicate', [WorkOrderController::class, 'duplicate']);

        Route::apiResource('invoices', InvoiceController::class);
        Route::post('invoices/from-work-order/{work_order}', [InvoiceController::class, 'createFromWorkOrder']);
        Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment']);
        Route::patch('invoices/{invoice}/send', [InvoiceController::class, 'markAsSent']);

        Route::apiResource('employees', EmployeeController::class);

        Route::apiResource('payroll', PayrollController::class);
        Route::post('payroll/generate', [PayrollController::class, 'generate']);
        Route::patch('payroll/{payroll_record}/approve', [PayrollController::class, 'approve']);
        Route::patch('payroll/{payroll_record}/pay', [PayrollController::class, 'markAsPaid']);

        Route::apiResource('tax-rates', TaxRateController::class);
        Route::get('transactions-summary', [TransactionController::class, 'summary']);
        Route::apiResource('transactions', TransactionController::class);
        Route::apiResource('journal-entries', JournalEntryController::class);

        Route::get('/settings', [CompanySettingController::class, 'index']);
        Route::put('/settings', [CompanySettingController::class, 'update']);
        Route::get('/settings/{key}', [CompanySettingController::class, 'show']);
        Route::put('/settings/{key}', [CompanySettingController::class, 'set']);

        Route::get('/dashboard', [DashboardController::class, 'index']);
    });
});
