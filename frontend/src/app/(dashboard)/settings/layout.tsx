"use client";

import { usePathname, useRouter } from "next/navigation";
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { useAuth } from "@/contexts/auth-context";

const settingsTabs: { href: string; label: string; permission: string }[] = [
  { href: "/settings/company", label: "Company", permission: "settings-view" },
  { href: "/settings/tax-rates", label: "Tax Rates", permission: "settings-view" },
  { href: "/settings/users", label: "Users", permission: "users-view" },
  { href: "/settings/roles", label: "Roles", permission: "roles-view" },
  { href: "/settings/audit-logs", label: "Audit Logs", permission: "audit_logs-view" },
];

function getActiveTab(pathname: string) {
  const match = settingsTabs.find((tab) => pathname === tab.href || pathname.startsWith(`${tab.href}/`));
  return match?.href ?? "/settings/company";
}

export default function SettingsLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const pathname = usePathname();
  const router = useRouter();
  const { hasPermission } = useAuth();
  const visibleTabs = settingsTabs.filter((tab) => hasPermission(tab.permission));
  const activeTab = getActiveTab(pathname);

  return (
    <div className="space-y-6">
      <Tabs value={activeTab} className="w-full">
        <TabsList className="grid w-full grid-cols-2 sm:grid-cols-5">
          {visibleTabs.map((tab) => (
            <TabsTrigger
              key={tab.href}
              value={tab.href}
              onClick={() => router.push(tab.href)}
            >
              {tab.label}
            </TabsTrigger>
          ))}
        </TabsList>
      </Tabs>
      {children}
    </div>
  );
}
