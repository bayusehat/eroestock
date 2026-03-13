"use client";

import * as React from "react";
import { useQuery } from "@tanstack/react-query";
import { apiClient } from "@/lib/api";
import type { Vendor } from "@/types";
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

interface VendorSelectProps {
  value?: number | null;
  onChange?: (vendorId: number | null) => void;
  placeholder?: string;
  disabled?: boolean;
}

export function VendorSelect({
  value,
  onChange,
  placeholder = "Select vendor...",
  disabled,
}: VendorSelectProps) {
  const { data: vendors = [] } = useQuery({
    queryKey: ["vendors"],
    queryFn: fetchVendors,
  });

  return (
    <Select
      value={value ? String(value) : "none"}
      onValueChange={(v) => onChange?.(v && v !== "none" ? parseInt(v, 10) : null)}
      disabled={disabled}
    >
      <SelectTrigger>
        <SelectValue placeholder={placeholder} />
      </SelectTrigger>
      <SelectContent>
        <SelectItem value="none">{placeholder}</SelectItem>
        {vendors.map((v) => (
          <SelectItem key={v.id} value={String(v.id)}>
            {v.name}
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  );
}
