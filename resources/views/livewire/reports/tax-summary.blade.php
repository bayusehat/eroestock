<div class="space-y-6">
    <x-page-header title="Tax Summary" description="Pajak dipungut, dipotong, dan kewajiban pajak">
        <div class="flex gap-2">
            <input wire:model.live="dateFrom" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
            <input wire:model.live="dateTo" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
        </div>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card title="Pajak Dipungut (PPN)" :value="App\Helpers\Format::currency($data['total_collected'])" icon="plus-circle" />
        <x-stat-card title="Pajak Dipotong (PPh 21)" :value="App\Helpers\Format::currency($data['total_withheld'])" icon="minus-circle" />
        <x-stat-card title="Total Kewajiban" :value="App\Helpers\Format::currency($data['net_liability'])" icon="file-text" />
    </div>

    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                    <th class="px-4 py-3 font-medium">Tipe Pajak</th>
                    <th class="px-4 py-3 font-medium">Nama Pajak</th>
                    <th class="px-4 py-3 text-right font-medium">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['rows'] as $row)
                    <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3 font-medium">{{ $row['tax_type'] }}</td>
                        <td class="px-4 py-3">{{ $row['tax_name'] }}</td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($row['amount']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-12 text-center text-muted-foreground">Tidak ada data pajak untuk periode ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
