"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
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

const navItems = [
  { section: "MAIN", items: [{ href: "/dashboard", label: "Dashboard", icon: LayoutDashboard }] },
  {
    section: "BUSINESS",
    items: [
      { href: "/work-orders", label: "Work Orders", icon: ClipboardList },
      { href: "/clients", label: "Clients", icon: Building2 },
      { href: "/vendors", label: "Vendors", icon: Truck },
      { href: "/invoices", label: "Invoices", icon: FileText },
    ],
  },
  {
    section: "FINANCE",
    items: [
      { href: "/transactions", label: "Transactions", icon: ArrowLeftRight },
      { href: "/journal-entries", label: "Journal Entries", icon: BookOpen },
      { href: "/accounts", label: "Chart of Accounts", icon: Network },
    ],
  },
  {
    section: "HR",
    items: [
      { href: "/employees", label: "Employees", icon: Users },
      { href: "/payroll", label: "Payroll", icon: Banknote },
    ],
  },
  { section: "REPORTS", items: [{ href: "/reports", label: "Reports", icon: BarChart3 }] },
  {
    section: "SETTINGS",
    items: [
      { href: "/settings/company", label: "Settings", icon: Settings },
      { href: "/settings/audit-logs", label: "Audit Logs", icon: Shield },
    ],
  },
];

export function AppSidebar() {
  const pathname = usePathname();
  const { user, logout } = useAuth();

  return (
    <Sidebar>
      <SidebarHeader className="border-b border-sidebar-border p-4">
        <Link href="/dashboard" className="flex items-center gap-2 font-semibold">
          <span className="text-lg">Personal Accounting</span>
        </Link>
      </SidebarHeader>
      <SidebarContent>
        {navItems.map((group) => (
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
            <DropdownMenuItem render={<Link href="/settings/profile">Profile</Link>} />
            <DropdownMenuItem render={<Link href="/settings/company">Settings</Link>} />
            <DropdownMenuSeparator />
            <DropdownMenuItem variant="destructive" onClick={() => logout()}>
              Log out
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </SidebarFooter>
    </Sidebar>
  );
}
