<div class="space-y-6">
    <x-page-header title="Invoices" description="Kelola invoice">
        <a wire:navigate href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
            <x-icon name="plus" class="size-4" /> Buat Invoice
        </a>
    </x-page-header>
    <div class="flex gap-3">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari invoice..."
               class="h-9 max-w-xs rounded-md border border-input bg-transparent px-3 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring" />
        <x-select wire:model.live="statusFilter" placeholder="Semua Status"
                  :options="['' => 'Semua Status', 'draft' => 'Draft', 'sent' => 'Sent', 'partially_paid' => 'Partially Paid', 'paid' => 'Paid', 'overdue' => 'Overdue', 'cancelled' => 'Cancelled']" class="w-44" />
    </div>
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                    <th class="px-4 py-3 font-medium">No. Invoice</th>
                    <th class="px-4 py-3 font-medium">Client</th>
                    <th class="px-4 py-3 font-medium">Tanggal</th>
                    <th class="px-4 py-3 font-medium">Jatuh Tempo</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 text-right font-medium">Total</th>
                    <th class="px-4 py-3 text-right font-medium">Sisa</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @php
                    $statusColors = ['draft'=>'bg-muted text-muted-foreground','sent'=>'bg-blue-500/15 text-blue-400','partially_paid'=>'bg-yellow-500/15 text-yellow-400','paid'=>'bg-green-500/15 text-green-400','overdue'=>'bg-red-500/15 text-red-400','cancelled'=>'bg-muted text-muted-foreground'];
                @endphp
                @forelse ($invoices as $inv)
                    <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3"><a wire:navigate href="{{ route('invoices.show', $inv) }}" class="font-medium text-primary hover:underline">{{ $inv->invoice_no }}</a></td>
                        <td class="px-4 py-3">{{ $inv->client?->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $inv->issue_date?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $inv->due_date?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $statusColors[$inv->status] ?? 'bg-muted' }}">
                                {{ str_replace('_', ' ', $inv->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($inv->grand_total) }}</td>
                        <td class="px-4 py-3 text-right {{ $inv->balance_due > 0 ? 'text-red-400' : 'text-green-400' }}">{{ App\Helpers\Format::currency($inv->balance_due) }}</td>
                        <td class="px-4 py-3 text-right flex gap-1 justify-end">
                            <a wire:navigate href="{{ route('invoices.show', $inv) }}" class="inline-flex items-center gap-1 rounded-md border px-2 py-1 text-xs hover:bg-accent"><x-icon name="eye" class="size-3" /> View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-muted-foreground">Tidak ada invoice</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $invoices->links() }}</div>
</div>
