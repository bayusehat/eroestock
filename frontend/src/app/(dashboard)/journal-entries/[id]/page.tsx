"use client";

import { useParams, useRouter } from "next/navigation";
import { useQuery } from "@tanstack/react-query";
import { ArrowLeft } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { JournalEntry } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button } from "@/components/ui/button";
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
import { formatCurrency, formatDate } from "@/lib/format";

async function fetchJournalEntry(id: string): Promise<JournalEntry> {
  const res = await apiClient.get<{ data: JournalEntry }>(
    `/journal-entries/${id}`
  );
  const body = res.data as { data: JournalEntry };
  return body.data ?? (body as unknown as JournalEntry);
}

export default function JournalEntryDetailPage() {
  const params = useParams();
  const router = useRouter();
  const id = params.id as string;

  const { data: entry, isLoading } = useQuery({
    queryKey: ["journal-entry", id],
    queryFn: () => fetchJournalEntry(id),
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

  if (!entry) {
    return null;
  }

  const lines = entry.lines ?? [];
  const totalDebits = lines.reduce((sum, l) => sum + l.debit, 0);
  const totalCredits = lines.reduce((sum, l) => sum + l.credit, 0);

  return (
    <div className="space-y-6">
      <PageHeader
        title={entry.journal_no}
        description={entry.description ?? "Journal entry details"}
        children={
          <Button variant="outline" onClick={() => router.back()}>
            <ArrowLeft className="mr-2 size-4" />
            Back
          </Button>
        }
      />
      <Card>
        <CardHeader>
          <CardTitle>Entry Information</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <p className="text-sm text-muted-foreground">Date</p>
              <p className="font-medium">{formatDate(entry.date)}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Total Amount</p>
              <p className="font-medium">
                {formatCurrency(totalDebits)}
              </p>
            </div>
          </div>
          {entry.description && (
            <div>
              <p className="text-sm text-muted-foreground">Description</p>
              <p className="font-medium">{entry.description}</p>
            </div>
          )}
        </CardContent>
      </Card>
      <Card>
        <CardHeader>
          <CardTitle>Lines</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Account</TableHead>
                <TableHead className="text-right">Debit</TableHead>
                <TableHead className="text-right">Credit</TableHead>
                <TableHead>Description</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {lines.map((line) => (
                <TableRow key={line.id}>
                  <TableCell>
                    {line.account?.name ?? `Account ${line.account_id}`}
                  </TableCell>
                  <TableCell className="text-right">
                    {line.debit > 0 ? formatCurrency(line.debit) : "-"}
                  </TableCell>
                  <TableCell className="text-right">
                    {line.credit > 0 ? formatCurrency(line.credit) : "-"}
                  </TableCell>
                  <TableCell>{line.description ?? "-"}</TableCell>
                </TableRow>
              ))}
            </TableBody>
            <tfoot>
              <TableRow className="font-semibold">
                <TableCell>Totals</TableCell>
                <TableCell className="text-right">
                  {formatCurrency(totalDebits)}
                </TableCell>
                <TableCell className="text-right">
                  {formatCurrency(totalCredits)}
                </TableCell>
                <TableCell />
              </TableRow>
            </tfoot>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
}
