"use client";

import { useEffect } from "react";
import { useRouter, useParams } from "next/navigation";
import { useForm, Controller } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useQuery } from "@tanstack/react-query";
import { Loader2 } from "lucide-react";
import { apiClient } from "@/lib/api";
import type { Account } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { CurrencyInput } from "@/components/ui/currency-input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Checkbox } from "@/components/ui/checkbox";
import { Skeleton } from "@/components/ui/skeleton";
import { toast } from "sonner";

const ACCOUNT_TYPES = ["asset", "liability", "equity", "revenue", "expense"] as const;

const editAccountSchema = z.object({
  code: z.string().min(1, "Code is required"),
  name: z.string().min(1, "Name is required"),
  type: z.enum(ACCOUNT_TYPES),
  sub_type: z.string().optional(),
  parent_id: z.number().nullable().optional(),
  is_header: z.boolean(),
  description: z.string().optional(),
  opening_balance: z.number(),
  is_active: z.boolean(),
});

type EditAccountForm = z.infer<typeof editAccountSchema>;

async function fetchAccount(id: string): Promise<Account> {
  const res = await apiClient.get<{ data: Account }>(`/accounts/${id}`);
  const body = res.data as { data: Account };
  return body.data ?? (body as unknown as Account);
}

async function fetchParentOptions(): Promise<Account[]> {
  const res = await apiClient.get<{ data: Account[] | { data: Account[] } }>("/accounts/tree");
  const body = res.data as { data?: Account[] | { data?: Account[] } };
  const raw = body.data;
  const data = Array.isArray(raw)
    ? raw
    : Array.isArray((raw as { data?: Account[] })?.data)
      ? (raw as { data: Account[] }).data
      : [];
  const flatten = (accs: Account[]): Account[] => {
    const result: Account[] = [];
    for (const a of accs) {
      result.push(a);
      if (a.children?.length) result.push(...flatten(a.children));
    }
    return result;
  };
  return flatten(data);
}

export default function EditAccountPage() {
  const router = useRouter();
  const params = useParams();
  const id = params.id as string;

  const { data: account, isLoading } = useQuery({
    queryKey: ["account", id],
    queryFn: () => fetchAccount(id),
    enabled: !!id,
  });

  const { data: parentOptions = [] } = useQuery({
    queryKey: ["accounts-tree"],
    queryFn: fetchParentOptions,
  });

  const {
    register,
    control,
    handleSubmit,
    setValue,
    watch,
    reset,
    formState: { errors, isSubmitting },
  } = useForm<EditAccountForm>({
    resolver: zodResolver(editAccountSchema),
    defaultValues: {
      code: "",
      name: "",
      type: "asset",
      sub_type: "",
      parent_id: null,
      is_header: false,
      description: "",
      opening_balance: 0,
      is_active: true,
    },
  });

  useEffect(() => {
    if (account) {
      reset({
        code: account.code,
        name: account.name,
        type: account.type as (typeof ACCOUNT_TYPES)[number],
        sub_type: account.sub_type ?? "",
        parent_id: account.parent_id ?? null,
        is_header: account.is_header ?? false,
        description: account.description ?? "",
        opening_balance: account.opening_balance ?? 0,
        is_active: account.is_active ?? true,
      });
    }
  }, [account, reset]);

  async function onSubmit(data: EditAccountForm) {
    try {
      await apiClient.put(`/accounts/${id}`, {
        code: data.code,
        name: data.name,
        type: data.type,
        sub_type: data.sub_type || undefined,
        parent_id: data.parent_id ?? undefined,
        is_header: data.is_header,
        description: data.description || undefined,
        opening_balance: data.opening_balance,
        is_active: data.is_active,
      });
      toast.success("Account updated successfully");
      router.push("/accounts");
    } catch (err: unknown) {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data?.message
          : "Failed to update account";
      toast.error(typeof message === "string" ? message : "Failed to update account");
    }
  }

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-64" />
      </div>
    );
  }

  if (!account) {
    return null;
  }

  return (
    <div className="space-y-6">
      <PageHeader
        title="Edit Account"
        description={`Editing ${account.code} - ${account.name}`}
      />
      <form onSubmit={handleSubmit(onSubmit)}>
        <Card>
          <CardHeader>
            <CardTitle>Account Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor="code">Code *</Label>
                <Input id="code" {...register("code")} aria-invalid={!!errors.code} />
                {errors.code && (
                  <p className="text-sm text-destructive">{errors.code.message}</p>
                )}
              </div>
              <div className="space-y-2">
                <Label htmlFor="name">Name *</Label>
                <Input id="name" {...register("name")} aria-invalid={!!errors.name} />
                {errors.name && (
                  <p className="text-sm text-destructive">{errors.name.message}</p>
                )}
              </div>
            </div>
            <div className="space-y-2">
              <Label>Type *</Label>
              <Select
                value={watch("type")}
                onValueChange={(v) => setValue("type", v as (typeof ACCOUNT_TYPES)[number])}
              >
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select type" />
                </SelectTrigger>
                <SelectContent>
                  {ACCOUNT_TYPES.map((t) => (
                    <SelectItem key={t} value={t}>
                      {t.charAt(0).toUpperCase() + t.slice(1)}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label htmlFor="sub_type">Sub Type</Label>
              <Input id="sub_type" {...register("sub_type")} />
            </div>
            <div className="space-y-2">
              <Label>Parent Account</Label>
              <Select
                value={watch("parent_id") ? String(watch("parent_id")) : "none"}
                onValueChange={(v) =>
                  setValue("parent_id", v && v !== "none" ? parseInt(v, 10) : null)
                }
              >
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="None">
                    {watch("parent_id")
                      ? (() => {
                          const a = parentOptions.find(
                            (acc) => acc.id === watch("parent_id")
                          );
                          return a ? `${a.code} - ${a.name}` : null;
                        })()
                      : null}
                  </SelectValue>
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="none">None</SelectItem>
                  {parentOptions
                    .filter((a) => a.id !== parseInt(id, 10))
                    .map((a) => (
                      <SelectItem key={a.id} value={String(a.id)}>
                        {a.code} - {a.name}
                      </SelectItem>
                    ))}
                </SelectContent>
              </Select>
            </div>
            <div className="flex items-center gap-2">
              <Checkbox
                id="is_header"
                checked={watch("is_header")}
                onCheckedChange={(c) => setValue("is_header", !!c)}
              />
              <Label htmlFor="is_header" className="font-normal cursor-pointer">
                Header account (no transactions)
              </Label>
            </div>
            <div className="space-y-2">
              <Label htmlFor="opening_balance">Opening Balance</Label>
              <Controller
                name="opening_balance"
                control={control}
                render={({ field }) => (
                  <CurrencyInput
                    id="opening_balance"
                    value={field.value}
                    onChange={field.onChange}
                  />
                )}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="description">Description</Label>
              <Textarea id="description" {...register("description")} rows={2} />
            </div>
            <div className="flex items-center gap-2">
              <Checkbox
                id="is_active"
                checked={watch("is_active")}
                onCheckedChange={(c) => setValue("is_active", !!c)}
              />
              <Label htmlFor="is_active" className="font-normal cursor-pointer">
                Active
              </Label>
            </div>
          </CardContent>
          <CardFooter>
            <Button
              type="button"
              variant="outline"
              onClick={() => router.push("/accounts")}
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
                "Save Changes"
              )}
            </Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  );
}
