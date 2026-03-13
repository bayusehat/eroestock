"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { type ColumnDef } from "@tanstack/react-table";
import {
  MoreHorizontal,
  Pencil,
  Eye,
  Check,
  DollarSign,
  Zap,
  FilePlus,
} from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Employee, PayrollRecord } from "@/types";
import { PageHeader } from "@/components/page-header";
import { DataTable } from "@/components/data-table";
import { Button, buttonVariants } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { StatCard } from "@/components/stat-card";
import { Wallet, Minus, TrendingUp } from "lucide-react";
import { formatCurrency } from "@/lib/format";
import { toast } from "sonner";

const PAYROLL_STATUS_COLORS: Record<string, string> = {
  draft: "bg-muted text-muted-foreground",
  approved: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
  paid: "bg-green-500/10 text-green-600 dark:text-green-400",
};

async function fetchPayrolls(): Promise<PayrollRecord[]> {
  const res = await apiClient.get<{ data: PayrollRecord[] }>("/payroll");
  const body = res.data as { data: PayrollRecord[] };
  return body.data ?? (body as unknown as PayrollRecord[]);
}

async function fetchEmployees(): Promise<Employee[]> {
  const res = await apiClient.get<{ data: Employee[] }>("/employees");
  const body = res.data as { data: Employee[] };
  return body.data ?? (body as unknown as Employee[]);
}

export default function PayrollPage() {
  const queryClient = useQueryClient();
  const [periodMonth, setPeriodMonth] = useState<string>("");
  const [periodYear, setPeriodYear] = useState<string>(
    String(new Date().getFullYear())
  );
  const [statusFilter, setStatusFilter] = useState<string>("all");
  const [employeeFilter, setEmployeeFilter] = useState<string>("all");
  const [generateDialogOpen, setGenerateDialogOpen] = useState(false);
  const [generateMonth, setGenerateMonth] = useState<string>(
    String(new Date().getMonth() + 1)
  );
  const [generateYear, setGenerateYear] = useState<string>(
    String(new Date().getFullYear())
  );

  const approveMutation = useMutation({
    mutationFn: (payrollId: number) =>
      apiClient.put(`/payroll/${payrollId}`, { status: "approved" }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["payroll"] });
      toast.success("Payroll approved");
    },
    onError: (err: unknown) => {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to approve";
      toast.error(typeof message === "string" ? message : "Failed to approve");
    },
  });

  const markPaidMutation = useMutation({
    mutationFn: (payrollId: number) =>
      apiClient.put(`/payroll/${payrollId}`, { status: "paid" }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["payroll"] });
      toast.success("Marked as paid");
    },
    onError: (err: unknown) => {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to mark as paid";
      toast.error(typeof message === "string" ? message : "Failed to mark as paid");
    },
  });

  const { data: payrolls = [], isLoading } = useQuery({
    queryKey: ["payroll"],
    queryFn: fetchPayrolls,
  });

  const { data: employees = [] } = useQuery({
    queryKey: ["employees"],
    queryFn: fetchEmployees,
  });

  const generateMutation = useMutation({
    mutationFn: (payload: { month: number; year: number }) =>
      apiClient.post("/payroll/generate", payload),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ["payroll"] });
      toast.success(`Generated payroll for ${variables.month}/${variables.year}`);
      setGenerateDialogOpen(false);
    },
    onError: (err: unknown) => {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to generate payroll";
      toast.error(typeof message === "string" ? message : "Failed to generate payroll");
    },
  });

  const filteredData = useMemo(() => {
    let result = payrolls;
    if (periodMonth) {
      const m = parseInt(periodMonth, 10);
      result = result.filter((p) => p.period_month === m);
    }
    if (periodYear) {
      const y = parseInt(periodYear, 10);
      result = result.filter((p) => p.period_year === y);
    }
    if (statusFilter !== "all") {
      result = result.filter((p) => p.status === statusFilter);
    }
    if (employeeFilter !== "all") {
      const empId = parseInt(employeeFilter, 10);
      result = result.filter((p) => p.employee_id === empId);
    }
    return result;
  }, [payrolls, periodMonth, periodYear, statusFilter, employeeFilter]);

  const summary = useMemo(() => {
    const totalGross = filteredData.reduce((sum, p) => sum + (p.gross_pay ?? 0), 0);
    const totalDeductions = filteredData.reduce(
      (sum, p) => sum + (p.total_deductions ?? 0) + (p.tax_amount ?? 0),
      0
    );
    const totalNet = filteredData.reduce((sum, p) => sum + (p.net_pay ?? 0), 0);
    return { totalGross, totalDeductions, totalNet };
  }, [filteredData]);

  const handleGenerate = () => {
    const month = parseInt(generateMonth, 10);
    const year = parseInt(generateYear, 10);
    if (isNaN(month) || isNaN(year)) {
      toast.error("Please select month and year");
      return;
    }
    generateMutation.mutate({ month, year });
  };

  const columns: ColumnDef<PayrollRecord>[] = [
    {
      accessorKey: "payroll_no",
      header: "Payroll No",
      cell: ({ row }) => (
        <Link
          href={`/payroll/${row.original.id}`}
          className="font-medium text-primary hover:underline"
        >
          {row.original.payroll_no}
        </Link>
      ),
    },
    {
      id: "employee",
      header: "Employee",
      cell: ({ row }) => row.original.employee?.name ?? "-",
    },
    {
      id: "period",
      header: "Period",
      cell: ({ row }) =>
        `${row.original.period_month}/${row.original.period_year}`,
    },
    {
      id: "base_salary",
      header: "Base Salary",
      cell: ({ row }) => formatCurrency(row.original.base_salary ?? 0),
    },
    {
      id: "overtime",
      header: "Overtime",
      cell: ({ row }) => formatCurrency(row.original.overtime_amount ?? 0),
    },
    {
      id: "allowances",
      header: "Allowances",
      cell: ({ row }) => formatCurrency(row.original.total_allowances ?? 0),
    },
    {
      id: "deductions",
      header: "Deductions",
      cell: ({ row }) => formatCurrency(row.original.total_deductions ?? 0),
    },
    {
      id: "gross_pay",
      header: "Gross Pay",
      cell: ({ row }) => formatCurrency(row.original.gross_pay ?? 0),
    },
    {
      id: "tax",
      header: "Tax",
      cell: ({ row }) => formatCurrency(row.original.tax_amount ?? 0),
    },
    {
      id: "net_pay",
      header: "Net Pay",
      cell: ({ row }) => formatCurrency(row.original.net_pay ?? 0),
    },
    {
      id: "status",
      header: "Status",
      cell: ({ row }) => (
        <Badge
          variant="outline"
          className={
            PAYROLL_STATUS_COLORS[row.original.status] ?? "bg-muted"
          }
        >
          {row.original.status}
        </Badge>
      ),
    },
    {
      id: "actions",
      header: "",
      cell: ({ row }) => (
        <DropdownMenu>
          <DropdownMenuTrigger
            render={
              <Button variant="ghost" size="icon-sm">
                <MoreHorizontal className="size-4" />
                <span className="sr-only">Toggle menu</span>
              </Button>
            }
          />
          <DropdownMenuContent align="end">
            <DropdownMenuItem
              render={
                <Link href={`/payroll/${row.original.id}`}>
                  <Eye className="mr-2 size-4" />
                  <span>View</span>
                </Link>
              }
            />
            {row.original.status === "draft" && (
              <DropdownMenuItem
                render={
                  <Link href={`/payroll/${row.original.id}/edit`}>
                    <Pencil className="mr-2 size-4" />
                    <span>Edit</span>
                  </Link>
                }
              />
            )}
            {row.original.status === "draft" && (
              <DropdownMenuItem
                onSelect={(e) => {
                  e.preventDefault();
                  approveMutation.mutate(row.original.id);
                }}
              >
                <Check className="mr-2 size-4" />
                <span>Approve</span>
              </DropdownMenuItem>
            )}
            {row.original.status === "approved" && (
              <DropdownMenuItem
                onSelect={(e) => {
                  e.preventDefault();
                  markPaidMutation.mutate(row.original.id);
                }}
              >
                <DollarSign className="mr-2 size-4" />
                <span>Mark as Paid</span>
              </DropdownMenuItem>
            )}
          </DropdownMenuContent>
        </DropdownMenu>
      ),
    },
  ];

  const months = Array.from({ length: 12 }, (_, i) => i + 1);
  const years = Array.from(
    { length: 5 },
    (_, i) => new Date().getFullYear() - i
  );

  return (
    <div className="space-y-6">
      <PageHeader
        title="Payroll"
        description="Manage payroll"
        children={
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              onClick={() => setGenerateDialogOpen(true)}
            >
              <Zap className="mr-2 size-4" />
              Generate Payroll
            </Button>
            <Link href="/payroll/create" className={buttonVariants()}>
              <FilePlus className="mr-2 size-4" />
              Create Single
            </Link>
          </div>
        }
      />
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:flex-wrap">
        <Select value={periodMonth} onValueChange={(v) => setPeriodMonth(v ?? "")}>
          <SelectTrigger className="w-[140px]">
            <SelectValue placeholder="Month" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="">All months</SelectItem>
            {months.map((m) => (
              <SelectItem key={m} value={String(m)}>
                {new Date(2000, m - 1).toLocaleString("default", {
                  month: "long",
                })}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
        <Select value={periodYear} onValueChange={(v) => setPeriodYear(v ?? "")}>
          <SelectTrigger className="w-[120px]">
            <SelectValue placeholder="Year" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="">All years</SelectItem>
            {years.map((y) => (
              <SelectItem key={y} value={String(y)}>
                {y}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      </div>
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <StatCard
          title="Total Gross Pay"
          value={formatCurrency(summary.totalGross)}
          icon={TrendingUp}
        />
        <StatCard
          title="Total Deductions"
          value={formatCurrency(summary.totalDeductions)}
          icon={Minus}
        />
        <StatCard
          title="Total Net Pay"
          value={formatCurrency(summary.totalNet)}
          icon={Wallet}
        />
      </div>
      <div className="space-y-4">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
          <Select
            value={statusFilter}
            onValueChange={(v) => setStatusFilter(v ?? "all")}
          >
            <SelectTrigger className="w-[160px]">
              <SelectValue placeholder="Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All status</SelectItem>
              <SelectItem value="draft">Draft</SelectItem>
              <SelectItem value="approved">Approved</SelectItem>
              <SelectItem value="paid">Paid</SelectItem>
            </SelectContent>
          </Select>
          <Select
            value={employeeFilter}
            onValueChange={(v) => setEmployeeFilter(v ?? "all")}
          >
            <SelectTrigger className="w-[180px]">
              <SelectValue placeholder="Employee" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All employees</SelectItem>
              {employees.map((e) => (
                <SelectItem key={e.id} value={String(e.id)}>
                  {e.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
        <DataTable
          columns={columns}
          data={filteredData}
          isLoading={isLoading}
          emptyMessage="No payroll records found."
        />
      </div>
      <Dialog open={generateDialogOpen} onOpenChange={setGenerateDialogOpen}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Generate Payroll</DialogTitle>
          </DialogHeader>
          <div className="grid gap-4 py-4">
            <div className="space-y-2">
              <Label>Month</Label>
              <Select value={generateMonth} onValueChange={(v) => setGenerateMonth(v ?? "")}>
                <SelectTrigger>
                  <SelectValue placeholder="Select month" />
                </SelectTrigger>
                <SelectContent>
                  {months.map((m) => (
                    <SelectItem key={m} value={String(m)}>
                      {new Date(2000, m - 1).toLocaleString("default", {
                        month: "long",
                      })}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label>Year</Label>
              <Select value={generateYear} onValueChange={(v) => setGenerateYear(v ?? "")}>
                <SelectTrigger>
                  <SelectValue placeholder="Select year" />
                </SelectTrigger>
                <SelectContent>
                  {years.map((y) => (
                    <SelectItem key={y} value={String(y)}>
                      {y}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </div>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => setGenerateDialogOpen(false)}
            >
              Cancel
            </Button>
            <Button
              onClick={handleGenerate}
              disabled={generateMutation.isPending}
            >
              {generateMutation.isPending ? "Generating..." : "Generate"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
