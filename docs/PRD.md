# Product Requirements Document (PRD)

## Personal Accounting Web Application

**Version:** 1.0
**Date:** March 13, 2026
**Status:** Draft

---

## 1. Overview

### 1.1 Purpose

A web-based accounting application designed for small-to-medium businesses to manage work orders, track financial transactions, handle employee payroll, and generate financial reports. The system provides a streamlined approach to bookkeeping without the complexity of enterprise accounting software.

### 1.2 Tech Stack

| Layer        | Technology                          |
| ------------ | ----------------------------------- |
| Backend      | Laravel 11 (PHP 8.3+)              |
| Frontend     | Next.js 15 (React 19, TypeScript)  |
| Database     | PostgreSQL 16                       |
| Cache        | Redis                               |
| Auth         | Laravel Sanctum (API tokens + SPA)  |
| API          | RESTful JSON API                    |
| File Storage | Local / S3-compatible               |
| Queue        | Laravel Queue (Redis driver)        |
| PDF          | DomPDF / Snappy                     |

### 1.3 Target Users

- Business owners managing service-based companies
- Accountants and bookkeepers handling day-to-day financials
- Administrators overseeing operations, payroll, and user access

---

## 2. Core Modules

### 2.1 Authentication & User Management

#### 2.1.1 Authentication

- Email + password login
- Session-based auth for SPA (Sanctum)
- Password reset via email
- Account lockout after 5 failed attempts (15 min cooldown)
- Remember me (optional persistent session)

#### 2.1.2 User Management

| Feature                | Description                                           |
| ---------------------- | ----------------------------------------------------- |
| User CRUD              | Create, read, update, deactivate users                |
| Roles                  | Predefined roles: `super_admin`, `admin`, `accountant`, `viewer` |
| Permissions            | Granular permission set per module (view, create, edit, delete) |
| Profile Management     | Each user can update their own name, email, password, avatar |
| Activity Log           | Track who did what and when (per user)                |

#### 2.1.3 Role Permission Matrix

| Permission            | Super Admin | Admin | Accountant | Viewer |
| --------------------- | :---------: | :---: | :--------: | :----: |
| Manage Users          | ✅          | ✅    | ❌         | ❌     |
| Manage Roles          | ✅          | ❌    | ❌         | ❌     |
| Work Orders           | ✅          | ✅    | ✅         | 👁️     |
| Financial Records     | ✅          | ✅    | ✅         | 👁️     |
| Chart of Accounts     | ✅          | ✅    | ✅         | 👁️     |
| Payroll               | ✅          | ✅    | ❌         | ❌     |
| Reports               | ✅          | ✅    | ✅         | 👁️     |
| Settings              | ✅          | ✅    | ❌         | ❌     |
| Audit Logs            | ✅          | ✅    | ❌         | ❌     |

*👁️ = Read-only access*

---

### 2.2 Work Order Management

Track and manage service requests received from external client companies.

#### 2.2.1 Work Order Fields

| Field              | Type       | Required | Description                          |
| ------------------ | ---------- | :------: | ------------------------------------ |
| `wo_number`        | string     | Auto     | Auto-generated (e.g., `WO-2026-0001`) |
| `client_id`        | FK         | ✅       | Reference to client company          |
| `title`            | string     | ✅       | Short description of the work        |
| `description`      | text       | ❌       | Detailed scope of work               |
| `category`         | enum       | ✅       | Service category                     |
| `priority`         | enum       | ❌       | Low / Medium / High / Urgent         |
| `status`           | enum       | Auto     | Draft → Confirmed → In Progress → Completed → Invoiced → Cancelled |
| `order_date`       | date       | ✅       | Date the order was received          |
| `due_date`         | date       | ❌       | Expected completion date             |
| `completed_date`   | date       | ❌       | Actual completion date               |
| `assigned_to`      | FK         | ❌       | Employee responsible                 |
| `attachments`      | files      | ❌       | Supporting documents / images        |
| `notes`            | text       | ❌       | Internal notes                       |

#### 2.2.2 Work Order Pricing

| Field              | Type       | Required | Description                          |
| ------------------ | ---------- | :------: | ------------------------------------ |
| `line_items`       | array      | ✅       | List of services/materials           |
| ↳ `description`   | string     | ✅       | Item description                     |
| ↳ `quantity`       | decimal    | ✅       | Quantity                             |
| ↳ `unit`           | string     | ❌       | Unit of measure (hrs, pcs, etc.)     |
| ↳ `unit_price`     | decimal    | ✅       | Price per unit                       |
| ↳ `discount`       | decimal    | ❌       | Discount percentage or amount        |
| ↳ `tax_rate`       | decimal    | ❌       | Applicable tax rate                  |
| ↳ `subtotal`       | decimal    | Auto     | Calculated subtotal                  |
| `total_before_tax` | decimal    | Auto     | Sum of line item subtotals           |
| `total_tax`        | decimal    | Auto     | Sum of taxes                         |
| `total_discount`   | decimal    | Auto     | Sum of discounts                     |
| `grand_total`      | decimal    | Auto     | Final amount                         |

#### 2.2.3 Work Order Features

- List view with filtering (status, client, date range, assigned employee)
- Status workflow with transition validation
- Duplicate an existing work order
- Convert completed work order → Invoice (auto-populate line items)
- Attach files (PDF, images, documents — max 10MB each)
- Internal notes timeline
- Print / Export to PDF

---

### 2.3 Client & Vendor Management

#### 2.3.1 Client (Customers)

Companies that send work orders / receive invoices.

| Field              | Type       | Required | Description                          |
| ------------------ | ---------- | :------: | ------------------------------------ |
| `name`             | string     | ✅       | Company name                         |
| `code`             | string     | Auto     | Short code (e.g., `CLT-001`)         |
| `email`            | string     | ❌       | Primary contact email                |
| `phone`            | string     | ❌       | Phone number                         |
| `address`          | text       | ❌       | Full address                         |
| `tax_id`           | string     | ❌       | Tax identification number (NPWP)     |
| `contact_person`   | string     | ❌       | Name of primary contact              |
| `payment_terms`    | integer    | ❌       | Default payment terms in days (e.g., 30) |
| `notes`            | text       | ❌       | Internal notes                       |
| `is_active`        | boolean    | Auto     | Soft-delete / deactivation           |

#### 2.3.2 Vendor (Suppliers)

Companies you pay for goods or services.

Same structure as Client with additional fields:

| Field              | Type       | Required | Description                          |
| ------------------ | ---------- | :------: | ------------------------------------ |
| `bank_name`        | string     | ❌       | Bank name for payments               |
| `bank_account`     | string     | ❌       | Bank account number                  |
| `bank_holder`      | string     | ❌       | Account holder name                  |

---

### 2.4 Chart of Accounts (Account Mapping)

Standard double-entry bookkeeping account structure.

#### 2.4.1 Account Fields

| Field              | Type       | Required | Description                          |
| ------------------ | ---------- | :------: | ------------------------------------ |
| `code`             | string     | ✅       | Account code (e.g., `1-1000`)        |
| `name`             | string     | ✅       | Account name                         |
| `type`             | enum       | ✅       | Asset / Liability / Equity / Revenue / Expense |
| `sub_type`         | string     | ❌       | Further classification               |
| `parent_id`        | FK         | ❌       | Parent account (for hierarchy)       |
| `is_header`        | boolean    | ❌       | Group header (non-postable)          |
| `description`      | text       | ❌       | Purpose of the account               |
| `opening_balance`  | decimal    | ❌       | Opening balance                      |
| `is_active`        | boolean    | Auto     | Active/Inactive                      |
| `is_system`        | boolean    | Auto     | System-generated, cannot be deleted  |

#### 2.4.2 Default Chart of Accounts (Seeded)

```
1-0000  ASSETS
├── 1-1000  Cash & Bank
│   ├── 1-1001  Petty Cash
│   ├── 1-1002  Bank Account - Primary
│   └── 1-1003  Bank Account - Secondary
├── 1-2000  Accounts Receivable
├── 1-3000  Inventory
└── 1-4000  Fixed Assets
    ├── 1-4001  Equipment
    └── 1-4002  Vehicles

2-0000  LIABILITIES
├── 2-1000  Accounts Payable
├── 2-2000  Tax Payable
├── 2-3000  Salary Payable
└── 2-4000  Loans

3-0000  EQUITY
├── 3-1000  Owner's Capital
└── 3-2000  Retained Earnings

4-0000  REVENUE
├── 4-1000  Service Revenue
├── 4-2000  Sales Revenue
└── 4-9000  Other Revenue

5-0000  EXPENSES
├── 5-1000  Salary & Wages
├── 5-2000  Rent Expense
├── 5-3000  Utilities Expense
├── 5-4000  Office Supplies
├── 5-5000  Transportation
├── 5-6000  Depreciation
├── 5-7000  Tax Expense
└── 5-9000  Miscellaneous Expense
```

#### 2.4.3 Features

- Tree view of account hierarchy
- Search and filter by type
- Cannot delete accounts with existing transactions (soft-delete only)
- Account balance summary

---

### 2.5 Financial Transactions (Income & Expense)

#### 2.5.1 Transaction Fields

| Field              | Type       | Required | Description                          |
| ------------------ | ---------- | :------: | ------------------------------------ |
| `transaction_no`   | string     | Auto     | Auto-generated (e.g., `TXN-2026-0001`) |
| `type`             | enum       | ✅       | Income / Expense / Transfer          |
| `date`             | date       | ✅       | Transaction date                     |
| `amount`           | decimal    | ✅       | Transaction amount                   |
| `account_id`       | FK         | ✅       | Chart of Account reference           |
| `contra_account_id`| FK         | ✅       | Offsetting account (double-entry)    |
| `client_id`        | FK         | ❌       | Related client (for income)          |
| `vendor_id`        | FK         | ❌       | Related vendor (for expense)         |
| `work_order_id`    | FK         | ❌       | Related work order                   |
| `invoice_id`       | FK         | ❌       | Related invoice                      |
| `category`         | string     | ❌       | Custom category tag                  |
| `description`      | text       | ❌       | Details                              |
| `reference_no`     | string     | ❌       | External ref (bank ref, check no.)   |
| `payment_method`   | enum       | ❌       | Cash / Bank Transfer / Check / Other |
| `attachments`      | files      | ❌       | Receipts, proof of payment           |
| `is_reconciled`    | boolean    | Auto     | Bank reconciliation status           |

#### 2.5.2 Journal Entries

For advanced users or corrections — manual double-entry postings.

| Field              | Type       | Required | Description                          |
| ------------------ | ---------- | :------: | ------------------------------------ |
| `journal_no`       | string     | Auto     | Auto-generated                       |
| `date`             | date       | ✅       | Entry date                           |
| `description`      | text       | ✅       | Reason for journal entry             |
| `lines`            | array      | ✅       | Min 2 lines                          |
| ↳ `account_id`    | FK         | ✅       | Account                              |
| ↳ `debit`         | decimal    | ❌       | Debit amount                         |
| ↳ `credit`        | decimal    | ❌       | Credit amount                        |
| ↳ `description`   | string     | ❌       | Line description                     |
| **Validation**     |            |          | Total debits MUST equal total credits |

#### 2.5.3 Features

- Quick-entry form for common income/expense
- Recurring transactions (daily, weekly, monthly, yearly)
- Bulk import from CSV/Excel
- Bank reconciliation workflow
- Split transactions across multiple accounts
- Void/reverse a transaction (never hard-delete)
- Filter by date range, type, account, client/vendor, amount range

---

### 2.6 Invoice Management

#### 2.6.1 Invoice Fields

| Field              | Type       | Required | Description                          |
| ------------------ | ---------- | :------: | ------------------------------------ |
| `invoice_no`       | string     | Auto     | Auto-generated (e.g., `INV-2026-0001`) |
| `client_id`        | FK         | ✅       | Billed to                            |
| `work_order_id`    | FK         | ❌       | Source work order                    |
| `issue_date`       | date       | ✅       | Invoice date                         |
| `due_date`         | date       | ✅       | Payment due date                     |
| `status`           | enum       | Auto     | Draft → Sent → Partially Paid → Paid → Overdue → Cancelled |
| `line_items`       | array      | ✅       | Same structure as WO line items      |
| `subtotal`         | decimal    | Auto     | Before tax                           |
| `tax_amount`       | decimal    | Auto     | Tax total                            |
| `discount_amount`  | decimal    | Auto     | Discount total                       |
| `grand_total`      | decimal    | Auto     | Final amount                         |
| `amount_paid`      | decimal    | Auto     | Payments received so far             |
| `balance_due`      | decimal    | Auto     | Remaining balance                    |
| `notes`            | text       | ❌       | Notes to client                      |
| `terms`            | text       | ❌       | Payment terms and conditions         |

#### 2.6.2 Features

- Generate from work order (one-click)
- PDF generation with company branding
- Record partial payments
- Automatic overdue status (daily cron)
- Payment reminders (future enhancement)
- Duplicate invoice
- Credit notes for refunds/adjustments

---

### 2.7 Employee & Payroll Management

#### 2.7.1 Employee Fields

| Field              | Type       | Required | Description                          |
| ------------------ | ---------- | :------: | ------------------------------------ |
| `employee_id`      | string     | Auto     | Auto-generated (e.g., `EMP-001`)    |
| `name`             | string     | ✅       | Full name                            |
| `email`            | string     | ❌       | Email address                        |
| `phone`            | string     | ❌       | Phone number                         |
| `position`         | string     | ✅       | Job title                            |
| `department`       | string     | ❌       | Department                           |
| `join_date`        | date       | ✅       | Date of joining                      |
| `end_date`         | date       | ❌       | Termination date                     |
| `status`           | enum       | Auto     | Active / On Leave / Terminated       |
| `base_salary`      | decimal    | ✅       | Monthly base salary                  |
| `bank_name`        | string     | ❌       | Bank for salary transfer             |
| `bank_account`     | string     | ❌       | Bank account number                  |
| `bank_holder`      | string     | ❌       | Account holder name                  |
| `tax_id`           | string     | ❌       | Tax ID (NPWP)                        |
| `address`          | text       | ❌       | Home address                         |
| `user_id`          | FK         | ❌       | Linked system user (optional)        |

#### 2.7.2 Payroll Record Fields

| Field              | Type       | Required | Description                          |
| ------------------ | ---------- | :------: | ------------------------------------ |
| `payroll_no`       | string     | Auto     | Auto-generated (e.g., `PAY-2026-03-001`) |
| `employee_id`      | FK         | ✅       | Employee reference                   |
| `period_month`     | integer    | ✅       | Month (1–12)                         |
| `period_year`      | integer    | ✅       | Year                                 |
| `base_salary`      | decimal    | Auto     | Copied from employee record          |
| `overtime_hours`   | decimal    | ❌       | Overtime hours worked                |
| `overtime_rate`    | decimal    | ❌       | Rate per overtime hour               |
| `overtime_amount`  | decimal    | Auto     | Calculated overtime pay              |
| `allowances`       | JSON       | ❌       | Array of {name, amount}              |
| `total_allowances` | decimal    | Auto     | Sum of allowances                    |
| `deductions`       | JSON       | ❌       | Array of {name, amount}              |
| `total_deductions` | decimal    | Auto     | Sum of deductions                    |
| `gross_pay`        | decimal    | Auto     | base + overtime + allowances         |
| `tax_amount`       | decimal    | Auto     | Income tax (PPh 21 or custom)        |
| `net_pay`          | decimal    | Auto     | gross - deductions - tax             |
| `status`           | enum       | Auto     | Draft → Approved → Paid             |
| `paid_date`        | date       | ❌       | Actual payment date                  |
| `payment_method`   | enum       | ❌       | Bank Transfer / Cash / Check         |
| `notes`            | text       | ❌       | Remarks                              |

#### 2.7.3 Payroll Features

- Batch payroll generation per month (auto-populate all active employees)
- Individual payroll editing (add allowances, deductions, overtime)
- Payroll approval workflow (Draft → Approved → Paid)
- Auto-post to financial transactions upon payment
- Payslip PDF generation per employee
- Payroll summary report per period
- Year-to-date earnings per employee

---

### 2.8 Tax Management

#### 2.8.1 Tax Rates

| Field              | Type       | Required | Description                          |
| ------------------ | ---------- | :------: | ------------------------------------ |
| `name`             | string     | ✅       | Tax name (e.g., PPN 11%)            |
| `rate`             | decimal    | ✅       | Percentage rate                      |
| `type`             | enum       | ✅       | Sales Tax / Income Tax / Withholding |
| `is_default`       | boolean    | ❌       | Default tax for new transactions     |
| `is_active`        | boolean    | Auto     | Active/Inactive                      |

#### 2.8.2 Features

- Configure multiple tax rates
- Apply tax per line item or per transaction
- Tax summary report by period
- Tax liability tracking

---

### 2.9 Financial Reports

All reports support date range filtering and PDF/Excel export.

| Report                    | Description                                              |
| ------------------------- | -------------------------------------------------------- |
| **Profit & Loss**         | Revenue minus expenses for a period                      |
| **Balance Sheet**         | Assets, liabilities, equity snapshot at a point in time  |
| **Cash Flow Statement**   | Cash inflows and outflows by category                    |
| **Trial Balance**         | All account balances (debit/credit totals)               |
| **General Ledger**        | All transactions per account                             |
| **Accounts Receivable Aging** | Outstanding client invoices grouped by age           |
| **Accounts Payable Aging**| Outstanding vendor bills grouped by age                  |
| **Income by Client**      | Revenue breakdown per client                             |
| **Expense by Category**   | Expense breakdown per account/category                   |
| **Work Order Summary**    | Work orders by status, client, period                    |
| **Payroll Summary**       | Total payroll costs per period                           |
| **Tax Summary**           | Tax collected and payable per period                     |

---

### 2.10 Dashboard

The main landing page after login, providing a financial overview at a glance.

#### Widgets

| Widget                    | Description                                              |
| ------------------------- | -------------------------------------------------------- |
| Total Revenue (MTD/YTD)   | Current month and year-to-date revenue                  |
| Total Expenses (MTD/YTD)  | Current month and year-to-date expenses                 |
| Net Profit (MTD/YTD)      | Revenue - Expenses                                      |
| Cash Balance              | Total cash across all bank/cash accounts                |
| Outstanding Receivables   | Total unpaid invoices                                   |
| Outstanding Payables      | Total unpaid bills                                      |
| Revenue vs Expense Chart  | Bar/line chart (last 12 months)                         |
| Recent Transactions       | Latest 10 transactions                                  |
| Work Order Pipeline       | Work orders by status (donut chart)                     |
| Overdue Invoices          | List of overdue invoices requiring attention             |
| Upcoming Payroll          | Next payroll due date and estimated amount               |

---

### 2.11 Audit Trail & Activity Logs

| Field              | Type       | Description                          |
| ------------------ | ---------- | ------------------------------------ |
| `user_id`          | FK         | Who performed the action             |
| `action`           | enum       | Created / Updated / Deleted / Viewed / Exported / Login / Logout |
| `module`           | string     | Which module (e.g., `work_orders`)   |
| `record_id`        | integer    | Affected record ID                   |
| `old_values`       | JSON       | Previous values (for updates)        |
| `new_values`       | JSON       | New values (for updates)             |
| `ip_address`       | string     | User's IP address                    |
| `user_agent`       | string     | Browser/device info                  |
| `timestamp`        | datetime   | When it happened                     |

- Immutable — logs cannot be edited or deleted
- Filterable by user, module, action, date range
- Exportable to CSV

---

### 2.12 Company Settings

| Setting             | Description                                              |
| ------------------- | -------------------------------------------------------- |
| Company Name        | Legal company name                                       |
| Company Logo        | Logo for invoices and reports                             |
| Address             | Business address                                         |
| Phone / Email       | Contact info                                             |
| Tax ID              | Company tax identification number                        |
| Currency            | Primary currency (IDR, USD, etc.)                        |
| Fiscal Year Start   | Start month of fiscal year                               |
| Invoice Prefix      | Custom prefix for invoice numbers                        |
| WO Prefix           | Custom prefix for work order numbers                     |
| Default Payment Terms | Default payment terms in days                           |
| Date Format         | Display date format preference                           |

---

## 3. Non-Functional Requirements

### 3.1 Performance

| Metric                  | Target                           |
| ----------------------- | -------------------------------- |
| API response time       | < 300ms (p95)                    |
| Page load (initial)     | < 2 seconds                      |
| Page navigation (SPA)   | < 500ms                          |
| Report generation       | < 5 seconds for 1 year of data   |
| Concurrent users        | Support up to 50 simultaneous    |

### 3.2 Security

- All API endpoints authenticated (except login/register)
- CSRF protection on all state-changing operations
- Input validation on both frontend and backend
- SQL injection prevention (Eloquent ORM parameterized queries)
- XSS prevention (React auto-escaping + server-side sanitization)
- Rate limiting: 60 requests/minute per user for general API, 5/minute for login
- Sensitive data encrypted at rest (bank accounts, tax IDs)
- HTTPS enforced in production
- File upload validation (type, size, malware scan)
- Session timeout after 30 minutes of inactivity

### 3.3 Data Integrity

- All monetary values stored as `DECIMAL(15,2)` — never float
- Double-entry validation: every journal entry must balance (debits = credits)
- Soft-delete on all financial records (never hard-delete)
- Database transactions for multi-table operations
- Unique constraints on all auto-generated numbers
- Referential integrity via foreign keys

### 3.4 Backup & Recovery

- Automated daily database backups
- Point-in-time recovery capability
- Data export to CSV/Excel for all modules

---

## 4. API Architecture

### 4.1 API Convention

```
Base URL:  /api/v1

Format:    JSON
Auth:      Bearer token (Sanctum)
Pagination: cursor-based, default 25 per page
Sorting:   ?sort=field&order=asc|desc
Filtering: ?filter[field]=value
Search:    ?search=keyword
```

### 4.2 Endpoint Groups

| Prefix                   | Module                    |
| ------------------------ | ------------------------- |
| `/api/v1/auth`           | Authentication            |
| `/api/v1/users`          | User Management           |
| `/api/v1/roles`          | Role & Permission         |
| `/api/v1/clients`        | Client Management         |
| `/api/v1/vendors`        | Vendor Management         |
| `/api/v1/work-orders`    | Work Orders               |
| `/api/v1/invoices`       | Invoices                  |
| `/api/v1/transactions`   | Financial Transactions    |
| `/api/v1/journal-entries`| Journal Entries           |
| `/api/v1/accounts`       | Chart of Accounts         |
| `/api/v1/employees`      | Employee Management       |
| `/api/v1/payroll`        | Payroll Records           |
| `/api/v1/taxes`          | Tax Configuration         |
| `/api/v1/reports`        | Financial Reports         |
| `/api/v1/dashboard`      | Dashboard Data            |
| `/api/v1/audit-logs`     | Audit Trail               |
| `/api/v1/settings`       | Company Settings          |

### 4.3 Standard Response Format

```json
{
  "success": true,
  "message": "Resource retrieved successfully",
  "data": {},
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 100,
    "next_cursor": "eyJpZCI6MjV9"
  }
}
```

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

---

## 5. Database Schema Overview

### 5.1 Entity Relationship Summary

```
users ──────────────── roles (many-to-many via role_user)
  │
  ├── audit_logs
  │
clients ──── work_orders ──── work_order_items
  │               │
  │               └──── invoices ──── invoice_items
  │                        │
  └────────────── transactions ──────── accounts (chart of accounts)
                      │
vendors ──────────────┘
                      
employees ──── payroll_records ──── transactions

accounts ──── journal_entries ──── journal_entry_lines
```

### 5.2 Key Tables

| Table                   | Description                              |
| ----------------------- | ---------------------------------------- |
| `users`                 | System users                             |
| `roles`                 | User roles                               |
| `permissions`           | Granular permissions                     |
| `role_user`             | Many-to-many pivot                       |
| `role_permission`       | Many-to-many pivot                       |
| `clients`               | Client companies                         |
| `vendors`               | Vendor/supplier companies                |
| `work_orders`           | Work order headers                       |
| `work_order_items`      | Work order line items                    |
| `invoices`              | Invoice headers                          |
| `invoice_items`         | Invoice line items                       |
| `accounts`              | Chart of accounts                        |
| `transactions`          | Financial transactions (income/expense)  |
| `journal_entries`       | Manual journal entry headers             |
| `journal_entry_lines`   | Journal entry debit/credit lines         |
| `employees`             | Employee records                         |
| `payroll_records`       | Monthly payroll per employee             |
| `tax_rates`             | Tax rate configurations                  |
| `company_settings`      | Company profile and preferences          |
| `audit_logs`            | Immutable activity trail                 |
| `attachments`           | Polymorphic file attachments             |

---

## 6. Frontend Structure (Next.js)

### 6.1 Page Map

```
/login
/forgot-password
/reset-password

/dashboard

/work-orders
/work-orders/create
/work-orders/[id]
/work-orders/[id]/edit

/clients
/clients/create
/clients/[id]
/clients/[id]/edit

/vendors
/vendors/create
/vendors/[id]
/vendors/[id]/edit

/invoices
/invoices/create
/invoices/[id]
/invoices/[id]/edit

/transactions
/transactions/create
/transactions/[id]

/journal-entries
/journal-entries/create
/journal-entries/[id]

/accounts               (chart of accounts tree)

/employees
/employees/create
/employees/[id]
/employees/[id]/edit

/payroll
/payroll/generate       (batch generate for a month)
/payroll/[id]
/payroll/[id]/edit

/reports
/reports/profit-loss
/reports/balance-sheet
/reports/cash-flow
/reports/trial-balance
/reports/general-ledger
/reports/receivable-aging
/reports/payable-aging
/reports/payroll-summary
/reports/tax-summary

/settings
/settings/company
/settings/taxes
/settings/users
/settings/roles
/settings/audit-logs
```

### 6.2 UI/UX Guidelines

- **Design System:** Tailwind CSS + shadcn/ui components
- **Layout:** Sidebar navigation (collapsible) + top header bar
- **Theme:** Light mode default, dark mode toggle
- **Typography:** Inter font family
- **Tables:** Sortable, filterable, paginated with bulk actions
- **Forms:** Inline validation, autosave drafts, confirmation dialogs for destructive actions
- **Responsive:** Desktop-first, functional on tablet (768px+)
- **Loading States:** Skeleton loaders, not spinners
- **Toast Notifications:** Success, error, warning, info
- **Keyboard Shortcuts:** Common actions (Ctrl+S save, Ctrl+N new, Esc cancel)
- **Empty States:** Helpful illustrations with call-to-action

---

## 7. Development Phases

### Phase 1 — Foundation (Weeks 1–3)

| Task                                | Priority |
| ----------------------------------- | :------: |
| Project setup (Laravel + Next.js)   | P0       |
| Authentication (login, logout, session) | P0   |
| User management + roles/permissions | P0       |
| Company settings                    | P0       |
| Chart of accounts (CRUD + seed)     | P0       |
| Database migrations + seeders       | P0       |

### Phase 2 — Core Business (Weeks 4–6)

| Task                                | Priority |
| ----------------------------------- | :------: |
| Client management                   | P0       |
| Vendor management                   | P0       |
| Work order management + pricing     | P0       |
| Financial transactions (income/expense) | P0   |
| Journal entries                     | P1       |

### Phase 3 — Billing & Payroll (Weeks 7–9)

| Task                                | Priority |
| ----------------------------------- | :------: |
| Invoice generation from work orders | P0       |
| Invoice management + payments       | P0       |
| Employee management                 | P0       |
| Payroll records + batch generation  | P0       |
| Tax configuration                   | P1       |

### Phase 4 — Reports & Polish (Weeks 10–12)

| Task                                | Priority |
| ----------------------------------- | :------: |
| Dashboard with widgets              | P0       |
| Financial reports (P&L, Balance Sheet, etc.) | P0 |
| Audit trail                         | P1       |
| PDF generation (invoices, payslips) | P1       |
| CSV/Excel export                    | P1       |
| Data import tooling                 | P2       |
| Dark mode                           | P2       |
| Final QA and bug fixes              | P0       |

---

## 8. Future Enhancements (Post-MVP)

| Feature                   | Description                                      |
| ------------------------- | ------------------------------------------------ |
| Multi-currency            | Support transactions in multiple currencies      |
| Bank Integration          | Auto-import bank statements via API              |
| Email Notifications       | Invoice reminders, payroll notifications         |
| Client Portal             | Clients view their invoices and make payments    |
| Budget Management         | Set and track budgets per account/department     |
| Expense Claims            | Employee expense submission and approval         |
| Inventory Tracking        | Basic stock management tied to work orders       |
| API Webhooks              | Notify external systems of events                |
| Mobile App                | React Native companion app                       |
| AI-powered Categorization | Auto-categorize transactions using ML            |
| Multi-company             | Manage multiple business entities                |

---

## 9. Glossary

| Term                | Definition                                                |
| ------------------- | --------------------------------------------------------- |
| Work Order (WO)     | A request from a client company for services              |
| Chart of Accounts   | The structured list of all financial accounts              |
| Double-Entry        | Every transaction has equal debits and credits             |
| Journal Entry       | A manual accounting entry with debit/credit lines         |
| Accounts Receivable | Money owed to you by clients                              |
| Accounts Payable    | Money you owe to vendors                                  |
| Trial Balance       | A report listing all account balances to verify balance   |
| Fiscal Year         | The 12-month period used for financial reporting          |
| Payroll             | The process of paying employees their wages               |
| Reconciliation      | Matching internal records with bank statements            |
| NPWP                | Indonesian tax identification number                      |
| PPh 21              | Indonesian income tax on employment                       |

---

*End of PRD — Version 1.0*
