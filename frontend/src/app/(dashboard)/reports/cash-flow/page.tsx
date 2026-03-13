"use client";

import { useState } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import {
  Line,
  LineChart,
  ResponsiveContainer,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
} from "recharts";
import { ArrowLeft, FileDown } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { CashFlowReport } from "@/types";
import { PageHeader } from "@/components/page-header";
import { StatCard } from "@/components/stat-card";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button, buttonVariants } from "@/components/ui/button";
import { DatePicker } from "@/components/ui/date-picker";
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency } from "@/lib/format";
import { downloadPdf } from "@/lib/download";
import { toast } from "sonner";
import { Wallet, ArrowUpDown, PiggyBank } from "lucide-react";

function getDefaultDateRange() {
  const now = new Date();
  const start = new Date(now.getFullYear(), now.getMonth(), 1);
  const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
  return {
    from: start.toISOString().split("T")[0] ?? "",
    to: end.toISOString().split("T")[0] ?? "",
  };
}

async function fetchCashFlow(dateFrom: string, dateTo: string): Promise<CashFlowReport> {
  const res = await apiClient.get<{ data: CashFlowReport }>("/reports/cash-flow", {
    params: { date_from: dateFrom, date_to: dateTo },
  });
  const body = res.data as { data: CashFlowReport };
  return body.data ?? (body as unknown as CashFlowReport);
}

export default function CashFlowPage() {
  const [range, setRange] = useState(getDefaultDateRange());

  const { data, isLoading } = useQuery({
    queryKey: ["reports", "cash-flow", range.from, range.to],
    queryFn: () => fetchCashFlow(range.from, range.to),
    enabled: !!range.from && !!range.to,
  });

  const handleExportPdf = async () => {
    try {
      await downloadPdf(
        `/exports/report/cash-flow/pdf?date_from=${range.from}&date_to=${range.to}`,
        `cash-flow-${range.from}-${range.to}.pdf`
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
    opening_balance: 0,
    closing_balance: 0,
    net_cash_flow: 0,
    operating: { inflows: 0, outflows: 0 },
    investing: { inflows: 0, outflows: 0 },
    financing: { inflows: 0, outflows: 0 },
    chart_data: [],
  };

  const chartData =
    report.chart_data && report.chart_data.length > 0
      ? report.chart_data
      : [
          { date: range.from, balance: report.opening_balance },
          { date: range.to, balance: report.closing_balance },
        ];

  return (
    <div className="space-y-6">
      <PageHeader
        title="Cash Flow Statement"
        description="Operating, investing, and financing activities"
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
          title="Opening Balance"
          value={formatCurrency(report.opening_balance)}
          icon={Wallet}
        />
        <StatCard
          title="Net Cash Flow"
          value={formatCurrency(report.net_cash_flow)}
          icon={ArrowUpDown}
        />
        <StatCard
          title="Closing Balance"
          value={formatCurrency(report.closing_balance)}
          icon={PiggyBank}
        />
      </div>
      <div className="grid gap-6 md:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle>Operating Activities</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Inflows</span>
              <span className="text-green-600">{formatCurrency(report.operating?.inflows ?? 0)}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Outflows</span>
              <span className="text-red-600">{formatCurrency(report.operating?.outflows ?? 0)}</span>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Investing Activities</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Inflows</span>
              <span className="text-green-600">{formatCurrency(report.investing?.inflows ?? 0)}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Outflows</span>
              <span className="text-red-600">{formatCurrency(report.investing?.outflows ?? 0)}</span>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Financing Activities</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Inflows</span>
              <span className="text-green-600">{formatCurrency(report.financing?.inflows ?? 0)}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Outflows</span>
              <span className="text-red-600">{formatCurrency(report.financing?.outflows ?? 0)}</span>
            </div>
          </CardContent>
        </Card>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Cash Balance Over Time</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="h-64">
            {chartData.length > 0 ? (
              <ResponsiveContainer width="100%" height="100%">
                <LineChart data={chartData}>
                  <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                  <XAxis dataKey="date" />
                  <YAxis tickFormatter={(v) => `${v / 1000}k`} />
                  <Tooltip
                    formatter={(v) => formatCurrency(Number(v ?? 0))}
                    contentStyle={{ backgroundColor: "hsl(var(--card))", border: "1px solid hsl(var(--border))" }}
                  />
                  <Line
                    type="monotone"
                    dataKey="balance"
                    stroke="hsl(var(--chart-1))"
                    strokeWidth={2}
                    name="Balance"
                  />
                </LineChart>
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
