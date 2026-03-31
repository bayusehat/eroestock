"use client";

import { useState, useMemo } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { Bar } from "react-chartjs-2";
import type { ChartOptions } from "chart.js";
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
import { t } from "@/lib/translations";
import { getChartColors } from "@/lib/chartjs";

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
  const colors = useMemo(() => getChartColors(2), []);

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
      toast.success(t.toast.pdfDownloaded);
    } catch {
      toast.error(t.toast.pdfFailed);
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

  const chartEntries =
    report.chart_data && report.chart_data.length > 0
      ? report.chart_data
      : [
          { month: t.reports.profitLoss.revenueLabel, revenue: report.total_revenue, expenses: 0 },
          { month: t.reports.profitLoss.expensesLabel, revenue: 0, expenses: report.total_expenses },
        ];

  const barChartData = {
    labels: chartEntries.map((d) => d.month),
    datasets: [
      {
        label: t.reports.profitLoss.revenueLabel,
        data: chartEntries.map((d) => d.revenue),
        backgroundColor: colors[0],
      },
      {
        label: t.reports.profitLoss.expensesLabel,
        data: chartEntries.map((d) => d.expenses),
        backgroundColor: colors[1],
      },
    ],
  };

  const barOptions: ChartOptions<"bar"> = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: "bottom" },
      tooltip: {
        callbacks: {
          label: (ctx) => `${ctx.dataset.label}: ${formatCurrency(ctx.parsed.y ?? 0)}`,
        },
      },
    },
    scales: {
      x: { grid: { display: false } },
      y: { ticks: { callback: (v) => `${Number(v) / 1000}k` } },
    },
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title={t.reports.profitLoss.title}
        description={t.reports.profitLoss.description}
        children={
          <div className="flex items-center gap-2">
            <Link href="/reports" className={buttonVariants({ variant: "outline" })}>
              <ArrowLeft className="mr-2 size-4" />
              {t.common.back}
            </Link>
            <Button onClick={handleExportPdf}>
              <FileDown className="mr-2 size-4" />
              {t.reports.profitLoss.exportPdf}
            </Button>
          </div>
        }
      />
      <div className="flex flex-wrap items-start gap-4">
        <div className="space-y-2 min-w-[160px]">
          <label className="block text-sm font-medium text-muted-foreground">
            {t.common.from}
          </label>
          <DatePicker
            value={range.from}
            onChange={(v) => setRange((r) => ({ ...r, from: v }))}
            placeholder={t.placeholders.fromDate}
            className="w-full"
          />
        </div>
        <div className="space-y-2 min-w-[160px]">
          <label className="block text-sm font-medium text-muted-foreground">
            {t.common.to}
          </label>
          <DatePicker
            value={range.to}
            onChange={(v) => setRange((r) => ({ ...r, to: v }))}
            placeholder={t.placeholders.toDate}
            className="w-full"
          />
        </div>
      </div>
      <div className="grid gap-4 sm:grid-cols-3">
        <StatCard
          title={t.reports.profitLoss.totalRevenue}
          value={formatCurrency(report.total_revenue)}
          icon={TrendingUp}
        />
        <StatCard
          title={t.reports.profitLoss.totalExpenses}
          value={formatCurrency(report.total_expenses)}
          icon={TrendingDown}
        />
        <StatCard
          title={t.reports.profitLoss.netProfit}
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
            <CardTitle>{t.reports.profitLoss.revenue}</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                    <TableHead>{t.reports.generalLedger.account}</TableHead>
                    <TableHead className="text-right">{t.common.amount}</TableHead>
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
                      {t.reports.profitLoss.noRevenueData}
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>{t.reports.profitLoss.expenses}</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                    <TableHead>{t.reports.generalLedger.account}</TableHead>
                    <TableHead className="text-right">{t.common.amount}</TableHead>
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
                      {t.reports.profitLoss.noExpenseData}
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
            <CardTitle>{t.reports.profitLoss.revenueVsExpenses}</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="h-64">
            {(report.revenue?.length ?? 0) > 0 || (report.expenses?.length ?? 0) > 0 ? (
              <Bar data={barChartData} options={barOptions} />
            ) : (
              <div className="flex h-full items-center justify-center text-muted-foreground">
                {t.reports.profitLoss.noChartData}
              </div>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
