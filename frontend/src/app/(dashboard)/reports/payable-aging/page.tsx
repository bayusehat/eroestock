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
  Tooltip,
  Legend,
  Cell,
} from "recharts";
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

const CHART_COLORS = [
  "hsl(var(--chart-1))",
  "hsl(var(--chart-2))",
  "hsl(var(--chart-3))",
  "hsl(var(--chart-4))",
  "hsl(var(--chart-5))",
];

async function fetchPayableAging(): Promise<AgingReport> {
  const res = await apiClient.get<{ data: AgingReport }>("/reports/payable-aging");
  const body = res.data as { data: AgingReport };
  return body.data ?? (body as unknown as AgingReport);
}

export default function PayableAgingPage() {
  const { data, isLoading } = useQuery({
    queryKey: ["reports", "payable-aging"],
    queryFn: fetchPayableAging,
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

  const chartData = [
    { name: "Current (0-30)", value: report.totals?.current ?? 0, fill: CHART_COLORS[0] },
    { name: "31-60 days", value: report.totals?.days_31_60 ?? 0, fill: CHART_COLORS[1] },
    { name: "61-90 days", value: report.totals?.days_61_90 ?? 0, fill: CHART_COLORS[2] },
    { name: "90+ days", value: report.totals?.over_90 ?? 0, fill: CHART_COLORS[3] },
  ].filter((d) => d.value > 0);

  return (
    <div className="space-y-6">
      <PageHeader
        title="Accounts Payable Aging"
        description="Outstanding payables by vendor and aging bucket"
        children={
          <Link href="/reports" className={buttonVariants({ variant: "outline" })}>
            <ArrowLeft className="mr-2 size-4" />
            Back
          </Link>
        }
      />
      <Card>
        <CardHeader>
          <CardTitle>Aging by Vendor</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Vendor Name</TableHead>
                <TableHead className="text-right">Current (0-30)</TableHead>
                <TableHead className="text-right">31-60 days</TableHead>
                <TableHead className="text-right">61-90 days</TableHead>
                <TableHead className="text-right">90+ days</TableHead>
                <TableHead className="text-right">Total</TableHead>
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
                    No payables data
                  </TableCell>
                </TableRow>
              )}
              {(report.rows?.length ?? 0) > 0 && report.totals && (
                <TableRow className="font-semibold">
                  <TableCell>Totals</TableCell>
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
      {chartData.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Aging Distribution</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-64">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={chartData} layout="vertical" margin={{ left: 80 }}>
                  <CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
                  <XAxis type="number" tickFormatter={(v) => `${v / 1000}k`} />
                  <YAxis type="category" dataKey="name" width={120} />
                  <Tooltip
                    formatter={(v) => formatCurrency(Number(v ?? 0))}
                    contentStyle={{ backgroundColor: "hsl(var(--card))", border: "1px solid hsl(var(--border))" }}
                  />
                  <Legend />
                  <Bar dataKey="value" name="Amount" radius={[0, 4, 4, 0]}>
                    {chartData.map((_, i) => (
                      <Cell key={i} fill={chartData[i].fill} />
                    ))}
                  </Bar>
                </BarChart>
              </ResponsiveContainer>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
