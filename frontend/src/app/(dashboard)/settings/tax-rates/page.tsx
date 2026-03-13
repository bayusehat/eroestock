"use client";

import { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  Plus,
  Pencil,
  Trash2,
  Check,
} from "lucide-react";
import { apiClient } from "@/lib/api";
import type { TaxRate } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button, buttonVariants } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Switch } from "@/components/ui/switch";
import { Checkbox } from "@/components/ui/checkbox";
import { toast } from "sonner";

async function fetchTaxRates(): Promise<TaxRate[]> {
  const res = await apiClient.get<{ data: TaxRate[] }>("/tax-rates");
  const body = res.data as { data: TaxRate[] };
  return body.data ?? (body as unknown as TaxRate[]);
}

export default function TaxRatesPage() {
  const queryClient = useQueryClient();
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editingTaxRate, setEditingTaxRate] = useState<TaxRate | null>(null);
  const [deleteTaxRate, setDeleteTaxRate] = useState<TaxRate | null>(null);
  const [formName, setFormName] = useState("");
  const [formRate, setFormRate] = useState("");
  const [formType, setFormType] = useState("sales_tax");
  const [formIsDefault, setFormIsDefault] = useState(false);

  const { data: taxRates = [], isLoading } = useQuery({
    queryKey: ["tax-rates"],
    queryFn: fetchTaxRates,
  });

  const createMutation = useMutation({
    mutationFn: (payload: {
      name: string;
      rate: number;
      type: string;
      is_default: boolean;
    }) => apiClient.post("/tax-rates", payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["tax-rates"] });
      toast.success("Tax rate added");
      setDialogOpen(false);
      resetForm();
    },
    onError: (err: unknown) => {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to add tax rate";
      toast.error(typeof message === "string" ? message : "Failed to add tax rate");
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({
      id,
      payload,
    }: {
      id: number;
      payload: {
        name: string;
        rate: number;
        type: string;
        is_default: boolean;
      };
    }) => apiClient.put(`/tax-rates/${id}`, payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["tax-rates"] });
      toast.success("Tax rate updated");
      setDialogOpen(false);
      setEditingTaxRate(null);
      resetForm();
    },
    onError: (err: unknown) => {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to update tax rate";
      toast.error(typeof message === "string" ? message : "Failed to update tax rate");
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => apiClient.delete(`/tax-rates/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["tax-rates"] });
      toast.success("Tax rate deleted");
      setDeleteTaxRate(null);
    },
    onError: (err: unknown) => {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to delete tax rate";
      toast.error(typeof message === "string" ? message : "Failed to delete tax rate");
    },
  });

  const toggleActiveMutation = useMutation({
    mutationFn: ({ id, is_active }: { id: number; is_active: boolean }) =>
      apiClient.put(`/tax-rates/${id}`, { is_active }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["tax-rates"] });
      toast.success("Tax rate updated");
    },
    onError: (err: unknown) => {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to update tax rate";
      toast.error(typeof message === "string" ? message : "Failed to update tax rate");
    },
  });

  function resetForm() {
    setFormName("");
    setFormRate("");
    setFormType("sales_tax");
    setFormIsDefault(false);
  }

  function openAddDialog() {
    setEditingTaxRate(null);
    resetForm();
    setDialogOpen(true);
  }

  function openEditDialog(rate: TaxRate) {
    setEditingTaxRate(rate);
    setFormName(rate.name);
    setFormRate(String(rate.rate));
    setFormType(rate.type);
    setFormIsDefault(rate.is_default);
    setDialogOpen(true);
  }

  function handleSubmit() {
    const rate = parseFloat(formRate);
    if (!formName.trim()) {
      toast.error("Name is required");
      return;
    }
    if (isNaN(rate) || rate < 0) {
      toast.error("Please enter a valid rate");
      return;
    }
    const payload = {
      name: formName.trim(),
      rate,
      type: formType,
      is_default: formIsDefault,
    };
    if (editingTaxRate) {
      updateMutation.mutate({ id: editingTaxRate.id, payload });
    } else {
      createMutation.mutate(payload);
    }
  }

  const TAX_TYPES = [
    { value: "sales_tax", label: "Sales Tax" },
    { value: "income_tax", label: "Income Tax" },
    { value: "withholding", label: "Withholding" },
  ];

  return (
    <div className="space-y-6">
      <PageHeader
        title="Tax Rates"
        description="Manage tax rates and configurations"
        children={
          <Button onClick={openAddDialog} className={buttonVariants()}>
            <Plus className="mr-2 size-4" />
            Add Tax Rate
          </Button>
        }
      />
      <div className="rounded-md border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Name</TableHead>
              <TableHead>Rate (%)</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Default</TableHead>
              <TableHead>Active</TableHead>
              <TableHead className="w-[100px]">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {isLoading ? (
              <TableRow>
                <TableCell colSpan={6} className="text-center text-muted-foreground">
                  Loading...
                </TableCell>
              </TableRow>
            ) : taxRates.length === 0 ? (
              <TableRow>
                <TableCell colSpan={6} className="text-center text-muted-foreground">
                  No tax rates configured
                </TableCell>
              </TableRow>
            ) : (
              taxRates.map((rate) => (
                <TableRow key={rate.id}>
                  <TableCell className="font-medium">{rate.name}</TableCell>
                  <TableCell>{rate.rate}%</TableCell>
                  <TableCell>
                    <Badge variant="outline">
                      {TAX_TYPES.find((t) => t.value === rate.type)?.label ??
                        rate.type}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    {rate.is_default ? (
                      <Check className="size-4 text-green-600" />
                    ) : (
                      "-"
                    )}
                  </TableCell>
                  <TableCell>
                    <Switch
                      checked={rate.is_active}
                      onCheckedChange={(checked) =>
                        toggleActiveMutation.mutate({
                          id: rate.id,
                          is_active: !!checked,
                        })
                      }
                    />
                  </TableCell>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      <Button
                        variant="ghost"
                        size="icon-sm"
                        onClick={() => openEditDialog(rate)}
                      >
                        <Pencil className="size-4" />
                      </Button>
                      <Button
                        variant="ghost"
                        size="icon-sm"
                        className="text-destructive hover:text-destructive"
                        onClick={() => setDeleteTaxRate(rate)}
                      >
                        <Trash2 className="size-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>
      </div>
      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>
              {editingTaxRate ? "Edit Tax Rate" : "Add Tax Rate"}
            </DialogTitle>
          </DialogHeader>
          <div className="grid gap-4 py-4">
            <div className="space-y-2">
              <Label>Name</Label>
              <Input
                value={formName}
                onChange={(e) => setFormName(e.target.value)}
                placeholder="e.g. VAT 11%"
              />
            </div>
            <div className="space-y-2">
              <Label>Rate (%)</Label>
              <Input
                type="number"
                step="0.01"
                value={formRate}
                onChange={(e) => setFormRate(e.target.value)}
                placeholder="0"
              />
            </div>
            <div className="space-y-2">
              <Label>Type</Label>
              <Select value={formType} onValueChange={(v) => setFormType(v ?? "sales_tax")}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {TAX_TYPES.map((t) => (
                    <SelectItem key={t.value} value={t.value}>
                      {t.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="flex items-center gap-2">
              <Checkbox
                id="is_default"
                checked={formIsDefault}
                onCheckedChange={(v) => setFormIsDefault(!!v)}
              />
              <Label htmlFor="is_default">Is Default</Label>
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setDialogOpen(false)}>
              Cancel
            </Button>
            <Button
              onClick={handleSubmit}
              disabled={
                createMutation.isPending || updateMutation.isPending
              }
            >
              {editingTaxRate ? "Save" : "Add"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
      <AlertDialog
        open={!!deleteTaxRate}
        onOpenChange={(open) => !open && setDeleteTaxRate(null)}
      >
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Delete Tax Rate</AlertDialogTitle>
            <AlertDialogDescription>
              Are you sure you want to delete {deleteTaxRate?.name}? This action
              cannot be undone.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction
              className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
              onClick={() =>
                deleteTaxRate && deleteMutation.mutate(deleteTaxRate.id)
              }
            >
              Delete
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  );
}
