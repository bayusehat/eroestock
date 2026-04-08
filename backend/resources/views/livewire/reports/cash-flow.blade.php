<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-page-header title="Cash Flow Statement" description="Laporan arus kas" />
        <div class="flex gap-2">
            <input wire:model.live="dateFrom" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
            <input wire:model.live="dateTo" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
        </div>
    </div>
    <div class="rounded-lg border bg-card shadow-sm">
        <table class="w-full text-sm">
            <tbody>
                <tr class="border-b font-semibold bg-muted/50"><td class="px-4 py-3" colspan="2">Arus Kas Operasi</td></tr>
                <tr class="border-b hover:bg-muted/30"><td class="px-4 py-3 pl-8">Penerimaan Kas</td><td class="px-4 py-3 text-right text-green-400">{{ App\Helpers\Format::currency($data['operating']['inflows']) }}</td></tr>
                <tr class="border-b hover:bg-muted/30"><td class="px-4 py-3 pl-8">Pengeluaran Kas</td><td class="px-4 py-3 text-right text-red-400">{{ App\Helpers\Format::currency($data['operating']['outflows']) }}</td></tr>
                <tr class="border-b bg-muted/20"><td class="px-4 py-3 font-medium">Net Arus Kas Operasi</td><td class="px-4 py-3 text-right font-medium">{{ App\Helpers\Format::currency($data['operating']['inflows'] - $data['operating']['outflows']) }}</td></tr>
                <tr class="border-b font-bold bg-muted/50"><td class="px-4 py-4">Net Cash Flow</td><td class="px-4 py-4 text-right {{ $data['net_cash_flow'] >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ App\Helpers\Format::currency($data['net_cash_flow']) }}</td></tr>
            </tbody>
        </table>
    </div>
</div>
