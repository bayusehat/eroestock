"use client";

import { useParams } from "next/navigation";
import Link from "next/link";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { ArrowLeft, Pencil, Check, X } from "lucide-react";
import { apiClient } from "@/lib/api";
import { fetchFlattenedAccounts } from "@/lib/accounts";
import type { BudgetRequest } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button, buttonVariants } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { formatCurrency, formatDate } from "@/lib/format";
import { Skeleton } from "@/components/ui/skeleton";
import { useState } from "react";
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

async function fetchRequest(id: string): Promise<BudgetRequest> {
  const res = await apiClient.get<{ data: BudgetRequest }>(`/budget-requests/${id}`);
  const body = res.data as { data: BudgetRequest };
  return body.data ?? (body as unknown as BudgetRequest);
}

export default function RequestDetailPage() {
  const params = useParams();
  const queryClient = useQueryClient();
  const id = params.id as string;
  const [reviewDialogOpen, setReviewDialogOpen] = useState(false);
  const [reviewStatus, setReviewStatus] = useState<"approved" | "rejected">("approved");
  const [reviewNotes, setReviewNotes] = useState("");
  const [reviewAccountId, setReviewAccountId] = useState("");
  const [reviewSubmitting, setReviewSubmitting] = useState(false);

  const { data: accounts = [] } = useQuery({
    queryKey: ["accounts"],
    queryFn: fetchFlattenedAccounts,
  });
  const leafAccounts = accounts.filter((a) => !a.is_header);

  const { data: request, isLoading } = useQuery({
    queryKey: ["budget-request", id],
    queryFn: () => fetchRequest(id),
    enabled: !!id,
  });

  const handleReview = async () => {
    if (!request) return;
    if (reviewStatus === "approved" && !reviewAccountId) {
      toast.error("Please select an account for budget allocation");
      return;
    }
    setReviewSubmitting(true);
    try {
      await apiClient.patch(`/budget-requests/${request.id}/review`, {
        status: reviewStatus,
        review_notes: reviewNotes || undefined,
        account_id: reviewStatus === "approved" ? parseInt(reviewAccountId, 10) : undefined,
      });
      toast.success(`Request ${reviewStatus}`);
      setReviewDialogOpen(false);
      setReviewNotes("");
      setReviewAccountId("");
      queryClient.invalidateQueries({ queryKey: ["budget-request", id] });
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

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-64" />
      </div>
    );
  }

  if (!request) {
    return (
      <div className="space-y-6">
        <p className="text-muted-foreground">Request not found.</p>
        <Link href="/requests" className={buttonVariants({ variant: "outline" })}>
          Back to Requests
        </Link>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title={request.request_no}
        description={request.title}
        children={
          <div className="flex gap-2">
            <Link href="/requests" className={buttonVariants({ variant: "outline" })}>
              <ArrowLeft className="mr-2 size-4" />
              Back
            </Link>
            {request.status === "pending" && (
              <>
                <Button
                  variant="outline"
                  onClick={() => {
                    setReviewStatus("approved");
                    setReviewNotes("");
                    setReviewAccountId("");
                    setReviewDialogOpen(true);
                  }}
                >
                  <Check className="mr-2 size-4" />
                  Approve
                </Button>
                <Button
                  variant="destructive"
                  onClick={() => {
                    setReviewStatus("rejected");
                    setReviewNotes("");
                    setReviewAccountId("");
                    setReviewDialogOpen(true);
                  }}
                >
                  <X className="mr-2 size-4" />
                  Reject
                </Button>
                <Link
                  href={`/requests/${request.id}/edit`}
                  className={buttonVariants()}
                >
                  <Pencil className="mr-2 size-4" />
                  Edit
                </Link>
              </>
            )}
          </div>
        }
      />

      <Card>
        <CardHeader>
          <div className="flex items-center justify-between">
            <CardTitle>Details</CardTitle>
            <Badge
              variant="outline"
              className={STATUS_COLORS[request.status] ?? ""}
            >
              {request.status}
            </Badge>
          </div>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <p className="text-sm text-muted-foreground">Type</p>
              <p className="font-medium">{REQUEST_TYPES[request.type] ?? request.type}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Amount</p>
              <p className="font-medium">
                {request.amount != null ? formatCurrency(request.amount) : "-"}
              </p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Requested by</p>
              <p className="font-medium">{request.created_by_user?.name ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Date</p>
              <p className="font-medium">{formatDate(request.created_at)}</p>
            </div>
          </div>
          {request.description && (
            <div>
              <p className="text-sm text-muted-foreground">Description</p>
              <p className="whitespace-pre-wrap">{request.description}</p>
            </div>
          )}
          {request.status !== "pending" && request.reviewed_by_user && (
            <div className="border-t pt-4 space-y-2">
              <p className="text-sm text-muted-foreground">Reviewed by</p>
              <p className="font-medium">{request.reviewed_by_user.name}</p>
              <p className="text-sm text-muted-foreground">
                {request.reviewed_at ? formatDate(request.reviewed_at) : ""}
              </p>
              {request.status === "approved" && request.account && (
                <div>
                  <p className="text-sm text-muted-foreground">Budget from account</p>
                  <p className="font-medium">
                    {request.account.code} – {request.account.name}
                  </p>
                </div>
              )}
              {request.review_notes && (
                <p className="mt-2 whitespace-pre-wrap">{request.review_notes}</p>
              )}
            </div>
          )}
        </CardContent>
      </Card>

      <Dialog open={reviewDialogOpen} onOpenChange={setReviewDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>
              {reviewStatus === "approved" ? "Approve" : "Reject"} Request
            </DialogTitle>
          </DialogHeader>
          <div className="space-y-4">
            <p className="text-sm text-muted-foreground">
              {request.request_no} – {request.title}
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
          <DialogFooter>
            <Button variant="outline" onClick={() => setReviewDialogOpen(false)}>
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
