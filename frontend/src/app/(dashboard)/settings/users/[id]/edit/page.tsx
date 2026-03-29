"use client";

import { useEffect } from "react";
import { useRouter, useParams } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useQuery } from "@tanstack/react-query";
import { Loader2 } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Role, User } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Checkbox } from "@/components/ui/checkbox";
import { Skeleton } from "@/components/ui/skeleton";
import { toast } from "sonner";

const editUserSchema = z
  .object({
    name: z.string().min(1, "Name is required"),
    email: z.string().email("Please enter a valid email"),
    password: z.string().optional(),
    password_confirmation: z.string().optional(),
    phone: z.string().optional(),
    role_ids: z.array(z.number()).optional(),
  })
  .refine(
    (data) =>
      !data.password || data.password === data.password_confirmation,
    { message: "Passwords do not match", path: ["password_confirmation"] }
  );

type EditUserForm = z.infer<typeof editUserSchema>;

async function fetchUser(id: string): Promise<User> {
  const res = await apiClient.get<{ data: User }>(`/users/${id}`);
  const body = res.data as { data: User };
  return body.data ?? (body as unknown as User);
}

async function fetchRoles(): Promise<Role[]> {
  const res = await apiClient.get<{ data: Role[] }>("/roles");
  const body = res.data as { data: Role[] };
  return body.data ?? (body as unknown as Role[]);
}

export default function EditUserPage() {
  const router = useRouter();
  const params = useParams();
  const id = params.id as string;

  const { data: user, isLoading } = useQuery({
    queryKey: ["user", id],
    queryFn: () => fetchUser(id),
    enabled: !!id,
  });

  const { data: roles = [] } = useQuery({
    queryKey: ["roles"],
    queryFn: fetchRoles,
  });

  const {
    register,
    handleSubmit,
    watch,
    setValue,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<EditUserForm>({
    resolver: zodResolver(editUserSchema),
    defaultValues: {
      name: "",
      email: "",
      password: "",
      password_confirmation: "",
      phone: "",
      role_ids: [],
    },
  });

  useEffect(() => {
    if (user) {
      reset({
        name: user.name,
        email: user.email,
        phone: user.phone ?? "",
        role_ids: user.roles?.map((r) => r.id) ?? [],
      });
    }
  }, [user, reset]);

  const roleIds = watch("role_ids") ?? [];

  async function onSubmit(data: EditUserForm) {
    try {
      const { password_confirmation, role_ids, ...rest } = data;
      const payload: Record<string, unknown> = { ...rest, roles: role_ids };
      if (rest.password) {
        payload.password = rest.password;
        payload.password_confirmation = password_confirmation;
      }
      await apiClient.put(`/users/${id}`, payload);
      toast.success("User updated successfully");
      router.push("/settings/users");
    } catch (err: unknown) {
      const message = err && typeof err === "object" && "response" in err
        ? (err as { response?: { data?: { message?: string } } }).response?.data?.message
        : "Failed to update user";
      toast.error(typeof message === "string" ? message : "Failed to update user");
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

  if (!user) {
    return null;
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="Edit User"
        description="Update user details"
      />
      <form onSubmit={handleSubmit(onSubmit)}>
        <Card>
          <CardHeader>
            <CardTitle>User Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="name">Name</Label>
              <Input id="name" {...register("name")} aria-invalid={!!errors.name} />
              {errors.name && (
                <p className="text-sm text-destructive">{errors.name.message}</p>
              )}
            </div>
            <div className="space-y-2">
              <Label htmlFor="email">Email</Label>
              <Input id="email" type="email" {...register("email")} aria-invalid={!!errors.email} />
              {errors.email && (
                <p className="text-sm text-destructive">{errors.email.message}</p>
              )}
            </div>
            <div className="space-y-2">
              <Label htmlFor="password">Password (leave blank to keep current)</Label>
              <Input id="password" type="password" {...register("password")} />
            </div>
            <div className="space-y-2">
              <Label htmlFor="password_confirmation">Confirm Password</Label>
              <Input
                id="password_confirmation"
                type="password"
                {...register("password_confirmation")}
                aria-invalid={!!errors.password_confirmation}
              />
              {errors.password_confirmation && (
                <p className="text-sm text-destructive">{errors.password_confirmation.message}</p>
              )}
            </div>
            <div className="space-y-2">
              <Label htmlFor="phone">Phone</Label>
              <Input id="phone" {...register("phone")} />
            </div>
            <div className="space-y-2">
              <Label>Roles</Label>
              <div className="space-y-2">
                {roles.map((role) => (
                  <div key={role.id} className="flex items-center space-x-2">
                    <Checkbox
                      id={`role-${role.id}`}
                      checked={roleIds.includes(role.id)}
                      onCheckedChange={(checked) => {
                        setValue(
                          "role_ids",
                          checked
                            ? [...roleIds, role.id]
                            : roleIds.filter((rid) => rid !== role.id)
                        );
                      }}
                    />
                    <label
                      htmlFor={`role-${role.id}`}
                      className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                    >
                      {role.name}
                    </label>
                  </div>
                ))}
              </div>
            </div>
          </CardContent>
          <CardFooter>
            <Button type="button" variant="outline" onClick={() => router.back()}>
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
