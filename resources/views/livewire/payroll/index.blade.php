<div class="space-y-6">
    <x-page-header title="Payroll" description="Kelola penggajian karyawan">
        <a wire:navigate href="{{ route('payroll.create') }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"><x-icon name="plus" class="size-4" /> Buat Payroll</a>
    </x-page-header>
    <div class="flex gap-3">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari karyawan..." class="h-9 max-w-xs rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
        <x-select wire:model.live="statusFilter" placeholder="Semua Status"
                  :options="['' => 'Semua Status', 'draft' => 'Draft', 'approved' => 'Approved', 'paid' => 'Paid']" class="w-40" />
    </div>
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b bg-muted/50 text-left text-muted-foreground"><th class="px-4 py-3 font-medium">No. Payroll</th><th class="px-4 py-3 font-medium">Karyawan</th><th class="px-4 py-3 font-medium">Periode</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 text-right font-medium">Gaji Bersih</th><th class="px-4 py-3"></th></tr></thead>
            <tbody>
                @php $statusColors = ['draft'=>'bg-muted text-muted-foreground','approved'=>'bg-blue-500/15 text-blue-400','paid'=>'bg-green-500/15 text-green-400']; @endphp
                @forelse ($records as $record)
                    <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3"><a wire:navigate href="{{ route('payroll.show', $record) }}" class="font-medium text-primary hover:underline">{{ $record->payroll_no }}</a></td>
                        <td class="px-4 py-3">{{ $record->employee?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ sprintf('%02d', $record->period_month) }}/{{ $record->period_year }}</td>
                        <td class="px-4 py-3"><span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $statusColors[$record->status] ?? 'bg-muted' }}">{{ $record->status }}</span></td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($record->net_pay) }}</td>
                        <td class="px-4 py-3 flex gap-1 justify-end">
                            @if ($record->status === 'draft')
                                <button wire:click="approve({{ $record->id }})" class="inline-flex items-center gap-1 rounded-md bg-blue-600 px-2 py-1 text-xs text-white hover:bg-blue-700"><x-icon name="check" class="size-3" /></button>
                            @elseif ($record->status === 'approved')
                                <button wire:click="markAsPaid({{ $record->id }})" class="inline-flex items-center gap-1 rounded-md bg-green-600 px-2 py-1 text-xs text-white hover:bg-green-700">Bayar</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-muted-foreground">Tidak ada data payroll</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $records->links() }}</div>
</div>
