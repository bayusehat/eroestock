"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { useQuery } from "@tanstack/react-query";
import { ArrowLeft } from "lucide-react";
import { apiClient } from "@/lib/api";
import { fetchFlattenedAccounts } from "@/lib/accounts";
import type { Account, GeneralLedgerReport } from "@/types";
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
import { formatCurrency, formatDate } from "@/lib/format";
import { t } from "@/lib/translations";


async function fetchGeneralLedger(
  accountId: number,
  dateFrom: string,
  dateTo: string
): Promise<GeneralLedgerReport> {
  const res = await apiClient.get<{ data: GeneralLedgerReport }>("/reports/general-ledger", {
    params: { account_id: accountId, date_from: dateFrom, date_to: dateTo },
  });
  const body = res.data as { data: GeneralLedgerReport };
  return body.data ?? (body as unknown as GeneralLedgerReport);
}

function getDefaultDateRange() {
  const now = new Date();
  const start = new Date(now.getFullYear(), now.getMonth(), 1);
  const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
  return {
    from: start.toISOString().split("T")[0] ?? "",
    to: end.toISOString().split("T")[0] ?? "",
  };
}

export default function GeneralLedgerPage() {
  const searchParams = useSearchParams();
  const [accountId, setAccountId] = useState<string>("");
  const [range, setRange] = useState(getDefaultDateRange());

  useEffect(() => {
    const id = searchParams.get("account_id");
    if (id) setAccountId(id);
  }, [searchParams]);

  const { data: accounts = [] } = useQuery({
    queryKey: ["accounts"],
    queryFn: fetchFlattenedAccounts,
  });

  const leafAccounts = accounts.filter((a) => !a.is_header);

  const { data, isLoading } = useQuery({
    queryKey: ["reports", "general-ledger", accountId, range.from, range.to],
    queryFn: () =>
      fetchGeneralLedger(parseInt(accountId, 10), range.from, range.to),
    enabled: !!accountId && !!range.from && !!range.to,
  });

  return (
    <div className="space-y-6">
      <PageHeader
        title={t.reports.generalLedger.title}
        description={t.reports.generalLedger.description}
        children={
          <Link href="/reports" className={buttonVariants({ variant: "outline" })}>
            <ArrowLeft className="mr-2 size-4" />
            {t.common.back}
          </Link>
        }
      />
      <div className="flex flex-wrap items-start gap-4">
        <div className="space-y-2 min-w-[200px]">
          <label className="block text-sm font-medium text-muted-foreground">
            {t.reports.generalLedger.account}
          </label>
          <Select value={accountId} onValueChange={(v) => setAccountId(v ?? "")}>
            <SelectTrigger className="w-full">
              <SelectValue placeholder={t.reports.generalLedger.selectAccount}>
                {accountId
                  ? (() => {
                      const a = leafAccounts.find((acc) => String(acc.id) === accountId);
                      return a ? `${a.code} - ${a.name}` : null;
                    })()
                  : null}
              </SelectValue>
            </SelectTrigger>
            <SelectContent>
              {leafAccounts.map((a) => (
                <SelectItem key={a.id} value={String(a.id)}>
                  {a.code} - {a.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
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
      {!accountId && (
        <Card>
          <CardContent className="flex h-32 items-center justify-center text-muted-foreground">
            {t.reports.generalLedger.selectAccountHint}
          </CardContent>
        </Card>
      )}
      {accountId && isLoading && (
        <Skeleton className="h-96" />
      )}
      {accountId && !isLoading && data && (
        <>
          <Card>
            <CardHeader>
              <CardTitle>
                {data.account?.code} - {data.account?.name}
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex gap-8">
                <div>
                  <p className="text-sm text-muted-foreground">{t.reports.generalLedger.openingBalance}</p>
                  <p className="font-semibold">{formatCurrency(data.opening_balance ?? 0)}</p>
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">{t.reports.generalLedger.closingBalance}</p>
                  <p className="font-semibold">{formatCurrency(data.closing_balance ?? 0)}</p>
                </div>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle>{t.reports.generalLedger.entries}</CardTitle>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>{t.table.date}</TableHead>
                    <TableHead>{t.common.description}</TableHead>
                    <TableHead>{t.reports.generalLedger.reference}</TableHead>
                    <TableHead className="text-right">{t.table.debit}</TableHead>
                    <TableHead className="text-right">{t.table.credit}</TableHead>
                    <TableHead className="text-right">{t.reports.generalLedger.runningBalance}</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {(data.entries ?? []).map((e, i) => (
                    <TableRow key={i}>
                      <TableCell>{formatDate(e.date)}</TableCell>
                      <TableCell>{e.description}</TableCell>
                      <TableCell>{e.reference ?? "-"}</TableCell>
                      <TableCell className="text-right">
                        {e.debit > 0 ? formatCurrency(e.debit) : "-"}
                      </TableCell>
                      <TableCell className="text-right">
                        {e.credit > 0 ? formatCurrency(e.credit) : "-"}
                      </TableCell>
                      <TableCell className="text-right">
                        {formatCurrency(e.running_balance)}
                      </TableCell>
                    </TableRow>
                  ))}
                  {(!data.entries || data.entries.length === 0) && (
                    <TableRow>
                      <TableCell colSpan={6} className="text-center text-muted-foreground">
                        {t.reports.generalLedger.noEntries}
                      </TableCell>
                    </TableRow>
                  )}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </>
      )}
    </div>
  );
}
