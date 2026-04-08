<div class="space-y-6">
    <x-page-header title="Reports" description="Laporan keuangan dan operasional" />
    @foreach ($reportGroups as $groupName => $reports)
        <div>
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground">{{ $groupName }}</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($reports as $report)
                    <a wire:navigate href="{{ route($report['route']) }}" class="group rounded-lg border bg-card p-4 shadow-sm hover:bg-accent transition-colors">
                        <h3 class="font-semibold group-hover:text-primary">{{ $report['title'] }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground">{{ $report['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
