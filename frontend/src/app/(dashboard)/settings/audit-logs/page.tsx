"use client";

import { PageHeader } from "@/components/page-header";

export default function AuditLogsPage() {
  return (
    <div className="space-y-6">
      <PageHeader
        title="Audit Logs"
        description="View system audit logs"
      />
      <p className="text-muted-foreground">Audit logs coming soon.</p>
    </div>
  );
}
