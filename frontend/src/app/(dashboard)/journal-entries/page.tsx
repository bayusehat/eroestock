"use client";

import { useState } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { type ColumnDef } from "@tanstack/react-table";
import { MoreHorizontal, Eye, FileText } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { JournalEntry } from "@/types";
import { PageHeader } from "@/components/page-header";
import { DataTable } from "@/components/data-table";
import { Button, buttonVariants } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Input } from "@/components/ui/input";
import { formatCurrency, formatDate } from "@/lib/format";

async function fetchJournalEntries(): Promise<JournalEntry[]> {
  const res = await apiClient.get<{ data: JournalEntry[] }>("/journal-entries");
  const body = res.data as { data: JournalEntry[] };
  return body.data ?? (body as unknown as JournalEntry[]);
}

export default function JournalEntriesPage() {
  const [searchQuery, setSearchQuery] = useState("");

  const { data: entries = [], isLoading } = useQuery({
    queryKey: ["journal-entries"],
    queryFn: fetchJournalEntries,
  });

  const filteredData = entries.filter((e) => {
    if (!searchQuery.trim()) return true;
    const q = searchQuery.toLowerCase();
    return (
      e.journal_no?.toLowerCase().includes(q) ||
      e.description?.toLowerCase().includes(q)
    );
  });

  const columns: ColumnDef<JournalEntry>[] = [
    {
      accessorKey: "journal_no",
      header: "Journal No",
      cell: ({ row }) => (
        <Link
          href={`/journal-entries/${row.original.id}`}
          className="font-medium text-primary hover:underline"
        >
          {row.original.journal_no}
        </Link>
      ),
    },
    {
      accessorKey: "date",
      header: "Date",
      cell: ({ row }) => formatDate(row.original.date),
    },
    {
      accessorKey: "description",
      header: "Description",
      cell: ({ row }) => row.original.description ?? "-",
    },
    {
      id: "total_amount",
      header: "Total Amount",
      cell: ({ row }) => {
        const total = (row.original.lines ?? []).reduce(
          (sum, l) => sum + l.debit + l.credit,
          0
        );
        return formatCurrency(total / 2);
      },
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
                <Link href={`/journal-entries/${row.original.id}`}>
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
        title="Journal Entries"
        description="Manage journal entries"
        children={
          <Link href="/journal-entries/create" className={buttonVariants()}>
            <FileText className="mr-2 size-4" />
            New Journal Entry
          </Link>
        }
      />
      <div className="space-y-4">
        <Input
          placeholder="Search by journal no or description..."
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          className="max-w-sm"
        />
        <DataTable
          columns={columns}
          data={filteredData}
          isLoading={isLoading}
          emptyMessage="No journal entries found."
        />
      </div>
    </div>
  );
}
