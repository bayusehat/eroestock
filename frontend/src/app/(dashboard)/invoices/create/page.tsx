"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { useForm, useFieldArray, Controller } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useQuery } from "@tanstack/react-query";
import { ClipboardList, Loader2, Plus, Trash2 } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { WorkOrder } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { CurrencyInput } from "@/components/ui/currency-input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Checkbox } from "@/components/ui/checkbox";
import { ClientSelect } from "@/components/client-select";
import { DatePicker } from "@/components/ui/date-picker";
import { formatCurrency, formatDate } from "@/lib/format";
import { toast } from "sonner";

const lineItemSchema = z.object({
  description: z.string().min(1, "Required"),
  quantity: z.number().min(0.01, "Required"),
  unit: z.string().min(1, "Required"),
  unit_price: z.number().min(0),
  discount: z.number().min(0),
  tax_rate: z.number().min(0).max(100),
});

const createInvoiceSchema = z.object({
  client_id: z.number().min(1, "Client is required"),
  work_order_id: z.number().nullable().optional(),
  issue_date: z.string().min(1, "Issue date is required"),
  due_date: z.string().min(1, "Due date is required"),
  notes: z.string().optional(),
  terms: z.string().optional(),
  items: z.array(lineItemSchema).min(1, "At least one item required"),
});

type CreateInvoiceForm = z.infer<typeof createInvoiceSchema>;

function calcSubtotal(
  qty: number,
  unitPrice: number,
  discount: number,
  taxRate: number
) {
  const beforeTax = qty * unitPrice - discount;
  const tax = beforeTax * (taxRate / 100);
  return beforeTax + tax;
}

export default function CreateInvoicePage() {
  const router = useRouter();
  const [woDialogOpen, setWoDialogOpen] = useState(false);
  const [woSearch, setWoSearch] = useState("");
  const [selectedWoId, setSelectedWoId] = useState<number | null>(null);

  const {
    register,
    handleSubmit,
    control,
    watch,
    setValue,
    formState: { errors, isSubmitting },
  } = useForm<CreateInvoiceForm>({
    resolver: zodResolver(createInvoiceSchema),
    defaultValues: {
      client_id: 0 as unknown as number,
      work_order_id: null,
      issue_date: new Date().toISOString().split("T")[0] ?? "",
      due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)
        .toISOString()
        .split("T")[0] ?? "",
      notes: "",
      terms: "",
      items: [
        {
          description: "",
          quantity: 1,
          unit: "pcs",
          unit_price: 0,
          discount: 0,
          tax_rate: 0,
        },
      ],
    },
  });

  const { fields, append, remove, replace } = useFieldArray({
    control,
    name: "items",
  });

  const items = watch("items");
  const linkedWoId = watch("work_order_id");

  const { data: workOrders, isLoading: woLoading } = useQuery({
    queryKey: ["work-orders-for-invoice", woSearch],
    queryFn: async () => {
      const params = new URLSearchParams();
      params.set("status", "completed");
      params.set("per_page", "50");
      if (woSearch) params.set("search", woSearch);
      const res = await apiClient.get<{ data: WorkOrder[] }>(
        `/work-orders?${params.toString()}`
      );
      const body = res.data as { data: WorkOrder[] };
      return body.data ?? [];
    },
    enabled: woDialogOpen,
  });

  const { data: linkedWo } = useQuery({
    queryKey: ["work-order", linkedWoId],
    queryFn: async () => {
      const res = await apiClient.get<{ data: WorkOrder }>(
        `/work-orders/${linkedWoId}`
      );
      const body = res.data as { data: WorkOrder };
      return body.data ?? (body as unknown as WorkOrder);
    },
    enabled: !!linkedWoId,
  });

  async function importWorkOrder(woId: number) {
    try {
      const res = await apiClient.get<{ data: WorkOrder }>(
        `/work-orders/${woId}`
      );
      const body = res.data as { data: WorkOrder };
      const wo = body.data ?? (body as unknown as WorkOrder);

      if (wo.client_id) {
        setValue("client_id", wo.client_id);
      }
      setValue("work_order_id", wo.id);

      if (wo.items && wo.items.length > 0) {
        replace(
          wo.items.map((item) => ({
            description: item.description,
            quantity: Number(item.quantity),
            unit: item.unit || "pcs",
            unit_price: Number(item.unit_price),
            discount: Number(item.discount ?? 0),
            tax_rate: Number(item.tax_rate ?? 0),
          }))
        );
      }

      setSelectedWoId(null);
      setWoDialogOpen(false);
      toast.success(`Imported items from ${wo.wo_number}`);
    } catch {
      toast.error("Failed to load work order details");
    }
  }

  function unlinkWorkOrder() {
    setValue("work_order_id", null);
  }

  const totals = (() => {
    let totalBeforeTax = 0;
    let totalTax = 0;
    let totalDiscount = 0;
    for (const item of items ?? []) {
      const beforeTax = item.quantity * item.unit_price;
      const discount = item.discount;
      const afterDiscount = beforeTax - discount;
      const tax = afterDiscount * (item.tax_rate / 100);
      totalBeforeTax += beforeTax;
      totalDiscount += discount;
      totalTax += tax;
    }
    return {
      subtotal: totalBeforeTax,
      totalDiscount,
      totalTax,
      grandTotal: totalBeforeTax - totalDiscount + totalTax,
    };
  })();

  async function onSubmit(data: CreateInvoiceForm) {
    try {
      const payload = {
        client_id: data.client_id,
        work_order_id: data.work_order_id || undefined,
        issue_date: data.issue_date,
        due_date: data.due_date,
        notes: data.notes || undefined,
        terms: data.terms || undefined,
        items: data.items.map((item) => ({
          description: item.description,
          quantity: item.quantity,
          unit: item.unit,
          unit_price: item.unit_price,
          discount: item.discount,
          tax_rate: item.tax_rate,
        })),
      };
      await apiClient.post("/invoices", payload);
      toast.success("Invoice created successfully");
      router.push("/invoices");
    } catch (err: unknown) {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response
              ?.data?.message
          : "Failed to create invoice";
      toast.error(
        typeof message === "string" ? message : "Failed to create invoice"
      );
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="Create Invoice"
        description="Create a new invoice manually or from a work order"
      />
      <form onSubmit={handleSubmit(onSubmit)}>
        <div className="grid gap-6 lg:grid-cols-2">
          <div className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Invoice Details</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label>Client *</Label>
                  <ClientSelect
                    value={watch("client_id") || null}
                    onChange={(id) => setValue("client_id", id ?? 0)}
                  />
                  {errors.client_id && (
                    <p className="text-sm text-destructive">
                      {errors.client_id.message}
                    </p>
                  )}
                </div>
                <div className="grid gap-4 sm:grid-cols-2">
                  <div className="space-y-2">
                    <Label>Issue Date *</Label>
                    <DatePicker
                      value={watch("issue_date")}
                      onChange={(v) => setValue("issue_date", v)}
                      placeholder="Select date"
                    />
                    {errors.issue_date && (
                      <p className="text-sm text-destructive">
                        {errors.issue_date.message}
                      </p>
                    )}
                  </div>
                  <div className="space-y-2">
                    <Label>Due Date *</Label>
                    <DatePicker
                      value={watch("due_date")}
                      onChange={(v) => setValue("due_date", v)}
                      placeholder="Select date"
                    />
                    {errors.due_date && (
                      <p className="text-sm text-destructive">
                        {errors.due_date.message}
                      </p>
                    )}
                  </div>
                </div>
                <div className="space-y-2">
                  <Label htmlFor="notes">Notes</Label>
                  <Textarea id="notes" {...register("notes")} rows={3} />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="terms">Terms</Label>
                  <Textarea id="terms" {...register("terms")} rows={3} />
                </div>
              </CardContent>
            </Card>

            {linkedWoId && linkedWo && (
              <Card className="border-blue-200 bg-blue-50/50 dark:border-blue-900 dark:bg-blue-950/20">
                <CardContent className="flex items-center justify-between pt-6">
                  <div className="flex items-center gap-3">
                    <ClipboardList className="size-5 text-blue-600" />
                    <div>
                      <p className="text-sm font-medium">
                        Linked to {linkedWo.wo_number}
                      </p>
                      <p className="text-xs text-muted-foreground">
                        {linkedWo.title} &middot;{" "}
                        {formatCurrency(Number(linkedWo.grand_total))}
                      </p>
                    </div>
                  </div>
                  <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={unlinkWorkOrder}
                    className="text-muted-foreground hover:text-destructive"
                  >
                    Unlink
                  </Button>
                </CardContent>
              </Card>
            )}
          </div>

          <div className="space-y-6">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between space-y-0">
                <CardTitle>Line Items</CardTitle>
                <Dialog open={woDialogOpen} onOpenChange={setWoDialogOpen}>
                  <DialogTrigger
                    render={
                      <Button type="button" variant="outline" size="sm">
                        <ClipboardList className="mr-2 size-4" />
                        Import from WO
                      </Button>
                    }
                  />
                  <DialogContent className="max-w-2xl">
                    <DialogHeader>
                      <DialogTitle>Select Work Order</DialogTitle>
                      <DialogDescription>
                        Choose a completed work order to import its line items
                        into this invoice.
                      </DialogDescription>
                    </DialogHeader>
                    <div className="space-y-4">
                      <Input
                        placeholder="Search by WO number or title..."
                        value={woSearch}
                        onChange={(e) => setWoSearch(e.target.value)}
                      />
                      <div className="max-h-80 overflow-y-auto space-y-2">
                        {woLoading && (
                          <p className="text-center text-sm text-muted-foreground py-8">
                            Loading work orders...
                          </p>
                        )}
                        {!woLoading && (!workOrders || workOrders.length === 0) && (
                          <p className="text-center text-sm text-muted-foreground py-8">
                            No completed work orders found.
                          </p>
                        )}
                        {workOrders?.map((wo) => (
                          <div
                            key={wo.id}
                            className={`flex items-center gap-3 rounded-lg border p-3 cursor-pointer transition-colors ${
                              selectedWoId === wo.id
                                ? "border-primary bg-primary/5"
                                : "hover:bg-muted/50"
                            }`}
                            onClick={() =>
                              setSelectedWoId(
                                selectedWoId === wo.id ? null : wo.id
                              )
                            }
                          >
                            <Checkbox
                              checked={selectedWoId === wo.id}
                              onCheckedChange={() =>
                                setSelectedWoId(
                                  selectedWoId === wo.id ? null : wo.id
                                )
                              }
                            />
                            <div className="flex-1 min-w-0">
                              <div className="flex items-center gap-2">
                                <span className="text-sm font-medium">
                                  {wo.wo_number}
                                </span>
                                <Badge variant="outline" className="text-xs">
                                  {wo.status}
                                </Badge>
                              </div>
                              <p className="text-sm text-muted-foreground truncate">
                                {wo.title}
                              </p>
                              {wo.client_work_order_id && (
                                <p className="text-xs text-muted-foreground">
                                  Client WO: {wo.client_work_order_id}
                                </p>
                              )}
                            </div>
                            <div className="text-right shrink-0">
                              <p className="text-sm font-medium">
                                {formatCurrency(Number(wo.grand_total))}
                              </p>
                              <p className="text-xs text-muted-foreground">
                                {wo.items?.length ?? 0} items
                              </p>
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                    <DialogFooter>
                      <Button
                        type="button"
                        variant="outline"
                        onClick={() => {
                          setSelectedWoId(null);
                          setWoDialogOpen(false);
                        }}
                      >
                        Cancel
                      </Button>
                      <Button
                        type="button"
                        disabled={!selectedWoId}
                        onClick={() => {
                          if (selectedWoId) importWorkOrder(selectedWoId);
                        }}
                      >
                        Import Items
                      </Button>
                    </DialogFooter>
                  </DialogContent>
                </Dialog>
              </CardHeader>
              <CardContent className="space-y-4">
                {fields.map((field, i) => (
                  <div
                    key={field.id}
                    className="rounded-lg border p-4 space-y-3"
                  >
                    <div className="flex items-center justify-between">
                      <span className="text-sm font-medium text-muted-foreground">
                        Item {i + 1}
                      </span>
                      <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        onClick={() => remove(i)}
                        disabled={fields.length === 1}
                        className="h-7 px-2 text-muted-foreground hover:text-destructive"
                      >
                        <Trash2 className="size-3.5 mr-1" />
                        Remove
                      </Button>
                    </div>
                    <div className="space-y-2">
                      <Label className="text-xs">Description</Label>
                      <Input
                        {...register(`items.${i}.description`)}
                        placeholder="Item description"
                      />
                    </div>
                    <div className="grid grid-cols-3 gap-3">
                      <div className="space-y-2">
                        <Label className="text-xs">Qty</Label>
                        <Input
                          type="number"
                          step="0.01"
                          {...register(`items.${i}.quantity`, {
                            valueAsNumber: true,
                          })}
                        />
                      </div>
                      <div className="space-y-2">
                        <Label className="text-xs">Unit</Label>
                        <Input
                          {...register(`items.${i}.unit`)}
                          placeholder="pcs"
                        />
                      </div>
                      <div className="space-y-2">
                        <Label className="text-xs">Unit Price</Label>
                        <Controller
                          name={`items.${i}.unit_price`}
                          control={control}
                          render={({ field }) => (
                            <CurrencyInput
                              value={field.value}
                              onChange={field.onChange}
                            />
                          )}
                        />
                      </div>
                    </div>
                    <div className="grid grid-cols-3 gap-3">
                      <div className="space-y-2">
                        <Label className="text-xs">Discount</Label>
                        <Controller
                          name={`items.${i}.discount`}
                          control={control}
                          render={({ field }) => (
                            <CurrencyInput
                              value={field.value}
                              onChange={field.onChange}
                            />
                          )}
                        />
                      </div>
                      <div className="space-y-2">
                        <Label className="text-xs">Tax %</Label>
                        <Input
                          type="number"
                          step="0.01"
                          {...register(`items.${i}.tax_rate`, {
                            valueAsNumber: true,
                          })}
                        />
                      </div>
                      <div className="space-y-2">
                        <Label className="text-xs">Subtotal</Label>
                        <div className="flex h-9 items-center rounded-md border bg-muted/50 px-3 text-sm font-medium">
                          {formatCurrency(
                            calcSubtotal(
                              items?.[i]?.quantity ?? 0,
                              items?.[i]?.unit_price ?? 0,
                              items?.[i]?.discount ?? 0,
                              items?.[i]?.tax_rate ?? 0
                            )
                          )}
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  className="w-full"
                  onClick={() =>
                    append({
                      description: "",
                      quantity: 1,
                      unit: "pcs",
                      unit_price: 0,
                      discount: 0,
                      tax_rate: 0,
                    })
                  }
                >
                  <Plus className="mr-2 size-4" />
                  Add Item
                </Button>
              </CardContent>
            </Card>
            <Card>
              <CardHeader>
                <CardTitle>Summary</CardTitle>
              </CardHeader>
              <CardContent className="space-y-2">
                <div className="flex justify-between text-sm">
                  <span className="text-muted-foreground">Subtotal</span>
                  <span>{formatCurrency(totals.subtotal)}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-muted-foreground">Total Discount</span>
                  <span>{formatCurrency(totals.totalDiscount)}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-muted-foreground">Total Tax</span>
                  <span>{formatCurrency(totals.totalTax)}</span>
                </div>
                <div className="flex justify-between font-semibold pt-2 border-t">
                  <span>Grand Total</span>
                  <span>{formatCurrency(totals.grandTotal)}</span>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
        <Card className="mt-6">
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
                "Create Invoice"
              )}
            </Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  );
}
