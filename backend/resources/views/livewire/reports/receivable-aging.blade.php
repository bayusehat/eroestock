<div class="space-y-6">
    <x-page-header title="Accounts Receivable Aging" description="Invoice belum dibayar per klien dan periode jatuh tempo" />
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                    <th class="px-4 py-3 font-medium">Client</th>
                    <th class="px-4 py-3 text-right font-medium">Current</th>
                    <th class="px-4 py-3 text-right font-medium">31-60 Hari</th>
                    <th class="px-4 py-3 text-right font-medium">61-90 Hari</th>
                    <th class="px-4 py-3 text-right font-medium">&gt;90 Hari</th>
                    <th class="px-4 py-3 text-right font-medium">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['rows'] as $row)
                    <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3 font-medium">{{ $row['name'] }}</td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($row['current']) }}</td>
                        <td class="px-4 py-3 text-right {{ $row['days_31_60'] > 0 ? 'text-yellow-400' : '' }}">{{ App\Helpers\Format::currency($row['days_31_60']) }}</td>
                        <td class="px-4 py-3 text-right {{ $row['days_61_90'] > 0 ? 'text-orange-400' : '' }}">{{ App\Helpers\Format::currency($row['days_61_90']) }}</td>
                        <td class="px-4 py-3 text-right {{ $row['over_90'] > 0 ? 'text-red-400' : '' }}">{{ App\Helpers\Format::currency($row['over_90']) }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ App\Helpers\Format::currency($row['total']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-muted-foreground">Tidak ada data piutang</td></tr>
                @endforelse
            </tbody>
            @if ($data['rows']->isNotEmpty())
                <tfoot>
                    <tr class="border-t bg-muted/30 font-bold">
                        <td class="px-4 py-3">Total</td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($data['totals']['current']) }}</td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($data['totals']['days_31_60']) }}</td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($data['totals']['days_61_90']) }}</td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($data['totals']['over_90']) }}</td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($data['totals']['total']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
