"use client";

import { useParams, useRouter } from "next/navigation";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { Pencil, ArrowLeft } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Vendor } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button, buttonVariants } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";

async function fetchVendor(id: string): Promise<Vendor> {
  const res = await apiClient.get<{ data: Vendor }>(`/vendors/${id}`);
  const body = res.data as { data: Vendor };
  return body.data ?? (body as unknown as Vendor);
}

export default function VendorDetailPage() {
  const params = useParams();
  const router = useRouter();
  const id = params.id as string;

  const { data: vendor, isLoading } = useQuery({
    queryKey: ["vendor", id],
    queryFn: () => fetchVendor(id),
    enabled: !!id,
  });

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-48" />
      </div>
    );
  }

  if (!vendor) {
    return null;
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title={vendor.name}
        description={vendor.code ? `Code: ${vendor.code}` : undefined}
        children={
          <div className="flex items-center gap-2">
            <Button variant="outline" onClick={() => router.back()}>
              <ArrowLeft className="mr-2 size-4" />
              Back
            </Button>
            <Link
              href={`/vendors/${id}/edit`}
              className={buttonVariants()}
            >
              <Pencil className="mr-2 size-4" />
              Edit
            </Link>
          </div>
        }
      />
      <Card>
        <CardHeader>
          <CardTitle>Vendor Information</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <p className="text-sm text-muted-foreground">Status</p>
              <Badge variant={vendor.is_active ? "default" : "secondary"}>
                {vendor.is_active ? "Active" : "Inactive"}
              </Badge>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Email</p>
              <p className="font-medium">{vendor.email ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Phone</p>
              <p className="font-medium">{vendor.phone ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Contact Person</p>
              <p className="font-medium">{vendor.contact_person ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Payment Terms</p>
              <p className="font-medium">{vendor.payment_terms ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Tax ID (NPWP)</p>
              <p className="font-medium">{vendor.tax_id ?? "-"}</p>
            </div>
          </div>
          {(vendor.bank_name || vendor.bank_account || vendor.bank_holder) && (
            <div className="border-t pt-4">
              <h4 className="mb-4 font-medium">Bank Information</h4>
              <div className="grid gap-4 sm:grid-cols-3">
                <div>
                  <p className="text-sm text-muted-foreground">Bank Name</p>
                  <p className="font-medium">{vendor.bank_name ?? "-"}</p>
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">Account Number</p>
                  <p className="font-medium">{vendor.bank_account ?? "-"}</p>
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">Account Holder</p>
                  <p className="font-medium">{vendor.bank_holder ?? "-"}</p>
                </div>
              </div>
            </div>
          )}
          {vendor.address && (
            <div>
              <p className="text-sm text-muted-foreground">Address</p>
              <p className="font-medium">{vendor.address}</p>
            </div>
          )}
          {vendor.notes && (
            <div>
              <p className="text-sm text-muted-foreground">Notes</p>
              <p className="font-medium">{vendor.notes}</p>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
