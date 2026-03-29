"use client";

import { useEffect } from "react";
import { useParams, useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import { Loader2 } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { BudgetRequest } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { CurrencyInput } from "@/components/ui/currency-input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { toast } from "sonner";
import Link from "next/link";
import { buttonVariants } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";

const REQUEST_TYPES = [
  { value: "ops_budget", label: "Ops Budget" },
  { value: "expense_approval", label: "Expense Approval" },
  { value: "other", label: "Other" },
];

const schema = z.object({
  type: z.enum(["ops_budget", "expense_approval", "other"]),
  title: z.string().min(1, "Title is required"),
  description: z.string().optional(),
  amount: z.number().min(0).optional().nullable(),
});

type FormData = z.infer<typeof schema>;

async function fetchRequest(id: string): Promise<BudgetRequest> {
  const res = await apiClient.get<{ data: BudgetRequest }>(`/budget-requests/${id}`);
  const body = res.data as { data: BudgetRequest };
  return body.data ?? (body as unknown as BudgetRequest);
}

export default function EditRequestPage() {
  const params = useParams();
  const router = useRouter();
  const queryClient = useQueryClient();
  const id = params.id as string;

  const { data: request, isLoading } = useQuery({
    queryKey: ["budget-request", id],
    queryFn: () => fetchRequest(id),
    enabled: !!id,
  });

  const {
    register,
    handleSubmit,
    watch,
    setValue,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: {
      type: "ops_budget",
      title: "",
      description: "",
      amount: null,
    },
  });

  useEffect(() => {
    if (request) {
      reset({
        type: request.type as FormData["type"],
        title: request.title,
        description: request.description ?? "",
        amount: request.amount ?? null,
      });
    }
  }, [request, reset]);

  async function onSubmit(data: FormData) {
    try {
      await apiClient.put(`/budget-requests/${id}`, {
        type: data.type,
        title: data.title,
        description: data.description || undefined,
        amount: data.amount ?? undefined,
      });
      toast.success("Request updated");
      queryClient.invalidateQueries({ queryKey: ["budget-request", id] });
      queryClient.invalidateQueries({ queryKey: ["budget-requests"] });
      router.push(`/requests/${id}`);
    } catch (err: unknown) {
      const msg =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data?.message
          : "Failed to update request";
      toast.error(typeof msg === "string" ? msg : "Failed to update request");
    }
  }

  if (isLoading || !request) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-64" />
      </div>
    );
  }

  if (request.status !== "pending") {
    return (
      <div className="space-y-6">
        <p className="text-muted-foreground">Can only edit pending requests.</p>
        <Link href={`/requests/${id}`} className={buttonVariants({ variant: "outline" })}>
          Back to Request
        </Link>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title={`Edit ${request.request_no}`}
        description={request.title}
        children={
          <Link href={`/requests/${id}`} className={buttonVariants({ variant: "outline" })}>
            Back
          </Link>
        }
      />
      <form onSubmit={handleSubmit(onSubmit)}>
        <Card>
          <CardHeader>
            <CardTitle>Request Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label>Type *</Label>
              <Select
                value={watch("type")}
                onValueChange={(v) => setValue("type", v as FormData["type"])}
              >
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select type">
                    {watch("type")
                      ? REQUEST_TYPES.find((t) => t.value === watch("type"))?.label ?? null
                      : null}
                  </SelectValue>
                </SelectTrigger>
                <SelectContent>
                  {REQUEST_TYPES.map((t) => (
                    <SelectItem key={t.value} value={t.value}>
                      {t.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label htmlFor="title">Title *</Label>
              <Input
                id="title"
                {...register("title")}
                placeholder="e.g. Q2 Ops Budget Request"
              />
              {errors.title && (
                <p className="text-sm text-destructive">{errors.title.message}</p>
              )}
            </div>
            <div className="space-y-2">
              <Label htmlFor="description">Description</Label>
              <Textarea
                id="description"
                {...register("description")}
                placeholder="Describe your request..."
                rows={4}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="amount">Amount (optional)</Label>
              <CurrencyInput
                value={watch("amount") ?? 0}
                onChange={(v) => setValue("amount", v)}
              />
            </div>
          </CardContent>
          <CardFooter>
            <Button type="submit" disabled={isSubmitting}>
              {isSubmitting ? (
                <>
                  <Loader2 className="mr-2 size-4 animate-spin" />
                  Saving...
                </>
              ) : (
                "Save Changes"
              )}
            </Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  );
}
