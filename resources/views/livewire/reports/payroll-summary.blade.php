<div class="space-y-6">
    <x-page-header title="Payroll Summary" description="Ringkasan penggajian per periode">
        <div class="flex gap-2 items-center">
            <x-select wire:model.live="period_month"
                      :options="collect(range(1, 12))->mapWithKeys(fn($m) => [$m => \Carbon\Carbon::create()->month($m)->format('F')])->toArray()" class="w-36" />
            <input wire:model.live="period_year" type="number" min="2020" class="h-9 w-24 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
        </div>
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-4">
        <x-stat-card title="Total Gaji Bruto" :value="App\Helpers\Format::currency($data['total_gross'])" icon="dollar-sign" />
        <x-stat-card title="Total Potongan" :value="App\Helpers\Format::currency($data['total_deductions'])" icon="minus-circle" />
        <x-stat-card title="Total Pajak" :value="App\Helpers\Format::currency($data['total_tax'])" icon="file-text" />
        <x-stat-card title="Total Gaji Bersih" :value="App\Helpers\Format::currency($data['total_net'])" icon="trending-up" />
    </div>

    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                    <th class="px-4 py-3 font-medium">Karyawan</th>
                    <th class="px-4 py-3 text-right font-medium">Gaji Pokok</th>
                    <th class="px-4 py-3 text-right font-medium">Lembur</th>
                    <th class="px-4 py-3 text-right font-medium">Tunjangan</th>
                    <th class="px-4 py-3 text-right font-medium">Potongan</th>
                    <th class="px-4 py-3 text-right font-medium">Pajak</th>
                    <th class="px-4 py-3 text-right font-medium">Gaji Bersih</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['by_employee'] as $row)
                    <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3 font-medium">{{ $row['employee_name'] }}</td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($row['base_salary']) }}</td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($row['overtime']) }}</td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($row['allowances']) }}</td>
                        <td class="px-4 py-3 text-right text-red-400">{{ App\Helpers\Format::currency($row['deductions']) }}</td>
                        <td class="px-4 py-3 text-right text-red-400">{{ App\Helpers\Format::currency($row['tax']) }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ App\Helpers\Format::currency($row['net_pay']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-muted-foreground">Tidak ada data payroll untuk periode ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
