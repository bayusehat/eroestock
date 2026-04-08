<div class="space-y-6">
    <x-page-header title="Transactions" description="Kelola transaksi keuangan">
        <a wire:navigate href="{{ route('transactions.create') }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"><x-icon name="plus" class="size-4" /> Buat Transaksi</a>
    </x-page-header>
    <div class="flex flex-wrap gap-3">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari transaksi..." class="h-9 max-w-xs rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
        <x-select wire:model.live="typeFilter" placeholder="Semua Tipe"
                  :options="['' => 'Semua Tipe', 'income' => 'Income', 'expense' => 'Expense', 'transfer' => 'Transfer']" class="w-40" />
        <input wire:model.live="dateFrom" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
        <input wire:model.live="dateTo" type="date" class="h-9 rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
    </div>
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b bg-muted/50 text-left text-muted-foreground"><th class="px-4 py-3 font-medium">No. Transaksi</th><th class="px-4 py-3 font-medium">Tanggal</th><th class="px-4 py-3 font-medium">Tipe</th><th class="px-4 py-3 font-medium">Akun</th><th class="px-4 py-3 font-medium">Keterangan</th><th class="px-4 py-3 text-right font-medium">Jumlah</th></tr></thead>
            <tbody>
                @forelse ($transactions as $txn)
                    <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3"><a wire:navigate href="{{ route('transactions.show', $txn) }}" class="font-medium text-primary hover:underline">{{ $txn->transaction_no }}</a></td>
                        <td class="px-4 py-3">{{ $txn->date?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 capitalize">{{ $txn->type }}</td>
                        <td class="px-4 py-3">{{ $txn->account?->name ?? '-' }}</td>
                        <td class="px-4 py-3 max-w-[200px] truncate">{{ $txn->description ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium {{ $txn->type === 'income' ? 'text-green-400' : ($txn->type === 'expense' ? 'text-red-400' : '') }}">
                            {{ App\Helpers\Format::currency($txn->amount) }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-muted-foreground">Tidak ada transaksi</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $transactions->links() }}</div>
</div>
