"use client";

import { useState, useMemo } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { Doughnut } from "react-chartjs-2";
import type { ChartOptions } from "chart.js";
import { ArrowLeft } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { WorkOrderSummaryReport } from "@/types";
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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency } from "@/lib/format";
import { Briefcase, DollarSign, BarChart3 } from "lucide-react";
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

async function fetchWorkOrderSummary(
  dateFrom: string,
  dateTo: string,
  clientId?: string
): Promise<WorkOrderSummaryReport> {
  const params: Record<string, string> = { date_from: dateFrom, date_to: dateTo };
  if (clientId) params.client_id = clientId;
  const res = await apiClient.get<{ data: WorkOrderSummaryReport }>("/reports/work-order-summary", {
    params,
  });
  const body = res.data as { data: WorkOrderSummaryReport };
  return body.data ?? (body as unknown as WorkOrderSummaryReport);
}

async function fetchClients(): Promise<{ id: number; name: string }[]> {
  const res = await apiClient.get<{ data: { id: number; name: string }[] }>("/clients");
  const body = res.data as { data: { id: number; name: string }[] };
  return body.data ?? [];
}

export default function WorkOrderSummaryPage() {
  const [range, setRange] = useState(getDefaultDateRange());
  const [clientId, setClientId] = useState<string>("all");
  const colors = useMemo(() => getChartColors(5), []);

  const { data: clients = [] } = useQuery({
    queryKey: ["clients"],
    queryFn: fetchClients,
  });

  const { data, isLoading } = useQuery({
    queryKey: ["reports", "work-order-summary", range.from, range.to, clientId],
    queryFn: () =>
      fetchWorkOrderSummary(
        range.from,
        range.to,
        clientId === "all" ? undefined : clientId
      ),
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
    total_count: 0,
    total_value: 0,
    average_value: 0,
    by_status: [],
  };
  const totalCount = report.total_count ?? report.total_work_orders ?? 0;

  const statusData = report.by_status ?? [];

  const doughnutData = {
    labels: statusData.map((s) => s.status),
    datasets: [
      {
        data: statusData.map((s) => s.total_value),
        backgroundColor: statusData.map((_, i) => colors[i % colors.length]),
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
        title={t.reports.workOrderSummary.title}
        description={t.reports.workOrderSummary.description}
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
        <div className="space-y-2 min-w-[180px]">
          <label className="block text-sm font-medium text-muted-foreground">
            {t.reports.workOrderSummary.client}
          </label>
          <Select value={clientId} onValueChange={(v) => setClientId(v ?? "all")}>
            <SelectTrigger className="w-full">
              <SelectValue placeholder={t.reports.workOrderSummary.allClients}>
                {clientId && clientId !== "all"
                  ? clients.find((c) => String(c.id) === clientId)?.name ?? null
                  : null}
              </SelectValue>
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">{t.reports.workOrderSummary.allClients}</SelectItem>
              {clients.map((c) => (
                <SelectItem key={c.id} value={String(c.id)}>
                  {c.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      </div>
      <div className="grid gap-4 sm:grid-cols-3">
        <StatCard
          title={t.reports.workOrderSummary.totalWorkOrders}
          value={totalCount}
          icon={Briefcase}
        />
        <StatCard
          title={t.reports.workOrderSummary.totalValue}
          value={formatCurrency(report.total_value)}
          icon={DollarSign}
        />
        <StatCard
          title={t.reports.workOrderSummary.averageValue}
          value={formatCurrency(report.average_value)}
          icon={BarChart3}
        />
      </div>
      <div className="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>{t.reports.workOrderSummary.statusBreakdown}</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                    <TableHead>{t.common.status}</TableHead>
                    <TableHead className="text-right">{t.common.count}</TableHead>
                    <TableHead className="text-right">{t.reports.workOrderSummary.totalValue}</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {statusData.map((s) => (
                  <TableRow key={s.status}>
                    <TableCell>{s.status}</TableCell>
                    <TableCell className="text-right">{s.count}</TableCell>
                    <TableCell className="text-right">
                      {formatCurrency(s.total_value)}
                    </TableCell>
                  </TableRow>
                ))}
                {statusData.length === 0 && (
                  <TableRow>
                    <TableCell colSpan={3} className="text-center text-muted-foreground">
                      {t.reports.workOrderSummary.noData}
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>{t.reports.workOrderSummary.byStatus}</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-64">
              {statusData.length > 0 ? (
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
