<div class="space-y-6">
    <x-page-header title="Work Order Summary" description="Ringkasan work order per status dan nilai">
        <div class="flex gap-2">
            <input wire:model.live="dateFrom" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
            <input wire:model.live="dateTo" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
        </div>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card title="Total Work Orders" :value="$data['total_work_orders']" icon="clipboard-list" />
        <x-stat-card title="Total Nilai" :value="App\Helpers\Format::currency($data['total_value'])" icon="dollar-sign" />
        <x-stat-card title="Rata-rata Nilai" :value="App\Helpers\Format::currency($data['average_value'])" icon="trending-up" />
    </div>

    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 text-right font-medium">Jumlah WO</th>
                    <th class="px-4 py-3 text-right font-medium">Total Nilai</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['by_status'] as $row)
                    <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3 capitalize font-medium">{{ str_replace('_', ' ', $row['status']) }}</td>
                        <td class="px-4 py-3 text-right">{{ $row['count'] }}</td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($row['total_value']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-12 text-center text-muted-foreground">Tidak ada data untuk periode ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
