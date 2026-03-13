"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { type ColumnDef } from "@tanstack/react-table";
import {
  MoreHorizontal,
  Pencil,
  Eye,
  Send,
  DollarSign,
  Trash2,
  FilePlus,
} from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Client, Invoice } from "@/types";
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
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { Input } from "@/components/ui/input";
import { StatCard } from "@/components/stat-card";
import { Wallet, AlertCircle, CheckCircle } from "lucide-react";
import { formatCurrency, formatDate } from "@/lib/format";
import { toast } from "sonner";

const INVOICE_STATUS_COLORS: Record<string, string> = {
  draft: "bg-muted text-muted-foreground",
  sent: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
  partially_paid: "bg-yellow-500/10 text-yellow-600 dark:text-yellow-400",
  paid: "bg-green-500/10 text-green-600 dark:text-green-400",
  overdue: "bg-red-500/10 text-red-600 dark:text-red-400",
  cancelled: "bg-muted text-muted-foreground",
};

async function fetchInvoices(): Promise<Invoice[]> {
  const res = await apiClient.get<{ data: Invoice[] }>("/invoices");
  const body = res.data as { data: Invoice[] };
  return body.data ?? (body as unknown as Invoice[]);
}

async function fetchClients(): Promise<Client[]> {
  const res = await apiClient.get<{ data: Client[] }>("/clients");
  const body = res.data as { data: Client[] };
  return body.data ?? (body as unknown as Client[]);
}

export default function InvoicesPage() {
  const queryClient = useQueryClient();
  const [statusFilter, setStatusFilter] = useState<string>("all");
  const [clientFilter, setClientFilter] = useState<string>("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [dateFrom, setDateFrom] = useState("");
  const [dateTo, setDateTo] = useState("");
  const [deleteInvoice, setDeleteInvoice] = useState<Invoice | null>(null);

  const { data: invoices = [], isLoading } = useQuery({
    queryKey: ["invoices"],
    queryFn: fetchInvoices,
  });

  const { data: clients = [] } = useQuery({
    queryKey: ["clients"],
    queryFn: fetchClients,
  });

  const sendMutation = useMutation({
    mutationFn: (id: number) => apiClient.post(`/invoices/${id}/send`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["invoices"] });
      toast.success("Invoice sent");
    },
    onError: (err: unknown) => {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to send invoice";
      toast.error(typeof message === "string" ? message : "Failed to send invoice");
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => apiClient.delete(`/invoices/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["invoices"] });
      toast.success("Invoice deleted");
      setDeleteInvoice(null);
    },
    onError: (err: unknown) => {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to delete invoice";
      toast.error(typeof message === "string" ? message : "Failed to delete invoice");
    },
  });

  const filteredData = useMemo(() => {
    let result = invoices;
    if (statusFilter !== "all") {
      result = result.filter((inv) => inv.status === statusFilter);
    }
    if (clientFilter !== "all") {
      const clientId = parseInt(clientFilter, 10);
      result = result.filter((inv) => inv.client_id === clientId);
    }
    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase();
      result = result.filter((inv) =>
        inv.invoice_no?.toLowerCase().includes(q)
      );
    }
    if (dateFrom) {
      result = result.filter((inv) => inv.issue_date >= dateFrom);
    }
    if (dateTo) {
      result = result.filter((inv) => inv.issue_date <= dateTo);
    }
    return result;
  }, [invoices, statusFilter, clientFilter, searchQuery, dateFrom, dateTo]);

  const summary = useMemo(() => {
    const outstanding = filteredData
      .filter((i) => ["sent", "partially_paid", "overdue"].includes(i.status))
      .reduce((sum, i) => sum + (i.balance_due ?? 0), 0);
    const overdue = filteredData
      .filter((i) => i.status === "overdue")
      .reduce((sum, i) => sum + (i.balance_due ?? 0), 0);
    const paid = filteredData
      .filter((i) => i.status === "paid")
      .reduce((sum, i) => sum + (i.amount_paid ?? 0), 0);
    return { outstanding, overdue, paid };
  }, [filteredData]);

  const columns: ColumnDef<Invoice>[] = [
    {
      accessorKey: "invoice_no",
      header: "Invoice No",
      cell: ({ row }) => (
        <Link
          href={`/invoices/${row.original.id}`}
          className="font-medium text-primary hover:underline"
        >
          {row.original.invoice_no}
        </Link>
      ),
    },
    {
      id: "client",
      header: "Client",
      cell: ({ row }) => row.original.client?.name ?? "-",
    },
    {
      accessorKey: "issue_date",
      header: "Issue Date",
      cell: ({ row }) => formatDate(row.original.issue_date),
    },
    {
      accessorKey: "due_date",
      header: "Due Date",
      cell: ({ row }) => formatDate(row.original.due_date),
    },
    {
      id: "grand_total",
      header: "Grand Total",
      cell: ({ row }) => formatCurrency(row.original.grand_total ?? 0),
    },
    {
      id: "amount_paid",
      header: "Amount Paid",
      cell: ({ row }) => formatCurrency(row.original.amount_paid ?? 0),
    },
    {
      id: "balance_due",
      header: "Balance Due",
      cell: ({ row }) => formatCurrency(row.original.balance_due ?? 0),
    },
    {
      id: "status",
      header: "Status",
      cell: ({ row }) => (
        <Badge
          variant="outline"
          className={
            INVOICE_STATUS_COLORS[row.original.status] ?? "bg-muted"
          }
        >
          {row.original.status}
        </Badge>
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
                <Link href={`/invoices/${row.original.id}`}>
                  <Eye className="mr-2 size-4" />
                  <span>View</span>
                </Link>
              }
            />
            {row.original.status === "draft" && (
              <DropdownMenuItem
                render={
                  <Link href={`/invoices/${row.original.id}/edit`}>
                    <Pencil className="mr-2 size-4" />
                    <span>Edit</span>
                  </Link>
                }
              />
            )}
            {row.original.status === "draft" && (
              <DropdownMenuItem
                onSelect={(e) => {
                  e.preventDefault();
                  sendMutation.mutate(row.original.id);
                }}
              >
                <Send className="mr-2 size-4" />
                <span>Send</span>
              </DropdownMenuItem>
            )}
            {["sent", "partially_paid"].includes(row.original.status) && (
              <DropdownMenuItem
                render={
                  <Link href={`/invoices/${row.original.id}?recordPayment=1`}>
                    <DollarSign className="mr-2 size-4" />
                    <span>Record Payment</span>
                  </Link>
                }
              />
            )}
            {row.original.status === "draft" && (
              <DropdownMenuItem
                variant="destructive"
                onSelect={(e) => {
                  e.preventDefault();
                  setDeleteInvoice(row.original);
                }}
              >
                <Trash2 className="mr-2 size-4" />
                <span>Delete</span>
              </DropdownMenuItem>
            )}
          </DropdownMenuContent>
        </DropdownMenu>
      ),
    },
  ];

  return (
    <div className="space-y-6">
      <PageHeader
        title="Invoices"
        description="Manage invoices"
        children={
          <Link href="/invoices/create" className={buttonVariants()}>
            <FilePlus className="mr-2 size-4" />
            Create Invoice
          </Link>
        }
      />
      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <StatCard
          title="Total Outstanding"
          value={formatCurrency(summary.outstanding)}
          icon={Wallet}
        />
        <StatCard
          title="Total Overdue"
          value={formatCurrency(summary.overdue)}
          icon={AlertCircle}
        />
        <StatCard
          title="Total Paid (this period)"
          value={formatCurrency(summary.paid)}
          icon={CheckCircle}
        />
      </div>
      <div className="space-y-4">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:flex-wrap">
          <Input
            placeholder="Search by invoice number..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="max-w-sm"
          />
          <Select
            value={statusFilter}
            onValueChange={(v) => setStatusFilter(v ?? "all")}
          >
            <SelectTrigger className="w-[160px]">
              <SelectValue placeholder="Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All status</SelectItem>
              <SelectItem value="draft">Draft</SelectItem>
              <SelectItem value="sent">Sent</SelectItem>
              <SelectItem value="partially_paid">Partially Paid</SelectItem>
              <SelectItem value="paid">Paid</SelectItem>
              <SelectItem value="overdue">Overdue</SelectItem>
              <SelectItem value="cancelled">Cancelled</SelectItem>
            </SelectContent>
          </Select>
          <Select
            value={clientFilter}
            onValueChange={(v) => setClientFilter(v ?? "all")}
          >
            <SelectTrigger className="w-[180px]">
              <SelectValue placeholder="Client" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All clients</SelectItem>
              {clients.map((c) => (
                <SelectItem key={c.id} value={String(c.id)}>
                  {c.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          <Input
            type="date"
            placeholder="From"
            value={dateFrom}
            onChange={(e) => setDateFrom(e.target.value)}
            className="w-[140px]"
          />
          <Input
            type="date"
            placeholder="To"
            value={dateTo}
            onChange={(e) => setDateTo(e.target.value)}
            className="w-[140px]"
          />
        </div>
        <DataTable
          columns={columns}
          data={filteredData}
          isLoading={isLoading}
          emptyMessage="No invoices found."
        />
      </div>
      <AlertDialog
        open={!!deleteInvoice}
        onOpenChange={(open) => !open && setDeleteInvoice(null)}
      >
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Delete Invoice</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to delete invoice {deleteInvoice?.invoice_no}?
              This action cannot be undone.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
              onClick={() => deleteInvoice && deleteMutation.mutate(deleteInvoice.id)}
            >
              Delete
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
