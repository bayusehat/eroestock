"use client";

import React, { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { ChevronDown, ChevronRight, Download } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { AuditLog, PaginatedResponse, User } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { DatePicker } from "@/components/ui/date-picker";
import { Skeleton } from "@/components/ui/skeleton";
import { formatDateTime } from "@/lib/format";
import { cn } from "@/lib/utils";

const ACTION_COLORS: Record<string, string> = {
  create: "bg-green-500/10 text-green-600 dark:text-green-400",
  update: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
  delete: "bg-red-500/10 text-red-600 dark:text-red-400",
  login: "bg-muted text-muted-foreground",
};

function getDefaultDateRange() {
  const now = new Date();
  const start = new Date(now.getFullYear(), now.getMonth(), 1);
  const end = new Date();
  return {
    from: start.toISOString().split("T")[0] ?? "",
    to: end.toISOString().split("T")[0] ?? "",
  };
}

async function fetchAuditLogs(params: {
  page?: number;
  user_id?: number;
  module?: string;
  action?: string;
  date_from?: string;
  date_to?: string;
}): Promise<PaginatedResponse<AuditLog>> {
  const res = await apiClient.get<{ data: PaginatedResponse<AuditLog> } | PaginatedResponse<AuditLog>>("/audit-logs", {
    params,
  });
  const body = res.data as { data?: PaginatedResponse<AuditLog> } & PaginatedResponse<AuditLog>;
  return body.data ?? body;
}

async function fetchUsers(): Promise<User[]> {
  const res = await apiClient.get<{ data: User[] }>("/users");
  const body = res.data as { data: User[] };
  return body.data ?? (body as unknown as User[]);
}

function ChangesSummary({ log }: { log: AuditLog }) {
  const oldV = log.old_values ?? {};
  const newV = log.new_values ?? {};
  const keys = new Set([...Object.keys(oldV), ...Object.keys(newV)]);
  const changed = [...keys].filter((k) => {
    const o = JSON.stringify(oldV[k]);
    const n = JSON.stringify(newV[k]);
    return o !== n;
  });
  if (changed.length === 0 && log.action !== "delete") return null;
  return (
    <span className="text-muted-foreground">
      {log.action === "delete"
        ? "Record deleted"
        : changed.length === 1
          ? changed[0]
          : `${changed.length} fields changed`}
    </span>
  );
}

function JsonDiff({ oldVal, newVal }: { oldVal: unknown; newVal: unknown }) {
  const oStr = JSON.stringify(oldVal, null, 2);
  const nStr = JSON.stringify(newVal, null, 2);
  const isSame = oStr === nStr;
  return (
    <div className="grid gap-4 text-xs font-mono sm:grid-cols-2">
      <div>
        <p className="mb-1 font-semibold text-muted-foreground">Old</p>
        <pre
          className={cn(
            "overflow-auto rounded border p-2",
            !isSame && "border-red-200 bg-red-50/50 dark:border-red-900 dark:bg-red-950/20"
          )}
        >
          {oStr || "—"}
        </pre>
      </div>
      <div>
        <p className="mb-1 font-semibold text-muted-foreground">New</p>
        <pre
          className={cn(
            "overflow-auto rounded border p-2",
            !isSame && "border-green-200 bg-green-50/50 dark:border-green-900 dark:bg-green-950/20"
          )}
        >
          {nStr || "—"}
        </pre>
      </div>
    </div>
  );
}

export default function AuditLogsPage() {
  const [page, setPage] = useState(1);
  const [userId, setUserId] = useState<string>("all");
  const [module, setModule] = useState<string>("all");
  const [action, setAction] = useState<string>("all");
  const [range, setRange] = useState(getDefaultDateRange());
  const [expandedId, setExpandedId] = useState<number | null>(null);

  const { data: users = [] } = useQuery({
    queryKey: ["users"],
    queryFn: fetchUsers,
  });

  const { data, isLoading } = useQuery({
    queryKey: [
      "audit-logs",
      page,
      userId,
      module,
      action,
      range.from,
      range.to,
    ],
    queryFn: () =>
      fetchAuditLogs({
        page,
        user_id: userId !== "all" ? parseInt(userId, 10) : undefined,
        module: module !== "all" ? module : undefined,
        action: action !== "all" ? action : undefined,
        date_from: range.from || undefined,
        date_to: range.to || undefined,
      }),
  });

  const handleExportCsv = async () => {
    try {
      const res = await apiClient.get("/audit-logs/export", {
        params: {
          user_id: userId !== "all" ? userId : undefined,
          module: module !== "all" ? module : undefined,
          action: action !== "all" ? action : undefined,
          date_from: range.from || undefined,
          date_to: range.to || undefined,
        },
        responseType: "blob",
      });
      const blob = new Blob([res.data as Blob], { type: "text/csv" });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = `audit-logs-${range.from}-${range.to}.csv`;
      link.click();
      URL.revokeObjectURL(link.href);
    } catch {
      // Fallback: build CSV from current page data
      const logs = data?.data ?? [];
      const headers = ["Timestamp", "User", "Action", "Module", "Record ID", "Changes"];
      const rows = logs.map((l) => [
        formatDateTime(l.created_at),
        l.user?.name ?? "-",
        l.action,
        l.module,
        l.record_id ?? "",
        Object.keys(l.new_values ?? l.old_values ?? {}).join(", "),
      ]);
      const csv = [headers.join(","), ...rows.map((r) => r.map((c) => `"${c}"`).join(","))].join("\n");
      const blob = new Blob([csv], { type: "text/csv" });
      const link = document.createElement("a");
      link.href = URL.createObjectURL(blob);
      link.download = `audit-logs-${range.from}-${range.to}.csv`;
      link.click();
      URL.revokeObjectURL(link.href);
    }
  };

  const logs = data?.data ?? [];
  const meta = data?.meta;
  const totalPages = meta?.last_page ?? 1;

  return (
    <div className="space-y-6">
      <PageHeader
        title="Audit Logs"
        description="System activity and change history"
        children={
          <Button variant="outline" onClick={handleExportCsv}>
            <Download className="mr-2 size-4" />
            Export CSV
          </Button>
        }
      />
      <div className="flex flex-wrap items-end gap-4">
        <div className="space-y-1">
          <label className="text-sm text-muted-foreground">User</label>
          <Select value={userId} onValueChange={(v) => setUserId(v ?? "all")}>
            <SelectTrigger className="w-[160px]">
              <SelectValue placeholder="All users" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All users</SelectItem>
              {users.map((u) => (
                <SelectItem key={u.id} value={String(u.id)}>
                  {u.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
        <div className="space-y-1">
          <label className="text-sm text-muted-foreground">Module</label>
          <Select value={module} onValueChange={(v) => setModule(v ?? "all")}>
            <SelectTrigger className="w-[140px]">
              <SelectValue placeholder="All modules" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All modules</SelectItem>
              <SelectItem value="invoices">Invoices</SelectItem>
              <SelectItem value="transactions">Transactions</SelectItem>
              <SelectItem value="accounts">Accounts</SelectItem>
              <SelectItem value="clients">Clients</SelectItem>
              <SelectItem value="vendors">Vendors</SelectItem>
              <SelectItem value="work_orders">Work Orders</SelectItem>
              <SelectItem value="payroll">Payroll</SelectItem>
              <SelectItem value="users">Users</SelectItem>
              <SelectItem value="roles">Roles</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div className="space-y-1">
          <label className="text-sm text-muted-foreground">Action</label>
          <Select value={action} onValueChange={(v) => setAction(v ?? "all")}>
            <SelectTrigger className="w-[120px]">
              <SelectValue placeholder="All actions" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All</SelectItem>
              <SelectItem value="create">Create</SelectItem>
              <SelectItem value="update">Update</SelectItem>
              <SelectItem value="delete">Delete</SelectItem>
              <SelectItem value="login">Login</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div className="space-y-1">
          <label className="text-sm text-muted-foreground">From</label>
          <DatePicker
            value={range.from}
            onChange={(v) => setRange((r) => ({ ...r, from: v }))}
            placeholder="From"
            className="w-[140px]"
          />
        </div>
        <div className="space-y-1">
          <label className="text-sm text-muted-foreground">To</label>
          <DatePicker
            value={range.to}
            onChange={(v) => setRange((r) => ({ ...r, to: v }))}
            placeholder="To"
            className="w-[140px]"
          />
        </div>
      </div>
      <div className="rounded-md border">
        {isLoading ? (
          <Skeleton className="h-96 w-full" />
        ) : (
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead className="w-8" />
                <TableHead>Timestamp</TableHead>
                <TableHead>User</TableHead>
                <TableHead>Action</TableHead>
                <TableHead>Module</TableHead>
                <TableHead>Record ID</TableHead>
                <TableHead>Changes Summary</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {logs.map((log) => (
                <React.Fragment key={log.id}>
                  <TableRow
                    key={log.id}
                    className="cursor-pointer hover:bg-muted/50"
                    onClick={() =>
                      setExpandedId((prev) => (prev === log.id ? null : log.id))
                    }
                  >
                    <TableCell>
                      {expandedId === log.id ? (
                        <ChevronDown className="size-4" />
                      ) : (
                        <ChevronRight className="size-4" />
                      )}
                    </TableCell>
                    <TableCell>{formatDateTime(log.created_at)}</TableCell>
                    <TableCell>{log.user?.name ?? "-"}</TableCell>
                    <TableCell>
                      <Badge
                        variant="outline"
                        className={
                          ACTION_COLORS[log.action] ?? "bg-muted text-muted-foreground"
                        }
                      >
                        {log.action}
                      </Badge>
                    </TableCell>
                    <TableCell>{log.module}</TableCell>
                    <TableCell>{log.record_id ?? "-"}</TableCell>
                    <TableCell>
                      <ChangesSummary log={log} />
                    </TableCell>
                  </TableRow>
                  {expandedId === log.id && (
                    <TableRow>
                      <TableCell colSpan={7} className="bg-muted/30 p-4">
                        <JsonDiff
                          oldVal={log.old_values}
                          newVal={log.new_values}
                        />
                      </TableCell>
                    </TableRow>
                  )}
                </React.Fragment>
              ))}
              {logs.length === 0 && (
                <TableRow>
                  <TableCell colSpan={7} className="h-24 text-center text-muted-foreground">
                    No audit logs found
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        )}
      </div>
      {meta && totalPages > 1 && (
        <div className="flex items-center justify-between">
          <p className="text-sm text-muted-foreground">
            Page {meta.current_page} of {totalPages} ({meta.total} total)
          </p>
          <div className="flex gap-2">
            <Button
              variant="outline"
              size="sm"
              onClick={() => setPage((p) => Math.max(1, p - 1))}
              disabled={page <= 1}
            >
              Previous
            </Button>
            <Button
              variant="outline"
              size="sm"
              onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
              disabled={page >= totalPages}
            >
              Next
            </Button>
          </div>
        </div>
      )}
    </div>
  );
}
