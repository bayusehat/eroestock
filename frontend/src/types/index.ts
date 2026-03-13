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
