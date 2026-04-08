<div class="space-y-6">
    <x-page-header title="Journal Entries" description="Kelola jurnal akuntansi">
        <a wire:navigate href="{{ route('journal-entries.create') }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"><x-icon name="plus" class="size-4" /> Buat Jurnal</a>
    </x-page-header>
    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari journal..." class="h-9 max-w-xs rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b bg-muted/50 text-left text-muted-foreground"><th class="px-4 py-3 font-medium">No. Jurnal</th><th class="px-4 py-3 font-medium">Tanggal</th><th class="px-4 py-3 font-medium">Keterangan</th><th class="px-4 py-3 font-medium">Lines</th></tr></thead>
            <tbody>
                @forelse ($entries as $entry)
                    <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3"><a wire:navigate href="{{ route('journal-entries.show', $entry) }}" class="font-medium text-primary hover:underline">{{ $entry->journal_no }}</a></td>
                        <td class="px-4 py-3">{{ $entry->date?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $entry->description ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $entry->lines_count }} lines</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-12 text-center text-muted-foreground">Tidak ada journal entry</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $entries->links() }}</div>
</div>
