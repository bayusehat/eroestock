<div class="space-y-6">
    <x-page-header :title="$journalEntry->journal_no" :description="$journalEntry->description ?? 'Detail jurnal'">
        <a wire:navigate href="{{ route('journal-entries.index') }}" class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm hover:bg-accent">
            <x-icon name="arrow-left" class="size-4" /> Kembali
        </a>
    </x-page-header>

    {{-- Entry Information --}}
    <div class="rounded-lg border bg-card shadow-sm">
        <div class="border-b p-4">
            <h3 class="font-semibold">Informasi Jurnal</h3>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-sm text-muted-foreground">No. Jurnal</p>
                    <p class="font-medium">{{ $journalEntry->journal_no }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Tanggal</p>
                    <p class="font-medium">{{ $journalEntry->date?->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Total Transaksi</p>
                    <p class="font-medium">{{ App\Helpers\Format::currency($journalEntry->lines->sum('debit')) }}</p>
                </div>
                <div>
                    <p class="text-sm text-muted-foreground">Dibuat oleh</p>
                    <p class="font-medium">{{ $journalEntry->createdBy?->name ?? '-' }}</p>
                </div>
            </div>
            @if ($journalEntry->description)
                <div>
                    <p class="text-sm text-muted-foreground">Keterangan</p>
                    <p class="font-medium whitespace-pre-wrap">{{ $journalEntry->description }}</p>
                </div>
            @endif
            @if ($journalEntry->invoice_id)
                <div>
                    <p class="text-sm text-muted-foreground">Terkait Invoice</p>
                    <a wire:navigate href="{{ route('invoices.show', $journalEntry->invoice_id) }}" class="font-medium text-primary hover:underline">
                        {{ $journalEntry->invoice?->invoice_no ?? 'Lihat Invoice' }}
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Journal Lines --}}
    <div class="rounded-lg border bg-card shadow-sm">
        <div class="border-b p-4">
            <h3 class="font-semibold">Baris Jurnal</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                        <th class="px-4 py-3 font-medium">Akun</th>
                        <th class="px-4 py-3 text-right font-medium">Debit</th>
                        <th class="px-4 py-3 text-right font-medium">Kredit</th>
                        <th class="px-4 py-3 font-medium">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalDebit = 0; $totalCredit = 0; @endphp
                    @foreach ($journalEntry->lines as $line)
                        @php $totalDebit += $line->debit; $totalCredit += $line->credit; @endphp
                        <tr class="border-b hover:bg-muted/30 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs text-muted-foreground">{{ $line->account?->code }}</span>
                                <span class="ml-1">{{ $line->account?->name }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                @if ($line->debit > 0)
                                    {{ App\Helpers\Format::currency($line->debit) }}
                                @else
                                    <span class="text-muted-foreground">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                @if ($line->credit > 0)
                                    {{ App\Helpers\Format::currency($line->credit) }}
                                @else
                                    <span class="text-muted-foreground">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">{{ $line->description ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t bg-muted/30 font-semibold">
                        <td class="px-4 py-3">Total</td>
                        <td class="px-4 py-3 text-right font-mono">{{ App\Helpers\Format::currency($totalDebit) }}</td>
                        <td class="px-4 py-3 text-right font-mono">{{ App\Helpers\Format::currency($totalCredit) }}</td>
                        <td class="px-4 py-3"></td>
                    </tr>
                    @if (abs($totalDebit - $totalCredit) > 0.001)
                        <tr class="bg-red-950/30 text-red-400 font-medium">
                            <td class="px-4 py-3">Selisih</td>
                            <td class="px-4 py-3 text-right font-mono" colspan="2">{{ App\Helpers\Format::currency(abs($totalDebit - $totalCredit)) }}</td>
                            <td class="px-4 py-3"></td>
                        </tr>
                    @else
                        <tr class="bg-green-950/30 text-green-400">
                            <td class="px-4 py-3 font-medium" colspan="4">
                                <span class="flex items-center gap-2"><x-icon name="check-circle" class="size-4" /> Jurnal seimbang (balanced)</span>
                            </td>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>
</div>
