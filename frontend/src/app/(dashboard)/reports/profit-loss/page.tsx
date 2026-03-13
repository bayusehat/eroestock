"use client";

import { useState } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import {
  Bar,
  BarChart,
  ResponsiveContainer,
  XAxis,
  YAxis,
  CartesianGrid,
  Legend,
  Tooltip,
} from "recharts";
import { ArrowLeft, FileDown } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { ProfitLossReport } from "@/types";
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
import { Button, buttonVariants } from "@/components/ui/button";
import { DatePicker } from "@/components/ui/date-picker";
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency } from "@/lib/format";
import { downloadPdf } from "@/lib/download";
import { toast } from "sonner";
import { TrendingUp, TrendingDown } from "lucide-react";

const CHART_COLORS = ["hsl(var(--chart-1))", "hsl(var(--chart-2))"];

function getDefaultDateRange() {
  const now = new Date();
  const start = new Date(now.getFullYear(), now.getMonth(), 1);
  const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
  return {
    from: start.toISOString().split("T")[0] ?? "",
    to: end.toISOString().split("T")[0] ?? "",
  };
}

async function fetchProfitLoss(dateFrom: string, dateTo: string): Promise<ProfitLossReport> {
  const res = await apiClient.get<{ data: ProfitLossReport }>("/reports/profit-loss", {
    params: { date_from: dateFrom, date_to: dateTo },
  });
  const body = res.data as { data: ProfitLossReport };
  return body.data ?? (body as unknown as ProfitLossReport);
}

export default function ProfitLossPage() {
  const [range, setRange] = useState(getDefaultDateRange());

  const { data, isLoading } = useQuery({
    queryKey: ["reports", "profit-loss", range.from, range.to],
    queryFn: () => fetchProfitLoss(range.from, range.to),
    enabled: !!range.from && !!range.to,
  });

  const handleExportPdf = async () => {
    try {
      await downloadPdf(
        `/exports/report/profit-loss/pdf?date_from=${range.from}&date_to=${range.to}`,
        `profit-loss-${range.from}-${range.to}.pdf`
      );
      toast.success("PDF downloaded");
    } catch {
      toast.error("Failed to download PDF");
    }
  };

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
    total_revenue: 0,
    total_expenses: 0,
    net_profit: 0,
    revenue: [],
    expenses: [],
    chart_data: [],
  };

  const chartData =
    report.chart_data && report.chart_data.length > 0
      ? report.chart_data
      : [
          { month: "Revenue", revenue: report.total_revenue, expenses: 0 },
          { month: "Expenses", revenue: 0, expenses: report.total_expenses },
        ];

  return (
    <div className="space-y-6">
      <PageHeader
        title="Profit & Loss"
        description="Revenue, expenses, and net profit"
        children={
          <div className="flex items-center gap-2">
            <Link href="/reports" className={buttonVariants({ variant: "outline" })}>
              <ArrowLeft className="mr-2 size-4" />
              Back
            </Link>
            <Button onClick={handleExportPdf}>
              <FileDown className="mr-2 size-4" />
              Export PDF
            </Button>
          </div>
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
          title="Total Revenue"
          value={formatCurrency(report.total_revenue)}
          icon={TrendingUp}
        />
        <StatCard
          title="Total Expenses"
          value={formatCurrency(report.total_expenses)}
          icon={TrendingDown}
        />
        <StatCard
          title="Net Profit"
          value={formatCurrency(report.net_profit)}
          icon={TrendingUp}
          className={
            report.net_profit >= 0
              ? "[&_[data-slot=card-content]]:text-green-600 dark:[&_[data-slot=card-content]]:text-green-400"
              : "[&_[data-slot=card-content]]:text-red-600 dark:[&_[data-slot=card-content]]:text-red-400"
          }
        />
      </div>
      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Revenue</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Account</TableHead>
                  <TableHead className="text-right">Amount</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {(report.revenue ?? []).map((r) => (
                  <TableRow key={r.account_code}>
                    <TableCell>
                      {r.account_code} - {r.account_name}
                    </TableCell>
                    <TableCell className="text-right">
                      {formatCurrency(r.amount)}
                    </TableCell>
                  </TableRow>
                ))}
                {(!report.revenue || report.revenue.length === 0) && (
                  <TableRow>
                    <TableCell colSpan={2} className="text-center text-muted-foreground">
                      No revenue data
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Expenses</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Account</TableHead>
                  <TableHead className="text-right">Amount</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {(report.expenses ?? []).map((r) => (
                  <TableRow key={r.account_code}>
                    <TableCell>
                      {r.account_code} - {r.account_name}
                    </TableCell>
                    <TableCell className="text-right">
                      {formatCurrency(r.amount)}
                    </TableCell>
                  </TableRow>
                ))}
                {(!report.expenses || report.expenses.length === 0) && (
                  <TableRow>
                    <TableCell colSpan={2} className="text-center text-muted-foreground">
                      No expense data
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Revenue vs Expenses</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="h-64">
            {(report.revenue?.length ?? 0) > 0 || (report.expenses?.length ?? 0) > 0 ? (
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={chartData}>
                  <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                  <XAxis dataKey="month" />
                  <YAxis tickFormatter={(v) => `${v / 1000}k`} />
                  <Tooltip
                    formatter={(v) => formatCurrency(Number(v ?? 0))}
                    contentStyle={{ backgroundColor: "hsl(var(--card))", border: "1px solid hsl(var(--border))" }}
                  />
                  <Legend />
                  <Bar dataKey="revenue" fill={CHART_COLORS[0]} name="Revenue" />
                  <Bar dataKey="expenses" fill={CHART_COLORS[1]} name="Expenses" />
                </BarChart>
              </ResponsiveContainer>
            ) : (
              <div className="flex h-full items-center justify-center text-muted-foreground">
                No chart data available
              </div>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
