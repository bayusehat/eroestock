"use client";

import { useParams, useRouter } from "next/navigation";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import {
  Pencil,
  FileText,
  Receipt,
  TrendingUp,
  ArrowLeft,
} from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Client } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button, buttonVariants } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency, formatDate } from "@/lib/format";

interface ClientDetailResponse {
  client: Client;
  total_work_orders?: number;
  total_invoices?: number;
  total_revenue?: number;
  recent_work_orders?: Array<{
    id: number;
    wo_number: string;
    title: string;
    status: string;
    grand_total: number;
    order_date: string;
  }>;
  recent_invoices?: Array<{
    id: number;
    invoice_no: string;
    grand_total: number;
    status: string;
    issue_date: string;
  }>;
}

async function fetchClientDetail(id: string): Promise<ClientDetailResponse> {
  const res = await apiClient.get<{ data: ClientDetailResponse }>(
    `/clients/${id}`
  );
  const body = res.data as { data: ClientDetailResponse };
  return body.data ?? (body as unknown as ClientDetailResponse);
}

export default function ClientDetailPage() {
  const params = useParams();
  const router = useRouter();
  const id = params.id as string;

  const { data, isLoading } = useQuery({
    queryKey: ["client", id],
    queryFn: () => fetchClientDetail(id),
    enabled: !!id,
  });

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-48" />
        <Skeleton className="h-64" />
      </div>
    );
  }

  if (!data) {
    return null;
  }

  const { client } = data;

  return (
    <div className="space-y-6">
      <PageHeader
        title={client.name}
        description={client.code ? `Code: ${client.code}` : undefined}
        children={
          <div className="flex items-center gap-2">
            <Button variant="outline" onClick={() => router.back()}>
              <ArrowLeft className="mr-2 size-4" />
              Back
            </Button>
            <Link
              href={`/clients/${id}/edit`}
              className={buttonVariants()}
            >
              <Pencil className="mr-2 size-4" />
              Edit
            </Link>
          </div>
        }
      />
      <div className="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Total Work Orders
            </CardTitle>
            <FileText className="size-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {data.total_work_orders ?? 0}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Total Invoices
            </CardTitle>
            <Receipt className="size-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {data.total_invoices ?? 0}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">
              Total Revenue
            </CardTitle>
            <TrendingUp className="size-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {formatCurrency(data.total_revenue ?? 0)}
            </div>
          </CardContent>
        </Card>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Client Information</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <p className="text-sm text-muted-foreground">Status</p>
              <Badge variant={client.is_active ? "default" : "secondary"}>
                {client.is_active ? "Active" : "Inactive"}
              </Badge>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Email</p>
              <p className="font-medium">{client.email ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Phone</p>
              <p className="font-medium">{client.phone ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Contact Person</p>
              <p className="font-medium">{client.contact_person ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Payment Terms</p>
              <p className="font-medium">{client.payment_terms ?? "-"}</p>
            </div>
            <div>
              <p className="text-sm text-muted-foreground">Tax ID (NPWP)</p>
              <p className="font-medium">{client.tax_id ?? "-"}</p>
            </div>
          </div>
          {client.address && (
            <div>
              <p className="text-sm text-muted-foreground">Address</p>
              <p className="font-medium">{client.address}</p>
            </div>
          )}
          {client.notes && (
            <div>
              <p className="text-sm text-muted-foreground">Notes</p>
              <p className="font-medium">{client.notes}</p>
            </div>
          )}
        </CardContent>
      </Card>
      {(data.recent_work_orders?.length ?? 0) > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Recent Work Orders</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>WO Number</TableHead>
                  <TableHead>Title</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Order Date</TableHead>
                  <TableHead className="text-right">Total</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {data.recent_work_orders?.map((wo) => (
                  <TableRow key={wo.id}>
                    <TableCell>
                      <Link
                        href={`/work-orders/${wo.id}`}
                        className="font-medium text-primary hover:underline"
                      >
                        {wo.wo_number}
                      </Link>
                    </TableCell>
                    <TableCell>{wo.title}</TableCell>
                    <TableCell>
                      <Badge variant="outline">{wo.status}</Badge>
                    </TableCell>
                    <TableCell>{formatDate(wo.order_date)}</TableCell>
                    <TableCell className="text-right">
                      {formatCurrency(wo.grand_total)}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      )}
      {(data.recent_invoices?.length ?? 0) > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Recent Invoices</CardTitle>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Invoice No</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Issue Date</TableHead>
                  <TableHead className="text-right">Total</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {data.recent_invoices?.map((inv) => (
                  <TableRow key={inv.id}>
                    <TableCell>
                      <Link
                        href={`/invoices/${inv.id}`}
                        className="font-medium text-primary hover:underline"
                      >
                        {inv.invoice_no}
                      </Link>
                    </TableCell>
                    <TableCell>
                      <Badge variant="outline">{inv.status}</Badge>
                    </TableCell>
                    <TableCell>{formatDate(inv.issue_date)}</TableCell>
                    <TableCell className="text-right">
                      {formatCurrency(inv.grand_total)}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      )}
      {(!data.recent_work_orders || data.recent_work_orders.length === 0) &&
        (!data.recent_invoices || data.recent_invoices.length === 0) && (
          <Card>
            <CardContent className="flex flex-col items-center justify-center py-12 text-center">
              <FileText className="mb-4 size-12 text-muted-foreground" />
              <p className="text-muted-foreground">
                No work orders or invoices yet for this client.
              </p>
            </CardContent>
          </Card>
        )}
    </div>
  );
}
