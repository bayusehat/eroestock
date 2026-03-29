"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { type ColumnDef } from "@tanstack/react-table";
import {
  MoreHorizontal,
  Pencil,
  Plus,
  Check,
  X,
  Eye,
} from "lucide-react";
import { apiClient } from "@/lib/api";
import { fetchFlattenedAccounts } from "@/lib/accounts";
import type { BudgetRequest } from "@/types";
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
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { formatCurrency, formatDate } from "@/lib/format";
import { toast } from "sonner";

const REQUEST_TYPES: Record<string, string> = {
  ops_budget: "Ops Budget",
  expense_approval: "Expense Approval",
  other: "Other",
};

const STATUS_COLORS: Record<string, string> = {
  pending: "bg-yellow-500/10 text-yellow-600 dark:text-yellow-400",
  approved: "bg-green-500/10 text-green-600 dark:text-green-400",
  rejected: "bg-red-500/10 text-red-600 dark:text-red-400",
};

async function fetchRequests(params: URLSearchParams): Promise<BudgetRequest[]> {
  const res = await apiClient.get<{ data: { data?: BudgetRequest[] } | BudgetRequest[] }>(
    `/budget-requests?${params.toString()}`
  );
  const body = res.data as { data?: { data?: BudgetRequest[] } | BudgetRequest[] };
  const inner = body.data;
  if (Array.isArray(inner)) return inner;
  return (inner as { data?: BudgetRequest[] })?.data ?? [];
}

export default function RequestsPage() {
  const [typeFilter, setTypeFilter] = useState<string>("all");
  const [statusFilter, setStatusFilter] = useState<string>("all");
  const [searchQuery, setSearchQuery] = useState("");
  const [debouncedSearch, setDebouncedSearch] = useState("");
  const [reviewRequest, setReviewRequest] = useState<BudgetRequest | null>(null);
  const [reviewStatus, setReviewStatus] = useState<"approved" | "rejected">("approved");
  const [reviewNotes, setReviewNotes] = useState("");
  const [reviewAccountId, setReviewAccountId] = useState<string>("");
  const [reviewSubmitting, setReviewSubmitting] = useState(false);
  const queryClient = useQueryClient();

  const { data: accounts = [] } = useQuery({
    queryKey: ["accounts"],
    queryFn: fetchFlattenedAccounts,
  });

  const leafAccounts = accounts.filter((a) => !a.is_header);

  useEffect(() => {
    const timer = setTimeout(() => setDebouncedSearch(searchQuery), 300);
    return () => clearTimeout(timer);
  }, [searchQuery]);

  const { data: requests = [], isLoading } = useQuery({
    queryKey: ["budget-requests", debouncedSearch, typeFilter, statusFilter],
    queryFn: async () => {
      const params = new URLSearchParams();
      params.set("per_page", "100");
      if (debouncedSearch) params.set("search", debouncedSearch);
      if (typeFilter !== "all") params.set("type", typeFilter);
      if (statusFilter !== "all") params.set("status", statusFilter);
      return fetchRequests(params);
    },
  });

  const handleReview = async () => {
    if (!reviewRequest) return;
    if (reviewStatus === "approved" && !reviewAccountId) {
      toast.error("Please select an account for budget allocation");
      return;
    }
    setReviewSubmitting(true);
    try {
      await apiClient.patch(`/budget-requests/${reviewRequest.id}/review`, {
        status: reviewStatus,
        review_notes: reviewNotes || undefined,
        account_id: reviewStatus === "approved" ? parseInt(reviewAccountId, 10) : undefined,
      });
      toast.success(`Request ${reviewStatus}`);
      setReviewRequest(null);
      setReviewNotes("");
      setReviewAccountId("");
      queryClient.invalidateQueries({ queryKey: ["budget-requests"] });
    } catch (err: unknown) {
      const msg =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data?.message
          : "Failed to review request";
      toast.error(typeof msg === "string" ? msg : "Failed to review request");
    } finally {
      setReviewSubmitting(false);
    }
  };

  const handleDelete = async (req: BudgetRequest) => {
    try {
      await apiClient.delete(`/budget-requests/${req.id}`);
      toast.success("Request deleted");
      queryClient.invalidateQueries({ queryKey: ["budget-requests"] });
    } catch (err: unknown) {
      const msg =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data?.message
          : "Failed to delete";
      toast.error(typeof msg === "string" ? msg : "Failed to delete");
    }
  };

  const columns: ColumnDef<BudgetRequest>[] = [
    {
      accessorKey: "request_no",
      header: "Request #",
      cell: ({ row }) => (
        <Link
          href={`/requests/${row.original.id}`}
          className="font-medium text-primary hover:underline"
        >
          {row.original.request_no}
        </Link>
      ),
    },
    {
      accessorKey: "type",
      header: "Type",
      cell: ({ row }) => (
        <span>{REQUEST_TYPES[row.original.type] ?? row.original.type}</span>
      ),
    },
    {
      accessorKey: "title",
      header: "Title",
    },
    {
      accessorKey: "amount",
      header: "Amount",
      cell: ({ row }) =>
        row.original.amount != null ? formatCurrency(row.original.amount) : "-",
    },
    {
      accessorKey: "status",
      header: "Status",
      cell: ({ row }) => (
        <Badge
          variant="outline"
          className={STATUS_COLORS[row.original.status] ?? ""}
        >
          {row.original.status}
        </Badge>
      ),
    },
    {
      accessorKey: "account",
      header: "Budget from",
      cell: ({ row }) =>
        row.original.account
          ? `${row.original.account.code} – ${row.original.account.name}`
          : "-",
    },
    {
      accessorKey: "created_by_user",
      header: "Requested by",
      cell: ({ row }) => row.original.created_by_user?.name ?? "-",
    },
    {
      accessorKey: "created_at",
      header: "Date",
      cell: ({ row }) => formatDate(row.original.created_at),
    },
    {
      id: "actions",
      cell: ({ row }) => {
        const req = row.original;
        return (
          <DropdownMenu>
            <DropdownMenuTrigger
              render={
                <Button variant="ghost" size="icon-sm">
                  <MoreHorizontal className="size-4" />
                  <span className="sr-only">Actions</span>
                </Button>
              }
            />
            <DropdownMenuContent align="end">
              <DropdownMenuItem
                render={
                  <Link href={`/requests/${req.id}`}>
                    <Eye className="mr-2 size-4" />
                    View
                  </Link>
                }
              />
              {req.status === "pending" && (
                <>
                  <DropdownMenuItem
                    onClick={() => {
                      setReviewRequest(req);
                      setReviewStatus("approved");
                      setReviewNotes("");
                      setReviewAccountId("");
                    }}
                  >
                    <Check className="mr-2 size-4" />
                    Approve
                  </DropdownMenuItem>
                  <DropdownMenuItem
                    onClick={() => {
                      setReviewRequest(req);
                      setReviewStatus("rejected");
                      setReviewNotes("");
                      setReviewAccountId("");
                    }}
                  >
                    <X className="mr-2 size-4" />
                    Reject
                  </DropdownMenuItem>
                  <DropdownMenuItem
                    render={
                      <Link href={`/requests/${req.id}/edit`}>
                        <Pencil className="mr-2 size-4" />
                        Edit
                      </Link>
                    }
                  />
                  <DropdownMenuItem
                    variant="destructive"
                    onClick={() => handleDelete(req)}
                  >
                    Delete
                  </DropdownMenuItem>
                </>
              )}
            </DropdownMenuContent>
          </DropdownMenu>
        );
      },
    },
  ];

  return (
    <div className="space-y-6">
      <PageHeader
        title="Requests"
        description="Budget and ops requests"
        children={
          <Link href="/requests/create" className={buttonVariants()}>
            <Plus className="mr-2 size-4" />
            New Request
          </Link>
        }
      />
      <div className="flex flex-col gap-4 sm:flex-row">
        <Input
          placeholder="Search requests..."
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          className="max-w-sm"
        />
        <Select value={typeFilter} onValueChange={(v) => setTypeFilter(v ?? "all")}>
          <SelectTrigger className="w-[180px]">
            <SelectValue placeholder="Type">
              {typeFilter && typeFilter !== "all"
                ? REQUEST_TYPES[typeFilter] ?? typeFilter
                : null}
            </SelectValue>
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All types</SelectItem>
            {Object.entries(REQUEST_TYPES).map(([value, label]) => (
              <SelectItem key={value} value={value}>
                {label}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
        <Select
          value={statusFilter}
          onValueChange={(v: string | null) => setStatusFilter(v ?? "all")}
        >
          <SelectTrigger className="w-[180px]">
            <SelectValue placeholder="Status">
              {statusFilter && statusFilter !== "all"
                ? (() => {
                    const labels: Record<string, string> = {
                      pending: "Pending",
                      approved: "Approved",
                      rejected: "Rejected",
                    };
                    return labels[statusFilter] ?? statusFilter;
                  })()
                : null}
            </SelectValue>
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All status</SelectItem>
            <SelectItem value="pending">Pending</SelectItem>
            <SelectItem value="approved">Approved</SelectItem>
            <SelectItem value="rejected">Rejected</SelectItem>
          </SelectContent>
        </Select>
      </div>
      <DataTable
        columns={columns}
        data={requests}
        isLoading={isLoading}
        emptyMessage="No requests found."
      />

      <Dialog open={!!reviewRequest} onOpenChange={(o) => !o && setReviewRequest(null)}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>
              {reviewStatus === "approved" ? "Approve" : "Reject"} Request
            </DialogTitle>
          </DialogHeader>
          {reviewRequest && (
            <div className="space-y-4">
              <p className="text-sm text-muted-foreground">
                {reviewRequest.request_no} – {reviewRequest.title}
              </p>
              {reviewStatus === "approved" && (
                <div className="space-y-2">
                  <Label htmlFor="review_account">Budget from account *</Label>
                  <Select
                    value={reviewAccountId}
                    onValueChange={(v) => setReviewAccountId(v ?? "")}
                  >
                    <SelectTrigger className="w-full">
                      <SelectValue placeholder="Select account">
                        {reviewAccountId
                          ? (() => {
                              const a = leafAccounts.find(
                                (acc) => String(acc.id) === reviewAccountId
                              );
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
                  <p className="text-xs text-muted-foreground">
                    Select the account to allocate budget from
                  </p>
                </div>
              )}
              <div className="space-y-2">
                <Label htmlFor="review_notes">Notes (optional)</Label>
                <Textarea
                  id="review_notes"
                  value={reviewNotes}
                  onChange={(e) => setReviewNotes(e.target.value)}
                  placeholder="Add review notes..."
                  rows={3}
                />
              </div>
            </div>
          )}
          <DialogFooter>
            <Button variant="outline" onClick={() => setReviewRequest(null)}>
              Cancel
            </Button>
            <Button
              variant={reviewStatus === "rejected" ? "destructive" : "default"}
              onClick={handleReview}
              disabled={
                reviewSubmitting ||
                (reviewStatus === "approved" && !reviewAccountId)
              }
            >
              {reviewSubmitting ? "Processing..." : reviewStatus === "approved" ? "Approve" : "Reject"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
