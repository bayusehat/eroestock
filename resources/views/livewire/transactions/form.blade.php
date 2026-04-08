<div class="space-y-6">
    <x-page-header title="Buat Transaksi" />
    <form wire:submit="save">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Detail Transaksi</h3></div>
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Tipe <span class="text-destructive">*</span></label>
                    <x-select wire:model="type"
                              :options="['income' => 'Income', 'expense' => 'Expense', 'transfer' => 'Transfer']" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Tanggal <span class="text-destructive">*</span></label>
                    <input wire:model="date" type="date" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Jumlah <span class="text-destructive">*</span></label>
                    <input wire:model="amount" type="number" step="100" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Metode Pembayaran</label>
                    <x-select wire:model="payment_method"
                              :options="['bank_transfer' => 'Transfer Bank', 'cash' => 'Tunai', 'check' => 'Cek']" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Akun <span class="text-destructive">*</span></label>
                    <x-select wire:model="account_id" placeholder="Pilih akun..." :searchable="true"
                              :options="$accounts->mapWithKeys(fn($a) => [$a->id => $a->code . ' - ' . $a->name])->toArray()" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Akun Lawan</label>
                    <x-select wire:model="contra_account_id" placeholder="Pilih akun lawan..." :searchable="true"
                              :options="$accounts->mapWithKeys(fn($a) => [$a->id => $a->code . ' - ' . $a->name])->toArray()" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">No. Referensi</label>
                    <input wire:model="reference_no" type="text" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Kategori</label>
                    <input wire:model="category" type="text" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-sm font-medium">Keterangan</label>
                    <textarea wire:model="description" rows="2" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring"></textarea>
                </div>
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <a wire:navigate href="{{ route('transactions.index') }}" class="rounded-md border px-4 py-2 text-sm hover:bg-accent">Batal</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                <span wire:loading.remove>Simpan</span><span wire:loading>Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
