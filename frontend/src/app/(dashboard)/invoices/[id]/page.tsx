"use client";

import { useParams, useRouter, useSearchParams } from "next/navigation";
import Link from "next/link";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { useEffect, useState } from "react";
import {
  Pencil,
  Send,
  DollarSign,
  FileDown,
  ArrowLeft,
} from "lucide-react";
import { apiClient } from "@/lib/api";
import { fetchFlattenedAccounts } from "@/lib/accounts";
import type { Account, Invoice } from "@/types";
import { PageHeader } from "@/components/page-header";
import { Button, buttonVariants } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
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
import { Input } from "@/components/ui/input";
import { CurrencyInput } from "@/components/ui/currency-input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { DatePicker } from "@/components/ui/date-picker";
import { Skeleton } from "@/components/ui/skeleton";
import { formatCurrency, formatDate } from "@/lib/format";
import { downloadPdf } from "@/lib/download";
import { toast } from "sonner";

const INVOICE_STATUS_COLORS: Record<string, string> = {
  draft: "bg-muted text-muted-foreground",
  sent: "bg-blue-500/10 text-blue-600 dark:text-blue-400",
  partially_paid: "bg-yellow-500/10 text-yellow-600 dark:text-yellow-400",
  paid: "bg-green-500/10 text-green-600 dark:text-green-400",
  overdue: "bg-red-500/10 text-red-600 dark:text-red-400",
  cancelled: "bg-muted text-muted-foreground",
};

const PAYMENT_METHODS: { value: string; label: string }[] = [
  { value: "cash", label: "Cash" },
  { value: "bank_transfer", label: "Bank Transfer" },
  { value: "check", label: "Check" },
  { value: "other", label: "Other" },
];

/** Default account codes by payment method (from standard chart of accounts) */
const DEFAULT_ACCOUNT_BY_METHOD: Record<string, string> = {
  cash: "1-1001",
  bank_transfer: "1-1002",
  check: "1-1002",
  other: "1-1002",
};

async function fetchInvoice(id: string): Promise<Invoice> {
  const res = await apiClient.get<{ data: Invoice }>(`/invoices/${id}`);
  const body = res.data as { data: Invoice };
  return body.data ?? (body as unknown as Invoice);
}

async function fetchAccounts(): Promise<Account[]> {
  return fetchFlattenedAccounts();
}

export default function InvoiceDetailPage() {
  const params = useParams();
  const router = useRouter();
  const searchParams = useSearchParams();
  const queryClient = useQueryClient();
  const id = params.id as string;
  const [paymentDialogOpen, setPaymentDialogOpen] = useState(false);
  const [paymentAmount, setPaymentAmount] = useState<number>(0);
  const [paymentDate, setPaymentDate] = useState(new Date().toISOString().split("T")[0] ?? "");
  const [paymentMethod, setPaymentMethod] = useState("");
  const [paymentAccountId, setPaymentAccountId] = useState<string>("");
  const [paymentRef, setPaymentRef] = useState("");

  const { data: invoice, isLoading } = useQuery({
    queryKey: ["invoice", id],
    queryFn: () => fetchInvoice(id),
    enabled: !!id,
  });

  const { data: accounts = [] } = useQuery({
    queryKey: ["accounts"],
    queryFn: fetchAccounts,
  });

  const sendMutation = useMutation({
    mutationFn: () => apiClient.patch(`/invoices/${id}/send`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["invoice", id] });
      toast.success("Invoice sent");
    },
    onError: (err: unknown) => {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to send invoice";
      toast.error(typeof message === "string" ? message : "Failed to send invoice");
    },
  });

  const paymentMutation = useMutation({
    mutationFn: (payload: {
      amount: number;
      payment_date: string;
      payment_method: string;
      account_id: number;
      reference_no?: string;
    }) => apiClient.post(`/invoices/${id}/payment`, payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["invoice", id] });
      toast.success("Payment recorded");
      setPaymentDialogOpen(false);
      setPaymentAmount(0);
      setPaymentDate(new Date().toISOString().split("T")[0] ?? "");
      setPaymentMethod("");
      setPaymentAccountId("");
      setPaymentRef("");
    },
    onError: (err: unknown) => {
      const message =
        err && typeof err === "object" && "response" in err
          ? (err as { response?: { data?: { message?: string } } }).response?.data
              ?.message
          : "Failed to record payment";
      toast.error(typeof message === "string" ? message : "Failed to record payment");
    },
  });

  useEffect(() => {
    if (searchParams.get("recordPayment") === "1") {
      setPaymentDialogOpen(true);
    }
  }, [searchParams]);

  useEffect(() => {
    if (!paymentDialogOpen || accounts.length === 0) return;
    const method = paymentMethod || "bank_transfer";
    if (!paymentMethod) setPaymentMethod(method);
    const preferredCode = DEFAULT_ACCOUNT_BY_METHOD[method];
    const preferred = accounts.find((a) => a.code === preferredCode);
    if (preferred) {
      setPaymentAccountId(String(preferred.id));
      return;
    }
    const cashBankAccounts = accounts.filter(
      (a) => a.code?.startsWith("1-100") && !a.is_header
    );
    if (cashBankAccounts.length > 0) {
      setPaymentAccountId(String(cashBankAccounts[0].id));
    }
  }, [paymentDialogOpen, accounts, paymentMethod]);

  const handleRecordPayment = () => {
    if (paymentAmount <= 0) {
      toast.error("Please enter a valid amount");
      return;
    }
    if (!paymentDate) {
      toast.error("Please select a date");
      return;
    }
    if (!paymentMethod) {
      toast.error("Please select a payment method");
      return;
    }
    if (!paymentAccountId) {
      toast.error("Please select an account (e.g. Bank)");
      return;
    }
    paymentMutation.mutate({
      amount: paymentAmount,
      payment_date: paymentDate,
      payment_method: paymentMethod,
      account_id: parseInt(paymentAccountId, 10),
      reference_no: paymentRef || undefined,
    });
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <Skeleton className="h-12 w-64" />
        <Skeleton className="h-48" />
        <Skeleton className="h-64" />
      </div>
    );
  }

  if (!invoice) {
    return null;
  }

  const canEdit = invoice.status === "draft";
  const canSend = invoice.status === "draft";
  const canRecordPayment = ["sent", "partially_paid"].includes(invoice.status);
  const balanceDue = invoice.balance_due ?? 0;

  return (
    <div className="space-y-6">
      <PageHeader
        title={invoice.invoice_no}
        description={`${invoice.client?.name ?? "-"} • ${formatDate(invoice.issue_date)}`}
        children={
          <div className="flex items-center gap-2">
            <Button variant="outline" onClick={() => router.back()}>
              <ArrowLeft className="mr-2 size-4" />
              Back
            </Button>
            {canEdit && (
              <Link
                href={`/invoices/${id}/edit`}
                className={buttonVariants()}
              >
                <Pencil className="mr-2 size-4" />
                Edit
              </Link>
            )}
            {canSend && (
              <Button
                onClick={() => sendMutation.mutate()}
                disabled={sendMutation.isPending}
              >
                <Send className="mr-2 size-4" />
                Send
              </Button>
            )}
            {canRecordPayment && (
              <Button
                onClick={() => setPaymentDialogOpen(true)}
              >
                <DollarSign className="mr-2 size-4" />
                Record Payment
              </Button>
            )}
            <Button
              variant="outline"
              onClick={async () => {
                try {
                  await downloadPdf(`/exports/invoice/${id}/pdf`, `invoice-${invoice.invoice_no}.pdf`);
                  toast.success("PDF downloaded");
                } catch {
                  toast.error("Failed to download PDF");
                }
              }}
            >
              <FileDown className="mr-2 size-4" />
              Download PDF
            </Button>
          </div>
        }
      />
      <div className="flex items-center gap-4">
        <Badge
          variant="outline"
          className={INVOICE_STATUS_COLORS[invoice.status] ?? "bg-muted"}
        >
          {invoice.status}
        </Badge>
      </div>
      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Client</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            <p className="font-medium">{invoice.client?.name ?? "-"}</p>
            {invoice.client?.email && (
              <p className="text-sm text-muted-foreground">{invoice.client.email}</p>
            )}
            {invoice.client?.address && (
              <p className="text-sm text-muted-foreground whitespace-pre-wrap">
                {invoice.client.address}
              </p>
            )}
          </CardContent>
        </Card>
        <Card>
          <CardHeader>
            <CardTitle>Invoice Details</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 sm:grid-cols-2">
              <div>
                <p className="text-sm text-muted-foreground">Issue Date</p>
                <p className="font-medium">{formatDate(invoice.issue_date)}</p>
              </div>
              <div>
                <p className="text-sm text-muted-foreground">Due Date</p>
                <p className="font-medium">{formatDate(invoice.due_date)}</p>
              </div>
            </div>
            {invoice.terms && (
              <div>
                <p className="text-sm text-muted-foreground">Terms</p>
                <p className="font-medium whitespace-pre-wrap">{invoice.terms}</p>
              </div>
            )}
            {invoice.notes && (
              <div>
                <p className="text-sm text-muted-foreground">Notes</p>
                <p className="font-medium whitespace-pre-wrap">{invoice.notes}</p>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
      <Card>
        <CardHeader>
          <CardTitle>Line Items</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Description</TableHead>
                <TableHead>Qty</TableHead>
                <TableHead>Unit</TableHead>
                <TableHead className="text-right">Unit Price</TableHead>
                <TableHead className="text-right">Discount</TableHead>
                <TableHead className="text-right">Tax %</TableHead>
                <TableHead className="text-right">Subtotal</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {(invoice.items ?? []).map((item) => (
                <TableRow key={item.id}>
                  <TableCell>{item.description}</TableCell>
                  <TableCell>{item.quantity}</TableCell>
                  <TableCell>{item.unit}</TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(item.unit_price)}
                  </TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(item.discount)}
                  </TableCell>
                  <TableCell className="text-right">{item.tax_rate}%</TableCell>
                  <TableCell className="text-right">
                    {formatCurrency(item.subtotal)}
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
      <Card>
        <CardHeader>
          <CardTitle>Totals</CardTitle>
        </CardHeader>
        <CardContent className="space-y-2">
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">Subtotal</span>
            <span>{formatCurrency(invoice.subtotal ?? 0)}</span>
          </div>
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">Total Discount</span>
            <span>{formatCurrency(invoice.discount_amount ?? 0)}</span>
          </div>
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">Total Tax</span>
            <span>{formatCurrency(invoice.tax_amount ?? 0)}</span>
          </div>
          <div className="flex justify-between font-semibold pt-2 border-t">
            <span>Grand Total</span>
            <span>{formatCurrency(invoice.grand_total ?? 0)}</span>
          </div>
          {invoice.amount_paid > 0 && (
            <>
              <div className="flex justify-between text-sm pt-2">
                <span className="text-muted-foreground">Amount Paid</span>
                <span>{formatCurrency(invoice.amount_paid ?? 0)}</span>
              </div>
              <div className="flex justify-between font-semibold pt-2 border-t">
                <span>Balance Due</span>
                <span>{formatCurrency(balanceDue)}</span>
              </div>
            </>
          )}
        </CardContent>
      </Card>
      {(invoice.payments?.length ?? 0) > 0 && (
        <Card>
          <CardHeader className="flex flex-row items-center justify-between">
            <CardTitle>Payment History</CardTitle>
            <Link
              href="/reports/general-ledger"
              className="text-sm text-primary hover:underline"
            >
              View in Ledger →
            </Link>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Date</TableHead>
                  <TableHead>Reference</TableHead>
                  <TableHead>Amount</TableHead>
                  <TableHead>Payment Method</TableHead>
                  <TableHead>Account</TableHead>
                  <TableHead></TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {invoice.payments?.map((p) => (
                  <TableRow key={p.id}>
                    <TableCell>{formatDate(p.date)}</TableCell>
                    <TableCell>
                      <Link
                        href={`/transactions/${p.id}`}
                        className="font-medium text-primary hover:underline"
                      >
                        {"transaction_no" in p ? p.transaction_no : `#${p.id}`}
                      </Link>
                    </TableCell>
                    <TableCell>{formatCurrency(p.amount)}</TableCell>
                    <TableCell>
                      {PAYMENT_METHODS.find((m) => m.value === p.payment_method)
                        ?.label ?? p.payment_method ?? "-"}
                    </TableCell>
                    <TableCell>{p.account?.name ?? "-"}</TableCell>
                    <TableCell>
                      {p.account_id && (
                        <Link
                          href={`/reports/general-ledger?account_id=${p.account_id}`}
                          className="text-xs text-muted-foreground hover:underline"
                        >
                          Ledger
                        </Link>
                      )}
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      )}
      <Dialog open={paymentDialogOpen} onOpenChange={setPaymentDialogOpen}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Record Payment</DialogTitle>
          </DialogHeader>
          <div className="grid gap-4 py-4">
            <div className="space-y-2">
              <Label>Amount *</Label>
              <CurrencyInput
                value={paymentAmount}
                onChange={setPaymentAmount}
                placeholder="0"
              />
              {balanceDue > 0 && (
                <p className="text-xs text-muted-foreground">
                  Balance due: {formatCurrency(balanceDue)}
                </p>
              )}
            </div>
            <div className="space-y-2">
              <Label>Date *</Label>
              <DatePicker
                value={paymentDate}
                onChange={setPaymentDate}
                placeholder="Select date"
              />
            </div>
            <div className="space-y-2">
              <Label>Payment Method *</Label>
              <Select value={paymentMethod} onValueChange={(v) => setPaymentMethod(v ?? "")}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select method" />
                </SelectTrigger>
                <SelectContent>
                  {PAYMENT_METHODS.map((m) => (
                    <SelectItem key={m.value} value={m.value}>
                      {m.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label>Account *</Label>
              <Select value={paymentAccountId} onValueChange={(v) => setPaymentAccountId(v ?? "")}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select account">
                    {paymentAccountId
                      ? (() => {
                          const a = accounts.find(
                            (acc) => String(acc.id) === paymentAccountId
                          );
                          return a ? `${a.code} - ${a.name}` : null;
                        })()
                      : null}
                  </SelectValue>
                </SelectTrigger>
                <SelectContent>
                  {accounts
                    .filter((a) => !a.is_header)
                    .map((a) => (
                      <SelectItem key={a.id} value={String(a.id)}>
                        {a.code} - {a.name}
                      </SelectItem>
                    ))}
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label>Reference Number</Label>
              <Input
                value={paymentRef}
                onChange={(e) => setPaymentRef(e.target.value)}
                placeholder="Optional"
              />
            </div>
          </div>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => setPaymentDialogOpen(false)}
            >
              Cancel
            </Button>
            <Button
              onClick={handleRecordPayment}
              disabled={paymentMutation.isPending}
            >
              {paymentMutation.isPending ? "Recording..." : "Record Payment"}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
