"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { type ColumnDef } from "@tanstack/react-table";
import {
  MoreHorizontal,
  Pencil,
  Eye,
  Copy,
  RefreshCw,
  FileText,
} from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Client, WorkOrder } from "@/types";
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
import { formatCurrency, formatDate } from "@/lib/format";

const WO_STATUS_COLORS: Record<string, string> = {
  draft: "bg-muted text-muted-foreground",
  confirmed: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
  in_progress: "bg-yellow-500/10 text-yellow-600 dark:text-yellow-400",
  completed: "bg-green-500/10 text-green-600 dark:text-green-400",
  invoiced: "bg-purple-500/10 text-purple-600 dark:text-purple-400",
  cancelled: "bg-red-500/10 text-red-600 dark:text-red-400",
};

const WO_PRIORITY_COLORS: Record<string, string> = {
  low: "bg-muted text-muted-foreground",
  medium: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
  high: "bg-yellow-500/10 text-yellow-600 dark:text-yellow-400",
  urgent: "bg-red-500/10 text-red-600 dark:text-red-400",
};

async function fetchWorkOrders(): Promise<WorkOrder[]> {
  const res = await apiClient.get<{ data: WorkOrder[] }>("/work-orders");
  const body = res.data as { data: WorkOrder[] };
  return body.data ?? (body as unknown as WorkOrder[]);
}

async function fetchClients(): Promise<Client[]> {
  const res = await apiClient.get<{ data: Client[] }>("/clients");
  const body = res.data as { data: Client[] };
  return body.data ?? (body as unknown as Client[]);
}

export default function WorkOrdersPage() {
  const [statusFilter, setStatusFilter] = useState<string>("all");
  const [clientFilter, setClientFilter] = useState<string>("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [changeStatusWo, setChangeStatusWo] = useState<WorkOrder | null>(null);

  const { data: workOrders = [], isLoading } = useQuery({
    queryKey: ["work-orders"],
    queryFn: fetchWorkOrders,
  });

  const { data: clients = [] } = useQuery({
    queryKey: ["clients"],
    queryFn: fetchClients,
  });

  const filteredData = useMemo(() => {
    let result = workOrders;
    if (statusFilter !== "all") {
      result = result.filter((wo) => wo.status === statusFilter);
    }
    if (clientFilter !== "all") {
      const clientId = parseInt(clientFilter, 10);
      result = result.filter((wo) => wo.client_id === clientId);
    }
    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase();
      result = result.filter(
        (wo) =>
          wo.wo_number?.toLowerCase().includes(q) ||
          wo.title?.toLowerCase().includes(q)
      );
    }
    return result;
  }, [workOrders, statusFilter, clientFilter, searchQuery]);

  const columns: ColumnDef<WorkOrder>[] = [
    {
      accessorKey: "wo_number",
      header: "WO Number",
      cell: ({ row }) => (
        <Link
          href={`/work-orders/${row.original.id}`}
          className="font-medium text-primary hover:underline"
        >
          {row.original.wo_number}
        </Link>
      ),
    },
    {
      id: "client",
      header: "Client",
      cell: ({ row }) => row.original.client?.name ?? "-",
    },
    {
      accessorKey: "title",
      header: "Title",
    },
    {
      accessorKey: "category",
      header: "Category",
      cell: ({ row }) => row.original.category ?? "-",
    },
    {
      id: "priority",
      header: "Priority",
      cell: ({ row }) => (
        <Badge
          variant="outline"
          className={
            WO_PRIORITY_COLORS[row.original.priority ?? "medium"] ??
            "bg-muted"
          }
        >
          {row.original.priority ?? "-"}
        </Badge>
      ),
    },
    {
      id: "status",
      header: "Status",
      cell: ({ row }) => (
        <Badge
          variant="outline"
          className={
            WO_STATUS_COLORS[row.original.status] ?? "bg-muted"
          }
        >
          {row.original.status}
        </Badge>
      ),
    },
    {
      accessorKey: "order_date",
      header: "Order Date",
      cell: ({ row }) => formatDate(row.original.order_date),
    },
    {
      accessorKey: "due_date",
      header: "Due Date",
      cell: ({ row }) =>
        row.original.due_date
          ? formatDate(row.original.due_date)
          : "-",
    },
    {
      id: "grand_total",
      header: "Grand Total",
      cell: ({ row }) => formatCurrency(row.original.grand_total ?? 0),
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
                <Link href={`/work-orders/${row.original.id}`}>
                  <Eye className="mr-2 size-4" />
                  <span>View</span>
                </Link>
              }
            />
            {(row.original.status === "draft" ||
              row.original.status === "confirmed") && (
              <DropdownMenuItem
                render={
                  <Link href={`/work-orders/${row.original.id}/edit`}>
                    <Pencil className="mr-2 size-4" />
                    <span>Edit</span>
                  </Link>
                }
              />
            )}
            <DropdownMenuItem>
              <Copy className="mr-2 size-4" />
              <span>Duplicate</span>
            </DropdownMenuItem>
            <DropdownMenuItem
              onSelect={(e) => {
                e.preventDefault();
                setChangeStatusWo(row.original);
              }}
            >
              <RefreshCw className="mr-2 size-4" />
              <span>Change Status</span>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      ),
    },
  ];

  return (
    <div className="space-y-6">
      <PageHeader
        title="Work Orders"
        description="Manage work orders"
        children={
          <Link href="/work-orders/create" className={buttonVariants()}>
            <FileText className="mr-2 size-4" />
            Create Work Order
          </Link>
        }
      />
      <div className="space-y-4">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
          <Input
            placeholder="Search by WO number or title..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="max-w-sm"
          />
          <Select value={statusFilter} onValueChange={(v) => setStatusFilter(v ?? "all")}>
            <SelectTrigger className="w-[160px]">
              <SelectValue placeholder="Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All status</SelectItem>
              <SelectItem value="draft">Draft</SelectItem>
              <SelectItem value="confirmed">Confirmed</SelectItem>
              <SelectItem value="in_progress">In Progress</SelectItem>
              <SelectItem value="completed">Completed</SelectItem>
              <SelectItem value="invoiced">Invoiced</SelectItem>
              <SelectItem value="cancelled">Cancelled</SelectItem>
            </SelectContent>
          </Select>
          <Select value={clientFilter} onValueChange={(v) => setClientFilter(v ?? "all")}>
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
        </div>
        <DataTable
          columns={columns}
          data={filteredData}
          isLoading={isLoading}
          emptyMessage="No work orders found."
        />
      </div>
      <AlertDialog
        open={!!changeStatusWo}
        onOpenChange={(open) => !open && setChangeStatusWo(null)}
      >
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Change Status</AlertDialogTitle>
            <AlertDialogDescription>
              Select a new status for work order {changeStatusWo?.wo_number}.
              This action will update the work order status.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction
              onClick={() => {
                setChangeStatusWo(null);
              }}
            >
              Confirm
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
