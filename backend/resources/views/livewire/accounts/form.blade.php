<div class="space-y-6">
    <x-page-header :title="$account->exists ? 'Edit Akun' : 'Tambah Akun'" />
    <form wire:submit="save">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Detail Akun</h3></div>
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Kode <span class="text-destructive">*</span></label>
                    <input wire:model="code" type="text" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring @error('code') border-destructive @enderror" />
                    @error('code') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Tipe <span class="text-destructive">*</span></label>
                    <x-select wire:model="type"
                              :options="['asset' => 'Asset', 'liability' => 'Liability', 'equity' => 'Equity', 'revenue' => 'Revenue', 'expense' => 'Expense']" />
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-sm font-medium">Nama <span class="text-destructive">*</span></label>
                    <input wire:model="name" type="text" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring @error('name') border-destructive @enderror" />
                    @error('name') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Parent Akun</label>
                    <x-select wire:model="parent_id" placeholder="Tidak ada parent" :searchable="true"
                              :options="$parentAccounts->mapWithKeys(fn($p) => [$p->id => $p->code . ' - ' . $p->name])->toArray()" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Saldo Awal</label>
                    <input wire:model="opening_balance" type="number" step="100" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
                <div class="flex items-center gap-2">
                    <input wire:model="is_active" id="is_active" type="checkbox" class="rounded border" />
                    <label for="is_active" class="text-sm font-medium">Aktif</label>
                </div>
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <a wire:navigate href="{{ route('accounts.index') }}" class="rounded-md border px-4 py-2 text-sm hover:bg-accent">Batal</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                <span wire:loading.remove>Simpan</span><span wire:loading>Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
