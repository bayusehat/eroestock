<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-page-header title="Balance Sheet" description="Neraca keuangan" />
        <input wire:model.live="asOfDate" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
    </div>
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-4">
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="border-b p-4"><h3 class="font-semibold">Assets</h3></div>
                <table class="w-full text-sm"><tbody>
                    @foreach ($data['assets'] as $row)
                        <tr class="border-b hover:bg-muted/30"><td class="px-4 py-3">{{ $row['account_code'] }} - {{ $row['account_name'] }}</td><td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($row['balance']) }}</td></tr>
                    @endforeach
                    <tr class="bg-muted/30 font-semibold"><td class="px-4 py-3">Total Assets</td><td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($data['total_assets']) }}</td></tr>
                </tbody></table>
            </div>
        </div>
        <div class="space-y-4">
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="border-b p-4"><h3 class="font-semibold">Liabilities</h3></div>
                <table class="w-full text-sm"><tbody>
                    @foreach ($data['liabilities'] as $row)
                        <tr class="border-b hover:bg-muted/30"><td class="px-4 py-3">{{ $row['account_code'] }} - {{ $row['account_name'] }}</td><td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($row['balance']) }}</td></tr>
                    @endforeach
                    <tr class="bg-muted/30 font-semibold"><td class="px-4 py-3">Total Liabilities</td><td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($data['total_liabilities']) }}</td></tr>
                </tbody></table>
            </div>
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="border-b p-4"><h3 class="font-semibold">Equity</h3></div>
                <table class="w-full text-sm"><tbody>
                    @foreach ($data['equity'] as $row)
                        <tr class="border-b hover:bg-muted/30"><td class="px-4 py-3">{{ $row['account_code'] }} - {{ $row['account_name'] }}</td><td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($row['balance']) }}</td></tr>
                    @endforeach
                    <tr class="bg-muted/30 font-semibold"><td class="px-4 py-3">Total Equity</td><td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($data['total_equity']) }}</td></tr>
                </tbody></table>
            </div>
        </div>
    </div>
</div>
