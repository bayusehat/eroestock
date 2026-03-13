"use client";

import { useState } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import {
  Pie,
  PieChart,
  Cell,
  ResponsiveContainer,
  Legend,
  Tooltip,
} from "recharts";
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
import { Button, buttonVariants } from "@/components/ui/button";
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

const CHART_COLORS = [
  "hsl(var(--chart-1))",
  "hsl(var(--chart-2))",
  "hsl(var(--chart-3))",
  "hsl(var(--chart-4))",
  "hsl(var(--chart-5))",
];

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

  const chartData = (report.by_status ?? []).map((s, i) => ({
    name: s.status,
    value: s.total_value,
    fill: CHART_COLORS[i % CHART_COLORS.length],
  }));

  return (
    <div className="space-y-6">
      <PageHeader
        title="Work Order Summary"
        description="Work orders by status and value"
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
        <div className="space-y-1">
          <label className="text-sm text-muted-foreground">Client</label>
          <Select value={clientId} onValueChange={(v) => setClientId(v ?? "all")}>
            <SelectTrigger className="w-[200px]">
              <SelectValue placeholder="All clients" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All clients</SelectItem>
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
          title="Total Work Orders"
          value={report.total_count}
          icon={Briefcase}
        />
        <StatCard
          title="Total Value"
          value={formatCurrency(report.total_value)}
          icon={DollarSign}
        />
        <StatCard
          title="Average Value"
          value={formatCurrency(report.average_value)}
          icon={BarChart3}
        />
      </div>
      <div className="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Status Breakdown</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Status</TableHead>
                  <TableHead className="text-right">Count</TableHead>
                  <TableHead className="text-right">Total Value</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {(report.by_status ?? []).map((s) => (
                  <TableRow key={s.status}>
                    <TableCell>{s.status}</TableCell>
                    <TableCell className="text-right">{s.count}</TableCell>
                    <TableCell className="text-right">
                      {formatCurrency(s.total_value)}
                    </TableCell>
                  </TableRow>
                ))}
                {(!report.by_status || report.by_status.length === 0) && (
                  <TableRow>
                    <TableCell colSpan={3} className="text-center text-muted-foreground">
                      No work order data for this period
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>By Status</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-64">
              {chartData.length > 0 ? (
                <ResponsiveContainer width="100%" height="100%">
                  <PieChart>
                    <Pie
                      data={chartData}
                      dataKey="value"
                      nameKey="name"
                      cx="50%"
                      cy="50%"
                      innerRadius={60}
                      outerRadius={80}
                      paddingAngle={2}
                      label={({ name, percent }) =>
                        `${name} ${((percent ?? 0) * 100).toFixed(0)}%`
                      }
                    >
                      {chartData.map((_, i) => (
                        <Cell key={i} fill={chartData[i].fill} />
                      ))}
                    </Pie>
                    <Tooltip
                      formatter={(v) => formatCurrency(Number(v ?? 0))}
                      contentStyle={{
                        backgroundColor: "hsl(var(--card))",
                        border: "1px solid hsl(var(--border))",
                      }}
                    />
                    <Legend />
                  </PieChart>
                </ResponsiveContainer>
              ) : (
                <div className="flex h-full items-center justify-center text-muted-foreground">
                  No data for chart
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
