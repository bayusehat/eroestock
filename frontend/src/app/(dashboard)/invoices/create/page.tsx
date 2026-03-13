"use client";

import { useRouter } from "next/navigation";
import { useForm, useFieldArray } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { Loader2, Plus, Trash2 } from "lucide-react";
import { apiClient } from "@/lib/api";
import { PageHeader } from "@/components/page-header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
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
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { ClientSelect } from "@/components/client-select";
import { DatePicker } from "@/components/ui/date-picker";
import { formatCurrency } from "@/lib/format";
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

  const { fields, append, remove } = useFieldArray({
    control,
    name: "items",
  });

  const items = watch("items");

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
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
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
        description="Create a new invoice"
      />
      <form onSubmit={handleSubmit(onSubmit)}>
        <div className="grid gap-6 lg:grid-cols-3">
          <div className="lg:col-span-2 space-y-6">
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
                  <Textarea
                    id="notes"
                    {...register("notes")}
                    rows={3}
                  />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="terms">Terms</Label>
                  <Textarea
                    id="terms"
                    {...register("terms")}
                    rows={3}
                  />
                </div>
              </CardContent>
            </Card>
          </div>
          <div className="space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Line Items</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="rounded-md border">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Description</TableHead>
                        <TableHead className="w-20">Qty</TableHead>
                        <TableHead className="w-20">Unit</TableHead>
                        <TableHead className="w-20">Unit Price</TableHead>
                        <TableHead className="w-20">Discount</TableHead>
                        <TableHead className="w-20">Tax %</TableHead>
                        <TableHead className="w-24 text-right">Subtotal</TableHead>
                        <TableHead className="w-10" />
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {fields.map((field, i) => (
                        <TableRow key={field.id}>
                          <TableCell>
                            <Input
                              {...register(`items.${i}.description`)}
                              className="h-8"
                              placeholder="Description"
                            />
                          </TableCell>
                          <TableCell>
                            <Input
                              type="number"
                              step="0.01"
                              {...register(`items.${i}.quantity`)}
                              className="h-8"
                            />
                          </TableCell>
                          <TableCell>
                            <Input
                              {...register(`items.${i}.unit`)}
                              className="h-8"
                              placeholder="pcs"
                            />
                          </TableCell>
                          <TableCell>
                            <Input
                              type="number"
                              step="0.01"
                              {...register(`items.${i}.unit_price`)}
                              className="h-8"
                            />
                          </TableCell>
                          <TableCell>
                            <Input
                              type="number"
                              step="0.01"
                              {...register(`items.${i}.discount`)}
                              className="h-8"
                            />
                          </TableCell>
                          <TableCell>
                            <Input
                              type="number"
                              step="0.01"
                              {...register(`items.${i}.tax_rate`)}
                              className="h-8"
                            />
                          </TableCell>
                          <TableCell className="text-right text-sm">
                            {formatCurrency(
                              calcSubtotal(
                                items?.[i]?.quantity ?? 0,
                                items?.[i]?.unit_price ?? 0,
                                items?.[i]?.discount ?? 0,
                                items?.[i]?.tax_rate ?? 0
                              )
                            )}
                          </TableCell>
                          <TableCell>
                            <Button
                              type="button"
                              variant="ghost"
                              size="icon-sm"
                              onClick={() => remove(i)}
                              disabled={fields.length === 1}
                            >
                              <Trash2 className="size-4" />
                            </Button>
                          </TableCell>
                        </TableRow>
                      ))}
                    </TableBody>
                  </Table>
                </div>
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  className="mt-4"
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
