<div class="space-y-6">
    <x-page-header :title="$account->name" :description="$account->code">
        <a wire:navigate href="{{ route('accounts.index') }}" class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm hover:bg-accent">
            <x-icon name="arrow-left" class="size-4" /> Kembali
        </a>
        <a wire:navigate href="{{ route('accounts.edit', $account) }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
            <x-icon name="pencil" class="size-4" /> Edit
        </a>
    </x-page-header>

    {{-- Account Info --}}
    <div class="rounded-lg border bg-card shadow-sm">
        <div class="border-b p-4"><h3 class="font-semibold">Detail Akun</h3></div>
        <div class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="text-sm text-muted-foreground">Kode</p>
                <p class="font-medium font-mono">{{ $account->code }}</p>
            </div>
            <div>
                <p class="text-sm text-muted-foreground">Tipe</p>
                <p class="font-medium capitalize">{{ $account->type }}</p>
            </div>
            <div>
                <p class="text-sm text-muted-foreground">Parent</p>
                <p class="font-medium">{{ $account->parent?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-muted-foreground">Saldo Awal</p>
                <p class="font-medium">{{ App\Helpers\Format::currency($account->opening_balance) }}</p>
            </div>
            <div>
                <p class="text-sm text-muted-foreground">Status</p>
                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $account->is_active ? 'bg-green-500/15 text-green-400' : 'bg-muted text-muted-foreground' }}">
                    {{ $account->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <div>
                <p class="text-sm text-muted-foreground">Jenis</p>
                <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {{ $account->is_header ? 'bg-blue-500/15 text-blue-400' : 'bg-muted text-muted-foreground' }}">
                    {{ $account->is_header ? 'Header' : 'Transaksi' }}
                </span>
            </div>
        </div>
        @if ($account->is_header && $account->children->count() > 0)
            <div class="border-t px-6 py-4">
                <p class="text-sm text-muted-foreground mb-2">Sub-akun</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($account->children as $child)
                        <a wire:navigate href="{{ route('accounts.show', $child) }}"
                           class="inline-flex items-center gap-1.5 rounded-md border px-3 py-1.5 text-xs font-medium hover:bg-accent transition-colors">
                            <span class="font-mono text-muted-foreground">{{ $child->code }}</span>
                            <span>{{ $child->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Summary Cards --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <p class="text-sm text-muted-foreground">Total Debit</p>
            <p class="mt-1 text-2xl font-bold">{{ App\Helpers\Format::currency($totalDebit) }}</p>
        </div>
        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <p class="text-sm text-muted-foreground">Total Kredit</p>
            <p class="mt-1 text-2xl font-bold">{{ App\Helpers\Format::currency($totalCredit) }}</p>
        </div>
        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <p class="text-sm text-muted-foreground">Saldo</p>
            <p class="mt-1 text-2xl font-bold {{ $balance >= 0 ? 'text-green-400' : 'text-red-400' }}">
                {{ App\Helpers\Format::currency(abs($balance)) }}
                <span class="text-sm font-normal text-muted-foreground">{{ $balance >= 0 ? '(Debit)' : '(Kredit)' }}</span>
            </p>
        </div>
    </div>

    {{-- Journal Lines --}}
    <div class="rounded-lg border bg-card shadow-sm">
        <div class="flex flex-col gap-3 border-b p-4 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="font-semibold">Riwayat Jurnal</h3>
            <div class="flex items-center gap-2">
                <input wire:model.live="dateFrom" type="date"
                       class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                <span class="text-sm text-muted-foreground">s/d</span>
                <input wire:model.live="dateTo" type="date"
                       class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                        <th class="px-4 py-3 font-medium">Tanggal</th>
                        <th class="px-4 py-3 font-medium">No. Jurnal</th>
                        @if ($isAggregate)
                            <th class="px-4 py-3 font-medium">Akun</th>
                        @endif
                        <th class="px-4 py-3 font-medium">Keterangan</th>
                        <th class="px-4 py-3 text-right font-medium">Debit</th>
                        <th class="px-4 py-3 text-right font-medium">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lines as $line)
                        <tr class="border-b hover:bg-muted/30 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">{{ $line->journalEntry?->date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <a wire:navigate href="{{ route('journal-entries.show', $line->journalEntry) }}" class="font-medium text-primary hover:underline">
                                    {{ $line->journalEntry?->journal_no }}
                                </a>
                            </td>
                            @if ($isAggregate)
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs text-muted-foreground">{{ $line->account?->code }}</span>
                                    <span class="ml-1">{{ $line->account?->name }}</span>
                                </td>
                            @endif
                            <td class="px-4 py-3 text-muted-foreground">{{ $line->description ?? $line->journalEntry?->description ?? '-' }}</td>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAggregate ? 6 : 5 }}" class="px-4 py-12 text-center text-muted-foreground">
                                Belum ada transaksi jurnal untuk akun ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($lines->count() > 0)
                    <tfoot>
                        <tr class="border-t bg-muted/30 font-semibold">
                            <td class="px-4 py-3" colspan="{{ $isAggregate ? 4 : 3 }}">Total</td>
                            <td class="px-4 py-3 text-right font-mono">{{ App\Helpers\Format::currency($totalDebit) }}</td>
                            <td class="px-4 py-3 text-right font-mono">{{ App\Helpers\Format::currency($totalCredit) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        @if ($lines->hasPages())
            <div class="border-t p-4">{{ $lines->links() }}</div>
        @endif
    </div>
</div>
