<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-page-header title="Trial Balance" description="Neraca saldo" />
        <div class="flex gap-2">
            <input wire:model.live="dateFrom" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
            <input wire:model.live="dateTo" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
        </div>
    </div>
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b bg-muted/50 text-left text-muted-foreground"><th class="px-4 py-3 font-medium">Kode</th><th class="px-4 py-3 font-medium">Akun</th><th class="px-4 py-3 text-right font-medium">Debit</th><th class="px-4 py-3 text-right font-medium">Kredit</th></tr></thead>
            <tbody>
                @forelse ($data['accounts'] as $row)
                    <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3">{{ $row['account_code'] }}</td>
                        <td class="px-4 py-3">{{ $row['account_name'] }}</td>
                        <td class="px-4 py-3 text-right">{{ $row['debit'] > 0 ? App\Helpers\Format::currency($row['debit']) : '-' }}</td>
                        <td class="px-4 py-3 text-right">{{ $row['credit'] > 0 ? App\Helpers\Format::currency($row['credit']) : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-12 text-center text-muted-foreground">Tidak ada data</td></tr>
                @endforelse
                <tr class="border-t bg-muted/30 font-bold">
                    <td class="px-4 py-3" colspan="2">Total</td>
                    <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($data['total_debits']) }}</td>
                    <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($data['total_credits']) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
