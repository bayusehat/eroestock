# Kucatat — Catat, Kelola, Tumbuh.

Kucatat adalah aplikasi pencatatan keuangan untuk UKM. Kelola work order, catat transaksi, atur payroll karyawan, dan buat laporan keuangan — semua dalam satu platform.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 (PHP 8.3+) |
| Frontend | Laravel Blade + Livewire 3 + Alpine.js |
| Database | SQLite (dev) / PostgreSQL (production) |
| Auth | Session-based (Laravel default) |
| UI | Tailwind CSS v4 |
| Charts | Chart.js (via Alpine.js) |
| PDF | DomPDF |

> **Migration note:** The app was migrated from a split Next.js + Laravel API architecture to a single Laravel monolith using Blade + Livewire. The `frontend/` directory is preserved but no longer served. The existing JSON API at `/api/v1` is kept for backward compatibility (e.g. mobile apps).

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 20+
- npm

### Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

The app runs at `http://localhost:8000`.

### Default Login

- **Email:** admin@example.com
- **Password:** password

## Modules

### Work Order Management
- Record and track service requests from client companies
- Line item pricing with tax and discount calculations
- Status workflow: Draft → Confirmed → In Progress → Completed → Invoiced
- Convert work orders to invoices

### Client & Vendor Management
- Client (customer) directory with contact details and payment terms
- Vendor (supplier) directory with banking information

### Financial Transactions
- Record income, expenses, and transfers
- Double-entry bookkeeping with contra accounts
- Payment method tracking
- Transaction summary by type and period

### Invoice Management
- Generate invoices with line item pricing, tax, and discounts
- Record partial and full payments
- Automatic overdue tracking
- PDF generation

### Chart of Accounts
- Hierarchical account structure (Assets, Liabilities, Equity, Revenue, Expenses)
- Default chart of accounts seeded on setup

### Journal Entries
- Manual double-entry journal postings
- Debit/credit balance validation

### Employee & Payroll
- Employee directory with personal, employment, and banking details
- Monthly payroll generation
- Overtime, allowances, and deductions
- Approval workflow: Draft → Approved → Paid
- Payslip PDF generation

### Tax Management
- Configurable tax rates (Sales, Income, Withholding)
- Tax summary reporting

### Financial Reports
- Profit & Loss Statement
- Balance Sheet
- Cash Flow Statement
- Trial Balance
- General Ledger
- Accounts Receivable Aging
- Income by Client
- Expense by Category
- Work Order Summary
- Payroll Summary
- Tax Summary

### User Management
- Role-based access control (Super Admin, Admin, Accountant, Viewer)
- Granular permissions per module
- Audit trail with full change history

### Dashboard
- Revenue, expenses, and net profit (MTD)
- Cash balance and outstanding receivables
- Revenue vs expense chart
- Work order pipeline
- Recent transactions

## Database Schema

```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        timestamp email_verified_at
        string password
        string avatar
        string phone
        boolean is_active
        timestamp last_login_at
    }

    accounts {
        bigint id PK
        string code UK
        string name
        enum type "asset | liability | equity | revenue | expense"
        string sub_type
        bigint parent_id FK
        boolean is_header
        text description
        decimal opening_balance
        boolean is_active
        boolean is_system
        timestamp deleted_at
    }

    clients {
        bigint id PK
        string name
        string code UK
        string email
        string phone
        text address
        string tax_id
        string contact_person
        int payment_terms
        boolean is_active
        timestamp deleted_at
    }

    vendors {
        bigint id PK
        string name
        string code UK
        string email
        string phone
        text address
        string tax_id
        string contact_person
        int payment_terms
        string bank_name
        string bank_account
        string bank_holder
        boolean is_active
        timestamp deleted_at
    }

    employees {
        bigint id PK
        string employee_id UK
        string name
        string email
        string phone
        string position
        string department
        date join_date
        date end_date
        enum status "active | on_leave | terminated"
        decimal base_salary
        string bank_name
        string bank_account
        string bank_holder
        string tax_id
        bigint user_id FK
        timestamp deleted_at
    }

    tax_rates {
        bigint id PK
        string name
        decimal rate
        enum type "sales | income | withholding"
        boolean is_default
        boolean is_active
    }

    work_orders {
        bigint id PK
        string wo_number UK
        bigint client_id FK
        string client_work_order_id
        string title
        text description
        string category
        enum priority "low | medium | high | urgent"
        enum status "draft | confirmed | in_progress | completed | invoiced | cancelled"
        date order_date
        date due_date
        date completed_date
        bigint assigned_to FK
        decimal grand_total
        bigint created_by FK
        timestamp deleted_at
    }

    work_order_items {
        bigint id PK
        bigint work_order_id FK
        string description
        decimal quantity
        string unit
        decimal unit_price
        decimal discount
        decimal tax_rate
        decimal subtotal
    }

    invoices {
        bigint id PK
        string invoice_no UK
        bigint client_id FK
        bigint work_order_id FK
        date issue_date
        date due_date
        enum status "draft | sent | partially_paid | paid | overdue | cancelled"
        decimal subtotal
        decimal tax_amount
        decimal discount_amount
        decimal grand_total
        decimal amount_paid
        decimal balance_due
        text notes
        text terms
        bigint created_by FK
        timestamp deleted_at
    }

    invoice_items {
        bigint id PK
        bigint invoice_id FK
        string description
        decimal quantity
        string unit
        decimal unit_price
        decimal discount
        decimal tax_rate
        decimal subtotal
    }

    transactions {
        bigint id PK
        string transaction_no UK
        enum type "income | expense | transfer"
        date date
        decimal amount
        bigint account_id FK
        bigint contra_account_id FK
        bigint client_id FK
        bigint vendor_id FK
        bigint work_order_id FK
        bigint invoice_id FK
        string category
        text description
        string reference_no
        enum payment_method "cash | bank_transfer | check | other"
        boolean is_reconciled
        bigint created_by FK
        timestamp deleted_at
    }

    journal_entries {
        bigint id PK
        string journal_no UK
        date date
        text description
        bigint invoice_id FK
        bigint created_by FK
        timestamp deleted_at
    }

    journal_entry_lines {
        bigint id PK
        bigint journal_entry_id FK
        bigint account_id FK
        decimal debit
        decimal credit
        string description
    }

    payroll_records {
        bigint id PK
        string payroll_no UK
        bigint employee_id FK
        tinyint period_month
        smallint period_year
        decimal base_salary
        decimal overtime_hours
        decimal overtime_rate
        decimal overtime_amount
        json allowances
        decimal total_allowances
        json deductions
        decimal total_deductions
        decimal gross_pay
        decimal tax_amount
        decimal net_pay
        enum status "draft | approved | paid"
        date paid_date
        enum payment_method "bank_transfer | cash | check"
        bigint created_by FK
        timestamp deleted_at
    }

    budget_requests {
        bigint id PK
        string request_no UK
        string type "ops_budget | expense_approval | other"
        string title
        text description
        decimal amount
        string status "pending | approved | rejected"
        bigint created_by FK
        bigint reviewed_by FK
        timestamp reviewed_at
        text review_notes
        bigint account_id FK
    }

    company_settings {
        bigint id PK
        string key UK
        text value
    }

    audit_logs {
        bigint id PK
        bigint user_id FK
        string action
        string module
        bigint record_id
        json old_values
        json new_values
        string ip_address
        text user_agent
        timestamp created_at
    }

    attachments {
        bigint id PK
        string attachable_type
        bigint attachable_id
        string file_name
        string file_path
        int file_size
        string mime_type
        bigint uploaded_by FK
    }

    roles {
        bigint id PK
        string name
        string guard_name
    }

    permissions {
        bigint id PK
        string name
        string guard_name
    }

    model_has_roles {
        bigint role_id FK
        string model_type
        bigint model_id
    }

    model_has_permissions {
        bigint permission_id FK
        string model_type
        bigint model_id
    }

    role_has_permissions {
        bigint permission_id FK
        bigint role_id FK
    }

    %% Self-referencing
    accounts ||--o{ accounts : "parent → children"

    %% User relationships
    users ||--o{ employees : "user_id"
    users ||--o{ work_orders : "created_by"
    users ||--o{ invoices : "created_by"
    users ||--o{ transactions : "created_by"
    users ||--o{ journal_entries : "created_by"
    users ||--o{ payroll_records : "created_by"
    users ||--o{ budget_requests : "created_by"
    users ||--o{ budget_requests : "reviewed_by"
    users ||--o{ audit_logs : "user_id"
    users ||--o{ attachments : "uploaded_by"

    %% Client relationships
    clients ||--o{ work_orders : "client_id"
    clients ||--o{ invoices : "client_id"
    clients ||--o{ transactions : "client_id"

    %% Vendor relationships
    vendors ||--o{ transactions : "vendor_id"

    %% Employee relationships
    employees ||--o{ work_orders : "assigned_to"
    employees ||--o{ payroll_records : "employee_id"

    %% Account relationships
    accounts ||--o{ transactions : "account_id"
    accounts ||--o{ transactions : "contra_account_id"
    accounts ||--o{ journal_entry_lines : "account_id"
    accounts ||--o{ budget_requests : "account_id"

    %% Work order relationships
    work_orders ||--o{ work_order_items : "work_order_id"
    work_orders ||--o| invoices : "work_order_id"
    work_orders ||--o{ transactions : "work_order_id"

    %% Invoice relationships
    invoices ||--o{ invoice_items : "invoice_id"
    invoices ||--o{ transactions : "invoice_id"
    invoices ||--o{ journal_entries : "invoice_id"

    %% Journal entry relationships
    journal_entries ||--o{ journal_entry_lines : "journal_entry_id"

    %% RBAC (Spatie)
    roles ||--o{ model_has_roles : "role_id"
    roles ||--o{ role_has_permissions : "role_id"
    permissions ||--o{ model_has_permissions : "permission_id"
    permissions ||--o{ role_has_permissions : "permission_id"
```

### Key Design Decisions

| Aspect | Details |
|--------|---------|
| **Bookkeeping** | Double-entry: every `transaction` records `account_id` (debit) and `contra_account_id` (credit) |
| **Chart of Accounts** | Self-referencing `parent_id` enables hierarchical account tree |
| **Soft Deletes** | `accounts`, `clients`, `vendors`, `employees`, `work_orders`, `invoices`, `transactions`, `journal_entries`, `payroll_records` |
| **Polymorphic** | `attachments` uses `attachable_type` / `attachable_id` morphs; `personal_access_tokens` uses `tokenable` morphs |
| **RBAC** | Spatie Permission — `roles`, `permissions`, and three pivot tables |
| **Audit Trail** | `audit_logs` stores JSON diffs (`old_values` / `new_values`) per module action |
| **Enums** | Status and type fields use database-level enums for data integrity |

## API

The backend preserves a RESTful JSON API at `/api/v1` with Sanctum token-based auth for backward compatibility. See `docs/PRD.md` for full API documentation.

## Project Structure

```
kucatat/
├── backend/                 # Laravel monolith (web + API)
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   ├── Api/V1/            # Existing JSON API controllers (preserved)
│   │   │   └── Auth/              # Web auth (LoginController)
│   │   ├── Livewire/              # Livewire components (all modules)
│   │   ├── Helpers/               # Format helper (currency, date)
│   │   ├── Models/                # Eloquent models
│   │   └── Traits/                # Shared traits
│   ├── database/
│   │   ├── migrations/            # Database schema
│   │   └── seeders/               # Default data
│   ├── resources/
│   │   ├── views/
│   │   │   ├── layouts/           # app.blade.php + guest.blade.php
│   │   │   ├── components/        # Blade UI components (icon, stat-card, badge, etc.)
│   │   │   ├── livewire/          # Livewire view templates (all modules)
│   │   │   ├── auth/              # login.blade.php
│   │   │   └── pdf/               # PDF templates (DomPDF)
│   │   ├── css/app.css            # Tailwind CSS v4 + design tokens
│   │   ├── js/app.js              # Alpine.js + Chart.js setup
│   │   └── lang/id.json           # Bahasa Indonesia strings
│   └── routes/
│       ├── web.php                # Web routes (Blade/Livewire)
│       └── api.php                # JSON API routes (preserved)
├── frontend/                # Next.js SPA (archived, no longer served)
└── docs/
    └── PRD.md                     # Product Requirements Document
```
