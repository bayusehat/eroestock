"use client";

import { useRouter } from "next/navigation";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useQuery } from "@tanstack/react-query";
import { Loader2 } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Role } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Checkbox } from "@/components/ui/checkbox";
import { toast } from "sonner";

const createUserSchema = z
  .object({
    name: z.string().min(1, "Name is required"),
    email: z.string().email("Please enter a valid email"),
    password: z.string().min(8, "Password must be at least 8 characters"),
    password_confirmation: z.string().min(1, "Please confirm your password"),
    phone: z.string().optional(),
    role_ids: z.array(z.number()).optional(),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: "Passwords do not match",
    path: ["password_confirmation"],
  });

type CreateUserForm = z.infer<typeof createUserSchema>;

async function fetchRoles(): Promise<Role[]> {
  const res = await apiClient.get<{ data: Role[] }>("/roles");
  const body = res.data as { data: Role[] };
  return body.data ?? (body as unknown as Role[]);
}

export default function CreateUserPage() {
  const router = useRouter();
  const { data: roles = [] } = useQuery({
    queryKey: ["roles"],
    queryFn: fetchRoles,
  });

  const {
    register,
    handleSubmit,
    watch,
    setValue,
    formState: { errors, isSubmitting },
  } = useForm<CreateUserForm>({
    resolver: zodResolver(createUserSchema),
    defaultValues: { name: "", email: "", password: "", password_confirmation: "", phone: "", role_ids: [] },
  });

  const roleIds = watch("role_ids") ?? [];

  async function onSubmit(data: CreateUserForm) {
    try {
      const { password_confirmation, role_ids, ...rest } = data;
      const payload = { ...rest, password_confirmation, roles: role_ids };
      await apiClient.post("/users", payload);
      toast.success("User created successfully");
      router.push("/settings/users");
    } catch (err: unknown) {
      const message = err && typeof err === "object" && "response" in err
        ? (err as { response?: { data?: { message?: string } } }).response?.data?.message
        : "Failed to create user";
      toast.error(typeof message === "string" ? message : "Failed to create user");
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="Create User"
        description="Add a new user to the system"
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
              <Label htmlFor="password">Password</Label>
              <Input
                id="password"
                type="password"
                {...register("password")}
                aria-invalid={!!errors.password}
              />
              {errors.password && (
                <p className="text-sm text-destructive">{errors.password.message}</p>
              )}
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
                            : roleIds.filter((id) => id !== role.id)
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
