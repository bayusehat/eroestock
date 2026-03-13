"use client";

import { useParams, useRouter } from "next/navigation";
import Link from "next/link";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { Pencil, Check, DollarSign, ArrowLeft, FileDown } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { PayrollRecord } from "@/types";
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
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency, formatDate } from "@/lib/format";
import { downloadPdf } from "@/lib/download";
import { toast } from "sonner";

const PAYROLL_STATUS_COLORS: Record<string, string> = {
  draft: "bg-muted text-muted-foreground",
  approved: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
  paid: "bg-green-500/10 text-green-600 dark:text-green-400",
};

async function fetchPayroll(id: string): Promise<PayrollRecord> {
  const res = await apiClient.get<{ data: PayrollRecord }>(`/payroll/${id}`);
  const body = res.data as { data: PayrollRecord };
  return body.data ?? (body as unknown as PayrollRecord);
}

export default function PayrollDetailPage() {
  const params = useParams();
  const router = useRouter();
  const queryClient = useQueryClient();
  const id = params.id as string;

  const { data: payroll, isLoading } = useQuery({
    queryKey: ["payroll", id],
    queryFn: () => fetchPayroll(id),
    enabled: !!id,
  });

  const approveMutation = useMutation({
    mutationFn: () => apiClient.put(`/payroll/${id}`, { status: "approved" }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["payroll", id] });
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
    mutationFn: () => apiClient.put(`/payroll/${id}`, { status: "paid" }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["payroll", id] });
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

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-48" />
        <Skeleton className="h-64" />
      </div>
    );
  }

  if (!payroll) {
    return null;
  }

  const canEdit = payroll.status === "draft";
  const canApprove = payroll.status === "draft";
  const canMarkPaid = payroll.status === "approved";

  const allowances = payroll.allowances
    ? Object.entries(payroll.allowances)
    : [];
  const deductions = payroll.deductions
    ? Object.entries(payroll.deductions)
    : [];

  return (
    <div className="space-y-6">
      <PageHeader
        title={payroll.payroll_no}
        description={`${payroll.employee?.name ?? "-"} • ${payroll.period_month}/${payroll.period_year}`}
        children={
          <div className="flex items-center gap-2">
            <Button variant="outline" onClick={() => router.back()}>
              <ArrowLeft className="mr-2 size-4" />
              Back
            </Button>
            {canEdit && (
              <Link
                href={`/payroll/${id}/edit`}
                className={buttonVariants()}
              >
                <Pencil className="mr-2 size-4" />
                Edit
              </Link>
            )}
            {canApprove && (
              <Button
                onClick={() => approveMutation.mutate()}
                disabled={approveMutation.isPending}
              >
                <Check className="mr-2 size-4" />
                Approve
              </Button>
            )}
            {canMarkPaid && (
              <Button
                onClick={() => markPaidMutation.mutate()}
                disabled={markPaidMutation.isPending}
              >
                <DollarSign className="mr-2 size-4" />
                Mark as Paid
              </Button>
            )}
            <Button
              variant="outline"
              onClick={async () => {
                try {
                  await downloadPdf(`/exports/payroll/${id}/pdf`, `payslip-${payroll.payroll_no}.pdf`);
                  toast.success("Payslip downloaded");
                } catch {
                  toast.error("Failed to download payslip");
                }
              }}
            >
              <FileDown className="mr-2 size-4" />
              Download Payslip
            </Button>
          </div>
        }
      />
      <div className="flex items-center gap-4">
        <Badge
          variant="outline"
          className={PAYROLL_STATUS_COLORS[payroll.status] ?? "bg-muted"}
        >
          {payroll.status}
        </Badge>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Earnings</CardTitle>
        </CardHeader>
        <CardContent className="space-y-2">
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">Base Salary</span>
            <span>{formatCurrency(payroll.base_salary ?? 0)}</span>
          </div>
          {(payroll.overtime_amount ?? 0) > 0 && (
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Overtime</span>
              <span>{formatCurrency(payroll.overtime_amount ?? 0)}</span>
            </div>
          )}
          {allowances.map(([name, amount]) => (
            <div key={name} className="flex justify-between text-sm">
              <span className="text-muted-foreground">{name}</span>
              <span>{formatCurrency(amount)}</span>
            </div>
          ))}
        </CardContent>
      </Card>
      <Card>
        <CardHeader>
          <CardTitle>Deductions</CardTitle>
        </CardHeader>
        <CardContent className="space-y-2">
          {deductions.map(([name, amount]) => (
            <div key={name} className="flex justify-between text-sm">
              <span className="text-muted-foreground">{name}</span>
              <span>{formatCurrency(amount)}</span>
            </div>
          ))}
          {(payroll.tax_amount ?? 0) > 0 && (
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Tax</span>
              <span>{formatCurrency(payroll.tax_amount ?? 0)}</span>
            </div>
          )}
        </CardContent>
      </Card>
      <Card>
        <CardHeader>
          <CardTitle>Summary</CardTitle>
        </CardHeader>
        <CardContent className="space-y-2">
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">Gross Pay</span>
            <span>{formatCurrency(payroll.gross_pay ?? 0)}</span>
          </div>
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">Total Deductions</span>
            <span>
              {formatCurrency(
                (payroll.total_deductions ?? 0) + (payroll.tax_amount ?? 0)
              )}
            </span>
          </div>
          <div className="flex justify-between font-semibold pt-2 border-t">
            <span>Net Pay</span>
            <span>{formatCurrency(payroll.net_pay ?? 0)}</span>
          </div>
        </CardContent>
      </Card>
      {payroll.status === "paid" && (payroll.paid_date || payroll.payment_method) && (
        <Card>
          <CardHeader>
            <CardTitle>Payment Info</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            {payroll.paid_date && (
              <div>
                <p className="text-sm text-muted-foreground">Paid Date</p>
                <p className="font-medium">{formatDate(payroll.paid_date)}</p>
              </div>
            )}
            {payroll.payment_method && (
              <div>
                <p className="text-sm text-muted-foreground">Payment Method</p>
                <p className="font-medium">{payroll.payment_method}</p>
              </div>
            )}
          </CardContent>
        </Card>
      )}
    </div>
  );
}
