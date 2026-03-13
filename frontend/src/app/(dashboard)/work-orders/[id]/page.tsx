"use client";

import { useParams, useRouter } from "next/navigation";
import Link from "next/link";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  Pencil,
  Check,
  Play,
  FileText,
  X,
  ArrowLeft,
} from "lucide-react";
import { apiClient } from "@/lib/api";
import type { WorkOrder } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button, buttonVariants } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
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
import { toast } from "sonner";

const WO_STATUS_COLORS: Record<string, string> = {
  draft: "bg-muted text-muted-foreground",
  confirmed: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
  in_progress: "bg-yellow-500/10 text-yellow-600 dark:text-yellow-400",
  completed: "bg-green-500/10 text-green-600 dark:text-green-400",
  invoiced: "bg-purple-500/10 text-purple-600 dark:text-purple-400",
  cancelled: "bg-red-500/10 text-red-600 dark:text-red-400",
};

async function fetchWorkOrder(id: string): Promise<WorkOrder> {
  const res = await apiClient.get<{ data: WorkOrder }>(`/work-orders/${id}`);
  const body = res.data as { data: WorkOrder };
  return body.data ?? (body as unknown as WorkOrder);
}

export default function WorkOrderDetailPage() {
  const params = useParams();
  const router = useRouter();
  const queryClient = useQueryClient();
  const id = params.id as string;

  const { data: workOrder, isLoading } = useQuery({
    queryKey: ["work-order", id],
    queryFn: () => fetchWorkOrder(id),
    enabled: !!id,
  });

  const statusMutation = useMutation({
    mutationFn: async (status: string) => {
      await apiClient.put(`/work-orders/${id}`, { status });
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["work-order", id] });
      toast.success("Status updated");
    },
    onError: (err: unknown) => {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response
              ?.data?.message
          : "Failed to update status";
      toast.error(typeof message === "string" ? message : "Failed to update status");
    },
  });

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-48" />
        <Skeleton className="h-64" />
      </div>
    );
  }

  if (!workOrder) {
    return null;
  }

  const canEdit =
    workOrder.status === "draft" || workOrder.status === "confirmed";

  return (
    <div className="space-y-6">
      <PageHeader
        title={workOrder.title}
        description={`${workOrder.wo_number} • ${workOrder.client?.name ?? "-"}`}
        children={
          <div className="flex items-center gap-2">
            <Button variant="outline" onClick={() => router.back()}>
              <ArrowLeft className="mr-2 size-4" />
              Back
            </Button>
            {canEdit && (
              <Link
                href={`/work-orders/${id}/edit`}
                className={buttonVariants()}
              >
                <Pencil className="mr-2 size-4" />
                Edit
              </Link>
            )}
          </div>
        }
      />
      <div className="flex items-center gap-4">
        <Badge
          variant="outline"
          className={WO_STATUS_COLORS[workOrder.status] ?? "bg-muted"}
        >
          {workOrder.status}
        </Badge>
        {workOrder.priority && (
          <Badge variant="outline">{workOrder.priority}</Badge>
        )}
      </div>
      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Work Order Info</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <p className="text-sm text-muted-foreground">Client</p>
                <p className="font-medium">
                  {workOrder.client?.name ?? "-"}
                </p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Category</p>
                <p className="font-medium">{workOrder.category ?? "-"}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Order Date</p>
                <p className="font-medium">
                  {formatDate(workOrder.order_date)}
                </p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Due Date</p>
                <p className="font-medium">
                  {workOrder.due_date
                    ? formatDate(workOrder.due_date)
                    : "-"}
                </p>
              </div>
            </div>
            {workOrder.description && (
              <div>
                <p className="text-sm text-muted-foreground">Description</p>
                <p className="font-medium whitespace-pre-wrap">
                  {workOrder.description}
                </p>
              </div>
            )}
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Totals</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Subtotal</span>
              <span>{formatCurrency(workOrder.total_before_tax ?? 0)}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Total Discount</span>
              <span>{formatCurrency(workOrder.total_discount ?? 0)}</span>
            </div>
            <div className="flex justify-between text-sm">
              <span className="text-muted-foreground">Total Tax</span>
              <span>{formatCurrency(workOrder.total_tax ?? 0)}</span>
            </div>
            <div className="flex justify-between font-semibold pt-2 border-t">
              <span>Grand Total</span>
              <span>{formatCurrency(workOrder.grand_total ?? 0)}</span>
            </div>
          </CardContent>
        </Card>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Line Items</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Description</TableHead>
                <TableHead>Qty</TableHead>
                <TableHead>Unit</TableHead>
                <TableHead className="text-right">Unit Price</TableHead>
                <TableHead className="text-right">Discount</TableHead>
                <TableHead className="text-right">Tax %</TableHead>
                <TableHead className="text-right">Subtotal</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {(workOrder.items ?? []).map((item) => (
                <TableRow key={item.id}>
                  <TableCell>{item.description}</TableCell>
                  <TableCell>{item.quantity}</TableCell>
                  <TableCell>{item.unit}</TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(item.unit_price)}
                  </TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(item.discount)}
                  </TableCell>
                  <TableCell className="text-right">{item.tax_rate}%</TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(item.subtotal)}
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
      {workOrder.status !== "cancelled" && (
        <Card>
          <CardHeader>
            <CardTitle>Actions</CardTitle>
          </CardHeader>
          <CardContent className="flex flex-wrap gap-2">
            {workOrder.status === "draft" && (
              <Button
                onClick={() => statusMutation.mutate("confirmed")}
                disabled={statusMutation.isPending}
              >
                <Check className="mr-2 size-4" />
                Confirm
              </Button>
            )}
            {workOrder.status === "confirmed" && (
              <Button
                onClick={() => statusMutation.mutate("in_progress")}
                disabled={statusMutation.isPending}
              >
                <Play className="mr-2 size-4" />
                Start
              </Button>
            )}
            {workOrder.status === "in_progress" && (
              <Button
                onClick={() => statusMutation.mutate("completed")}
                disabled={statusMutation.isPending}
              >
                <Check className="mr-2 size-4" />
                Complete
              </Button>
            )}
            {workOrder.status === "completed" && (
              <Button disabled>
                <FileText className="mr-2 size-4" />
                Create Invoice
              </Button>
            )}
            {workOrder.status !== "cancelled" && (
              <Button
                variant="destructive"
                onClick={() => statusMutation.mutate("cancelled")}
                disabled={statusMutation.isPending}
              >
                <X className="mr-2 size-4" />
                Cancel
              </Button>
            )}
          </CardContent>
        </Card>
      )}
    </div>
  );
}
