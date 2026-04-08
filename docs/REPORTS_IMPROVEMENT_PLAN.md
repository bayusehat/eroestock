# Reports Feature Improvement Plan

> **Status: Implemented** (March 2025)

## Executive Summary

The reports pages are not displaying correctly due to **API response structure mismatches** between backend and frontend, and **incomplete data sources** for revenue (invoice revenue flows through Journal Entries, not Transactions). This plan addresses both issues.

---

## Data Flow Context

```
Work Order → Invoice (linked via work_order_id)
     ↓
Invoice (mark as sent) → Journal Entry: Debit A/R, Credit Revenue (4-1000)
     ↓
Invoice (record payment) → Transaction: Debit Cash, Credit A/R (type=income, invoice_id, client_id)
```

**Key insight**: Revenue from invoices is recognized in **Journal Entries** when the invoice is marked as sent. The payment Transaction only moves cash from A/R to Bank—it does NOT represent revenue. The current Profit & Loss report only queries `Transaction::where('type','income')`, which groups by `account_id` (the cash account), so invoice revenue never appears correctly.

---

## Issues Identified

### 1. Profit & Loss Report

| Issue | Backend | Frontend | Fix |
|-------|---------|----------|-----|
| Revenue data source | Only `Transaction` type=income (groups by cash account) | Expects revenue by account | **Backend**: Include JournalEntryLine credits to revenue accounts |
| Property names | `revenue_accounts`, `expense_accounts` | `revenue`, `expenses` | Align: use `revenue`/`expenses` or adapt frontend |
| Item structure | `{ account_id, code, name, amount }` | `{ account_code, account_name, amount }` | Normalize to `account_code`, `account_name` |

### 2. Income by Client Report

| Issue | Backend | Frontend | Fix |
|-------|---------|----------|-----|
| Data source | `Transaction` type=income with client_id | - | Payment transactions have client_id—this works for paid invoices |
| Response key | `by_client` | `rows` | Map `by_client` → `rows` in frontend or change backend |
| Item structure | `{ client_id, client_name, client_code, amount, percentage }` | `{ client_name, amount, percentage }` | Frontend can use client_name; ensure rows array |

### 3. Work Order Summary Report

| Issue | Backend | Frontend | Fix |
|-------|---------|----------|-----|
| `by_status` format | Object: `{ "draft": { count, total_value }, "invoiced": {...} }` | Array: `[{ status, count, total_value }]` | **Backend**: Return array with status in each item |
| Stat keys | `total_work_orders`, `total_value`, `average_value` | `total_count`, `total_value`, `average_value` | Align `total_work_orders` → `total_count` |

### 4. Receivable Aging Report

| Issue | Backend | Frontend | Fix |
|-------|---------|----------|-----|
| Rows key | `by_client` | `rows` | Map or rename |
| Bucket keys | `31_60`, `61_90`, `90_plus` | `days_31_60`, `days_61_90`, `over_90` | Align key names |
| Totals | `grand_total` | `total` | Align |

### 5. Balance Sheet Report

| Issue | Backend | Frontend | Fix |
|-------|---------|----------|-----|
| Item structure | `{ account_id, code, name, balance }` | `{ account_code, account_name, balance }` | Use `account_code`, `account_name` |

### 6. Payroll Summary Report

| Issue | Backend | Frontend | Fix |
|-------|---------|----------|-----|
| Date params | Expects `date_from`, `date_to` | Sends `month`, `year` | **Backend**: Accept month/year and derive date range |
| `by_employee` | `{ gross_pay, total_deductions, tax_amount, net_pay }` | `{ base_salary, overtime, allowances, deductions, tax, net_pay }` | Map or extend backend |
| `by_department` | `{ count, total_gross, total_deductions, total_tax, total_net }` | `{ department, count, total }` | Add department key, map total_net→total |

### 7. Expense by Category Report

| Issue | Backend | Frontend | Fix |
|-------|---------|----------|-----|
| Response key | `by_category` | `rows` | Map or rename |
| Item structure | `{ account_id, code, name, amount, percentage }` | `{ category, amount, percentage }` | Map `name`→`category` |

### 8. Tax Summary Report

| Issue | Backend | Frontend | Fix |
|-------|---------|----------|-----|
| Response structure | `by_type` object, `total` | `rows`, `total_collected`, `total_withheld`, `net_liability` | Transform to rows + computed fields |

### 9. General Ledger

- Uses both Transactions and JournalEntryLines—already correct.
- Ensure `account` filter and date range work as expected.

---

## Implementation Plan

### Phase 1: Backend ReportController Fixes (High Priority)

1. **Profit & Loss** (`profitLoss`)
   - Add revenue from `JournalEntryLine` where `account.type = 'revenue'` and `credit > 0`, within date range.
   - Merge with existing transaction-based revenue (for non-invoice income).
   - Ensure expense includes both Transactions and JournalEntryLine debits to expense accounts (if any).
   - Return `revenue` and `expenses` (not `revenue_accounts`/`expense_accounts`) with `account_code`, `account_name`, `amount`.

2. **Work Order Summary** (`workOrderSummary`)
   - Transform `by_status` from object to array: `Object.entries(byStatus).map(([status, data]) => ({ status, ...data }))`.
   - Add `total_count` as alias for `total_work_orders` for frontend compatibility.

3. **Receivable Aging** (`receivableAging`)
   - Return `rows` instead of (or in addition to) `by_client`, with keys: `name`, `current`, `days_31_60`, `days_61_90`, `over_90`, `total`.
   - Return `totals` with `days_31_60`, `days_61_90`, `over_90`, `total` (instead of `31_60`, `61_90`, `90_plus`, `grand_total`).

4. **Income by Client** (`incomeByClient`)
   - Include revenue from Journal Entries linked to invoices (requires `invoice_id` on `journal_entries` or matching via description).
   - **Simpler approach**: Keep transaction-based for now; ensure `by_client` is returned and frontend maps to `rows`.
   - Return `rows` as alias: `rows: by_client` with normalized keys.

5. **Payroll Summary** (`payrollSummary`)
   - Accept `month` and `year` query params; compute `date_from` = first day of month, `date_to` = last day of month.
   - Normalize `by_employee` to include `base_salary`, `overtime`, `allowances`, `deductions`, `tax`, `net_pay` (map from backend fields).
   - Normalize `by_department` to `{ department, count, total }` array.

6. **Balance Sheet, Expense by Category**
   - Use `account_code` and `account_name` in response items.

7. **Tax Summary**
   - Return `rows` array from `by_type`, plus `total_collected`, `total_withheld`, `net_liability`.

### Phase 2: Frontend Adaptations (Defensive)

1. **Profit & Loss**: Support both `revenue_accounts`/`expense_accounts` and `revenue`/`expenses`; support both `code`/`name` and `account_code`/`account_name`.
2. **Income by Client**: Use `data.by_client ?? data.rows` and map to rows.
3. **Work Order Summary**: Handle `by_status` as object or array; use `total_count ?? total_work_orders`.
4. **Receivable Aging**: Use `data.by_client ?? data.rows`; map bucket keys.
5. **Payroll Summary**: Send `date_from`/`date_to` derived from month/year if backend doesn’t accept month/year.
6. **Expense by Category**: Use `by_category ?? rows`; map `name`→`category`.
7. **Tax Summary**: Build rows from `by_type` if `rows` is missing.

### Phase 3: Schema Enhancement (Optional)

- Add `invoice_id` to `journal_entries` when created from invoice (mark as sent).
- Use this for accurate Income by Client from accrued revenue (in addition to payment-based).

---

## Recommended Implementation Order

1. **Work Order Summary** – Quick fix (transform by_status to array).
2. **Profit & Loss** – Critical; add journal-based revenue.
3. **Receivable Aging** – Align keys with frontend.
4. **Income by Client** – Align response structure.
5. **Payroll Summary** – Accept month/year; normalize by_employee/by_department.
6. **Balance Sheet, Expense by Category, Tax Summary** – Normalize field names.
7. **Frontend defensive handling** – Ensure robustness if backend lags.

---

## Testing Checklist

- [ ] Create Work Order → Invoice from WO → Mark as Sent → Record Payment.
- [ ] Verify Profit & Loss shows revenue from invoice (journal entry).
- [ ] Verify General Ledger for revenue account (4-1000) shows journal entry.
- [ ] Verify General Ledger for A/R (1-2000) shows debit (sent) and credit (payment).
- [ ] Verify Income by Client shows client and amount.
- [ ] Verify Work Order Summary shows by_status as table and chart.
- [ ] Verify Receivable Aging shows unpaid invoices by client.
- [ ] Verify Payroll Summary with month/year selector.
- [ ] Verify Balance Sheet balances.
- [ ] Verify Trial Balance debits = credits.

---

## Files to Modify

### Backend
- `app/Http/Controllers/Api/V1/ReportController.php` – All report methods

### Optional
- `database/migrations/xxxx_add_invoice_id_to_journal_entries.php`
- `app/Models/JournalEntry.php` – Add invoice relationship
