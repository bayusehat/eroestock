"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { useForm, Controller } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useQuery } from "@tanstack/react-query";
import { Loader2 } from "lucide-react";
import { apiClient } from "@/lib/api";
import { fetchFlattenedAccounts } from "@/lib/accounts";
import { PageHeader } from "@/components/page-header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { CurrencyInput } from "@/components/ui/currency-input";
import { Label } from "@/components/ui/label";
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
import { DatePicker } from "@/components/ui/date-picker";
import { ClientSelect } from "@/components/client-select";
import { VendorSelect } from "@/components/vendor-select";
import { toast } from "sonner";

const createTransactionSchema = z.object({
  type: z.enum(["income", "expense", "transfer"]),
  date: z.string().min(1, "Date is required"),
  amount: z.number().min(0.01, "Amount is required"),
  account_id: z.number().min(1, "Account is required"),
  contra_account_id: z.number().min(1, "Contra account is required"),
  description: z.string().optional(),
  reference_no: z.string().optional(),
  payment_method: z.string().optional(),
  category: z.string().optional(),
  client_id: z.number().optional(),
  vendor_id: z.number().optional(),
}).refine(
  (data) => data.account_id !== data.contra_account_id,
  { message: "Account and contra account must be different", path: ["contra_account_id"] }
);

type CreateTransactionForm = z.infer<typeof createTransactionSchema>;

const PAYMENT_METHODS = ["Cash", "Bank Transfer", "Check", "Credit Card", "Debit Card", "Other"];

export default function CreateTransactionPage() {
  const router = useRouter();
  const [txType, setTxType] = useState<"income" | "expense" | "transfer">("expense");

  const { data: accounts = [] } = useQuery({
    queryKey: ["accounts"],
    queryFn: fetchFlattenedAccounts,
  });

  const {
    register,
    control,
    handleSubmit,
    watch,
    setValue,
    formState: { errors, isSubmitting },
  } = useForm<CreateTransactionForm>({
    resolver: zodResolver(createTransactionSchema),
    defaultValues: {
      type: "expense",
      date: new Date().toISOString().split("T")[0] ?? "",
      amount: 0,
      account_id: 0 as unknown as number,
      contra_account_id: 0 as unknown as number,
      description: "",
      reference_no: "",
      payment_method: "",
      category: "",
      client_id: undefined,
      vendor_id: undefined,
    },
  });

  async function onSubmit(data: CreateTransactionForm) {
    try {
      const payload = {
        type: data.type,
        date: data.date,
        amount:
          data.type === "expense" ? -Math.abs(data.amount) : Math.abs(data.amount),
        account_id: data.account_id,
        contra_account_id: data.contra_account_id,
        description: data.description || undefined,
        reference_no: data.reference_no || undefined,
        payment_method: data.payment_method ? data.payment_method : undefined,
        category: data.category || undefined,
        client_id: data.client_id,
        vendor_id: data.vendor_id,
      };
      await apiClient.post("/transactions", payload);
      toast.success("Transaction recorded successfully");
      router.push("/transactions");
    } catch (err: unknown) {
      let message = "Failed to record transaction";
      if (err && typeof err === "object" && "response" in err) {
        const data = (err as { response?: { data?: unknown } }).response?.data;
        if (data && typeof data === "object") {
          const d = data as { message?: string; errors?: Record<string, string[]> };
          if (typeof d.message === "string") message = d.message;
          else if (d.errors && typeof d.errors === "object") {
            const first = Object.values(d.errors).flat()[0];
            if (typeof first === "string") message = first;
          }
        }
      }
      toast.error(message);
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="Record Transaction"
        description="Add a new transaction"
      />
      <form onSubmit={handleSubmit(onSubmit)}>
        <Card>
          <CardHeader>
            <CardTitle>Transaction Type</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="flex gap-2">
              {(["income", "expense", "transfer"] as const).map((type) => (
                <Button
                  key={type}
                  type="button"
                  variant={txType === type ? "default" : "outline"}
                  onClick={() => {
                    setTxType(type);
                    setValue("type", type);
                  }}
                >
                  {type.charAt(0).toUpperCase() + type.slice(1)}
                </Button>
              ))}
            </div>
          </CardContent>
        </Card>
        <Card className="mt-6">
          <CardHeader>
            <CardTitle>Transaction Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <Label>Date *</Label>
                <DatePicker
                  value={watch("date")}
                  onChange={(v) => setValue("date", v)}
                  placeholder="Select date"
                />
                {errors.date && (
                  <p className="text-sm text-destructive">
                    {errors.date.message}
                  </p>
                )}
              </div>
              <div className="space-y-2">
                <Label htmlFor="amount">Amount *</Label>
                <Controller
                  name="amount"
                  control={control}
                  render={({ field }) => (
                    <CurrencyInput
                      id="amount"
                      value={field.value}
                      onChange={field.onChange}
                      aria-invalid={!!errors.amount}
                    />
                  )}
                />
                {errors.amount && (
                  <p className="text-sm text-destructive">
                    {errors.amount.message}
                  </p>
                )}
              </div>
            </div>
            <div className="space-y-2">
              <Label>Account *</Label>
              <Select
                value={watch("account_id") ? String(watch("account_id")) : ""}
                onValueChange={(v) => setValue("account_id", parseInt(v ?? "0", 10))}
              >
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select account">
                    {watch("account_id")
                      ? (() => {
                          const a = accounts.find((acc) => acc.id === watch("account_id"));
                          return a ? `${a.code} - ${a.name}` : null;
                        })()
                      : null}
                  </SelectValue>
                </SelectTrigger>
                <SelectContent>
                  {accounts
                    .filter((a) => !a.is_header && a.id !== watch("contra_account_id"))
                    .map((a) => (
                      <SelectItem key={a.id} value={String(a.id)}>
                        {a.code} - {a.name}
                      </SelectItem>
                    ))}
                </SelectContent>
              </Select>
              {errors.account_id && (
                <p className="text-sm text-destructive">
                  {errors.account_id.message}
                </p>
              )}
            </div>
            <div className="space-y-2">
              <Label>Contra Account *</Label>
              <Select
                value={
                  watch("contra_account_id")
                    ? String(watch("contra_account_id"))
                    : ""
                }
                onValueChange={(v) =>
                  setValue("contra_account_id", v ? parseInt(v, 10) : 0)
                }
              >
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select contra account">
                    {watch("contra_account_id")
                      ? (() => {
                          const a = accounts.find(
                            (acc) => acc.id === watch("contra_account_id")
                          );
                          return a ? `${a.code} - ${a.name}` : null;
                        })()
                      : null}
                  </SelectValue>
                </SelectTrigger>
                <SelectContent>
                  {accounts
                    .filter((a) => !a.is_header && a.id !== watch("account_id"))
                    .map((a) => (
                      <SelectItem key={a.id} value={String(a.id)}>
                        {a.code} - {a.name}
                      </SelectItem>
                    ))}
                </SelectContent>
              </Select>
              {errors.contra_account_id && (
                <p className="text-sm text-destructive">
                  {errors.contra_account_id.message}
                </p>
              )}
            </div>
            <div className="space-y-2">
              <Label htmlFor="description">Description</Label>
              <Input id="description" {...register("description")} />
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor="reference_no">Reference Number</Label>
                <Input id="reference_no" {...register("reference_no")} />
              </div>
              <div className="space-y-2">
                <Label>Payment Method</Label>
                <Select
                  value={watch("payment_method") || ""}
                  onValueChange={(v) => setValue("payment_method", v ?? "")}
                >
                  <SelectTrigger className="w-full">
                    <SelectValue placeholder="Select method" />
                  </SelectTrigger>
                  <SelectContent>
                    {PAYMENT_METHODS.map((m) => (
                      <SelectItem key={m} value={m}>
                        {m}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            </div>
            <div className="w-full space-y-2">
              <Label htmlFor="category">Category</Label>
              <Input id="category" {...register("category")} />
            </div>
            {txType === "income" && (
              <div className="w-full space-y-2">
                <Label>Client</Label>
                <ClientSelect
                  value={watch("client_id") ?? null}
                  onChange={(id) => setValue("client_id", id ?? undefined)}
                />
              </div>
            )}
            {txType === "expense" && (
              <div className="w-full space-y-2">
                <Label>Vendor</Label>
                <VendorSelect
                  value={watch("vendor_id") ?? null}
                  onChange={(id) => setValue("vendor_id", id ?? undefined)}
                />
              </div>
            )}
          </CardContent>
          <CardFooter>
            <Button
              type="button"
              variant="outline"
              onClick={() => router.back()}
            >
              Cancel
            </Button>
            <Button type="submit" disabled={isSubmitting}>
              {isSubmitting ? (
                <>
                  <Loader2 className="mr-2 size-4 animate-spin" />
                  Saving...
                </>
              ) : (
                "Record Transaction"
              )}
            </Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  );
}
