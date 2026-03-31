"use client";

import { useState, useMemo } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { Doughnut } from "react-chartjs-2";
import type { ChartOptions } from "chart.js";
import { ArrowLeft } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { IncomeByClientReport } from "@/types";
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
import { buttonVariants } from "@/components/ui/button";
import { DatePicker } from "@/components/ui/date-picker";
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency } from "@/lib/format";
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

async function fetchIncomeByClient(
  dateFrom: string,
  dateTo: string
): Promise<IncomeByClientReport> {
  const res = await apiClient.get<{ data: IncomeByClientReport }>("/reports/income-by-client", {
    params: { date_from: dateFrom, date_to: dateTo },
  });
  const body = res.data as { data: IncomeByClientReport };
  return body.data ?? (body as unknown as IncomeByClientReport);
}

export default function IncomeByClientPage() {
  const [range, setRange] = useState(getDefaultDateRange());
  const colors = useMemo(() => getChartColors(5), []);

  const { data, isLoading } = useQuery({
    queryKey: ["reports", "income-by-client", range.from, range.to],
    queryFn: () => fetchIncomeByClient(range.from, range.to),
    enabled: !!range.from && !!range.to,
  });

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-10 w-80" />
        <div className="grid gap-4 md:grid-cols-2">
          <Skeleton className="h-80" />
          <Skeleton className="h-80" />
        </div>
      </div>
    );
  }

  const report = data ?? { rows: [] };
  const sortedRows = [...(report.rows ?? [])].sort((a, b) => b.amount - a.amount);

  const doughnutData = {
    labels: sortedRows.map((r) => r.client_name),
    datasets: [
      {
        data: sortedRows.map((r) => r.amount),
        backgroundColor: sortedRows.map((_, i) => colors[i % colors.length]),
        borderWidth: 2,
      },
    ],
  };

  const doughnutOptions: ChartOptions<"doughnut"> = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: "bottom" },
      tooltip: {
        callbacks: {
          label: (ctx) => {
            const total = ctx.dataset.data.reduce((a: number, b: number) => a + b, 0);
            const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : "0";
            return `${ctx.label}: ${formatCurrency(ctx.parsed)} (${pct}%)`;
          },
        },
      },
    },
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title={t.reports.incomeByClient.title}
        description={t.reports.incomeByClient.description}
        children={
          <Link href="/reports" className={buttonVariants({ variant: "outline" })}>
            <ArrowLeft className="mr-2 size-4" />
            {t.common.back}
          </Link>
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
      <div className="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>{t.reports.incomeByClient.byClient}</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Client</TableHead>
                  <TableHead className="text-right">Amount</TableHead>
                  <TableHead className="text-right">%</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {sortedRows.map((r) => (
                  <TableRow key={r.client_name}>
                    <TableCell>{r.client_name}</TableCell>
                    <TableCell className="text-right">
                      {formatCurrency(r.amount)}
                    </TableCell>
                    <TableCell className="text-right">
                      {r.percentage?.toFixed(1) ?? "-"}%
                    </TableCell>
                  </TableRow>
                ))}
                {sortedRows.length === 0 && (
                  <TableRow>
                    <TableCell colSpan={3} className="text-center text-muted-foreground">
                      {t.reports.incomeByClient.noData}
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>{t.reports.incomeByClient.distribution}</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-64">
              {sortedRows.length > 0 ? (
                <Doughnut data={doughnutData} options={doughnutOptions} />
              ) : (
                <div className="flex h-full items-center justify-center text-muted-foreground">
                  {t.reports.profitLoss.noChartData}
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
