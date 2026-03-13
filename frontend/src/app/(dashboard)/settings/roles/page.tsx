"use client";

import { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { Pencil, Trash2, Plus } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Role, Permission } from "@/types";
import { PageHeader } from "@/components/page-header";
import { DataTable } from "@/components/data-table";
import { type ColumnDef } from "@tanstack/react-table";
import { Button, buttonVariants } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from "@/components/ui/dialog";
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";
import { ScrollArea } from "@/components/ui/scroll-area";
import { toast } from "sonner";

interface PermissionWithModule extends Permission {
  module?: string;
}

async function fetchRoles(): Promise<Role[]> {
  const res = await apiClient.get<{ data: Role[] }>("/roles");
  const body = res.data as { data: Role[] };
  return body.data ?? (body as unknown as Role[]);
}

async function fetchPermissions(): Promise<PermissionWithModule[]> {
  const res = await apiClient.get<{ data: PermissionWithModule[] }>("/permissions");
  const body = res.data as { data: PermissionWithModule[] };
  return body.data ?? (body as unknown as PermissionWithModule[]);
}

function groupByModule(permissions: PermissionWithModule[]): Record<string, PermissionWithModule[]> {
  const grouped: Record<string, PermissionWithModule[]> = {};
  for (const p of permissions) {
    const mod = (p as PermissionWithModule).module ?? "General";
    if (!grouped[mod]) grouped[mod] = [];
    grouped[mod].push(p);
  }
  return grouped;
}

export default function RolesPage() {
  const queryClient = useQueryClient();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editingRole, setEditingRole] = useState<Role | null>(null);
  const [roleName, setRoleName] = useState("");
  const [selectedPerms, setSelectedPerms] = useState<number[]>([]);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [roleToDelete, setRoleToDelete] = useState<Role | null>(null);

  const { data: roles = [], isLoading } = useQuery({
    queryKey: ["roles"],
    queryFn: fetchRoles,
  });

  const { data: permissions = [] } = useQuery({
    queryKey: ["permissions"],
    queryFn: fetchPermissions,
  });

  const createMutation = useMutation({
    mutationFn: (payload: { name: string; permission_ids: number[] }) =>
      apiClient.post("/roles", payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["roles"] });
      setDialogOpen(false);
      resetForm();
      toast.success("Role created");
    },
    onError: (err: unknown) => {
      const msg =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data?.message
          : "Failed to create role";
      toast.error(typeof msg === "string" ? msg : "Failed to create role");
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({
      id,
      payload,
    }: {
      id: number;
      payload: { name: string; permission_ids: number[] };
    }) => apiClient.put(`/roles/${id}`, payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["roles"] });
      setDialogOpen(false);
      setEditingRole(null);
      resetForm();
      toast.success("Role updated");
    },
    onError: (err: unknown) => {
      const msg =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data?.message
          : "Failed to update role";
      toast.error(typeof msg === "string" ? msg : "Failed to update role");
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => apiClient.delete(`/roles/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["roles"] });
      setDeleteDialogOpen(false);
      setRoleToDelete(null);
      toast.success("Role deleted");
    },
    onError: (err: unknown) => {
      const msg =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data?.message
          : "Failed to delete role";
      toast.error(typeof msg === "string" ? msg : "Failed to delete role");
    },
  });

  function resetForm() {
    setRoleName("");
    setSelectedPerms([]);
  }

  function openAdd() {
    setEditingRole(null);
    resetForm();
    setDialogOpen(true);
  }

  function openEdit(role: Role) {
    setEditingRole(role);
    setRoleName(role.name);
    setSelectedPerms(role.permissions?.map((p) => p.id) ?? []);
    setDialogOpen(true);
  }

  function handleSubmit() {
    if (!roleName.trim()) {
      toast.error("Role name is required");
      return;
    }
    if (editingRole) {
      updateMutation.mutate({
        id: editingRole.id,
        payload: { name: roleName.trim(), permission_ids: selectedPerms },
      });
    } else {
      createMutation.mutate({ name: roleName.trim(), permission_ids: selectedPerms });
    }
  }

  function togglePermission(id: number) {
    setSelectedPerms((prev) =>
      prev.includes(id) ? prev.filter((p) => p !== id) : [...prev, id]
    );
  }

  const groupedPerms = groupByModule(permissions);

  const columns: ColumnDef<Role>[] = [
    {
      accessorKey: "name",
      header: "Role Name",
    },
    {
      id: "permissions_count",
      header: "Permissions",
      cell: ({ row }) => row.original.permissions?.length ?? 0,
    },
    {
      id: "users_count",
      header: "Users",
      cell: ({ row }) => {
        const role = row.original;
        return (role as Role & { users_count?: number }).users_count ?? "-";
      },
    },
    {
      id: "actions",
      header: "",
      cell: ({ row }) => {
        const role = row.original;
        const usersCount = (role as Role & { users_count?: number }).users_count ?? 0;
        return (
          <div className="flex gap-2">
            <Button variant="ghost" size="icon-sm" onClick={() => openEdit(role)}>
              <Pencil className="size-4" />
              <span className="sr-only">Edit</span>
            </Button>
            <Button
              variant="ghost"
              size="icon-sm"
              onClick={() => {
                setRoleToDelete(role);
                setDeleteDialogOpen(true);
              }}
              disabled={usersCount > 0}
              title={usersCount > 0 ? "Cannot delete role with assigned users" : "Delete"}
            >
              <Trash2 className="size-4" />
              <span className="sr-only">Delete</span>
            </Button>
          </div>
        );
      },
    },
  ];

  return (
    <div className="space-y-6">
      <PageHeader
        title="Roles & Permissions"
        description="Manage user roles and their permissions"
        children={
          <Button onClick={openAdd} className={buttonVariants()}>
            <Plus className="mr-2 size-4" />
            Add Role
          </Button>
        }
      />
      <DataTable
        columns={columns}
        data={roles}
        searchKey="name"
        searchPlaceholder="Search roles..."
        isLoading={isLoading}
        emptyMessage="No roles found."
      />
      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent className="sm:max-w-lg">
          <DialogHeader>
            <DialogTitle>{editingRole ? "Edit Role" : "Add Role"}</DialogTitle>
          </DialogHeader>
          <div className="grid gap-4 py-4">
            <div className="space-y-2">
              <Label htmlFor="role-name">Role Name</Label>
              <Input
                id="role-name"
                value={roleName}
                onChange={(e) => setRoleName(e.target.value)}
                placeholder="e.g. Accountant"
              />
            </div>
            <div className="space-y-2">
              <Label>Permissions</Label>
              <ScrollArea className="h-64 rounded-md border p-4">
                <div className="space-y-4">
                  {Object.entries(groupedPerms).map(([mod, perms]) => (
                    <div key={mod}>
                      <p className="mb-2 text-sm font-medium text-muted-foreground">
                        {mod}
                      </p>
                      <div className="flex flex-wrap gap-3">
                        {perms.map((p) => (
                          <div
                            key={p.id}
                            className="flex items-center space-x-2"
                          >
                            <Checkbox
                              id={`perm-${p.id}`}
                              checked={selectedPerms.includes(p.id)}
                              onCheckedChange={() => togglePermission(p.id)}
                            />
                            <label
                              htmlFor={`perm-${p.id}`}
                              className="text-sm cursor-pointer"
                            >
                              {p.name}
                            </label>
                          </div>
                        ))}
                      </div>
                    </div>
                  ))}
                  {permissions.length === 0 && (
                    <p className="text-sm text-muted-foreground">
                      No permissions available
                    </p>
                  )}
                </div>
              </ScrollArea>
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setDialogOpen(false)}>
              Cancel
            </Button>
            <Button
              onClick={handleSubmit}
              disabled={createMutation.isPending || updateMutation.isPending}
            >
              {editingRole ? "Update" : "Create"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
      <AlertDialog open={deleteDialogOpen} onOpenChange={setDeleteDialogOpen}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Delete Role</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to delete &quot;{roleToDelete?.name}&quot;? This action cannot
              be undone.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction
              onClick={() => roleToDelete && deleteMutation.mutate(roleToDelete.id)}
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
            >
              Delete
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
