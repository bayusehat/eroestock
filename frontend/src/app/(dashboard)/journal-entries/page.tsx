"use client";

import { PageHeader } from "@/components/page-header";

export default function JournalEntriesPage() {
  return (
    <div className="space-y-6">
      <PageHeader title="Journal Entries" description="Manage journal entries" />
      <p className="text-muted-foreground">Journal entries coming soon.</p>
    </div>
  );
}
