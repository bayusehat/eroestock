<div class="space-y-6">
    <x-page-header title="Expense by Category" description="Rincian pengeluaran per kategori">
        <div class="flex gap-2">
            <input wire:model.live="dateFrom" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
            <input wire:model.live="dateTo" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
        </div>
    </x-page-header>

    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                    <th class="px-4 py-3 font-medium">Kategori / Akun</th>
                    <th class="px-4 py-3 text-right font-medium">Jumlah</th>
                    <th class="px-4 py-3 text-right font-medium">%</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['rows'] as $row)
                    <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3 font-medium">{{ $row['category'] }}</td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($row['amount']) }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="h-1.5 w-20 rounded-full bg-muted">
                                    <div class="h-1.5 rounded-full bg-destructive" style="width: {{ $row['percentage'] }}%"></div>
                                </div>
                                <span>{{ $row['percentage'] }}%</span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-12 text-center text-muted-foreground">Tidak ada data untuk periode ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
