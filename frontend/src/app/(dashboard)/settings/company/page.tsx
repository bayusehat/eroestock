"use client";

import { useEffect } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { Loader2 } from "lucide-react";
import { apiClient } from "@/lib/api";
import { PageHeader } from "@/components/page-header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { toast } from "sonner";

const companySettingsSchema = z.object({
  company_name: z.string().min(1, "Company name is required"),
  company_logo: z.string().optional(),
  company_address: z.string().optional(),
  company_phone: z.string().optional(),
  company_email: z.string().email().optional().or(z.literal("")),
  company_tax_id: z.string().optional(),
  currency: z.string().optional(),
  fiscal_year_start: z.string().optional(),
  invoice_prefix: z.string().optional(),
  wo_prefix: z.string().optional(),
  default_payment_terms: z.string().optional(),
  date_format: z.string().optional(),
});

type CompanySettingsForm = z.infer<typeof companySettingsSchema>;

async function fetchSettings(): Promise<Record<string, string>> {
  const res = await apiClient.get<{ data: Record<string, string> }>("/settings");
  const body = res.data as { data?: Record<string, string> };
  return body.data ?? {};
}

export default function CompanySettingsPage() {
  const queryClient = useQueryClient();
  const { data: settings, isLoading } = useQuery({
    queryKey: ["company-settings"],
    queryFn: fetchSettings,
  });

  const mutation = useMutation({
    mutationFn: async (data: CompanySettingsForm) => {
      const settings: Record<string, string> = {
        company_name: data.company_name ?? "",
        company_logo: data.company_logo ?? "",
        address: data.company_address ?? "",
        phone: data.company_phone ?? "",
        email: data.company_email ?? "",
        tax_id: data.company_tax_id ?? "",
        currency: data.currency ?? "",
        fiscal_year_start: data.fiscal_year_start ?? "",
        invoice_prefix: data.invoice_prefix ?? "",
        wo_prefix: data.wo_prefix ?? "",
        default_payment_terms: data.default_payment_terms ?? "",
        date_format: data.date_format ?? "",
      };
      await apiClient.put("/settings", { settings });
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["company-settings"] });
      toast.success("Settings saved successfully");
    },
    onError: () => {
      toast.error("Failed to save settings");
    },
  });

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<CompanySettingsForm>({
    resolver: zodResolver(companySettingsSchema),
    defaultValues: {
      company_name: "",
      company_address: "",
      company_phone: "",
      company_email: "",
      company_tax_id: "",
      currency: "IDR",
      fiscal_year_start: "1",
      invoice_prefix: "INV",
      wo_prefix: "WO",
      default_payment_terms: "30",
      date_format: "DD/MM/YYYY",
    },
  });

  useEffect(() => {
    if (settings) {
      reset({
        company_name: settings.company_name ?? "",
        company_logo: settings.company_logo ?? "",
        company_address: settings.address ?? "",
        company_phone: settings.phone ?? "",
        company_email: settings.email ?? "",
        company_tax_id: settings.tax_id ?? "",
        currency: settings.currency ?? "IDR",
        fiscal_year_start: settings.fiscal_year_start ?? "1",
        invoice_prefix: settings.invoice_prefix ?? "INV",
        wo_prefix: settings.wo_prefix ?? "WO",
        default_payment_terms: settings.default_payment_terms ?? "30",
        date_format: settings.date_format ?? "DD/MM/YYYY",
      });
    }
  }, [settings, reset]);

  return (
    <div className="space-y-6">
      <PageHeader
        title="Company Settings"
        description="Manage your company information"
      />
      <form onSubmit={handleSubmit((data) => mutation.mutate(data))}>
        <Card>
          <CardHeader>
            <CardTitle>Company Information</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor="company_name">Company Name</Label>
                <Input
                  id="company_name"
                  {...register("company_name")}
                  aria-invalid={!!errors.company_name}
                  disabled={isLoading}
                />
                {errors.company_name && (
                  <p className="text-sm text-destructive">{errors.company_name.message}</p>
                )}
              </div>
              <div className="space-y-2">
                <Label htmlFor="company_email">Email</Label>
                <Input
                  id="company_email"
                  type="email"
                  {...register("company_email")}
                  disabled={isLoading}
                />
              </div>
            </div>
            <div className="space-y-2">
              <Label htmlFor="company_address">Address</Label>
              <Input id="company_address" {...register("company_address")} disabled={isLoading} />
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor="company_phone">Phone</Label>
                <Input id="company_phone" {...register("company_phone")} disabled={isLoading} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="company_tax_id">Tax ID</Label>
                <Input id="company_tax_id" {...register("company_tax_id")} disabled={isLoading} />
              </div>
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor="currency">Currency</Label>
                <Input id="currency" {...register("currency")} disabled={isLoading} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="fiscal_year_start">Fiscal Year Start (Month)</Label>
                <Input id="fiscal_year_start" {...register("fiscal_year_start")} disabled={isLoading} />
              </div>
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor="invoice_prefix">Invoice Prefix</Label>
                <Input id="invoice_prefix" {...register("invoice_prefix")} disabled={isLoading} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="wo_prefix">Work Order Prefix</Label>
                <Input id="wo_prefix" {...register("wo_prefix")} disabled={isLoading} />
              </div>
            </div>
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor="default_payment_terms">Default Payment Terms (days)</Label>
                <Input id="default_payment_terms" {...register("default_payment_terms")} disabled={isLoading} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="date_format">Date Format</Label>
                <Input id="date_format" {...register("date_format")} disabled={isLoading} />
              </div>
            </div>
          </CardContent>
          <CardFooter>
            <Button type="submit" disabled={mutation.isPending || isLoading}>
              {mutation.isPending ? (
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
