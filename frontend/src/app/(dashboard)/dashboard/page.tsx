"use client";

import { useMemo } from "react";
import { useQuery } from "@tanstack/react-query";
import { Bar, Pie } from "react-chartjs-2";
import {
  TrendingUp,
  TrendingDown,
  Wallet,
  Receipt,
  CreditCard,
  ArrowLeftRight,
} from "lucide-react";
import { apiClient } from "@/lib/api";
import { formatCurrency } from "@/lib/format";
import type { DashboardData } from "@/types";
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
import { Skeleton } from "@/components/ui/skeleton";
import { useAuth } from "@/contexts/auth-context";
import { t } from "@/lib/translations";
import { getChartColors, commonBarOptions, commonPieOptions } from "@/lib/chartjs";

async function fetchDashboard(): Promise<DashboardData> {
  const res = await apiClient.get<{ data: DashboardData }>("/dashboard");
  const body = res.data as { data: DashboardData };
  return body.data ?? (body as unknown as DashboardData);
}

export default function DashboardPage() {
  const { user } = useAuth();
  const { data, isLoading } = useQuery({
    queryKey: ["dashboard"],
    queryFn: fetchDashboard,
  });

  const colors = useMemo(() => getChartColors(5), []);

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          {[1, 2, 3, 4].map((i) => (
            <Skeleton key={i} className="h-32" />
          ))}
        </div>
        <div className="grid gap-4 md:grid-cols-2">
          <Skeleton className="h-80" />
          <Skeleton className="h-80" />
        </div>
      </div>
    );
  }

  const revenueData = data
    ? Object.entries(data.work_order_pipeline ?? {}).map(([name, value]) => ({
        name,
        value,
      }))
    : [];

  const barEntries = data?.recent_transactions?.slice(0, 12).map((t, i) => ({
    month: `T${i + 1}`,
    revenue: t.amount > 0 ? t.amount : 0,
    expense: t.amount < 0 ? Math.abs(t.amount) : 0,
  })) ?? Array.from({ length: 12 }, (_, i) => ({
    month: `M${i + 1}`,
    revenue: 0,
    expense: 0,
  }));

  const barChartData = {
    labels: barEntries.map((d) => d.month),
    datasets: [
      {
        label: t.reports.profitLoss.revenueLabel,
        data: barEntries.map((d) => d.revenue),
        backgroundColor: colors[0],
      },
      {
        label: t.reports.profitLoss.expensesLabel,
        data: barEntries.map((d) => d.expense),
        backgroundColor: colors[1],
      },
    ],
  };

  const pieChartData = {
    labels: revenueData.map((d) => d.name),
    datasets: [
      {
        data: revenueData.map((d) => d.value),
        backgroundColor: revenueData.map((_, i) => colors[i % colors.length]),
      },
    ],
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title={`${t.dashboard.welcomeBack}, ${user?.name ?? "Pengguna"}`}
        description={t.dashboard.overview}
      />
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard
          title={t.dashboard.revenueMtd}
          value={formatCurrency(data?.revenue_mtd ?? 0)}
          icon={TrendingUp}
        />
        <StatCard
          title={t.dashboard.expensesMtd}
          value={formatCurrency(data?.expenses_mtd ?? 0)}
          icon={TrendingDown}
        />
        <StatCard
          title={t.dashboard.netProfitMtd}
          value={formatCurrency(data?.net_profit_mtd ?? 0)}
          icon={TrendingUp}
        />
        <StatCard
          title={t.dashboard.cashBalance}
          value={formatCurrency(data?.cash_balance ?? 0)}
          icon={Wallet}
        />
      </div>
      <div className="grid gap-4 sm:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Receipt className="size-4" />
              {t.dashboard.outstandingReceivables}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-bold">
              {formatCurrency(data?.outstanding_receivables ?? 0)}
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <CreditCard className="size-4" />
              {t.dashboard.outstandingPayables}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-2xl font-bold">
              {formatCurrency(data?.outstanding_payables ?? 0)}
            </p>
          </CardContent>
        </Card>
      </div>
      <div className="grid gap-4 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>{t.dashboard.revenueVsExpense}</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-64">
              <Bar data={barChartData} options={commonBarOptions} />
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>{t.dashboard.workOrderPipeline}</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="h-64">
              {revenueData.length > 0 ? (
                <Pie data={pieChartData} options={commonPieOptions} />
              ) : (
                <div className="flex h-full items-center justify-center text-muted-foreground">
                  {t.dashboard.noWorkOrderData}
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
      <Card>
        <CardHeader>
            <CardTitle className="flex items-center gap-2">
            <ArrowLeftRight className="size-4" />
            {t.dashboard.recentTransactions}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>{t.table.date}</TableHead>
                <TableHead>{t.common.description}</TableHead>
                <TableHead>{t.common.status}</TableHead>
                <TableHead className="text-right">{t.common.amount}</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {(data?.recent_transactions ?? []).slice(0, 10).map((t) => (
                <TableRow key={t.id}>
                  <TableCell>{new Date(t.date).toLocaleDateString("id-ID")}</TableCell>
                  <TableCell>{t.description ?? t.transaction_no}</TableCell>
                  <TableCell>{t.type}</TableCell>
                  <TableCell className="text-right">
                    <span className={t.amount >= 0 ? "text-green-600" : "text-red-600"}>
                      {formatCurrency(t.amount)}
                    </span>
                  </TableCell>
                </TableRow>
              ))}
              {(!data?.recent_transactions || data.recent_transactions.length === 0) && (
                <TableRow>
                  <TableCell colSpan={4} className="text-center text-muted-foreground">
                    {t.dashboard.noRecentTransactions}
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
