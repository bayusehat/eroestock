"use client";

import { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import {
  ChevronDown,
  ChevronRight,
  Plus,
  Pencil,
  MoreHorizontal,
} from "lucide-react";
import { apiClient } from "@/lib/api";
import { formatCurrency } from "@/lib/format";
import type { Account } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import { Skeleton } from "@/components/ui/skeleton";
import { toast } from "sonner";

const ACCOUNT_TYPES = ["Asset", "Liability", "Equity", "Revenue", "Expense"];

async function fetchAccounts(): Promise<Account[]> {
  const res = await apiClient.get<{ data: Account[] }>("/accounts");
  const body = res.data as { data: Account[] };
  return body.data ?? (body as unknown as Account[]);
}

function buildAccountTree(accounts: Account[], parentId?: number): Account[] {
  return accounts
    .filter((a) => (parentId ? a.parent_id === parentId : !a.parent_id))
    .sort((a, b) => a.code.localeCompare(b.code));
}

function AccountRow({
  account,
  accounts,
  level,
  typeFilter,
  searchQuery,
}: {
  account: Account;
  accounts: Account[];
  level: number;
  typeFilter: string;
  searchQuery: string;
}) {
  const [expanded, setExpanded] = useState(true);
  const children = buildAccountTree(accounts, account.id);
  const hasChildren = children.length > 0;
  const matchesType = !typeFilter || account.type === typeFilter;
  const matchesSearch =
    !searchQuery ||
    account.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    account.code.toLowerCase().includes(searchQuery.toLowerCase());

  if (!matchesType && !children.some((c) => c.type === typeFilter)) {
    return null;
  }

  if (!matchesSearch && !children.some((c) => c.name.toLowerCase().includes(searchQuery.toLowerCase()))) {
    return null;
  }

  return (
    <>
      <tr
        className="border-b hover:bg-muted/50"
        style={{ paddingLeft: level * 24 }}
      >
        <td className="p-2" style={{ paddingLeft: level * 24 + 8 }}>
          <div className="flex items-center gap-1">
            {hasChildren ? (
              <button
                type="button"
                onClick={() => setExpanded(!expanded)}
                className="p-0.5 hover:bg-muted rounded"
              >
                {expanded ? (
                  <ChevronDown className="size-4" />
                ) : (
                  <ChevronRight className="size-4" />
                )}
              </button>
            ) : (
              <span className="w-5" />
            )}
            <span className="font-mono text-sm">{account.code}</span>
          </div>
        </td>
        <td className="p-2">{account.name}</td>
        <td className="p-2">
          <Badge variant="outline">{account.type}</Badge>
        </td>
        <td className="p-2 text-right font-mono">
          {formatCurrency(account.opening_balance ?? 0)}
        </td>
        <td className="p-2">
          <DropdownMenu>
            <DropdownMenuTrigger
              render={
                <Button variant="ghost" size="icon-sm">
                  <MoreHorizontal className="size-4" />
                  <span className="sr-only">Actions</span>
                </Button>
              }
            />
            <DropdownMenuContent align="end">
              <DropdownMenuItem>Edit</DropdownMenuItem>
              {!account.is_system && (
                <DropdownMenuItem variant="destructive">Delete</DropdownMenuItem>
              )}
            </DropdownMenuContent>
          </DropdownMenu>
        </td>
      </tr>
      {expanded &&
        hasChildren &&
        children.map((child) => (
          <AccountRow
            key={child.id}
            account={child}
            accounts={accounts}
            level={level + 1}
            typeFilter={typeFilter}
            searchQuery={searchQuery}
          />
        ))}
    </>
  );
}

export default function AccountsPage() {
  const [typeFilter, setTypeFilter] = useState("");
  const [searchQuery, setSearchQuery] = useState("");
  const [addDialogOpen, setAddDialogOpen] = useState(false);

  const { data: accounts = [], isLoading } = useQuery({
    queryKey: ["accounts"],
    queryFn: fetchAccounts,
  });

  const rootAccounts = buildAccountTree(accounts);

  return (
    <div className="space-y-6">
      <PageHeader
        title="Chart of Accounts"
        description="Manage your account structure"
        children={
          <Dialog open={addDialogOpen} onOpenChange={setAddDialogOpen}>
            <DialogTrigger
              render={
                <Button>
                  <Plus className="mr-2 size-4" />
                  Add Account
                </Button>
              }
            />
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Add Account</DialogTitle>
              </DialogHeader>
              <div className="grid gap-4 py-4">
                <div className="space-y-2">
                  <Label htmlFor="code">Code</Label>
                  <Input id="code" placeholder="e.g. 1000" />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="name">Name</Label>
                  <Input id="name" placeholder="Account name" />
                </div>
                <div className="space-y-2">
                  <Label htmlFor="type">Type</Label>
                  <Input id="type" placeholder="Asset, Liability, etc." />
                </div>
              </div>
              <DialogFooter>
                <Button
                  variant="outline"
                  onClick={() => setAddDialogOpen(false)}
                >
                  Cancel
                </Button>
                <Button
                  onClick={() => {
                    setAddDialogOpen(false);
                    toast.success("Account added");
                  }}
                >
                  Add
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>
        }
      />
      <div className="flex flex-col gap-4 sm:flex-row">
        <Input
          placeholder="Search accounts..."
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          className="max-w-sm"
        />
        <select
          value={typeFilter}
          onChange={(e) => setTypeFilter(e.target.value)}
          className="h-8 rounded-lg border border-input bg-background px-3 text-sm"
        >
          <option value="">All types</option>
          {ACCOUNT_TYPES.map((type) => (
            <option key={type} value={type}>
              {type}
            </option>
          ))}
        </select>
      </div>
      <div className="rounded-md border">
        {isLoading ? (
          <Skeleton className="h-64 w-full" />
        ) : (
          <table className="w-full">
            <thead>
              <tr className="border-b bg-muted/50">
                <th className="p-2 text-left font-medium">Code</th>
                <th className="p-2 text-left font-medium">Name</th>
                <th className="p-2 text-left font-medium">Type</th>
                <th className="p-2 text-right font-medium">Balance</th>
                <th className="w-12" />
              </tr>
            </thead>
            <tbody>
              {rootAccounts.map((account) => (
                <AccountRow
                  key={account.id}
                  account={account}
                  accounts={accounts}
                  level={0}
                  typeFilter={typeFilter}
                  searchQuery={searchQuery}
                />
              ))}
              {rootAccounts.length === 0 && (
                <tr>
                  <td colSpan={5} className="p-8 text-center text-muted-foreground">
                    No accounts found
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
}
