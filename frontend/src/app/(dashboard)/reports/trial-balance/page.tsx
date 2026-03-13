"use client";

import { useState } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { ArrowLeft, Check, X } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { TrialBalanceReport } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Button, buttonVariants } from "@/components/ui/button";
import { DatePicker } from "@/components/ui/date-picker";
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency } from "@/lib/format";

function getDefaultDateRange() {
  const now = new Date();
  const start = new Date(now.getFullYear(), now.getMonth(), 1);
  const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
  return {
    from: start.toISOString().split("T")[0] ?? "",
    to: end.toISOString().split("T")[0] ?? "",
  };
}

async function fetchTrialBalance(dateFrom: string, dateTo: string): Promise<TrialBalanceReport> {
  const res = await apiClient.get<{ data: TrialBalanceReport }>("/reports/trial-balance", {
    params: { date_from: dateFrom, date_to: dateTo },
  });
  const body = res.data as { data: TrialBalanceReport };
  return body.data ?? (body as unknown as TrialBalanceReport);
}

export default function TrialBalancePage() {
  const [range, setRange] = useState(getDefaultDateRange());

  const { data, isLoading } = useQuery({
    queryKey: ["reports", "trial-balance", range.from, range.to],
    queryFn: () => fetchTrialBalance(range.from, range.to),
    enabled: !!range.from && !!range.to,
  });

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-10 w-80" />
        <Skeleton className="h-96" />
      </div>
    );
  }

  const report = data ?? {
    accounts: [],
    total_debits: 0,
    total_credits: 0,
  };

  const balanced = Math.abs(report.total_debits - report.total_credits) < 0.01;

  return (
    <div className="space-y-6">
      <PageHeader
        title="Trial Balance"
        description="Debit and credit balances by account"
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
        <div
          className={`flex items-center gap-2 rounded-lg border px-3 py-2 ${
            balanced ? "border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-950/30" : "border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-950/30"
          }`}
        >
          {balanced ? (
            <>
              <Check className="size-4 text-green-600" />
              <span className="text-sm font-medium text-green-600">Balanced</span>
            </>
          ) : (
            <>
              <X className="size-4 text-red-600" />
              <span className="text-sm font-medium text-red-600">Not balanced</span>
            </>
          )}
        </div>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Trial Balance</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Account Code</TableHead>
                <TableHead>Account Name</TableHead>
                <TableHead className="text-right">Debit Balance</TableHead>
                <TableHead className="text-right">Credit Balance</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {(report.accounts ?? []).map((a) => (
                <TableRow key={a.account_code}>
                  <TableCell>{a.account_code}</TableCell>
                  <TableCell>{a.account_name}</TableCell>
                  <TableCell className="text-right">
                    {a.debit > 0 ? formatCurrency(a.debit) : "-"}
                  </TableCell>
                  <TableCell className="text-right">
                    {a.credit > 0 ? formatCurrency(a.credit) : "-"}
                  </TableCell>
                </TableRow>
              ))}
              {(!report.accounts || report.accounts.length === 0) && (
                <TableRow>
                  <TableCell colSpan={4} className="text-center text-muted-foreground">
                    No data for this period
                  </TableCell>
                </TableRow>
              )}
              {(report.accounts?.length ?? 0) > 0 && (
                <TableRow className="font-semibold">
                  <TableCell colSpan={2}>Totals</TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(report.total_debits)}
                  </TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(report.total_credits)}
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
