"use client";

import { useState } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { ArrowLeft } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { TaxSummaryReport } from "@/types";
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
import { DatePicker } from "@/components/ui/date-picker";
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency } from "@/lib/format";
import { Receipt, FileDown, Shield } from "lucide-react";

function getDefaultDateRange() {
  const now = new Date();
  const start = new Date(now.getFullYear(), now.getMonth(), 1);
  const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
  return {
    from: start.toISOString().split("T")[0] ?? "",
    to: end.toISOString().split("T")[0] ?? "",
  };
}

async function fetchTaxSummary(
  dateFrom: string,
  dateTo: string
): Promise<TaxSummaryReport> {
  const res = await apiClient.get<{ data: TaxSummaryReport }>("/reports/tax-summary", {
    params: { date_from: dateFrom, date_to: dateTo },
  });
  const body = res.data as { data: TaxSummaryReport };
  return body.data ?? (body as unknown as TaxSummaryReport);
}

export default function TaxSummaryPage() {
  const [range, setRange] = useState(getDefaultDateRange());

  const { data, isLoading } = useQuery({
    queryKey: ["reports", "tax-summary", range.from, range.to],
    queryFn: () => fetchTaxSummary(range.from, range.to),
    enabled: !!range.from && !!range.to,
  });

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-10 w-80" />
        <div className="grid gap-4 sm:grid-cols-3">
          {[1, 2, 3].map((i) => (
            <Skeleton key={i} className="h-32" />
          ))}
        </div>
        <Skeleton className="h-80" />
      </div>
    );
  }

  const report = data ?? {
    rows: [],
    total_collected: 0,
    total_withheld: 0,
    net_liability: 0,
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Tax Summary"
        description="Tax collected, withheld, and liability"
        children={
          <Link href="/reports" className={buttonVariants({ variant: "outline" })}>
            <ArrowLeft className="mr-2 size-4" />
            Back
          </Link>
        }
      />
      <div className="flex flex-wrap items-end gap-4">
        <div className="space-y-1">
          <label className="text-sm text-muted-foreground">From</label>
          <DatePicker
            value={range.from}
            onChange={(v) => setRange((r) => ({ ...r, from: v }))}
            placeholder="From date"
            className="w-[160px]"
          />
        </div>
        <div className="space-y-1">
          <label className="text-sm text-muted-foreground">To</label>
          <DatePicker
            value={range.to}
            onChange={(v) => setRange((r) => ({ ...r, to: v }))}
            placeholder="To date"
            className="w-[160px]"
          />
        </div>
      </div>
      <div className="grid gap-4 sm:grid-cols-3">
        <StatCard
          title="Total Tax Collected (from invoices)"
          value={formatCurrency(report.total_collected)}
          icon={Receipt}
        />
        <StatCard
          title="Total Tax Withheld (from payroll)"
          value={formatCurrency(report.total_withheld)}
          icon={FileDown}
        />
        <StatCard
          title="Net Tax Liability"
          value={formatCurrency(report.net_liability)}
          icon={Shield}
        />
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Tax by Type</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Tax Type</TableHead>
                <TableHead>Tax Name</TableHead>
                <TableHead className="text-right">Amount</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {(report.rows ?? []).map((r) => (
                <TableRow key={`${r.tax_type}-${r.tax_name}`}>
                  <TableCell>{r.tax_type}</TableCell>
                  <TableCell>{r.tax_name}</TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(r.amount)}
                  </TableCell>
                </TableRow>
              ))}
              {(!report.rows || report.rows.length === 0) && (
                <TableRow>
                  <TableCell colSpan={3} className="text-center text-muted-foreground">
                    No tax data for this period
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
}
