"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { type ColumnDef } from "@tanstack/react-table";
import { MoreHorizontal, Pencil, Eye, UserMinus, UserPlus } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Vendor } from "@/types";
import { PageHeader } from "@/components/page-header";
import { DataTable } from "@/components/data-table";
import { Button, buttonVariants } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

async function fetchVendors(): Promise<Vendor[]> {
  const res = await apiClient.get<{ data: Vendor[] }>("/vendors");
  const body = res.data as { data: Vendor[] };
  return body.data ?? (body as unknown as Vendor[]);
}

export default function VendorsPage() {
  const [statusFilter, setStatusFilter] = useState<string>("all");

  const { data: vendors = [], isLoading } = useQuery({
    queryKey: ["vendors"],
    queryFn: fetchVendors,
  });

  const filteredData = useMemo(() => {
    if (statusFilter === "all") return vendors;
    if (statusFilter === "active") return vendors.filter((v) => v.is_active);
    return vendors.filter((v) => !v.is_active);
  }, [vendors, statusFilter]);

  const columns: ColumnDef<Vendor>[] = [
    {
      accessorKey: "code",
      header: "Code",
      cell: ({ row }) => row.original.code ?? "-",
    },
    {
      accessorKey: "name",
      header: "Name",
    },
    {
      accessorKey: "email",
      header: "Email",
      cell: ({ row }) => row.original.email ?? "-",
    },
    {
      accessorKey: "phone",
      header: "Phone",
      cell: ({ row }) => row.original.phone ?? "-",
    },
    {
      accessorKey: "contact_person",
      header: "Contact Person",
      cell: ({ row }) => row.original.contact_person ?? "-",
    },
    {
      accessorKey: "bank_name",
      header: "Bank Name",
      cell: ({ row }) => row.original.bank_name ?? "-",
    },
    {
      accessorKey: "bank_account",
      header: "Bank Account",
      cell: ({ row }) => row.original.bank_account ?? "-",
    },
    {
      id: "status",
      header: "Status",
      cell: ({ row }) => (
        <Badge variant={row.original.is_active ? "default" : "secondary"}>
          {row.original.is_active ? "Active" : "Inactive"}
        </Badge>
      ),
    },
    {
      id: "actions",
      header: "",
      cell: ({ row }) => (
        <DropdownMenu>
          <DropdownMenuTrigger
            render={
              <Button variant="ghost" size="icon-sm">
                <MoreHorizontal className="size-4" />
                <span className="sr-only">Toggle menu</span>
              </Button>
            }
          />
          <DropdownMenuContent align="end">
            <DropdownMenuItem
              render={
                <Link href={`/vendors/${row.original.id}`}>
                  <Eye className="mr-2 size-4" />
                  <span>View</span>
                </Link>
              }
            />
            <DropdownMenuItem
              render={
                <Link href={`/vendors/${row.original.id}/edit`}>
                  <Pencil className="mr-2 size-4" />
                  <span>Edit</span>
                </Link>
              }
            />
            <DropdownMenuItem>
              {row.original.is_active ? (
                <>
                  <UserMinus className="mr-2 size-4" />
                  <span>Deactivate</span>
                </>
              ) : (
                <>
                  <UserPlus className="mr-2 size-4" />
                  <span>Activate</span>
                </>
              )}
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      ),
    },
  ];

  return (
    <div className="space-y-6">
      <PageHeader
        title="Vendors"
        description="Manage your vendors"
        children={
          <Link href="/vendors/create" className={buttonVariants()}>
            <UserPlus className="mr-2 size-4" />
            Add Vendor
          </Link>
        }
      />
      <div className="space-y-4">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <Select value={statusFilter} onValueChange={(v) => setStatusFilter(v ?? "all")}>
            <SelectTrigger className="w-[140px]">
              <SelectValue placeholder="Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All status</SelectItem>
              <SelectItem value="active">Active</SelectItem>
              <SelectItem value="inactive">Inactive</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <DataTable
          columns={columns}
          data={filteredData}
          searchKey="name"
          searchPlaceholder="Search vendors..."
          isLoading={isLoading}
          emptyMessage="No vendors found."
        />
      </div>
    </div>
  );
}
