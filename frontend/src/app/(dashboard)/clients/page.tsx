"use client";

import { useMemo, useState } from "react";
import Link from "next/link";
import { useQuery } from "@tanstack/react-query";
import { type ColumnDef } from "@tanstack/react-table";
import { MoreHorizontal, Pencil, Eye, UserMinus, UserPlus } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Client } from "@/types";
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

async function fetchClients(): Promise<Client[]> {
  const res = await apiClient.get<{ data: Client[] }>("/clients");
  const body = res.data as { data: Client[] };
  return body.data ?? (body as unknown as Client[]);
}

export default function ClientsPage() {
  const [statusFilter, setStatusFilter] = useState<string>("all");

  const { data: clients = [], isLoading } = useQuery({
    queryKey: ["clients"],
    queryFn: fetchClients,
  });

  const filteredData = useMemo(() => {
    if (statusFilter === "all") return clients;
    if (statusFilter === "active") return clients.filter((c) => c.is_active);
    return clients.filter((c) => !c.is_active);
  }, [clients, statusFilter]);

  const columns: ColumnDef<Client>[] = [
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
      accessorKey: "payment_terms",
      header: "Payment Terms",
      cell: ({ row }) => row.original.payment_terms ?? "-",
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
                <Link href={`/clients/${row.original.id}`}>
                  <Eye className="mr-2 size-4" />
                  <span>View</span>
                </Link>
              }
            />
            <DropdownMenuItem
              render={
                <Link href={`/clients/${row.original.id}/edit`}>
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
        title="Clients"
        description="Manage your clients"
        children={
          <Link href="/clients/create" className={buttonVariants()}>
            <UserPlus className="mr-2 size-4" />
            Add Client
          </Link>
        }
      />
      <div className="space-y-4">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <Select value={statusFilter} onValueChange={(v) => setStatusFilter(v ?? "all")}>
            <SelectTrigger className="w-full">
              <SelectValue placeholder="Status">
                {statusFilter && statusFilter !== "all"
                  ? statusFilter === "active"
                    ? "Active"
                    : "Inactive"
                  : null}
              </SelectValue>
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
          searchPlaceholder="Search clients..."
          isLoading={isLoading}
          emptyMessage="No clients found."
        />
      </div>
    </div>
  );
}
