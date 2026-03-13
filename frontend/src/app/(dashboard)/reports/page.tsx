"use client";

import Link from "next/link";
import {
  TrendingUp,
  Scale,
  ArrowLeftRight,
  BookOpen,
  FileText,
  Users,
  CreditCard,
  PieChart,
  FolderOpen,
  Receipt,
  Briefcase,
  Calculator,
  ChevronRight,
} from "lucide-react";
import { PageHeader } from "@/components/page-header";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { buttonVariants } from "@/components/ui/button";

const REPORT_CARDS = [
  {
    category: "FINANCIAL",
    reports: [
      {
        href: "/reports/profit-loss",
        title: "Profit & Loss",
        description: "Revenue, expenses, and net profit for a period",
        icon: TrendingUp,
      },
      {
        href: "/reports/balance-sheet",
        title: "Balance Sheet",
        description: "Assets, liabilities, and equity at a point in time",
        icon: Scale,
      },
      {
        href: "/reports/cash-flow",
        title: "Cash Flow Statement",
        description: "Operating, investing, and financing cash flows",
        icon: ArrowLeftRight,
      },
      {
        href: "/reports/trial-balance",
        title: "Trial Balance",
        description: "Debit and credit balances by account",
        icon: BookOpen,
      },
      {
        href: "/reports/general-ledger",
        title: "General Ledger",
        description: "Transaction history for a specific account",
        icon: FileText,
      },
    ],
  },
  {
    category: "BUSINESS",
    reports: [
      {
        href: "/reports/receivable-aging",
        title: "Accounts Receivable Aging",
        description: "Outstanding invoices by client and aging bucket",
        icon: Users,
      },
      {
        href: "/reports/payable-aging",
        title: "Accounts Payable Aging",
        description: "Outstanding payables by vendor and aging bucket",
        icon: CreditCard,
      },
      {
        href: "/reports/income-by-client",
        title: "Income by Client",
        description: "Revenue breakdown by client",
        icon: PieChart,
      },
      {
        href: "/reports/expense-by-category",
        title: "Expense by Category",
        description: "Expense breakdown by account/category",
        icon: FolderOpen,
      },
    ],
  },
  {
    category: "OPERATIONS",
    reports: [
      {
        href: "/reports/work-order-summary",
        title: "Work Order Summary",
        description: "Work orders by status and value",
        icon: Briefcase,
      },
      {
        href: "/reports/payroll-summary",
        title: "Payroll Summary",
        description: "Payroll totals by period and employee",
        icon: Receipt,
      },
      {
        href: "/reports/tax-summary",
        title: "Tax Summary",
        description: "Tax collected, withheld, and liability",
        icon: Calculator,
      },
    ],
  },
];

export default function ReportsPage() {
  return (
    <div className="space-y-8">
      <PageHeader
        title="Reports"
        description="Financial and operational reports"
      />
      <div className="space-y-10">
        {REPORT_CARDS.map((section) => (
          <div key={section.category}>
            <h2 className="mb-4 text-sm font-semibold uppercase tracking-wider text-muted-foreground">
              {section.category}
            </h2>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {section.reports.map((report) => {
                const Icon = report.icon;
                return (
                  <Card key={report.href} className="flex flex-col">
                    <CardHeader className="pb-2">
                      <div className="flex items-start gap-3">
                        <div className="rounded-lg bg-primary/10 p-2">
                          <Icon className="size-5 text-primary" />
                        </div>
                        <div>
                          <CardTitle className="font-semibold">
                            {report.title}
                          </CardTitle>
                          <p className="mt-1 text-sm text-muted-foreground">
                            {report.description}
                          </p>
                        </div>
                      </div>
                    </CardHeader>
                    <CardContent className="mt-auto pt-0">
                      <Link
                        href={report.href}
                        className={buttonVariants({ variant: "outline", size: "sm" })}
                      >
                        View Report
                        <ChevronRight className="ml-1 size-4" />
                      </Link>
                    </CardContent>
                  </Card>
                );
              })}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
