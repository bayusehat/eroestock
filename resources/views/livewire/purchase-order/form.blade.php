<div class="space-y-6">
    <x-page-header :title="$isEditing ? 'Edit Item' : 'Tambah Item'" />
    <form wire:submit="save">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Informasi Purchase Order</h3></div>
            <div class="grid gap-4 p-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Client / Supplier <span class="text-destructive">*</span></label>
                    <x-select wire:model="client_id" placeholder="Pilih client..." :searchable="true"
                                          :options="$clients->pluck('name', 'id')->toArray()" />
                    @error('client_id') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Deskripsi </label>
                   <textarea wire:model="description" rows="3" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring"></textarea>
                </div>
            </div>
            <div class="grid gap-4 p-4">
                <div class="rounded-lg border bg-card shadow-sm">
                    <div class="border-b p-4">
                        <h3 class="font-semibold">Line Stock</h3>
                    </div>
                    <div class="space-y-4 p-4">
                        @foreach ($items as $i => $item)
                            <div class="rounded-lg border p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-muted-foreground">Item {{ $i + 1 }}</span>
                                    <button type="button" wire:click="removeItem({{ $i }})"
                                            @if(count($items) === 1) disabled @endif
                                            class="flex items-center gap-1 text-xs text-muted-foreground hover:text-destructive disabled:opacity-40">
                                        <x-icon name="trash-2" class="size-3.5" /> Remove
                                    </button>
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium">Pilih Item / SKU<span class="text-destructive">*</span></label>
                                        <x-select wire:model="inventory_id" placeholder="Pilih Item..." :searchable="true"
                                            :options="collect($inventoryItem[0])->pluck('sku','id')->toArray()" />
                                        @error('items.'.$i.'.inventory_id') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium">Quantity<span class="text-destructive">*</span></label>
                                        <input wire:model="items.{{ $i }}.quantity" type="number" placeholder="0"
                                               class="h-8 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                                        @error('items.'.$i.'.quantity') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <button type="button" wire:click="addItem"
                                class="flex w-full items-center justify-center gap-2 rounded-md border border-dashed px-4 py-2 text-sm bg-mist-50 text-muted-foreground hover:bg-accent">
                            <x-icon name="plus" class="size-4" /> Tambah Item
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4 flex gap-2">
            <a wire:navigate href="{{ route('items.index') }}" class="rounded-md border px-4 py-2 text-sm hover:bg-accent">Batal</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                <span wire:loading.remove>Simpan</span>
                <span wire:loading class="flex items-center gap-2"><x-icon name="loader-2" class="size-4 animate-spin" /> Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
