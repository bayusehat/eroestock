export interface Permission {
  id: number;
  name: string;
}

export interface Role {
  id: number;
  name: string;
  permissions: Permission[];
}

export interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  phone?: string;
  is_active: boolean;
  last_login_at?: string;
  roles: Role[];
  permissions: Permission[];
  created_at: string;
}

export interface Account {
  id: number;
  code: string;
  name: string;
  type: string;
  sub_type?: string;
  parent_id?: number;
  is_header: boolean;
  description?: string;
  opening_balance: number;
  is_active: boolean;
  is_system: boolean;
  children?: Account[];
  created_at: string;
}

export interface Client {
  id: number;
  name: string;
  code?: string;
  email?: string;
  phone?: string;
  address?: string;
  tax_id?: string;
  contact_person?: string;
  payment_terms?: string;
  notes?: string;
  is_active: boolean;
}

export interface Vendor extends Client {
  bank_name?: string;
  bank_account?: string;
  bank_holder?: string;
}

export interface WorkOrderItem {
  id: number;
  description: string;
  quantity: number;
  unit: string;
  unit_price: number;
  discount: number;
  tax_rate: number;
  subtotal: number;
}

export interface WorkOrder {
  id: number;
  wo_number: string;
  client_id: number;
  client?: Client;
  title: string;
  description?: string;
  category?: string;
  priority?: string;
  status: string;
  order_date: string;
  due_date?: string;
  completed_date?: string;
  assigned_to?: number;
  notes?: string;
  items: WorkOrderItem[];
  total_before_tax: number;
  total_tax: number;
  total_discount: number;
  grand_total: number;
}

export interface InvoiceItem {
  id: number;
  description: string;
  quantity: number;
  unit: string;
  unit_price: number;
  discount: number;
  tax_rate: number;
  subtotal: number;
}

export interface InvoicePayment {
  id: number;
  invoice_id: number;
  amount: number;
  date: string;
  payment_method?: string;
  account_id?: number;
  account?: Account;
  reference_no?: string;
}

export interface Invoice {
  id: number;
  invoice_no: string;
  client_id: number;
  client?: Client;
  work_order_id?: number;
  issue_date: string;
  due_date: string;
  status: string;
  items: InvoiceItem[];
  subtotal: number;
  tax_amount: number;
  discount_amount: number;
  grand_total: number;
  amount_paid: number;
  balance_due: number;
  notes?: string;
  terms?: string;
  payments?: InvoicePayment[];
}

export interface Transaction {
  id: number;
  transaction_no: string;
  type: string;
  date: string;
  amount: number;
  account_id: number;
  account?: Account;
  contra_account_id?: number;
  contra_account?: Account;
  client_id?: number;
  vendor_id?: number;
  work_order_id?: number;
  invoice_id?: number;
  category?: string;
  description?: string;
  reference_no?: string;
  payment_method?: string;
  is_reconciled: boolean;
}

export interface JournalEntryLine {
  id: number;
  account_id: number;
  account?: Account;
  debit: number;
  credit: number;
  description?: string;
}

export interface JournalEntry {
  id: number;
  journal_no: string;
  date: string;
  description?: string;
  lines: JournalEntryLine[];
}

export interface Employee {
  id: number;
  employee_id: string;
  name: string;
  email?: string;
  phone?: string;
  position?: string;
  department?: string;
  join_date: string;
  end_date?: string;
  status: string;
  base_salary: number;
  bank_name?: string;
  bank_account?: string;
  bank_holder?: string;
  tax_id?: string;
  address?: string;
}

export interface PayrollRecord {
  id: number;
  payroll_no: string;
  employee_id: number;
  employee?: Employee;
  period_month: number;
  period_year: number;
  base_salary: number;
  overtime_hours: number;
  overtime_rate: number;
  overtime_amount: number;
  allowances: Record<string, number>;
  total_allowances: number;
  deductions: Record<string, number>;
  total_deductions: number;
  gross_pay: number;
  tax_amount: number;
  net_pay: number;
  status: string;
  paid_date?: string;
  payment_method?: string;
  notes?: string;
}

export interface TaxRate {
  id: number;
  name: string;
  rate: number;
  type: string;
  is_default: boolean;
  is_active: boolean;
}

export interface CompanySetting {
  id: number;
  key: string;
  value: string;
}

export interface AuditLog {
  id: number;
  user_id: number;
  user?: User;
  action: string;
  module: string;
  record_id?: number;
  old_values?: Record<string, unknown>;
  new_values?: Record<string, unknown>;
  ip_address?: string;
  created_at: string;
}

export interface DashboardData {
  revenue_mtd: number;
  revenue_ytd: number;
  expenses_mtd: number;
  expenses_ytd: number;
  net_profit_mtd: number;
  net_profit_ytd: number;
  cash_balance: number;
  outstanding_receivables: number;
  outstanding_payables: number;
  recent_transactions: Transaction[];
  work_order_pipeline: Record<string, number>;
}

export interface ApiResponse<T> {
  success: boolean;
  message?: string;
  data: T;
  meta?: Record<string, unknown>;
}

export interface PaginatedMeta {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: PaginatedMeta;
}

export interface ProfitLossReport {
  total_revenue: number;
  total_expenses: number;
  net_profit: number;
  revenue: { account_code: string; account_name: string; amount: number }[];
  expenses: { account_code: string; account_name: string; amount: number }[];
  chart_data?: { month: string; revenue: number; expenses: number }[];
}

export interface BalanceSheetReport {
  assets: { account_code: string; account_name: string; balance: number }[];
  liabilities: { account_code: string; account_name: string; balance: number }[];
  equity: { account_code: string; account_name: string; balance: number }[];
  total_assets: number;
  total_liabilities: number;
  total_equity: number;
}

export interface CashFlowReport {
  opening_balance: number;
  closing_balance: number;
  net_cash_flow: number;
  operating: { inflows: number; outflows: number };
  investing: { inflows: number; outflows: number };
  financing: { inflows: number; outflows: number };
  chart_data?: { date: string; balance: number }[];
}

export interface TrialBalanceReport {
  accounts: { account_code: string; account_name: string; debit: number; credit: number }[];
  total_debits: number;
  total_credits: number;
}

export interface GeneralLedgerReport {
  account: { id: number; code: string; name: string };
  opening_balance: number;
  closing_balance: number;
  entries: {
    date: string;
    description: string;
    reference?: string;
    debit: number;
    credit: number;
    running_balance: number;
  }[];
}

export interface AgingReport {
  rows: { name: string; current: number; days_31_60: number; days_61_90: number; over_90: number; total: number }[];
  totals: { current: number; days_31_60: number; days_61_90: number; over_90: number; total: number };
}

export interface IncomeByClientReport {
  rows: { client_name: string; amount: number; percentage: number }[];
}

export interface ExpenseByCategoryReport {
  rows: { category: string; amount: number; percentage: number }[];
}

export interface WorkOrderSummaryReport {
  total_count: number;
  total_value: number;
  average_value: number;
  by_status: { status: string; count: number; total_value: number }[];
}

export interface PayrollSummaryReport {
  total_gross: number;
  total_deductions: number;
  total_tax: number;
  total_net: number;
  by_employee: {
    employee_name: string;
    base_salary: number;
    overtime: number;
    allowances: number;
    deductions: number;
    tax: number;
    net_pay: number;
  }[];
  by_department?: { department: string; count: number; total: number }[];
}

export interface TaxSummaryReport {
  rows: { tax_type: string; tax_name: string; amount: number }[];
  total_collected: number;
  total_withheld: number;
  net_liability: number;
}
