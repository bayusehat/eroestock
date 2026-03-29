"use client";

import Link from "next/link";
import { t } from "@/lib/translations";
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
    category: t.reports.financial,
    reports: [
      {
        href: "/reports/profit-loss",
        title: t.reports.profitLoss.title,
        description: t.reports.profitLoss.description,
        icon: TrendingUp,
      },
      {
        href: "/reports/balance-sheet",
        title: t.reports.balanceSheet.title,
        description: t.reports.balanceSheet.description,
        icon: Scale,
      },
      {
        href: "/reports/cash-flow",
        title: t.reports.cashFlow.title,
        description: t.reports.cashFlow.description,
        icon: ArrowLeftRight,
      },
      {
        href: "/reports/trial-balance",
        title: t.reports.trialBalance.title,
        description: t.reports.trialBalance.description,
        icon: BookOpen,
      },
      {
        href: "/reports/general-ledger",
        title: t.reports.generalLedger.title,
        description: t.reports.generalLedger.description,
        icon: FileText,
      },
    ],
  },
  {
    category: t.reports.business,
    reports: [
      {
        href: "/reports/receivable-aging",
        title: t.reports.receivableAging.title,
        description: t.reports.receivableAging.description,
        icon: Users,
      },
      {
        href: "/reports/payable-aging",
        title: t.reports.payableAging.title,
        description: t.reports.payableAging.description,
        icon: CreditCard,
      },
      {
        href: "/reports/income-by-client",
        title: t.reports.incomeByClient.title,
        description: t.reports.incomeByClient.description,
        icon: PieChart,
      },
      {
        href: "/reports/expense-by-category",
        title: t.reports.expenseByCategory.title,
        description: t.reports.expenseByCategory.description,
        icon: FolderOpen,
      },
    ],
  },
  {
    category: t.reports.operations,
    reports: [
      {
        href: "/reports/work-order-summary",
        title: t.reports.workOrderSummary.title,
        description: t.reports.workOrderSummary.description,
        icon: Briefcase,
      },
      {
        href: "/reports/payroll-summary",
        title: t.reports.payrollSummary.title,
        description: t.reports.payrollSummary.description,
        icon: Receipt,
      },
      {
        href: "/reports/tax-summary",
        title: t.reports.taxSummary.title,
        description: t.reports.taxSummary.description,
        icon: Calculator,
      },
    ],
  },
];

export default function ReportsPage() {
  return (
    <div className="space-y-8">
      <PageHeader
        title={t.reports.title}
        description={t.reports.description}
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
                        {t.reports.viewReport}
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
