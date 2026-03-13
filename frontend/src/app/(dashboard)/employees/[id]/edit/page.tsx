"use client";

import { useEffect } from "react";
import { useRouter, useParams } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useQuery } from "@tanstack/react-query";
import { Loader2 } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Employee } from "@/types";
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
import { DatePicker } from "@/components/ui/date-picker";
import { Skeleton } from "@/components/ui/skeleton";
import { toast } from "sonner";

const editEmployeeSchema = z.object({
  name: z.string().min(1, "Name is required"),
  email: z.union([z.string().email(), z.literal("")]).optional(),
  phone: z.string().optional(),
  address: z.string().optional(),
  position: z.string().min(1, "Position is required"),
  department: z.string().optional(),
  join_date: z.string().min(1, "Join date is required"),
  base_salary: z.number().min(0, "Base salary is required"),
  bank_name: z.string().optional(),
  bank_account: z.string().optional(),
  bank_holder: z.string().optional(),
  tax_id: z.string().optional(),
});

type EditEmployeeForm = z.infer<typeof editEmployeeSchema>;

async function fetchEmployee(id: string): Promise<Employee> {
  const res = await apiClient.get<{ data: Employee }>(`/employees/${id}`);
  const body = res.data as { data: Employee };
  return body.data ?? (body as unknown as Employee);
}

export default function EditEmployeePage() {
  const router = useRouter();
  const params = useParams();
  const id = params.id as string;

  const { data: employee, isLoading } = useQuery({
    queryKey: ["employee", id],
    queryFn: () => fetchEmployee(id),
    enabled: !!id,
  });

  const {
    register,
    handleSubmit,
    watch,
    setValue,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<EditEmployeeForm>({
    resolver: zodResolver(editEmployeeSchema),
    defaultValues: {
      name: "",
      email: "",
      phone: "",
      address: "",
      position: "",
      department: "",
      join_date: "",
      base_salary: 0,
      bank_name: "",
      bank_account: "",
      bank_holder: "",
      tax_id: "",
    },
  });

  useEffect(() => {
    if (employee) {
      reset({
        name: employee.name,
        email: employee.email ?? "",
        phone: employee.phone ?? "",
        address: employee.address ?? "",
        position: employee.position ?? "",
        department: employee.department ?? "",
        join_date: employee.join_date,
        base_salary: employee.base_salary ?? 0,
        bank_name: employee.bank_name ?? "",
        bank_account: employee.bank_account ?? "",
        bank_holder: employee.bank_holder ?? "",
        tax_id: employee.tax_id ?? "",
      });
    }
  }, [employee, reset]);

  async function onSubmit(data: EditEmployeeForm) {
    try {
      const payload = {
        name: data.name,
        email: data.email || undefined,
        phone: data.phone || undefined,
        address: data.address || undefined,
        position: data.position,
        department: data.department || undefined,
        join_date: data.join_date,
        base_salary: data.base_salary,
        bank_name: data.bank_name || undefined,
        bank_account: data.bank_account || undefined,
        bank_holder: data.bank_holder || undefined,
        tax_id: data.tax_id || undefined,
      };
      await apiClient.put(`/employees/${id}`, payload);
      toast.success("Employee updated successfully");
      router.push(`/employees/${id}`);
    } catch (err: unknown) {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to update employee";
      toast.error(
        typeof message === "string" ? message : "Failed to update employee"
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

  if (!employee) {
    return null;
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="Edit Employee"
        description={employee.employee_id}
      />
      <form onSubmit={handleSubmit(onSubmit)}>
        <div className="grid gap-6 lg:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle>Personal Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="name">Name *</Label>
                <Input
                  id="name"
                  {...register("name")}
                  aria-invalid={!!errors.name}
                />
                {errors.name && (
                  <p className="text-sm text-destructive">{errors.name.message}</p>
                )}
              </div>
              <div className="space-y-2">
                <Label htmlFor="email">Email</Label>
                <Input
                  id="email"
                  type="email"
                  {...register("email")}
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="phone">Phone</Label>
                <Input id="phone" {...register("phone")} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="address">Address</Label>
                <Textarea
                  id="address"
                  {...register("address")}
                  rows={3}
                />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle>Employment</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="position">Position *</Label>
                <Input
                  id="position"
                  {...register("position")}
                  aria-invalid={!!errors.position}
                />
                {errors.position && (
                  <p className="text-sm text-destructive">
                    {errors.position.message}
                  </p>
                )}
              </div>
              <div className="space-y-2">
                <Label htmlFor="department">Department</Label>
                <Input id="department" {...register("department")} />
              </div>
              <div className="space-y-2">
                <Label>Join Date *</Label>
                <DatePicker
                  value={watch("join_date")}
                  onChange={(v) => setValue("join_date", v)}
                  placeholder="Select date"
                />
                {errors.join_date && (
                  <p className="text-sm text-destructive">
                    {errors.join_date.message}
                  </p>
                )}
              </div>
              <div className="space-y-2">
                <Label htmlFor="base_salary">Base Salary *</Label>
                <Input
                  id="base_salary"
                  type="number"
                  step="0.01"
                  {...register("base_salary", { valueAsNumber: true })}
                  aria-invalid={!!errors.base_salary}
                />
                {errors.base_salary && (
                  <p className="text-sm text-destructive">
                    {errors.base_salary.message}
                  </p>
                )}
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle>Banking</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="bank_name">Bank Name</Label>
                <Input id="bank_name" {...register("bank_name")} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="bank_account">Bank Account Number</Label>
                <Input id="bank_account" {...register("bank_account")} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="bank_holder">Bank Account Holder</Label>
                <Input id="bank_holder" {...register("bank_holder")} />
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle>Tax</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="tax_id">Tax ID (NPWP)</Label>
                <Input id="tax_id" {...register("tax_id")} />
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
