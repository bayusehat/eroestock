"use client";

import { useState } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { ArrowLeft, FileDown, Check, X } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { BalanceSheetReport } from "@/types";
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
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency } from "@/lib/format";
import { downloadPdf } from "@/lib/download";
import { toast } from "sonner";

function getDefaultDate() {
  return new Date().toISOString().split("T")[0] ?? "";
}

async function fetchBalanceSheet(asOf: string): Promise<BalanceSheetReport> {
  const res = await apiClient.get<{ data: BalanceSheetReport }>("/reports/balance-sheet", {
    params: { as_of: asOf },
  });
  const body = res.data as { data: BalanceSheetReport };
  return body.data ?? (body as unknown as BalanceSheetReport);
}

export default function BalanceSheetPage() {
  const [asOf, setAsOf] = useState(getDefaultDate());

  const { data, isLoading } = useQuery({
    queryKey: ["reports", "balance-sheet", asOf],
    queryFn: () => fetchBalanceSheet(asOf),
    enabled: !!asOf,
  });

  const handleExportPdf = async () => {
    try {
      await downloadPdf(
        `/exports/report/balance-sheet/pdf?as_of=${asOf}`,
        `balance-sheet-${asOf}.pdf`
      );
      toast.success("PDF downloaded");
    } catch {
      toast.error("Failed to download PDF");
    }
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-10 w-48" />
        <div className="grid gap-4 md:grid-cols-3">
          {[1, 2, 3].map((i) => (
            <Skeleton key={i} className="h-64" />
          ))}
        </div>
      </div>
    );
  }

  const report = data ?? {
    assets: [],
    liabilities: [],
    equity: [],
    total_assets: 0,
    total_liabilities: 0,
    total_equity: 0,
  };

  const balanced =
    Math.abs(report.total_assets - (report.total_liabilities + report.total_equity)) < 0.01;

  return (
    <div className="space-y-6">
      <PageHeader
        title="Balance Sheet"
        description="Assets, liabilities, and equity"
        children={
          <div className="flex items-center gap-2">
            <Link href="/reports" className={buttonVariants({ variant: "outline" })}>
              <ArrowLeft className="mr-2 size-4" />
              Back
            </Link>
            <Button onClick={handleExportPdf}>
              <FileDown className="mr-2 size-4" />
              Export PDF
            </Button>
          </div>
        }
      />
      <div className="flex items-end gap-4">
        <div className="space-y-1">
          <label className="text-sm text-muted-foreground">As of</label>
          <DatePicker
            value={asOf}
            onChange={setAsOf}
            placeholder="As of date"
            className="w-[160px]"
          />
        </div>
        <div className="flex items-center gap-2 rounded-lg border px-3 py-2">
          {balanced ? (
            <>
              <Check className="size-4 text-green-600" />
              <span className="text-sm font-medium text-green-600">
                Balanced: Assets = Liabilities + Equity
              </span>
            </>
          ) : (
            <>
              <X className="size-4 text-red-600" />
              <span className="text-sm font-medium text-red-600">Not balanced</span>
            </>
          )}
        </div>
      </div>
      <div className="grid gap-6 md:grid-cols-3">
        <Card>
          <CardHeader>
            <CardTitle>Assets</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Account</TableHead>
                  <TableHead className="text-right">Balance</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {(report.assets ?? []).map((a) => (
                  <TableRow key={a.account_code}>
                    <TableCell>
                      {a.account_code} - {a.account_name}
                    </TableCell>
                    <TableCell className="text-right">
                      {formatCurrency(a.balance)}
                    </TableCell>
                  </TableRow>
                ))}
                {(!report.assets || report.assets.length === 0) && (
                  <TableRow>
                    <TableCell colSpan={2} className="text-center text-muted-foreground">
                      No data
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
            <div className="mt-4 flex justify-between border-t pt-4 font-semibold">
              <span>Total Assets</span>
              <span>{formatCurrency(report.total_assets)}</span>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Liabilities</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Account</TableHead>
                  <TableHead className="text-right">Balance</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {(report.liabilities ?? []).map((l) => (
                  <TableRow key={l.account_code}>
                    <TableCell>
                      {l.account_code} - {l.account_name}
                    </TableCell>
                    <TableCell className="text-right">
                      {formatCurrency(l.balance)}
                    </TableCell>
                  </TableRow>
                ))}
                {(!report.liabilities || report.liabilities.length === 0) && (
                  <TableRow>
                    <TableCell colSpan={2} className="text-center text-muted-foreground">
                      No data
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
            <div className="mt-4 flex justify-between border-t pt-4 font-semibold">
              <span>Total Liabilities</span>
              <span>{formatCurrency(report.total_liabilities)}</span>
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Equity</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Account</TableHead>
                  <TableHead className="text-right">Balance</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {(report.equity ?? []).map((e) => (
                  <TableRow key={e.account_code}>
                    <TableCell>
                      {e.account_code} - {e.account_name}
                    </TableCell>
                    <TableCell className="text-right">
                      {formatCurrency(e.balance)}
                    </TableCell>
                  </TableRow>
                ))}
                {(!report.equity || report.equity.length === 0) && (
                  <TableRow>
                    <TableCell colSpan={2} className="text-center text-muted-foreground">
                      No data
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
            <div className="mt-4 flex justify-between border-t pt-4 font-semibold">
              <span>Total Equity</span>
              <span>{formatCurrency(report.total_equity)}</span>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
