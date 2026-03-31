"use client";

import { useMemo } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { Bar } from "react-chartjs-2";
import type { ChartOptions } from "chart.js";
import { ArrowLeft } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { AgingReport } from "@/types";
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
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency } from "@/lib/format";
import { t } from "@/lib/translations";
import { getChartColors } from "@/lib/chartjs";

async function fetchReceivableAging(): Promise<AgingReport> {
  const res = await apiClient.get<{ data: AgingReport }>("/reports/receivable-aging");
  const body = res.data as { data: AgingReport };
  return body.data ?? (body as unknown as AgingReport);
}

export default function ReceivableAgingPage() {
  const colors = useMemo(() => getChartColors(4), []);

  const { data, isLoading } = useQuery({
    queryKey: ["reports", "receivable-aging"],
    queryFn: fetchReceivableAging,
  });

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-96" />
      </div>
    );
  }

  const report = data ?? {
    rows: [],
    totals: { current: 0, days_31_60: 0, days_61_90: 0, over_90: 0, total: 0 },
  };

  const chartLabels = ["Current (0-30)", "31-60 days", "61-90 days", "90+ days"];
  const chartValues = [
    report.totals?.current ?? 0,
    report.totals?.days_31_60 ?? 0,
    report.totals?.days_61_90 ?? 0,
    report.totals?.over_90 ?? 0,
  ];
  const hasChartData = chartValues.some((v) => v > 0);

  const barChartData = {
    labels: chartLabels,
    datasets: [
      {
        label: "Amount",
        data: chartValues,
        backgroundColor: colors,
        borderRadius: 4,
      },
    ],
  };

  const barOptions: ChartOptions<"bar"> = {
    indexAxis: "y",
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (ctx) => formatCurrency(ctx.parsed.x ?? 0),
        },
      },
    },
    scales: {
      x: { ticks: { callback: (v) => `${Number(v) / 1000}k` } },
      y: { grid: { display: false } },
    },
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title={t.reports.receivableAging.title}
        description={t.reports.receivableAging.description}
        children={
          <Link href="/reports" className={buttonVariants({ variant: "outline" })}>
            <ArrowLeft className="mr-2 size-4" />
            {t.common.back}
          </Link>
        }
      />
      <Card>
        <CardHeader>
          <CardTitle>{t.reports.receivableAging.byClient}</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Client Name</TableHead>
                <TableHead className="text-right">{t.reports.receivableAging.current}</TableHead>
                <TableHead className="text-right">{t.reports.receivableAging.days31_60}</TableHead>
                <TableHead className="text-right">{t.reports.receivableAging.days61_90}</TableHead>
                <TableHead className="text-right">{t.reports.receivableAging.over90}</TableHead>
                <TableHead className="text-right">{t.common.total}</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {(report.rows ?? []).map((r) => (
                <TableRow key={r.name}>
                  <TableCell>{r.name}</TableCell>
                  <TableCell className="text-right">{formatCurrency(r.current)}</TableCell>
                  <TableCell className="text-right">{formatCurrency(r.days_31_60)}</TableCell>
                  <TableCell className="text-right">{formatCurrency(r.days_61_90)}</TableCell>
                  <TableCell className="text-right">{formatCurrency(r.over_90)}</TableCell>
                  <TableCell className="text-right font-medium">{formatCurrency(r.total)}</TableCell>
                </TableRow>
              ))}
              {(!report.rows || report.rows.length === 0) && (
                <TableRow>
                  <TableCell colSpan={6} className="text-center text-muted-foreground">
                    {t.reports.receivableAging.noData}
                  </TableCell>
                </TableRow>
              )}
              {(report.rows?.length ?? 0) > 0 && report.totals && (
                <TableRow className="font-semibold">
                  <TableCell>{t.table.totals}</TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(report.totals.current)}
                  </TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(report.totals.days_31_60)}
                  </TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(report.totals.days_61_90)}
                  </TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(report.totals.over_90)}
                  </TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(report.totals.total)}
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
      {hasChartData && (
        <Card>
          <CardHeader>
            <CardTitle>Distribusi Aging</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-64">
              <Bar data={barChartData} options={barOptions} />
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
