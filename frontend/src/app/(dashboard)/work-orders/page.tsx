"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useQuery, useQueryClient } from "@tanstack/react-query";
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
import { toast } from "sonner";

const WO_STATUS_COLORS: Record<string, string> = {
  draft: "bg-muted text-muted-foreground",
  confirmed: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
  in_progress: "bg-yellow-500/10 text-yellow-600 dark:text-yellow-400",
  completed: "bg-green-500/10 text-green-600 dark:text-green-400",
  invoiced: "bg-purple-500/10 text-purple-600 dark:text-purple-400",
  cancelled: "bg-red-500/10 text-red-600 dark:text-red-400",
};

const WO_TRANSITIONS: Record<string, string[]> = {
  draft: ["confirmed", "cancelled"],
  confirmed: ["in_progress", "cancelled"],
  in_progress: ["completed", "cancelled"],
  completed: ["invoiced"],
};

const WO_PRIORITY_COLORS: Record<string, string> = {
  low: "bg-muted text-muted-foreground",
  medium: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
  high: "bg-yellow-500/10 text-yellow-600 dark:text-yellow-400",
  urgent: "bg-red-500/10 text-red-600 dark:text-red-400",
};

async function fetchClients(): Promise<Client[]> {
  const res = await apiClient.get<{ data: Client[] }>("/clients");
  const body = res.data as { data: Client[] };
  return body.data ?? (body as unknown as Client[]);
}

export default function WorkOrdersPage() {
  const [statusFilter, setStatusFilter] = useState<string>("all");
  const [clientFilter, setClientFilter] = useState<string>("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [debouncedSearch, setDebouncedSearch] = useState("");
  const [changeStatusWo, setChangeStatusWo] = useState<WorkOrder | null>(null);
  const [newStatus, setNewStatus] = useState<string>("");
  const [statusUpdating, setStatusUpdating] = useState(false);
  const queryClient = useQueryClient();

  useEffect(() => {
    const timer = setTimeout(() => setDebouncedSearch(searchQuery), 300);
    return () => clearTimeout(timer);
  }, [searchQuery]);

  const { data: workOrders = [], isLoading } = useQuery({
    queryKey: ["work-orders", debouncedSearch, statusFilter, clientFilter],
    queryFn: async () => {
      const params = new URLSearchParams();
      params.set("per_page", "100");
      if (debouncedSearch) params.set("search", debouncedSearch);
      if (statusFilter !== "all") params.set("status", statusFilter);
      if (clientFilter !== "all") params.set("client_id", clientFilter);
      const res = await apiClient.get<{ data: WorkOrder[] }>(
        `/work-orders?${params.toString()}`
      );
      const body = res.data as { data: WorkOrder[] };
      return body.data ?? (body as unknown as WorkOrder[]);
    },
  });

  const { data: clients = [] } = useQuery({
    queryKey: ["clients"],
    queryFn: fetchClients,
  });

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
              onClick={() => setChangeStatusWo(row.original)}
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
            <SelectTrigger className="w-full">
              <SelectValue placeholder="Status">
                {statusFilter && statusFilter !== "all"
                  ? (() => {
                      const labels: Record<string, string> = {
                        draft: "Draft",
                        confirmed: "Confirmed",
                        in_progress: "In Progress",
                        completed: "Completed",
                        invoiced: "Invoiced",
                        cancelled: "Cancelled",
                      };
                      return labels[statusFilter] ?? statusFilter.replace("_", " ");
                    })()
                  : null}
              </SelectValue>
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
            <SelectTrigger className="w-full">
              <SelectValue placeholder="Client">
                {clientFilter && clientFilter !== "all"
                  ? clients.find((c) => String(c.id) === clientFilter)?.name ?? null
                  : null}
              </SelectValue>
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
          data={workOrders}
          isLoading={isLoading}
          emptyMessage="No work orders found."
        />
      </div>
      <AlertDialog
        open={!!changeStatusWo}
        onOpenChange={(open) => {
          if (!open) {
            setChangeStatusWo(null);
            setNewStatus("");
          }
        }}
      >
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Change Status</AlertDialogTitle>
            <AlertDialogDescription>
              Select a new status for work order{" "}
              <strong>{changeStatusWo?.wo_number}</strong>.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <div className="py-4">
            <Select
              value={newStatus}
              onValueChange={(v) => setNewStatus(v ?? "")}
            >
              <SelectTrigger className="w-full">
                <SelectValue placeholder="Select new status">
                  {newStatus ? newStatus.replace("_", " ") : null}
                </SelectValue>
              </SelectTrigger>
              <SelectContent>
                {(WO_TRANSITIONS[changeStatusWo?.status ?? ""] ?? []).map(
                  (s) => (
                    <SelectItem key={s} value={s}>
                      <Badge
                        variant="outline"
                        className={WO_STATUS_COLORS[s] ?? "bg-muted"}
                      >
                        {s.replace("_", " ")}
                      </Badge>
                    </SelectItem>
                  )
                )}
              </SelectContent>
            </Select>
          </div>
          <AlertDialogFooter>
            <AlertDialogCancel disabled={statusUpdating}>
              Cancel
            </AlertDialogCancel>
            <Button
              disabled={!newStatus || statusUpdating}
              onClick={async () => {
                if (!changeStatusWo || !newStatus) return;
                setStatusUpdating(true);
                try {
                  await apiClient.patch(
                    `/work-orders/${changeStatusWo.id}/status`,
                    { status: newStatus }
                  );
                  toast.success(
                    `${changeStatusWo.wo_number} status updated to ${newStatus.replace("_", " ")}`
                  );
                  queryClient.invalidateQueries({ queryKey: ["work-orders"] });
                  setChangeStatusWo(null);
                  setNewStatus("");
                } catch {
                  toast.error("Failed to update status");
                } finally {
                  setStatusUpdating(false);
                }
              }}
            >
              {statusUpdating ? "Updating..." : "Confirm"}
            </Button>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
