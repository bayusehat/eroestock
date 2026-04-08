<div class="space-y-6">
        <x-page-header title="Audit logs" description="Activity history across the application">
            <button type="button" wire:click="export"
                    class="inline-flex h-9 items-center gap-2 rounded-md border border-input bg-background px-4 text-sm font-medium hover:bg-accent transition-colors">
                <x-icon name="download" class="size-4" />
                Export CSV
            </button>
        </x-page-header>

        <div class="rounded-lg border bg-card shadow-sm">
            <div class="flex flex-col gap-4 border-b p-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="relative max-w-md flex-1">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <input type="search"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Search user, action, module, record ID, IP…"
                           class="flex h-9 w-full rounded-md border border-input bg-transparent py-1 pl-9 pr-3 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
                <div class="flex items-center gap-2">
                    <x-icon name="filter" class="size-4 text-muted-foreground" />
                    <x-select wire:model.live="moduleFilter" placeholder="All modules"
                              :options="collect(['' => 'All modules'])->union(collect($modules)->mapWithKeys(fn($m) => [$m => $m]))->toArray()" class="min-w-[12rem]" />
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-t text-left text-sm text-muted-foreground">
                            <th class="px-6 py-3 font-medium">Date</th>
                            <th class="px-6 py-3 font-medium">User</th>
                            <th class="px-6 py-3 font-medium">Action</th>
                            <th class="px-6 py-3 font-medium">Module</th>
                            <th class="px-6 py-3 font-medium">Record ID</th>
                            <th class="px-6 py-3 font-medium">IP address</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @forelse ($logs as $log)
                            <tr class="border-t" wire:key="audit-{{ $log->id }}">
                                <td class="whitespace-nowrap px-6 py-3 text-muted-foreground">
                                    {{ $log->created_at?->format('Y-m-d H:i:s') ?? '—' }}
                                </td>
                                <td class="px-6 py-3">{{ $log->user?->name ?? '—' }}</td>
                                <td class="px-6 py-3 font-medium">{{ $log->action }}</td>
                                <td class="px-6 py-3 text-muted-foreground">{{ $log->module }}</td>
                                <td class="px-6 py-3 text-muted-foreground">{{ $log->record_id ?? '—' }}</td>
                                <td class="px-6 py-3 font-mono text-xs text-muted-foreground">{{ $log->ip_address ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr class="border-t">
                                <td colspan="6" class="px-6 py-10 text-center text-muted-foreground">
                                    No audit entries match your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($logs->hasPages())
                <div class="border-t px-6 py-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
</div>
