<div class="space-y-6">
    <x-page-header :title="$isEditing ? 'Edit Request' : 'Buat Request'" />
    <form wire:submit="save">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Detail Request</h3></div>
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Tipe <span class="text-destructive">*</span></label>
                    <x-select wire:model="type"
                              :options="['purchase' => 'Purchase', 'reimbursement' => 'Reimbursement', 'advance' => 'Advance', 'other' => 'Other']" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Jumlah</label>
                    <input wire:model="amount" type="number" step="100" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-sm font-medium">Judul <span class="text-destructive">*</span></label>
                    <input wire:model="title" type="text" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring @error('title') border-destructive @enderror" />
                    @error('title') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-sm font-medium">Deskripsi</label>
                    <textarea wire:model="description" rows="3" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring"></textarea>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Akun</label>
                    <x-select wire:model="account_id" placeholder="Pilih akun..." :searchable="true"
                              :options="$accounts->pluck('name', 'id')->toArray()" />
                </div>
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <a wire:navigate href="{{ route('requests.index') }}" class="rounded-md border px-4 py-2 text-sm hover:bg-accent">Batal</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                <span wire:loading.remove>Simpan</span><span wire:loading>Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
