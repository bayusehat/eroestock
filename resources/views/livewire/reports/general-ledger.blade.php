<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <x-page-header title="General Ledger" description="Buku besar" />
        <div class="flex flex-wrap gap-2">
            <x-select wire:model.live="accountId" placeholder="Pilih akun..." :searchable="true"
                      :options="$accounts->mapWithKeys(fn($a) => [$a->id => $a->code . ' - ' . $a->name . ($a->is_header ? ' (Header)' : '')])->toArray()" class="w-64" />
            <input wire:model.live="dateFrom" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
            <input wire:model.live="dateTo" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
        </div>
    </div>

    @if (!empty($data))
        {{-- Account Info & Summary --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-lg border bg-card p-5 shadow-sm">
                <p class="text-sm text-muted-foreground">Akun</p>
                <p class="mt-1 text-lg font-bold">{{ $data['account']['code'] ?? '' }} - {{ $data['account']['name'] ?? '' }}</p>
                @if (!empty($data['is_aggregate']))
                    <span class="mt-1 inline-flex items-center rounded-md border bg-blue-500/15 px-2 py-0.5 text-xs font-medium text-blue-400">
                        Termasuk sub-akun
                    </span>
                @endif
            </div>
            <div class="rounded-lg border bg-card p-5 shadow-sm">
                <p class="text-sm text-muted-foreground">Saldo Awal</p>
                <p class="mt-1 text-lg font-bold">{{ App\Helpers\Format::currency($data['opening_balance'] ?? 0) }}</p>
            </div>
            <div class="rounded-lg border bg-card p-5 shadow-sm">
                <p class="text-sm text-muted-foreground">Saldo Akhir</p>
                <p class="mt-1 text-lg font-bold {{ ($data['closing_balance'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ App\Helpers\Format::currency($data['closing_balance'] ?? 0) }}
                </p>
            </div>
        </div>

        {{-- Ledger Table --}}
        <div class="rounded-md border overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                        <th class="px-4 py-3 font-medium w-8"></th>
                        <th class="px-4 py-3 font-medium">Tanggal</th>
                        <th class="px-4 py-3 font-medium">Referensi</th>
                        <th class="px-4 py-3 font-medium">Keterangan</th>
                        @if (!empty($data['is_aggregate']))
                            <th class="px-4 py-3 font-medium">Akun</th>
                        @endif
                        <th class="px-4 py-3 text-right font-medium">Debit</th>
                        <th class="px-4 py-3 text-right font-medium">Kredit</th>
                        <th class="px-4 py-3 text-right font-medium">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Opening Balance Row --}}
                    @if (($data['opening_balance'] ?? 0) != 0)
                        <tr class="border-b bg-muted/20">
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3 text-muted-foreground italic" colspan="{{ !empty($data['is_aggregate']) ? 5 : 4 }}">Saldo awal</td>
                            <td class="px-4 py-3 text-right font-medium">{{ App\Helpers\Format::currency($data['opening_balance']) }}</td>
                        </tr>
                    @endif

                    @forelse ($data['entries'] as $row)
                        @php
                            $journalEntryId = $row['journal_entry_id'] ?? null;
                            $isExpanded = $journalEntryId && $expandedEntryId === $journalEntryId;
                        @endphp
                        <tr class="border-b hover:bg-muted/30 transition-colors {{ $isExpanded ? 'bg-primary/5' : '' }} {{ $journalEntryId ? 'cursor-pointer' : '' }}"
                            @if ($journalEntryId) wire:click="toggleEntry({{ $journalEntryId }})" @endif
                            role="{{ $journalEntryId ? 'button' : '' }}"
                            @if ($journalEntryId) title="Klik untuk melihat detail jurnal" @endif>
                            <td class="px-4 py-3 text-center text-muted-foreground">
                                @if ($journalEntryId)
                                    <svg class="size-4 transition-transform {{ $isExpanded ? 'rotate-90' : '' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $row['date'] }}</td>
                            <td class="px-4 py-3">
                                @if ($journalEntryId)
                                    <span class="font-medium text-primary">{{ $row['reference'] }}</span>
                                @else
                                    {{ $row['reference'] ?? '-' }}
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $row['description'] ?? '-' }}</td>
                            @if (!empty($data['is_aggregate']))
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs text-muted-foreground">{{ $row['account_code'] ?? '' }}</span>
                                    <span class="ml-1">{{ $row['account_name'] ?? '' }}</span>
                                </td>
                            @endif
                            <td class="px-4 py-3 text-right font-mono">{{ $row['debit'] > 0 ? App\Helpers\Format::currency($row['debit']) : '-' }}</td>
                            <td class="px-4 py-3 text-right font-mono">{{ $row['credit'] > 0 ? App\Helpers\Format::currency($row['credit']) : '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium font-mono">{{ App\Helpers\Format::currency($row['running_balance']) }}</td>
                        </tr>

                        {{-- Expanded Journal Detail --}}
                        @if ($isExpanded && $expandedJournal)
                            <tr class="border-b">
                                <td colspan="{{ !empty($data['is_aggregate']) ? 8 : 7 }}" class="p-0">
                                    <div class="bg-muted/20 border-l-4 border-primary/50 px-6 py-4 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-semibold text-foreground flex items-center gap-2">
                                                <svg class="size-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                </svg>
                                                Detail Jurnal: {{ $expandedJournal->journal_no }}
                                            </h4>
                                            <a wire:navigate href="{{ route('journal-entries.show', $expandedJournal->id) }}"
                                               wire:click.stop
                                               class="inline-flex items-center gap-1 rounded-md border bg-background px-3 py-1.5 text-xs font-medium hover:bg-accent transition-colors">
                                                Lihat Detail
                                                <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                </svg>
                                            </a>
                                        </div>

                                        @if ($expandedJournal->description)
                                            <p class="text-sm text-muted-foreground">{{ $expandedJournal->description }}</p>
                                        @endif

                                        <div class="rounded-md border bg-background overflow-hidden">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                                                        <th class="px-4 py-2 font-medium text-xs">Akun</th>
                                                        <th class="px-4 py-2 text-right font-medium text-xs">Debit</th>
                                                        <th class="px-4 py-2 text-right font-medium text-xs">Kredit</th>
                                                        <th class="px-4 py-2 font-medium text-xs">Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $jTotalDebit = 0; $jTotalCredit = 0; @endphp
                                                    @foreach ($expandedJournal->lines as $line)
                                                        @php $jTotalDebit += $line->debit; $jTotalCredit += $line->credit; @endphp
                                                        <tr class="border-b last:border-0 hover:bg-muted/20 transition-colors">
                                                            <td class="px-4 py-2">
                                                                <span class="font-mono text-xs text-muted-foreground">{{ $line->account?->code }}</span>
                                                                <span class="ml-1">{{ $line->account?->name }}</span>
                                                            </td>
                                                            <td class="px-4 py-2 text-right font-mono">
                                                                @if ($line->debit > 0)
                                                                    {{ App\Helpers\Format::currency($line->debit) }}
                                                                @else
                                                                    <span class="text-muted-foreground">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-2 text-right font-mono">
                                                                @if ($line->credit > 0)
                                                                    {{ App\Helpers\Format::currency($line->credit) }}
                                                                @else
                                                                    <span class="text-muted-foreground">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-2 text-muted-foreground text-xs">{{ $line->description ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="border-t bg-muted/30 font-semibold text-xs">
                                                        <td class="px-4 py-2">Total</td>
                                                        <td class="px-4 py-2 text-right font-mono">{{ App\Helpers\Format::currency($jTotalDebit) }}</td>
                                                        <td class="px-4 py-2 text-right font-mono">{{ App\Helpers\Format::currency($jTotalCredit) }}</td>
                                                        <td class="px-4 py-2">
                                                            @if (abs($jTotalDebit - $jTotalCredit) < 0.001)
                                                                <span class="text-green-400 flex items-center gap-1">
                                                                    <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                                    </svg>
                                                                    Seimbang
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="{{ !empty($data['is_aggregate']) ? 8 : 7 }}" class="px-4 py-12 text-center text-muted-foreground">Tidak ada data untuk periode ini</td></tr>
                    @endforelse
                </tbody>
                @if (!empty($data['entries']) && count($data['entries']) > 0)
                    <tfoot>
                        <tr class="border-t bg-muted/30 font-semibold">
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3" colspan="{{ !empty($data['is_aggregate']) ? 3 : 2 }}">Total</td>
                            <td class="px-4 py-3 text-right font-mono">
                                {{ App\Helpers\Format::currency(collect($data['entries'])->sum('debit')) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                {{ App\Helpers\Format::currency(collect($data['entries'])->sum('credit')) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                {{ App\Helpers\Format::currency($data['closing_balance'] ?? 0) }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @else
        <div class="flex items-center justify-center rounded-lg border border-dashed p-16 text-muted-foreground">
            Pilih akun untuk melihat buku besar
        </div>
    @endif
</div>
