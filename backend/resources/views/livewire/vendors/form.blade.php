<div class="space-y-6">
    <x-page-header :title="$isEditing ? 'Edit Vendor' : 'Tambah Vendor'" />
    <form wire:submit="save">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="border-b p-4"><h3 class="font-semibold">Informasi Vendor</h3></div>
                <div class="grid gap-4 p-4 sm:grid-cols-2">
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-sm font-medium">Nama <span class="text-destructive">*</span></label>
                        <input wire:model="name" type="text" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring @error('name') border-destructive @enderror" />
                        @error('name') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Email</label>
                        <input wire:model="email" type="email" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Telepon</label>
                        <input wire:model="phone" type="text" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                    </div>
                    <div class="space-y-1.5 sm:col-span-2">
                        <label class="text-sm font-medium">Alamat</label>
                        <textarea wire:model="address" rows="2" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input wire:model="is_active" id="is_active" type="checkbox" class="rounded border" />
                        <label for="is_active" class="text-sm font-medium">Aktif</label>
                    </div>
                </div>
            </div>
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="border-b p-4"><h3 class="font-semibold">Informasi Bank</h3></div>
                <div class="grid gap-4 p-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Nama Bank</label>
                        <input wire:model="bank_name" type="text" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Nomor Rekening</label>
                        <input wire:model="bank_account" type="text" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-sm font-medium">Atas Nama</label>
                        <input wire:model="bank_holder" type="text" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <a wire:navigate href="{{ route('vendors.index') }}" class="rounded-md border px-4 py-2 text-sm hover:bg-accent">Batal</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                <span wire:loading.remove>Simpan</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
