"use client";

import { useState } from "react";
import { useParams, useRouter } from "next/navigation";
import Link from "next/link";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { Pencil, UserX, ArrowLeft } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Employee, PayrollRecord } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button, buttonVariants } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency, formatDate } from "@/lib/format";
import { toast } from "sonner";

const EMPLOYEE_STATUS_COLORS: Record<string, string> = {
  active: "bg-green-500/10 text-green-600 dark:text-green-400",
  on_leave: "bg-yellow-500/10 text-yellow-600 dark:text-yellow-400",
  terminated: "bg-red-500/10 text-red-600 dark:text-red-400",
};

async function fetchEmployee(id: string): Promise<Employee> {
  const res = await apiClient.get<{ data: Employee }>(`/employees/${id}`);
  const body = res.data as { data: Employee };
  return body.data ?? (body as unknown as Employee);
}

async function fetchEmployeePayrolls(employeeId: number): Promise<PayrollRecord[]> {
  const res = await apiClient.get<{ data: PayrollRecord[] }>(
    `/payroll?employee_id=${employeeId}`
  );
  const body = res.data as { data: PayrollRecord[] };
  return body.data ?? (body as unknown as PayrollRecord[]);
}

export default function EmployeeDetailPage() {
  const params = useParams();
  const router = useRouter();
  const queryClient = useQueryClient();
  const id = params.id as string;
  const [showTerminate, setShowTerminate] = useState(false);

  const { data: employee, isLoading } = useQuery({
    queryKey: ["employee", id],
    queryFn: () => fetchEmployee(id),
    enabled: !!id,
  });

  const { data: payrolls = [] } = useQuery({
    queryKey: ["payroll", "employee", id],
    queryFn: () => fetchEmployeePayrolls(parseInt(id, 10)),
    enabled: !!id && !!employee,
  });

  const terminateMutation = useMutation({
    mutationFn: () =>
      apiClient.put(`/employees/${id}`, { status: "terminated" }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["employee", id] });
      toast.success("Employee terminated");
      setShowTerminate(false);
    },
    onError: (err: unknown) => {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to terminate employee";
      toast.error(typeof message === "string" ? message : "Failed to terminate employee");
    },
  });

  const monthsEmployed = employee
    ? Math.floor(
        (Date.now() - new Date(employee.join_date).getTime()) /
          (30 * 24 * 60 * 60 * 1000)
      )
    : 0;

  const ytdEarnings = payrolls
    .filter((p) => new Date(p.period_year, (p.period_month ?? 1) - 1).getFullYear() === new Date().getFullYear())
    .reduce((sum, p) => sum + (p.net_pay ?? 0), 0);

  const latestPayroll = payrolls[0];

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-48" />
        <Skeleton className="h-64" />
      </div>
    );
  }

  if (!employee) {
    return null;
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title={employee.name}
        description={`${employee.employee_id} • ${employee.position ?? "-"}`}
        children={
          <div className="flex items-center gap-2">
            <Button variant="outline" onClick={() => router.back()}>
              <ArrowLeft className="mr-2 size-4" />
              Back
            </Button>
            {employee.status !== "terminated" && (
              <>
                <Link
                  href={`/employees/${id}/edit`}
                  className={buttonVariants()}
                >
                  <Pencil className="mr-2 size-4" />
                  Edit
                </Link>
                <Button
                  variant="destructive"
                  onClick={() => setShowTerminate(true)}
                >
                  <UserX className="mr-2 size-4" />
                  Terminate
                </Button>
              </>
            )}
          </div>
        }
      />
      <div className="flex items-center gap-4">
        <Badge
          variant="outline"
          className={EMPLOYEE_STATUS_COLORS[employee.status] ?? "bg-muted"}
        >
          {employee.status}
        </Badge>
      </div>
      <div className="grid gap-4 sm:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle className="text-sm font-medium text-muted-foreground">
              Months Employed
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-bold">{monthsEmployed}</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-sm font-medium text-muted-foreground">
              YTD Earnings
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-bold">{formatCurrency(ytdEarnings)}</p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="text-sm font-medium text-muted-foreground">
              Latest Payroll
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-bold">
              {latestPayroll
                ? formatCurrency(latestPayroll.net_pay ?? 0)
                : "-"}
            </p>
          </CardContent>
        </Card>
      </div>
      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Personal</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <p className="text-sm text-muted-foreground">Email</p>
              <p className="font-medium">{employee.email ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Phone</p>
              <p className="font-medium">{employee.phone ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Address</p>
              <p className="font-medium whitespace-pre-wrap">
                {employee.address ?? "-"}
              </p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Employment</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <p className="text-sm text-muted-foreground">Position</p>
              <p className="font-medium">{employee.position ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Department</p>
              <p className="font-medium">{employee.department ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Join Date</p>
              <p className="font-medium">{formatDate(employee.join_date)}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Base Salary</p>
              <p className="font-medium">
                {formatCurrency(employee.base_salary ?? 0)}
              </p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Banking</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <p className="text-sm text-muted-foreground">Bank Name</p>
              <p className="font-medium">{employee.bank_name ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Account Number</p>
              <p className="font-medium">{employee.bank_account ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Account Holder</p>
              <p className="font-medium">{employee.bank_holder ?? "-"}</p>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Tax</CardTitle>
          </CardHeader>
          <CardContent>
            <div>
              <p className="text-sm text-muted-foreground">Tax ID (NPWP)</p>
              <p className="font-medium">{employee.tax_id ?? "-"}</p>
            </div>
          </CardContent>
        </Card>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Recent Payroll Records</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Payroll No</TableHead>
                <TableHead>Period</TableHead>
                <TableHead>Gross Pay</TableHead>
                <TableHead>Net Pay</TableHead>
                <TableHead>Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {payrolls.slice(0, 10).map((p) => (
                <TableRow key={p.id}>
                  <TableCell>
                    <Link
                      href={`/payroll/${p.id}`}
                      className="font-medium text-primary hover:underline"
                    >
                      {p.payroll_no}
                    </Link>
                  </TableCell>
                  <TableCell>
                    {p.period_month}/{p.period_year}
                  </TableCell>
                  <TableCell>{formatCurrency(p.gross_pay ?? 0)}</TableCell>
                  <TableCell>{formatCurrency(p.net_pay ?? 0)}</TableCell>
                  <TableCell>
                    <Badge variant="outline">{p.status}</Badge>
                  </TableCell>
                </TableRow>
              ))}
              {payrolls.length === 0 && (
                <TableRow>
                  <TableCell colSpan={5} className="text-center text-muted-foreground">
                    No payroll records
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
      <AlertDialog
        open={showTerminate}
        onOpenChange={setShowTerminate}
      >
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Terminate Employee</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to terminate {employee.name}? This action
              will update the employee status to terminated.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
              onClick={() => terminateMutation.mutate()}
            >
              Terminate
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
