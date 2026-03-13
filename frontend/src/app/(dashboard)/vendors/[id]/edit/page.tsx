"use client";

import { useEffect } from "react";
import { useRouter, useParams } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useQuery } from "@tanstack/react-query";
import { Loader2 } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Vendor } from "@/types";
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
import { Skeleton } from "@/components/ui/skeleton";
import { toast } from "sonner";

const editVendorSchema = z.object({
  name: z.string().min(1, "Name is required"),
  email: z.string().email("Please enter a valid email").optional().or(z.literal("")),
  phone: z.string().optional(),
  address: z.string().optional(),
  tax_id: z.string().optional(),
  contact_person: z.string().optional(),
  payment_terms: z.number().min(0),
  bank_name: z.string().optional(),
  bank_account: z.string().optional(),
  bank_holder: z.string().optional(),
  notes: z.string().optional(),
});

type EditVendorForm = z.infer<typeof editVendorSchema>;

async function fetchVendor(id: string): Promise<Vendor> {
  const res = await apiClient.get<{ data: Vendor }>(`/vendors/${id}`);
  const body = res.data as { data: Vendor };
  return body.data ?? (body as unknown as Vendor);
}

export default function EditVendorPage() {
  const router = useRouter();
  const params = useParams();
  const id = params.id as string;

  const { data: vendor, isLoading } = useQuery({
    queryKey: ["vendor", id],
    queryFn: () => fetchVendor(id),
    enabled: !!id,
  });

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<EditVendorForm>({
    resolver: zodResolver(editVendorSchema),
    defaultValues: {
      name: "",
      email: "",
      phone: "",
      address: "",
      tax_id: "",
      contact_person: "",
      payment_terms: 30,
      bank_name: "",
      bank_account: "",
      bank_holder: "",
      notes: "",
    },
  });

  useEffect(() => {
    if (vendor) {
      reset({
        name: vendor.name,
        email: vendor.email ?? "",
        phone: vendor.phone ?? "",
        address: vendor.address ?? "",
        tax_id: vendor.tax_id ?? "",
        contact_person: vendor.contact_person ?? "",
        payment_terms: vendor.payment_terms
          ? parseInt(String(vendor.payment_terms), 10) || 30
          : 30,
        bank_name: vendor.bank_name ?? "",
        bank_account: vendor.bank_account ?? "",
        bank_holder: vendor.bank_holder ?? "",
        notes: vendor.notes ?? "",
      });
    }
  }, [vendor, reset]);

  async function onSubmit(data: EditVendorForm) {
    try {
      const payload = {
        ...data,
        email: data.email || undefined,
      };
      await apiClient.put(`/vendors/${id}`, payload);
      toast.success("Vendor updated successfully");
      router.push(`/vendors/${id}`);
    } catch (err: unknown) {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to update vendor";
      toast.error(
        typeof message === "string" ? message : "Failed to update vendor"
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

  if (!vendor) {
    return null;
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="Edit Vendor"
        description="Update vendor details"
      />
      <form onSubmit={handleSubmit(onSubmit)}>
        <Card>
          <CardHeader>
            <CardTitle>Vendor Details</CardTitle>
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
                <p className="text-sm text-destructive">
                  {errors.name.message}
                </p>
              )}
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor="email">Email</Label>
                <Input
                  id="email"
                  type="email"
                  {...register("email")}
                  aria-invalid={!!errors.email}
                />
                {errors.email && (
                  <p className="text-sm text-destructive">
                    {errors.email.message}
                  </p>
                )}
              </div>
              <div className="space-y-2">
                <Label htmlFor="phone">Phone</Label>
                <Input id="phone" {...register("phone")} />
              </div>
            </div>
            <div className="space-y-2">
              <Label htmlFor="address">Address</Label>
              <Textarea id="address" {...register("address")} rows={3} />
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor="tax_id">Tax ID (NPWP)</Label>
                <Input id="tax_id" {...register("tax_id")} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="contact_person">Contact Person</Label>
                <Input id="contact_person" {...register("contact_person")} />
              </div>
            </div>
            <div className="space-y-2">
              <Label htmlFor="payment_terms">Payment Terms (days)</Label>
              <Input
                id="payment_terms"
                type="number"
                {...register("payment_terms")}
              />
              {errors.payment_terms && (
                <p className="text-sm text-destructive">
                  {errors.payment_terms.message}
                </p>
              )}
            </div>
            <div className="border-t pt-4">
              <h4 className="mb-4 font-medium">Bank Information</h4>
              <div className="grid gap-4 sm:grid-cols-3">
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
              </div>
            </div>
            <div className="space-y-2">
              <Label htmlFor="notes">Notes</Label>
              <Textarea id="notes" {...register("notes")} rows={3} />
            </div>
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
                "Save"
              )}
            </Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  );
}
