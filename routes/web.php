<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Dashboard;
use App\Livewire\Reports\BalanceSheet;
use App\Livewire\Reports\CashFlow;
use App\Livewire\Reports\ExpenseByCategory;
use App\Livewire\Reports\GeneralLedger;
use App\Livewire\Reports\IncomeByClient;
use App\Livewire\Reports\PayableAging;
use App\Livewire\Reports\PayrollSummary;
use App\Livewire\Reports\ProfitLoss;
use App\Livewire\Reports\ReceivableAging;
use App\Livewire\Reports\TaxSummary;
use App\Livewire\Reports\TrialBalance;
use App\Livewire\Reports\WorkOrderSummary;
use App\Livewire\Settings\AuditLogs;
use App\Livewire\Settings\Company;
use App\Livewire\Settings\Roles;
use App\Livewire\Settings\TaxRates;
use App\Livewire\WorkOrders\Form;
use App\Livewire\WorkOrders\Index;
use App\Livewire\WorkOrders\Show;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/tutorial', fn () => view('pages.tutorial'))->name('tutorial');

    // Work Orders
    Route::get('/work-orders', Index::class)->name('work-orders.index');
    Route::get('/work-orders/create', Form::class)->name('work-orders.create');
    Route::get('/work-orders/{workOrder}', Show::class)->name('work-orders.show');
    Route::get('/work-orders/{workOrder}/edit', Form::class)->name('work-orders.edit');

    // Budget Requests
    Route::get('/requests', App\Livewire\Requests\Index::class)->name('requests.index');
    Route::get('/requests/create', App\Livewire\Requests\Form::class)->name('requests.create');
    Route::get('/requests/{budgetRequest}', App\Livewire\Requests\Show::class)->name('requests.show');
    Route::get('/requests/{budgetRequest}/edit', App\Livewire\Requests\Form::class)->name('requests.edit');

    // Brands
    Route::get('/brands', App\Livewire\Brand\Index::class)->name('brands.index');
    Route::get('/brands/create', App\Livewire\Brand\Form::class)->name('brands.create');
    Route::get('/brands/{brand}', App\Livewire\Brand\Show::class)->name('brands.show');
    Route::get('/brands/{brand}/edit', App\Livewire\Brand\Form::class)->name('brands.edit');

    // Inventory
    Route::get('/items', App\Livewire\Inventory\Index::class)->name('items.index');
    Route::get('/items/create', App\Livewire\Inventory\Form::class)->name('items.create');
    Route::get('/items/{item}', App\Livewire\Inventory\Show::class)->name('items.show');
    Route::get('/items/{item}/edit', App\Livewire\Inventory\Form::class)->name('items.edit');

    // Clients
    Route::get('/clients', App\Livewire\Clients\Index::class)->name('clients.index');
    Route::get('/clients/create', App\Livewire\Clients\Form::class)->name('clients.create');
    Route::get('/clients/{client}', App\Livewire\Clients\Show::class)->name('clients.show');
    Route::get('/clients/{client}/edit', App\Livewire\Clients\Form::class)->name('clients.edit');

    // Vendors
    Route::get('/vendors', App\Livewire\Vendors\Index::class)->name('vendors.index');
    Route::get('/vendors/create', App\Livewire\Vendors\Form::class)->name('vendors.create');
    Route::get('/vendors/{vendor}', App\Livewire\Vendors\Show::class)->name('vendors.show');
    Route::get('/vendors/{vendor}/edit', App\Livewire\Vendors\Form::class)->name('vendors.edit');

    // Invoices
    Route::get('/invoices', App\Livewire\Invoices\Index::class)->name('invoices.index');
    Route::get('/invoices/create', App\Livewire\Invoices\Form::class)->name('invoices.create');
    Route::get('/invoices/{invoice}', App\Livewire\Invoices\Show::class)->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', App\Livewire\Invoices\Form::class)->name('invoices.edit');

    // Transactions
    Route::get('/transactions', App\Livewire\Transactions\Index::class)->name('transactions.index');
    Route::get('/transactions/create', App\Livewire\Transactions\Form::class)->name('transactions.create');
    Route::get('/transactions/{transaction}', App\Livewire\Transactions\Show::class)->name('transactions.show');

    // Journal Entries
    Route::get('/journal-entries', App\Livewire\JournalEntries\Index::class)->name('journal-entries.index');
    Route::get('/journal-entries/create', App\Livewire\JournalEntries\Form::class)->name('journal-entries.create');
    Route::get('/journal-entries/{journalEntry}', App\Livewire\JournalEntries\Show::class)->name('journal-entries.show');

    // Chart of Accounts
    Route::get('/accounts', App\Livewire\Accounts\Index::class)->name('accounts.index');
    Route::get('/accounts/create', App\Livewire\Accounts\Form::class)->name('accounts.create');
    Route::get('/accounts/{account}', App\Livewire\Accounts\Show::class)->name('accounts.show');
    Route::get('/accounts/{account}/edit', App\Livewire\Accounts\Form::class)->name('accounts.edit');

    // Employees
    Route::get('/employees', App\Livewire\Employees\Index::class)->name('employees.index');
    Route::get('/employees/create', App\Livewire\Employees\Form::class)->name('employees.create');
    Route::get('/employees/{employee}', App\Livewire\Employees\Show::class)->name('employees.show');
    Route::get('/employees/{employee}/edit', App\Livewire\Employees\Form::class)->name('employees.edit');

    // Payroll
    Route::get('/payroll', App\Livewire\Payroll\Index::class)->name('payroll.index');
    Route::get('/payroll/create', App\Livewire\Payroll\Form::class)->name('payroll.create');
    Route::get('/payroll/{payrollRecord}', App\Livewire\Payroll\Show::class)->name('payroll.show');
    Route::get('/payroll/{payrollRecord}/edit', App\Livewire\Payroll\Form::class)->name('payroll.edit');

    // Reports
    Route::get('/reports', App\Livewire\Reports\Index::class)->name('reports.index');
    Route::get('/reports/profit-loss', ProfitLoss::class)->name('reports.profit-loss');
    Route::get('/reports/balance-sheet', BalanceSheet::class)->name('reports.balance-sheet');
    Route::get('/reports/cash-flow', CashFlow::class)->name('reports.cash-flow');
    Route::get('/reports/trial-balance', TrialBalance::class)->name('reports.trial-balance');
    Route::get('/reports/general-ledger', GeneralLedger::class)->name('reports.general-ledger');
    Route::get('/reports/receivable-aging', ReceivableAging::class)->name('reports.receivable-aging');
    Route::get('/reports/payable-aging', PayableAging::class)->name('reports.payable-aging');
    Route::get('/reports/income-by-client', IncomeByClient::class)->name('reports.income-by-client');
    Route::get('/reports/expense-by-category', ExpenseByCategory::class)->name('reports.expense-by-category');
    Route::get('/reports/work-order-summary', WorkOrderSummary::class)->name('reports.work-order-summary');
    Route::get('/reports/payroll-summary', PayrollSummary::class)->name('reports.payroll-summary');
    Route::get('/reports/tax-summary', TaxSummary::class)->name('reports.tax-summary');

    // Settings
    Route::get('/settings/company', Company::class)->name('settings.company');
    Route::get('/settings/users', App\Livewire\Settings\Users\Index::class)->name('settings.users.index');
    Route::get('/settings/users/create', App\Livewire\Settings\Users\Form::class)->name('settings.users.create');
    Route::get('/settings/users/{user}/edit', App\Livewire\Settings\Users\Form::class)->name('settings.users.edit');
    Route::get('/settings/roles', Roles::class)->name('settings.roles');
    Route::get('/settings/tax-rates', TaxRates::class)->name('settings.tax-rates');
    Route::get('/settings/audit-logs', AuditLogs::class)->name('settings.audit-logs');
});
