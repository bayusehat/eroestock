"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { t } from "@/lib/translations";
import {
  LayoutDashboard,
  ClipboardList,
  Building2,
  Truck,
  FileText,
  ArrowLeftRight,
  BookOpen,
  Network,
  Users,
  Banknote,
  BarChart3,
  Settings,
  Shield,
  Send,
} from "lucide-react";
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from "@/components/ui/sidebar";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { useAuth } from "@/contexts/auth-context";

const navItems: { section: string; items: { href: string; label: string; icon: typeof LayoutDashboard; permission?: string }[] }[] = [
  { section: t.nav.main, items: [{ href: "/dashboard", label: t.nav.dashboard, icon: LayoutDashboard }] },
  {
    section: t.nav.business,
    items: [
      { href: "/work-orders", label: t.nav.workOrders, icon: ClipboardList, permission: "work_orders-view" },
      { href: "/requests", label: t.nav.requests, icon: Send, permission: "budget_requests-view" },
      { href: "/clients", label: t.nav.clients, icon: Building2, permission: "clients-view" },
      { href: "/vendors", label: t.nav.vendors, icon: Truck, permission: "vendors-view" },
      { href: "/invoices", label: t.nav.invoices, icon: FileText, permission: "invoices-view" },
    ],
  },
  {
    section: t.nav.finance,
    items: [
      { href: "/transactions", label: t.nav.transactions, icon: ArrowLeftRight, permission: "transactions-view" },
      { href: "/journal-entries", label: t.nav.journalEntries, icon: BookOpen, permission: "journal_entries-view" },
      { href: "/accounts", label: t.nav.chartOfAccounts, icon: Network, permission: "accounts-view" },
    ],
  },
  {
    section: t.nav.hr,
    items: [
      { href: "/employees", label: t.nav.employees, icon: Users, permission: "employees-view" },
      { href: "/payroll", label: t.nav.payroll, icon: Banknote, permission: "payroll-view" },
    ],
  },
  { section: t.nav.reports, items: [{ href: "/reports", label: t.nav.reportsMenu, icon: BarChart3, permission: "reports-view" }] },
  {
    section: t.nav.settings,
    items: [
      { href: "/settings/company", label: t.nav.companySettings, icon: Settings, permission: "settings-view" },
      { href: "/settings/users", label: "Users", icon: Users, permission: "users-view" },
      { href: "/settings/roles", label: "Roles", icon: Shield, permission: "roles-view" },
      { href: "/settings/audit-logs", label: t.nav.auditLogs, icon: Shield, permission: "audit_logs-view" },
    ],
  },
];

export function AppSidebar() {
  const pathname = usePathname();
  const { user, logout, hasPermission } = useAuth();

  const filteredNavItems = navItems.map((group) => ({
    ...group,
    items: group.items.filter((item) => !item.permission || hasPermission(item.permission)),
  })).filter((group) => group.items.length > 0);

  return (
    <Sidebar>
      <SidebarHeader className="border-b border-sidebar-border p-4">
        <Link href="/dashboard" className="flex flex-col gap-0.5">
          <span className="text-lg font-bold tracking-tight">{t.nav.appName}</span>
          <span className="text-[11px] text-muted-foreground">{t.nav.tagline}</span>
        </Link>
      </SidebarHeader>
      <SidebarContent>
        {filteredNavItems.map((group) => (
          <SidebarGroup key={group.section}>
            <SidebarGroupLabel>{group.section}</SidebarGroupLabel>
            <SidebarGroupContent>
              <SidebarMenu>
                {group.items.map((item) => {
                  const isActive = pathname === item.href || pathname.startsWith(`${item.href}/`);
                  const Icon = item.icon;
                  return (
                    <SidebarMenuItem key={item.href}>
                      <SidebarMenuButton
                        render={<Link href={item.href}><Icon /><span>{item.label}</span></Link>}
                        isActive={isActive}
                      />
                    </SidebarMenuItem>
                  );
                })}
              </SidebarMenu>
            </SidebarGroupContent>
          </SidebarGroup>
        ))}
      </SidebarContent>
      <SidebarFooter className="border-t border-sidebar-border p-2">
        <DropdownMenu>
          <DropdownMenuTrigger
            render={
              <button className="flex w-full items-center gap-2 rounded-md p-2 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                <Avatar className="size-8">
                  <AvatarImage src={user?.avatar} alt={user?.name} />
                  <AvatarFallback>{user?.name?.charAt(0) ?? "U"}</AvatarFallback>
                </Avatar>
                <div className="flex flex-1 flex-col items-start text-left text-sm">
                  <span className="font-medium">{user?.name ?? "User"}</span>
                  <span className="text-xs text-muted-foreground">{user?.email}</span>
                </div>
              </button>
            }
          />
          <DropdownMenuContent align="start" className="w-56">
            <DropdownMenuItem render={<Link href="/settings/profile">{t.nav.profile}</Link>} />
            <DropdownMenuItem render={<Link href="/settings/company">{t.nav.companySettings}</Link>} />
            <DropdownMenuSeparator />
            <DropdownMenuItem variant="destructive" onClick={() => logout()}>
              {t.nav.logOut}
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </SidebarFooter>
    </Sidebar>
  );
}
