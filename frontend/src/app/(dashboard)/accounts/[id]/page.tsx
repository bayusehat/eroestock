"use client";

import { useState } from "react";
import Link from "next/link";
import { useParams } from "next/navigation";
import { useQuery } from "@tanstack/react-query";
import { ArrowLeft, Pencil, BookOpen } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Account, GeneralLedgerReport } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button, buttonVariants } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { DatePicker } from "@/components/ui/date-picker";
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency, formatDate } from "@/lib/format";
import { t } from "@/lib/translations";

async function fetchAccount(id: string): Promise<Account> {
  const res = await apiClient.get<{ data: Account }>(`/accounts/${id}`);
  const body = res.data as { data: Account };
  return body.data ?? (body as unknown as Account);
}

async function fetchGeneralLedger(
  accountId: number,
  dateFrom: string,
  dateTo: string
): Promise<GeneralLedgerReport> {
  const res = await apiClient.get<{ data: GeneralLedgerReport }>(
    "/reports/general-ledger",
    {
      params: { account_id: accountId, date_from: dateFrom, date_to: dateTo },
    }
  );
  const body = res.data as { data: GeneralLedgerReport };
  return body.data ?? (body as unknown as GeneralLedgerReport);
}

function getDefaultDateRange() {
  const now = new Date();
  const start = new Date(now.getFullYear(), 0, 1);
  const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
  return {
    from: start.toISOString().split("T")[0] ?? "",
    to: end.toISOString().split("T")[0] ?? "",
  };
}

function getAccountNormalSide(type: string): "debit" | "credit" {
  const debitTypes = ["Asset", "Expense"];
  return debitTypes.includes(type) ? "debit" : "credit";
}

export default function AccountDetailPage() {
  const params = useParams();
  const id = params.id as string;
  const [range, setRange] = useState(getDefaultDateRange());

  const { data: account, isLoading: accountLoading } = useQuery({
    queryKey: ["account", id],
    queryFn: () => fetchAccount(id),
    enabled: !!id,
  });

  const { data: ledger, isLoading: ledgerLoading } = useQuery({
    queryKey: ["account-ledger", id, range.from, range.to],
    queryFn: () => fetchGeneralLedger(parseInt(id, 10), range.from, range.to),
    enabled: !!id && !!range.from && !!range.to,
  });

  if (accountLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-32" />
        <Skeleton className="h-96" />
      </div>
    );
  }

  if (!account) {
    return (
      <div className="flex h-64 items-center justify-center text-muted-foreground">
        {t.accounts.notFound}
      </div>
    );
  }

  const entries = ledger?.entries ?? [];
  const totalDebits = entries.reduce((sum, e) => sum + e.debit, 0);
  const totalCredits = entries.reduce((sum, e) => sum + e.credit, 0);
  const normalSide = getAccountNormalSide(account.type);
  const isAggregate = ledger?.is_aggregate ?? account.is_header;
  const colSpan = isAggregate ? 4 : 3;

  return (
    <div className="space-y-6">
      <PageHeader
        title={`${account.code} — ${account.name}`}
        description={t.accounts.detailDescription}
        children={
          <div className="flex items-center gap-2">
            <Link
              href={`/reports/general-ledger?account_id=${account.id}`}
              className={buttonVariants({ variant: "outline", size: "sm" })}
            >
              <BookOpen className="mr-2 size-4" />
              {t.accounts.viewFullLedger}
            </Link>
            <Link
              href={`/accounts/${account.id}/edit`}
              className={buttonVariants({ variant: "outline", size: "sm" })}
            >
              <Pencil className="mr-2 size-4" />
              {t.common.edit}
            </Link>
            <Link
              href="/accounts"
              className={buttonVariants({ variant: "outline", size: "sm" })}
            >
              <ArrowLeft className="mr-2 size-4" />
              {t.common.back}
            </Link>
          </div>
        }
      />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardContent className="pt-6">
            <p className="text-sm text-muted-foreground">{t.accounts.type}</p>
            <div className="mt-1">
              <Badge variant="outline">{account.type}</Badge>
              {account.sub_type && (
                <span className="ml-2 text-sm text-muted-foreground">
                  / {account.sub_type}
                </span>
              )}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <p className="text-sm text-muted-foreground">
              {t.accounts.normalBalance}
            </p>
            <p className="mt-1 font-semibold capitalize">{normalSide}</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <p className="text-sm text-muted-foreground">
              {t.accounts.openingBalance}
            </p>
            <p className="mt-1 font-semibold font-mono">
              {formatCurrency(account.opening_balance ?? 0)}
            </p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <p className="text-sm text-muted-foreground">
              {t.accounts.currentBalance}
            </p>
            <p className="mt-1 font-semibold font-mono">
              {formatCurrency(
                ledger?.closing_balance ??
                  account.balance ??
                  account.opening_balance ??
                  0
              )}
            </p>
          </CardContent>
        </Card>
      </div>

      {account.description && (
        <Card>
          <CardContent className="pt-6">
            <p className="text-sm text-muted-foreground">
              {t.common.description}
            </p>
            <p className="mt-1">{account.description}</p>
          </CardContent>
        </Card>
      )}

      <Card>
        <CardHeader>
          <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <CardTitle>{t.accounts.ledgerEntries}</CardTitle>
            <div className="flex items-center gap-3">
              <div className="flex items-center gap-2">
                <label className="text-sm text-muted-foreground whitespace-nowrap">
                  {t.common.from}
                </label>
                <DatePicker
                  value={range.from}
                  onChange={(v) => setRange((r) => ({ ...r, from: v }))}
                  placeholder={t.placeholders.fromDate}
                  className="w-[140px]"
                />
              </div>
              <div className="flex items-center gap-2">
                <label className="text-sm text-muted-foreground whitespace-nowrap">
                  {t.common.to}
                </label>
                <DatePicker
                  value={range.to}
                  onChange={(v) => setRange((r) => ({ ...r, to: v }))}
                  placeholder={t.placeholders.toDate}
                  className="w-[140px]"
                />
              </div>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          {ledgerLoading ? (
            <Skeleton className="h-64" />
          ) : (
            <>
              <div className="mb-4 flex flex-wrap gap-6">
                <div>
                  <p className="text-sm text-muted-foreground">
                    {t.reports.generalLedger.openingBalance}
                  </p>
                  <p className="font-semibold font-mono">
                    {formatCurrency(ledger?.opening_balance ?? 0)}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">
                    {t.accounts.totalDebit}
                  </p>
                  <p className="font-semibold font-mono text-emerald-600 dark:text-emerald-400">
                    {formatCurrency(totalDebits)}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">
                    {t.accounts.totalCredit}
                  </p>
                  <p className="font-semibold font-mono text-rose-600 dark:text-rose-400">
                    {formatCurrency(totalCredits)}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">
                    {t.reports.generalLedger.closingBalance}
                  </p>
                  <p className="font-semibold font-mono">
                    {formatCurrency(ledger?.closing_balance ?? 0)}
                  </p>
                </div>
              </div>

              <div className="rounded-md border">
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>{t.table.date}</TableHead>
                      {isAggregate && (
                        <TableHead>{t.table.account}</TableHead>
                      )}
                      <TableHead>{t.common.description}</TableHead>
                      <TableHead>
                        {t.reports.generalLedger.reference}
                      </TableHead>
                      <TableHead className="text-right">
                        {t.table.debit}
                      </TableHead>
                      <TableHead className="text-right">
                        {t.table.credit}
                      </TableHead>
                      <TableHead className="text-right">
                        {t.reports.generalLedger.runningBalance}
                      </TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {entries.length === 0 ? (
                      <TableRow>
                        <TableCell
                          colSpan={isAggregate ? 7 : 6}
                          className="h-24 text-center text-muted-foreground"
                        >
                          {t.reports.generalLedger.noEntries}
                        </TableCell>
                      </TableRow>
                    ) : (
                      entries.map((entry, i) => (
                        <TableRow key={i}>
                          <TableCell className="whitespace-nowrap">
                            {formatDate(entry.date)}
                          </TableCell>
                          {isAggregate && (
                            <TableCell className="text-sm">
                              <span className="font-mono text-muted-foreground">
                                {entry.account_code}
                              </span>
                              {entry.account_name && (
                                <span className="ml-1.5">
                                  {entry.account_name}
                                </span>
                              )}
                            </TableCell>
                          )}
                          <TableCell>{entry.description}</TableCell>
                          <TableCell className="text-sm text-muted-foreground">
                            {entry.reference ?? "-"}
                          </TableCell>
                          <TableCell className="text-right font-mono">
                            {entry.debit > 0 ? (
                              <span className="text-emerald-600 dark:text-emerald-400">
                                {formatCurrency(entry.debit)}
                              </span>
                            ) : (
                              "-"
                            )}
                          </TableCell>
                          <TableCell className="text-right font-mono">
                            {entry.credit > 0 ? (
                              <span className="text-rose-600 dark:text-rose-400">
                                {formatCurrency(entry.credit)}
                              </span>
                            ) : (
                              "-"
                            )}
                          </TableCell>
                          <TableCell className="text-right font-mono font-medium">
                            {formatCurrency(entry.running_balance)}
                          </TableCell>
                        </TableRow>
                      ))
                    )}
                  </TableBody>
                  {entries.length > 0 && (
                    <tfoot>
                      <TableRow className="bg-muted/50 font-semibold">
                        <TableCell colSpan={colSpan}>
                          {t.common.total}
                        </TableCell>
                        <TableCell className="text-right font-mono text-emerald-600 dark:text-emerald-400">
                          {formatCurrency(totalDebits)}
                        </TableCell>
                        <TableCell className="text-right font-mono text-rose-600 dark:text-rose-400">
                          {formatCurrency(totalCredits)}
                        </TableCell>
                        <TableCell className="text-right font-mono">
                          {formatCurrency(ledger?.closing_balance ?? 0)}
                        </TableCell>
                      </TableRow>
                    </tfoot>
                  )}
                </Table>
              </div>
            </>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
