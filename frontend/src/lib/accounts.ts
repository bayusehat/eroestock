import { apiClient } from "@/lib/api";
import type { Account } from "@/types";

function flattenAccounts(accounts: Account[]): Account[] {
  if (!Array.isArray(accounts)) return [];
  const result: Account[] = [];
  for (const a of accounts) {
    result.push({ ...a, children: undefined });
    if (a.children?.length) {
      result.push(...flattenAccounts(a.children));
    }
  }
  return result;
}

export async function fetchFlattenedAccounts(): Promise<Account[]> {
  const res = await apiClient.get<{ data: Account[] | { data: Account[] } }>("/accounts/tree");
  const body = res.data as { data?: Account[] | { data?: Account[] } };
  const raw = body.data;
  const data = Array.isArray(raw)
    ? raw
    : Array.isArray((raw as { data?: Account[] })?.data)
      ? (raw as { data: Account[] }).data
      : [];
  return flattenAccounts(data);
}

export async function fetchAccountsTree(): Promise<Account[]> {
  const res = await apiClient.get<{ data: Account[] | { data: Account[] } }>("/accounts/tree");
  const body = res.data as { data?: Account[] | { data?: Account[] } };
  const raw = body.data;
  return Array.isArray(raw)
    ? raw
    : Array.isArray((raw as { data?: Account[] })?.data)
      ? (raw as { data: Account[] }).data
      : [];
}
