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
import type { ExpenseByCategoryReport } from "@/types";
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

async function fetchExpenseByCategory(
  dateFrom: string,
  dateTo: string
): Promise<ExpenseByCategoryReport> {
  const res = await apiClient.get<{ data: ExpenseByCategoryReport }>("/reports/expense-by-category", {
    params: { date_from: dateFrom, date_to: dateTo },
  });
  const body = res.data as { data: ExpenseByCategoryReport };
  return body.data ?? (body as unknown as ExpenseByCategoryReport);
}

export default function ExpenseByCategoryPage() {
  const [range, setRange] = useState(getDefaultDateRange());

  const { data, isLoading } = useQuery({
    queryKey: ["reports", "expense-by-category", range.from, range.to],
    queryFn: () => fetchExpenseByCategory(range.from, range.to),
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
  const chartData = sortedRows.map((r, i) => ({
    name: r.category,
    value: r.amount,
    fill: CHART_COLORS[i % CHART_COLORS.length],
  }));

  return (
    <div className="space-y-6">
      <PageHeader
        title="Expense by Category"
        description="Expense breakdown by account/category"
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
      <div className="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Expense by Category</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Account / Category</TableHead>
                  <TableHead className="text-right">Amount</TableHead>
                  <TableHead className="text-right">%</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {sortedRows.map((r) => (
                  <TableRow key={r.category}>
                    <TableCell>{r.category}</TableCell>
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
                      No expense data for this period
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Distribution</CardTitle>
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
