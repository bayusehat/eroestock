"use client";

import { useState } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { ArrowLeft } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { PayrollSummaryReport } from "@/types";
import { PageHeader } from "@/components/page-header";
import { StatCard } from "@/components/stat-card";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { buttonVariants } from "@/components/ui/button";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency } from "@/lib/format";
import { Wallet, Minus, Percent, DollarSign } from "lucide-react";
import { t } from "@/lib/translations";

function getDefaultPeriod() {
  const now = new Date();
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`;
}

async function fetchPayrollSummary(
  period: string
): Promise<PayrollSummaryReport> {
  const [year, month] = period.split("-").map(Number);
  const res = await apiClient.get<{ data: PayrollSummaryReport }>("/reports/payroll-summary", {
    params: { month, year },
  });
  const body = res.data as { data: PayrollSummaryReport };
  return body.data ?? (body as unknown as PayrollSummaryReport);
}

function getPeriodOptions() {
  const options: string[] = [];
  const now = new Date();
  for (let i = 0; i < 12; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    options.push(
      `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}`
    );
  }
  return options;
}

export default function PayrollSummaryPage() {
  const [period, setPeriod] = useState(getDefaultPeriod());
  const periodOptions = getPeriodOptions();

  const { data, isLoading } = useQuery({
    queryKey: ["reports", "payroll-summary", period],
    queryFn: () => fetchPayrollSummary(period),
    enabled: !!period,
  });

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-10 w-48" />
        <div className="grid gap-4 sm:grid-cols-4">
          {[1, 2, 3, 4].map((i) => (
            <Skeleton key={i} className="h-32" />
          ))}
        </div>
        <Skeleton className="h-80" />
      </div>
    );
  }

  const report = data ?? {
    total_gross: 0,
    total_deductions: 0,
    total_tax: 0,
    total_net: 0,
    by_employee: [],
    by_department: [],
  };

  const periodLabel = period
    ? new Date(period + "-01").toLocaleDateString("id-ID", {
        month: "long",
        year: "numeric",
      })
    : "";

  return (
    <div className="space-y-6">
      <PageHeader
        title={t.reports.payrollSummary.title}
        description={t.reports.payrollSummary.description}
        children={
          <Link href="/reports" className={buttonVariants({ variant: "outline" })}>
            <ArrowLeft className="mr-2 size-4" />
            {t.common.back}
          </Link>
        }
      />
      <div className="flex flex-wrap items-start gap-4">
        <div className="space-y-2 min-w-[180px]">
          <label className="block text-sm font-medium text-muted-foreground">
            {t.reports.payrollSummary.period}
          </label>
          <Select value={period} onValueChange={(v) => setPeriod(v ?? "")}>
            <SelectTrigger className="w-full">
              <SelectValue placeholder={t.reports.payrollSummary.selectPeriod}>
                {period
                  ? new Date(period + "-01").toLocaleDateString("id-ID", {
                      month: "long",
                      year: "numeric",
                    })
                  : null}
              </SelectValue>
            </SelectTrigger>
            <SelectContent>
              {periodOptions.map((p) => (
                <SelectItem key={p} value={p}>
                  {new Date(p + "-01").toLocaleDateString("id-ID", {
                    month: "long",
                    year: "numeric",
                  })}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      </div>
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard
          title={t.reports.payrollSummary.totalGross}
          value={formatCurrency(report.total_gross)}
          icon={Wallet}
        />
        <StatCard
          title={t.reports.payrollSummary.totalDeductions}
          value={formatCurrency(report.total_deductions)}
          icon={Minus}
        />
        <StatCard
          title={t.reports.payrollSummary.totalTax}
          value={formatCurrency(report.total_tax)}
          icon={Percent}
        />
        <StatCard
          title={t.reports.payrollSummary.totalNet}
          value={formatCurrency(report.total_net)}
          icon={DollarSign}
        />
      </div>
      <Card>
        <CardHeader>
            <CardTitle>{t.reports.payrollSummary.byEmployee} ({periodLabel})</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>{t.reports.payrollSummary.employee}</TableHead>
                <TableHead className="text-right">{t.reports.payrollSummary.baseSalary}</TableHead>
                <TableHead className="text-right">{t.reports.payrollSummary.overtime}</TableHead>
                <TableHead className="text-right">{t.reports.payrollSummary.allowances}</TableHead>
                <TableHead className="text-right">{t.reports.payrollSummary.deductions}</TableHead>
                <TableHead className="text-right">{t.reports.payrollSummary.tax}</TableHead>
                <TableHead className="text-right">{t.reports.payrollSummary.netPay}</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {(report.by_employee ?? []).map((e) => (
                <TableRow key={e.employee_name}>
                  <TableCell>{e.employee_name}</TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(e.base_salary)}
                  </TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(e.overtime)}
                  </TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(e.allowances)}
                  </TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(e.deductions)}
                  </TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(e.tax)}
                  </TableCell>
                  <TableCell className="text-right font-medium">
                    {formatCurrency(e.net_pay)}
                  </TableCell>
                </TableRow>
              ))}
              {(!report.by_employee || report.by_employee.length === 0) && (
                <TableRow>
                  <TableCell colSpan={7} className="text-center text-muted-foreground">
                    {t.reports.payrollSummary.noData}
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
      {(report.by_department?.length ?? 0) > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>{t.reports.payrollSummary.byDepartment}</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                <TableHead>Department</TableHead>
                <TableHead className="text-right">{t.common.count}</TableHead>
                <TableHead className="text-right">{t.common.total}</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {(report.by_department ?? []).map((d) => (
                  <TableRow key={d.department}>
                    <TableCell>{d.department}</TableCell>
                    <TableCell className="text-right">{d.count}</TableCell>
                    <TableCell className="text-right">
                      {formatCurrency(d.total)}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
