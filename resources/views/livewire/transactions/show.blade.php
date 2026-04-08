<div class="space-y-6">
    <x-page-header :title="$transaction->transaction_no" :description="ucfirst($transaction->type)">
        <a wire:navigate href="{{ route('transactions.index') }}" class="inline-flex items-center gap-2 rounded-md border px-4 py-2 text-sm hover:bg-accent"><x-icon name="arrow-left" class="size-4" /> Kembali</a>
    </x-page-header>
    <div class="rounded-lg border bg-card shadow-sm">
        <div class="border-b p-4"><h3 class="font-semibold">Detail Transaksi</h3></div>
        <div class="grid gap-4 p-4 sm:grid-cols-2">
            <div><p class="text-sm text-muted-foreground">Tipe</p><p class="font-medium capitalize">{{ $transaction->type }}</p></div>
            <div><p class="text-sm text-muted-foreground">Tanggal</p><p class="font-medium">{{ $transaction->date?->format('d/m/Y') }}</p></div>
            <div><p class="text-sm text-muted-foreground">Jumlah</p><p class="text-xl font-bold {{ $transaction->type === 'income' ? 'text-green-400' : ($transaction->type === 'expense' ? 'text-red-400' : '') }}">{{ App\Helpers\Format::currency($transaction->amount) }}</p></div>
            <div><p class="text-sm text-muted-foreground">Metode</p><p class="font-medium capitalize">{{ str_replace('_', ' ', $transaction->payment_method ?? '-') }}</p></div>
            <div><p class="text-sm text-muted-foreground">Akun</p><p class="font-medium">{{ $transaction->account?->name ?? '-' }}</p></div>
            <div><p class="text-sm text-muted-foreground">Akun Lawan</p><p class="font-medium">{{ $transaction->contraAccount?->name ?? '-' }}</p></div>
            @if ($transaction->description)
                <div class="sm:col-span-2"><p class="text-sm text-muted-foreground">Keterangan</p><p class="font-medium">{{ $transaction->description }}</p></div>
            @endif
        </div>
    </div>
</div>
