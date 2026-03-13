"use client";

import { useRouter } from "next/navigation";
import { useForm, useFieldArray } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useQuery } from "@tanstack/react-query";
import { Loader2, Plus, Trash2 } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Account } from "@/types";
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
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { DatePicker } from "@/components/ui/date-picker";
import { formatCurrency } from "@/lib/format";
import { toast } from "sonner";

const lineSchema = z.object({
  account_id: z.number().min(1, "Account is required"),
  debit: z.number().min(0),
  credit: z.number().min(0),
  description: z.string().optional(),
});

const createJournalEntrySchema = z.object({
  date: z.string().min(1, "Date is required"),
  description: z.string().optional(),
  lines: z.array(lineSchema).min(2, "At least 2 lines required"),
});

type CreateJournalEntryForm = z.infer<typeof createJournalEntrySchema>;

async function fetchAccounts(): Promise<Account[]> {
  const res = await apiClient.get<{ data: Account[] }>("/accounts");
  const body = res.data as { data: Account[] };
  return body.data ?? (body as unknown as Account[]);
}

export default function CreateJournalEntryPage() {
  const router = useRouter();

  const { data: accounts = [] } = useQuery({
    queryKey: ["accounts"],
    queryFn: fetchAccounts,
  });

  const {
    register,
    handleSubmit,
    control,
    watch,
    setValue,
    formState: { errors, isSubmitting },
  } = useForm<CreateJournalEntryForm>({
    resolver: zodResolver(createJournalEntrySchema),
    defaultValues: {
      date: new Date().toISOString().split("T")[0] ?? "",
      description: "",
      lines: [
        { account_id: 0 as unknown as number, debit: 0, credit: 0, description: "" },
        { account_id: 0 as unknown as number, debit: 0, credit: 0, description: "" },
      ],
    },
  });

  const { fields, append, remove } = useFieldArray({
    control,
    name: "lines",
  });

  const lines = watch("lines");

  const totals = (() => {
    let totalDebits = 0;
    let totalCredits = 0;
    for (const line of lines ?? []) {
      totalDebits += line.debit ?? 0;
      totalCredits += line.credit ?? 0;
    }
    return {
      totalDebits,
      totalCredits,
      difference: totalDebits - totalCredits,
    };
  })();

  const isBalanced = Math.abs(totals.difference) < 0.01;

  async function onSubmit(data: CreateJournalEntryForm) {
    if (!isBalanced) return;
    try {
      const payload = {
        date: data.date,
        description: data.description || undefined,
        lines: data.lines.map((l) => ({
          account_id: l.account_id,
          debit: l.debit,
          credit: l.credit,
          description: l.description || undefined,
        })),
      };
      await apiClient.post("/journal-entries", payload);
      toast.success("Journal entry created successfully");
      router.push("/journal-entries");
    } catch (err: unknown) {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to create journal entry";
      toast.error(
        typeof message === "string" ? message : "Failed to create journal entry"
      );
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="New Journal Entry"
        description="Create a new journal entry"
      />
      <form onSubmit={handleSubmit(onSubmit)}>
        <Card>
          <CardHeader>
            <CardTitle>Entry Details</CardTitle>
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
            </div>
            <div className="space-y-2">
              <Label htmlFor="description">Description</Label>
              <Textarea
                id="description"
                {...register("description")}
                rows={2}
              />
            </div>
          </CardContent>
        </Card>
        <Card className="mt-6">
          <CardHeader>
            <CardTitle>Lines</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="rounded-md border">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Account</TableHead>
                    <TableHead className="w-32 text-right">Debit</TableHead>
                    <TableHead className="w-32 text-right">Credit</TableHead>
                    <TableHead>Description</TableHead>
                    <TableHead className="w-10" />
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {fields.map((field, i) => (
                    <TableRow key={field.id}>
                      <TableCell>
                        <Select
                          value={
                            watch(`lines.${i}.account_id`)
                              ? String(watch(`lines.${i}.account_id`))
                              : ""
                          }
                          onValueChange={(v) =>
                            setValue(`lines.${i}.account_id`, parseInt(v ?? "0", 10))
                          }
                        >
                          <SelectTrigger className="h-8">
                            <SelectValue placeholder="Select account" />
                          </SelectTrigger>
                          <SelectContent>
                            {accounts
                              .filter((a) => !a.is_header)
                              .map((a) => (
                                <SelectItem key={a.id} value={String(a.id)}>
                                  {a.code} - {a.name}
                                </SelectItem>
                              ))}
                          </SelectContent>
                        </Select>
                      </TableCell>
                      <TableCell>
                        <Input
                          type="number"
                          step="0.01"
                          {...register(`lines.${i}.debit`)}
                          className="h-8 text-right"
                        />
                      </TableCell>
                      <TableCell>
                        <Input
                          type="number"
                          step="0.01"
                          {...register(`lines.${i}.credit`)}
                          className="h-8 text-right"
                        />
                      </TableCell>
                      <TableCell>
                        <Input
                          {...register(`lines.${i}.description`)}
                          className="h-8"
                          placeholder="Description"
                        />
                      </TableCell>
                      <TableCell>
                        <Button
                          type="button"
                          variant="ghost"
                          size="icon-sm"
                          onClick={() => remove(i)}
                          disabled={fields.length === 2}
                        >
                          <Trash2 className="size-4" />
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
                <tfoot>
                  <TableRow className="font-semibold">
                    <TableCell>Totals</TableCell>
                    <TableCell className="text-right">
                      {formatCurrency(totals.totalDebits)}
                    </TableCell>
                    <TableCell className="text-right">
                      {formatCurrency(totals.totalCredits)}
                    </TableCell>
                    <TableCell />
                    <TableCell />
                  </TableRow>
                  <TableRow>
                    <TableCell colSpan={2}>Difference</TableCell>
                    <TableCell
                      className={`text-right font-semibold ${
                        !isBalanced ? "text-destructive" : ""
                      }`}
                    >
                      {formatCurrency(totals.difference)}
                    </TableCell>
                    <TableCell colSpan={2} />
                  </TableRow>
                </tfoot>
              </Table>
            </div>
            <Button
              type="button"
              variant="outline"
              size="sm"
              className="mt-4"
              onClick={() =>
                append({
                  account_id: 0 as unknown as number,
                  debit: 0,
                  credit: 0,
                  description: "",
                })
              }
            >
              <Plus className="mr-2 size-4" />
              Add Line
            </Button>
          </CardContent>
          <CardFooter>
            <Button
              type="button"
              variant="outline"
              onClick={() => router.back()}
            >
              Cancel
            </Button>
            <Button type="submit" disabled={isSubmitting || !isBalanced}>
              {isSubmitting ? (
                <>
                  <Loader2 className="mr-2 size-4 animate-spin" />
                  Saving...
                </>
              ) : (
                "Create Journal Entry"
              )}
            </Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  );
}
