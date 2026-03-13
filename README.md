# Personal Accounting Web Application

A full-featured accounting web application for small-to-medium businesses to manage work orders, track financial transactions, handle employee payroll, and generate financial reports.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 (PHP 8.3+) |
| Frontend | Next.js 15 (React 19, TypeScript) |
| Database | SQLite (dev) / PostgreSQL (production) |
| Auth | Laravel Sanctum |
| UI | Tailwind CSS + shadcn/ui |
| Charts | Recharts |
| PDF | DomPDF |

## Getting Started

### Prerequisites

- PHP 8.3+
- Composer
- Node.js 20+
- npm

### Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

The API runs at `http://localhost:8000`.

### Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

The app runs at `http://localhost:3000`.

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
- Activity history per client/vendor

### Financial Transactions
- Record income, expenses, and transfers
- Double-entry bookkeeping with contra accounts
- Payment method tracking and bank reconciliation
- Transaction summary by type and period

### Invoice Management
- Generate invoices from work orders
- Line item pricing with tax and discounts
- Record partial and full payments
- Automatic overdue tracking
- PDF generation

### Chart of Accounts
- Hierarchical account structure (Assets, Liabilities, Equity, Revenue, Expenses)
- Default chart of accounts seeded on setup
- Account balance tracking

### Journal Entries
- Manual double-entry journal postings
- Debit/credit balance validation
- Corrections and adjustments

### Employee & Payroll
- Employee directory with personal, employment, and banking details
- Monthly payroll generation (batch or individual)
- Overtime, allowances, and deductions
- Approval workflow: Draft → Approved → Paid
- Payslip PDF generation
- Auto-post payroll payments to financial transactions

### Tax Management
- Configurable tax rates (Sales, Income, Withholding)
- Apply taxes to invoices and transactions
- Tax summary reporting

### Financial Reports
- Profit & Loss Statement
- Balance Sheet
- Cash Flow Statement
- Trial Balance
- General Ledger
- Accounts Receivable Aging
- Accounts Payable Aging
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
- Revenue, expenses, and net profit (MTD/YTD)
- Cash balance and outstanding receivables/payables
- Revenue vs expense chart (12 months)
- Work order pipeline
- Recent transactions

## API

The backend exposes a RESTful JSON API at `/api/v1` with 97 endpoints covering all modules. See `docs/PRD.md` for full API documentation.

## Project Structure

```
personal-accounting/
├── backend/                 # Laravel API
│   ├── app/
│   │   ├── Http/Controllers/Api/V1/   # API controllers
│   │   ├── Http/Requests/Api/V1/      # Form validation
│   │   ├── Http/Resources/V1/         # API resources
│   │   ├── Models/                    # Eloquent models
│   │   └── Traits/                    # Shared traits
│   ├── database/
│   │   ├── migrations/                # Database schema
│   │   └── seeders/                   # Default data
│   ├── resources/views/pdf/           # PDF templates
│   └── routes/api.php                 # API routes
├── frontend/                # Next.js SPA
│   └── src/
│       ├── app/                       # Pages (App Router)
│       ├── components/                # UI components
│       ├── contexts/                  # Auth context
│       ├── lib/                       # Utilities
│       ├── providers/                 # React Query, Theme
│       └── types/                     # TypeScript types
└── docs/
    └── PRD.md                         # Product Requirements Document
```
