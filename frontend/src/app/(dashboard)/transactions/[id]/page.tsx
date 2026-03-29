"use client";

import { useParams, useRouter } from "next/navigation";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { ArrowLeft, ExternalLink } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Transaction } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency, formatDate } from "@/lib/format";

const TX_TYPE_COLORS: Record<string, string> = {
  income: "bg-green-500/10 text-green-600 dark:text-green-400",
  expense: "bg-red-500/10 text-red-600 dark:text-red-400",
  transfer: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
};

async function fetchTransaction(id: string): Promise<Transaction> {
  const res = await apiClient.get<{ data: Transaction }>(`/transactions/${id}`);
  const body = res.data as { data: Transaction };
  return body.data ?? (body as unknown as Transaction);
}

export default function TransactionDetailPage() {
  const params = useParams();
  const router = useRouter();
  const id = params.id as string;

  const { data: transaction, isLoading } = useQuery({
    queryKey: ["transaction", id],
    queryFn: () => fetchTransaction(id),
    enabled: !!id,
  });

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-48" />
      </div>
    );
  }

  if (!transaction) {
    return null;
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title={transaction.transaction_no}
        description={transaction.description ?? "Transaction details"}
        children={
          <div className="flex gap-2">
            {transaction.invoice_id && (
              <Link
                href={`/invoices/${transaction.invoice_id}`}
                className="inline-flex items-center gap-2 rounded-md border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground"
              >
                <ExternalLink className="size-4" />
                View Invoice
              </Link>
            )}
            {(transaction.account_id || transaction.contra_account_id) && (
              <Link
                href={`/reports/general-ledger?account_id=${transaction.account_id ?? transaction.contra_account_id}`}
                className="inline-flex items-center gap-2 rounded-md border border-input bg-background px-4 py-2 text-sm font-medium hover:bg-accent hover:text-accent-foreground"
              >
                View in Ledger
              </Link>
            )}
            <Button variant="outline" onClick={() => router.back()}>
              <ArrowLeft className="mr-2 size-4" />
              Back
            </Button>
          </div>
        }
      />
      <Card>
        <CardHeader>
          <CardTitle>Transaction Information</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex items-center gap-4">
            <Badge
              variant="outline"
              className={TX_TYPE_COLORS[transaction.type] ?? "bg-muted"}
            >
              {transaction.type}
            </Badge>
            <span
              className={
                transaction.type === "income"
                  ? "text-green-600 dark:text-green-400 font-semibold"
                  : transaction.type === "expense"
                    ? "text-red-600 dark:text-red-400 font-semibold"
                    : "font-semibold"
              }
            >
              {formatCurrency(transaction.amount)}
            </span>
          </div>
          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <p className="text-sm text-muted-foreground">Date</p>
              <p className="font-medium">{formatDate(transaction.date)}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Account</p>
              <p className="font-medium">
                {transaction.account?.name ?? "-"}
              </p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Contra Account</p>
              <p className="font-medium">
                {transaction.contra_account?.name ?? "-"}
              </p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Payment Method</p>
              <p className="font-medium">
                {transaction.payment_method ?? "-"}
              </p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Reference No</p>
              <p className="font-medium">
                {transaction.reference_no ?? "-"}
              </p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Category</p>
              <p className="font-medium">
                {transaction.category ?? "-"}
              </p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Reconciled</p>
              <p className="font-medium">
                {transaction.is_reconciled ? "Yes" : "No"}
              </p>
            </div>
          </div>
          {transaction.description && (
            <div>
              <p className="text-sm text-muted-foreground">Description</p>
              <p className="font-medium">{transaction.description}</p>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
