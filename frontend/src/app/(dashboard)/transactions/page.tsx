"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { type ColumnDef } from "@tanstack/react-table";
import { MoreHorizontal, Eye, Plus, TrendingUp, TrendingDown } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Transaction } from "@/types";
import { PageHeader } from "@/components/page-header";
import { DataTable } from "@/components/data-table";
import { Button, buttonVariants } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Input } from "@/components/ui/input";
import { StatCard } from "@/components/stat-card";
import { formatCurrency, formatDate } from "@/lib/format";

const TX_TYPE_COLORS: Record<string, string> = {
  income: "bg-green-500/10 text-green-600 dark:text-green-400",
  expense: "bg-red-500/10 text-red-600 dark:text-red-400",
  transfer: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
};

async function fetchTransactions(): Promise<Transaction[]> {
  const res = await apiClient.get<{ data: Transaction[] }>("/transactions");
  const body = res.data as { data: Transaction[] };
  return body.data ?? (body as unknown as Transaction[]);
}

export default function TransactionsPage() {
  const [typeFilter, setTypeFilter] = useState<string>("all");
  const [accountFilter, setAccountFilter] = useState<string>("all");
  const [reconciledFilter, setReconciledFilter] = useState<string>("all");
  const [searchQuery, setSearchQuery] = useState("");

  const { data: transactions = [], isLoading } = useQuery({
    queryKey: ["transactions"],
    queryFn: fetchTransactions,
  });

  const { data: accounts = [] } = useQuery({
    queryKey: ["accounts"],
    queryFn: async () => {
      const res = await apiClient.get<{ data: { id: number; name: string }[] }>("/accounts");
      const body = res.data as { data: { id: number; name: string }[] };
      return body.data ?? [];
    },
  });

  const filteredData = useMemo(() => {
    let result = transactions;
    if (typeFilter !== "all") {
      result = result.filter((t) => t.type === typeFilter);
    }
    if (accountFilter !== "all") {
      const accountId = parseInt(accountFilter, 10);
      result = result.filter(
        (t) => t.account_id === accountId || t.contra_account_id === accountId
      );
    }
    if (reconciledFilter !== "all") {
      const reconciled = reconciledFilter === "yes";
      result = result.filter((t) => t.is_reconciled === reconciled);
    }
    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase();
      result = result.filter(
        (t) =>
          t.transaction_no?.toLowerCase().includes(q) ||
          t.description?.toLowerCase().includes(q)
      );
    }
    return result;
  }, [transactions, typeFilter, accountFilter, reconciledFilter, searchQuery]);

  const summary = useMemo(() => {
    let totalIncome = 0;
    let totalExpense = 0;
    for (const t of filteredData) {
      if (t.type === "income") totalIncome += t.amount;
      else if (t.type === "expense") totalExpense += Math.abs(t.amount);
      else if (t.type === "transfer") {
        if (t.amount > 0) totalIncome += t.amount;
        else totalExpense += Math.abs(t.amount);
      }
    }
    return {
      totalIncome,
      totalExpense,
      net: totalIncome - totalExpense,
    };
  }, [filteredData]);

  const columns: ColumnDef<Transaction>[] = [
    {
      accessorKey: "transaction_no",
      header: "Transaction No",
      cell: ({ row }) => (
        <Link
          href={`/transactions/${row.original.id}`}
          className="font-medium text-primary hover:underline"
        >
          {row.original.transaction_no}
        </Link>
      ),
    },
    {
      accessorKey: "date",
      header: "Date",
      cell: ({ row }) => formatDate(row.original.date),
    },
    {
      id: "type",
      header: "Type",
      cell: ({ row }) => (
        <Badge
          variant="outline"
          className={TX_TYPE_COLORS[row.original.type] ?? "bg-muted"}
        >
          {row.original.type}
        </Badge>
      ),
    },
    {
      accessorKey: "description",
      header: "Description",
      cell: ({ row }) => row.original.description ?? "-",
    },
    {
      id: "account",
      header: "Account",
      cell: ({ row }) => row.original.account?.name ?? "-",
    },
    {
      id: "amount",
      header: "Amount",
      cell: ({ row }) => (
        <span
          className={
            row.original.type === "income"
              ? "text-green-600 dark:text-green-400"
              : row.original.type === "expense"
                ? "text-red-600 dark:text-red-400"
                : ""
          }
        >
          {formatCurrency(row.original.amount)}
        </span>
      ),
    },
    {
      accessorKey: "payment_method",
      header: "Payment Method",
      cell: ({ row }) => row.original.payment_method ?? "-",
    },
    {
      id: "reconciled",
      header: "Reconciled",
      cell: ({ row }) =>
        row.original.is_reconciled ? (
          <span className="text-green-600">✓</span>
        ) : (
          <span className="text-muted-foreground">-</span>
        ),
    },
    {
      id: "actions",
      header: "",
      cell: ({ row }) => (
        <DropdownMenu>
          <DropdownMenuTrigger
            render={
              <Button variant="ghost" size="icon-sm">
                <MoreHorizontal className="size-4" />
                <span className="sr-only">Toggle menu</span>
              </Button>
            }
          />
          <DropdownMenuContent align="end">
            <DropdownMenuItem
              render={
                <Link href={`/transactions/${row.original.id}`}>
                  <Eye className="mr-2 size-4" />
                  <span>View</span>
                </Link>
              }
            />
          </DropdownMenuContent>
        </DropdownMenu>
      ),
    },
  ];

  return (
    <div className="space-y-6">
      <PageHeader
        title="Transactions"
        description="Manage your transactions"
        children={
          <Link href="/transactions/create" className={buttonVariants()}>
            <Plus className="mr-2 size-4" />
            Record Transaction
          </Link>
        }
      />
      <div className="grid gap-4 sm:grid-cols-3">
        <StatCard
          title="Total Income (period)"
          value={formatCurrency(summary.totalIncome)}
          icon={TrendingUp}
        />
        <StatCard
          title="Total Expenses (period)"
          value={formatCurrency(summary.totalExpense)}
          icon={TrendingDown}
        />
        <StatCard
          title="Net (period)"
          value={formatCurrency(summary.net)}
          icon={TrendingUp}
        />
      </div>
      <div className="space-y-4">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
          <Input
            placeholder="Search by transaction no or description..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="max-w-sm"
          />
          <Select value={typeFilter} onValueChange={(v) => setTypeFilter(v ?? "all")}>
            <SelectTrigger className="w-[140px]">
              <SelectValue placeholder="Type" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All types</SelectItem>
              <SelectItem value="income">Income</SelectItem>
              <SelectItem value="expense">Expense</SelectItem>
              <SelectItem value="transfer">Transfer</SelectItem>
            </SelectContent>
          </Select>
          <Select value={accountFilter} onValueChange={(v) => setAccountFilter(v ?? "all")}>
            <SelectTrigger className="w-[160px]">
              <SelectValue placeholder="Account" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All accounts</SelectItem>
              {accounts.map((a) => (
                <SelectItem key={a.id} value={String(a.id)}>
                  {a.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          <Select value={reconciledFilter} onValueChange={(v) => setReconciledFilter(v ?? "all")}>
            <SelectTrigger className="w-[140px]">
              <SelectValue placeholder="Reconciled" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All</SelectItem>
              <SelectItem value="yes">Reconciled</SelectItem>
              <SelectItem value="no">Not reconciled</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <DataTable
          columns={columns}
          data={filteredData}
          isLoading={isLoading}
          emptyMessage="No transactions found."
        />
      </div>
    </div>
  );
}
