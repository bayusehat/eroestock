<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-page-header title="Profit & Loss" description="Laporan laba rugi" />
        <div class="flex gap-2">
            <input wire:model.live="dateFrom" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
            <input wire:model.live="dateTo" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card title="Total Revenue" :value="App\Helpers\Format::currency($data['total_revenue'])" icon="trending-up" />
        <x-stat-card title="Total Expenses" :value="App\Helpers\Format::currency($data['total_expenses'])" icon="trending-down" />
        <x-stat-card title="Net Profit" :value="App\Helpers\Format::currency($data['net_profit'])" icon="bar-chart-2" />
    </div>
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Revenue</h3></div>
            <table class="w-full text-sm"><tbody>
                @forelse ($data['revenue'] as $row)
                    <tr class="border-b hover:bg-muted/30"><td class="px-4 py-3">{{ $row['account_code'] }} - {{ $row['account_name'] }}</td><td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($row['amount']) }}</td></tr>
                @empty
                    <tr><td class="px-4 py-8 text-center text-muted-foreground" colspan="2">Tidak ada data</td></tr>
                @endforelse
                <tr class="bg-muted/30 font-semibold"><td class="px-4 py-3">Total Revenue</td><td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($data['total_revenue']) }}</td></tr>
            </tbody></table>
        </div>
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Expenses</h3></div>
            <table class="w-full text-sm"><tbody>
                @forelse ($data['expenses'] as $row)
                    <tr class="border-b hover:bg-muted/30"><td class="px-4 py-3">{{ $row['account_code'] }} - {{ $row['account_name'] }}</td><td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($row['amount']) }}</td></tr>
                @empty
                    <tr><td class="px-4 py-8 text-center text-muted-foreground" colspan="2">Tidak ada data</td></tr>
                @endforelse
                <tr class="bg-muted/30 font-semibold"><td class="px-4 py-3">Total Expenses</td><td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($data['total_expenses']) }}</td></tr>
            </tbody></table>
        </div>
    </div>
    <div class="rounded-lg border bg-card p-4 shadow-sm">
        <div class="flex items-center justify-between">
            <span class="text-lg font-bold">Net Profit / (Loss)</span>
            <span class="text-lg font-bold {{ $data['net_profit'] >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ App\Helpers\Format::currency($data['net_profit']) }}</span>
        </div>
    </div>
</div>
