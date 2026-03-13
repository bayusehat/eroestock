"use client";

import { usePathname, useRouter } from "next/navigation";
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs";

const settingsTabs = [
  { href: "/settings/company", label: "Company" },
  { href: "/settings/tax-rates", label: "Tax Rates" },
  { href: "/settings/users", label: "Users" },
  { href: "/settings/roles", label: "Roles" },
  { href: "/settings/audit-logs", label: "Audit Logs" },
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
  const activeTab = getActiveTab(pathname);

  return (
    <div className="space-y-6">
      <Tabs value={activeTab} className="w-full">
        <TabsList className="grid w-full grid-cols-2 sm:grid-cols-5">
          {settingsTabs.map((tab) => (
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
