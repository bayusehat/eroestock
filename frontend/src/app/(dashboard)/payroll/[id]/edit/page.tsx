"use client";

import { useEffect } from "react";
import { useRouter, useParams } from "next/navigation";
import { useForm, useFieldArray } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useQuery } from "@tanstack/react-query";
import { Loader2, Plus, Trash2 } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Employee, PayrollRecord } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
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
import { formatCurrency } from "@/lib/format";
import { Skeleton } from "@/components/ui/skeleton";
import { toast } from "sonner";

const allowanceSchema = z.object({
  name: z.string(),
  amount: z.number().min(0),
});

const deductionSchema = z.object({
  name: z.string(),
  amount: z.number().min(0),
});

const editPayrollSchema = z.object({
  employee_id: z.number().min(1, "Employee is required"),
  period_month: z.number().min(1).max(12),
  period_year: z.number().min(2000),
  base_salary: z.number().min(0),
  overtime_hours: z.number().min(0),
  overtime_rate: z.number().min(0),
  allowances: z.array(allowanceSchema),
  deductions: z.array(deductionSchema),
  tax_amount: z.number().min(0),
});

type EditPayrollForm = z.infer<typeof editPayrollSchema>;

async function fetchPayroll(id: string): Promise<PayrollRecord> {
  const res = await apiClient.get<{ data: PayrollRecord }>(`/payroll/${id}`);
  const body = res.data as { data: PayrollRecord };
  return body.data ?? (body as unknown as PayrollRecord);
}

async function fetchEmployees(): Promise<Employee[]> {
  const res = await apiClient.get<{ data: Employee[] }>("/employees");
  const body = res.data as { data: Employee[] };
  return body.data ?? (body as unknown as Employee[]);
}

export default function EditPayrollPage() {
  const router = useRouter();
  const params = useParams();
  const id = params.id as string;

  const { data: payroll, isLoading } = useQuery({
    queryKey: ["payroll", id],
    queryFn: () => fetchPayroll(id),
    enabled: !!id,
  });

  const { data: employees = [] } = useQuery({
    queryKey: ["employees"],
    queryFn: fetchEmployees,
  });

  const {
    register,
    handleSubmit,
    control,
    watch,
    setValue,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<EditPayrollForm>({
    resolver: zodResolver(editPayrollSchema),
    defaultValues: {
      employee_id: 0 as unknown as number,
      period_month: 1,
      period_year: new Date().getFullYear(),
      base_salary: 0,
      overtime_hours: 0,
      overtime_rate: 0,
      allowances: [],
      deductions: [],
      tax_amount: 0,
    },
  });

  const { fields: allowanceFields, append: appendAllowance, remove: removeAllowance } =
    useFieldArray({ control, name: "allowances" });
  const { fields: deductionFields, append: appendDeduction, remove: removeDeduction } =
    useFieldArray({ control, name: "deductions" });

  const formValues = watch();

  useEffect(() => {
    if (payroll) {
      if (payroll.status !== "draft") {
        router.replace(`/payroll/${id}`);
        return;
      }
      const allowances = payroll.allowances
        ? Object.entries(payroll.allowances).map(([name, amount]) => ({
            name,
            amount,
          }))
        : [];
      const deductions = payroll.deductions
        ? Object.entries(payroll.deductions).map(([name, amount]) => ({
            name,
            amount,
          }))
        : [];
      reset({
        employee_id: payroll.employee_id,
        period_month: payroll.period_month,
        period_year: payroll.period_year,
        base_salary: payroll.base_salary ?? 0,
        overtime_hours: payroll.overtime_hours ?? 0,
        overtime_rate: payroll.overtime_rate ?? 0,
        allowances,
        deductions,
        tax_amount: payroll.tax_amount ?? 0,
      });
    }
  }, [payroll, reset, id, router]);

  const totals = (() => {
    const baseSalary = formValues.base_salary ?? 0;
    const overtimeAmount =
      (formValues.overtime_hours ?? 0) * (formValues.overtime_rate ?? 0);
    const totalAllowances = (formValues.allowances ?? []).reduce(
      (sum: number, a: { amount: number }) => sum + (a.amount ?? 0),
      0
    );
    const totalDeductions = (formValues.deductions ?? []).reduce(
      (sum: number, d: { amount: number }) => sum + (d.amount ?? 0),
      0
    );
    const taxAmount = formValues.tax_amount ?? 0;
    const grossPay = baseSalary + overtimeAmount + totalAllowances;
    const totalDeductionsWithTax = totalDeductions + taxAmount;
    const netPay = grossPay - totalDeductionsWithTax;
    return {
      overtimeAmount,
      totalAllowances,
      totalDeductions,
      totalDeductionsWithTax,
      grossPay,
      netPay,
    };
  })();

  async function onSubmit(data: EditPayrollForm) {
    try {
      const allowances: Record<string, number> = {};
      data.allowances
        .filter((a) => a.name.trim())
        .forEach((a) => {
          allowances[a.name] = a.amount;
        });
      const deductions: Record<string, number> = {};
      data.deductions
        .filter((d) => d.name.trim())
        .forEach((d) => {
          deductions[d.name] = d.amount;
        });
      const payload = {
        employee_id: data.employee_id,
        period_month: data.period_month,
        period_year: data.period_year,
        base_salary: data.base_salary,
        overtime_hours: data.overtime_hours,
        overtime_rate: data.overtime_rate,
        overtime_amount: totals.overtimeAmount,
        allowances,
        deductions,
        tax_amount: data.tax_amount,
      };
      await apiClient.put(`/payroll/${id}`, payload);
      toast.success("Payroll updated successfully");
      router.push(`/payroll/${id}`);
    } catch (err: unknown) {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to update payroll";
      toast.error(
        typeof message === "string" ? message : "Failed to update payroll"
      );
    }
  }

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-96" />
      </div>
    );
  }

  if (!payroll) {
    return null;
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="Edit Payroll"
        description={payroll.payroll_no}
      />
      <form onSubmit={handleSubmit(onSubmit)}>
        <div className="grid gap-6 lg:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle>Basic Info</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label>Employee *</Label>
                <Select
                  value={
                    formValues.employee_id
                      ? String(formValues.employee_id)
                      : ""
                  }
                  onValueChange={(v) => {
                    const empId = v ? parseInt(v, 10) : 0;
                    setValue("employee_id", empId);
                    const emp = employees.find((e) => e.id === empId);
                    if (emp) setValue("base_salary", emp.base_salary ?? 0);
                  }}
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select employee" />
                  </SelectTrigger>
                  <SelectContent>
                    {employees.map((e) => (
                      <SelectItem key={e.id} value={String(e.id)}>
                        {e.name}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
              <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-2">
                  <Label>Period Month</Label>
                  <Select
                    value={String(formValues.period_month)}
                    onValueChange={(v) => setValue("period_month", parseInt(v ?? "1", 10))}
                  >
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      {Array.from({ length: 12 }, (_, i) => i + 1).map((m) => (
                        <SelectItem key={m} value={String(m)}>
                          {new Date(2000, m - 1).toLocaleString("default", {
                            month: "long",
                          })}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label>Period Year</Label>
                  <Input
                    type="number"
                    {...register("period_year", { valueAsNumber: true })}
                  />
                </div>
              </div>
              <div className="space-y-2">
                <Label>Base Salary *</Label>
                <Input
                  type="number"
                  step="0.01"
                  {...register("base_salary", { valueAsNumber: true })}
                />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle>Overtime</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid gap-4 sm:grid-cols-2">
                <div className="space-y-2">
                  <Label>Hours</Label>
                  <Input
                    type="number"
                    step="0.01"
                    {...register("overtime_hours", { valueAsNumber: true })}
                  />
                </div>
                <div className="space-y-2">
                  <Label>Rate</Label>
                  <Input
                    type="number"
                    step="0.01"
                    {...register("overtime_rate", { valueAsNumber: true })}
                  />
                </div>
              </div>
              <p className="text-sm text-muted-foreground">
                Overtime Amount: {formatCurrency(totals.overtimeAmount)}
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle>Allowances</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {allowanceFields.map((field, i) => (
                <div key={field.id} className="flex gap-2">
                  <Input
                    {...register(`allowances.${i}.name`)}
                    placeholder="Name"
                    className="flex-1"
                  />
                  <Input
                    type="number"
                    step="0.01"
                    {...register(`allowances.${i}.amount`, {
                      valueAsNumber: true,
                    })}
                    placeholder="Amount"
                    className="w-32"
                  />
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    onClick={() => removeAllowance(i)}
                  >
                    <Trash2 className="size-4" />
                  </Button>
                </div>
              ))}
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={() => appendAllowance({ name: "", amount: 0 })}
              >
                <Plus className="mr-2 size-4" />
                Add Allowance
              </Button>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle>Deductions</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              {deductionFields.map((field, i) => (
                <div key={field.id} className="flex gap-2">
                  <Input
                    {...register(`deductions.${i}.name`)}
                    placeholder="Name"
                    className="flex-1"
                  />
                  <Input
                    type="number"
                    step="0.01"
                    {...register(`deductions.${i}.amount`, {
                      valueAsNumber: true,
                    })}
                    placeholder="Amount"
                    className="w-32"
                  />
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    onClick={() => removeDeduction(i)}
                  >
                    <Trash2 className="size-4" />
                  </Button>
                </div>
              ))}
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={() => appendDeduction({ name: "", amount: 0 })}
              >
                <Plus className="mr-2 size-4" />
                Add Deduction
              </Button>
            </CardContent>
          </Card>
          <Card className="lg:col-span-2">
            <CardHeader>
              <CardTitle>Tax & Summary</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label>Tax Amount</Label>
                <Input
                  type="number"
                  step="0.01"
                  {...register("tax_amount", { valueAsNumber: true })}
                />
              </div>
              <div className="space-y-2 border-t pt-4">
                <div className="flex justify-between text-sm">
                  <span className="text-muted-foreground">Gross Pay</span>
                  <span>{formatCurrency(totals.grossPay)}</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-muted-foreground">Total Deductions</span>
                  <span>{formatCurrency(totals.totalDeductionsWithTax)}</span>
                </div>
                <div className="flex justify-between font-semibold pt-2 border-t">
                  <span>Net Pay</span>
                  <span>{formatCurrency(totals.netPay)}</span>
                </div>
              </div>
            </CardContent>
          </Card>
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
                "Save"
              )}
            </Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  );
}
