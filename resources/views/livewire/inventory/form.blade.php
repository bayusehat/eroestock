<div class="space-y-6">
    <x-page-header :title="$isEditing ? 'Edit Item' : 'Tambah Item'" />
    <form wire:submit="save">
        <div class="rounded-lg border bg-card shadow-sm">
            <div class="border-b p-4"><h3 class="font-semibold">Informasi Item</h3></div>
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Name <span class="text-destructive">*</span></label>
                    <input wire:model="name" type="text" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring @error('name') border-destructive @enderror" />
                    @error('name') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Brand</label>
                    <x-select wire:model="id_brand" placeholder="Pilih brand..." :searchable="true" :options="$brands->pluck('name', 'id')->toArray()" />
                    @error('id_brand') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Buy Price</label>
                    <input wire:model="buy_price" type="number" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                    @error('buy_price') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium">Sell Price</label>
                    <input wire:model="sell_price" type="number" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                    @error('sell_price') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
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
                                <div class="space-y-1.5">
                                    <label class="text-xs font-medium">SKU<span class="text-destructive">*</span></label>
                                    <input wire:model="items.{{ $i }}.sku" type="text" placeholder="Item SKU"
                                           class="h-8 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                                    @error('items.'.$i.'.sku') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-medium">Color<span class="text-destructive">*</span></label>
                                    <input wire:model="items.{{ $i }}.color" type="text" placeholder="Color"
                                           class="h-8 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                                    @error('items.'.$i.'.color') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium">Size<span class="text-destructive">*</span></label>
                                        <input wire:model.lazy="items.{{ $i }}.size" type="number"
                                               class="h-8 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                                        @error('items.'.$i.'.size') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium">Store Stock<span class="text-destructive">*</span></label>
                                        <input wire:model="items.{{ $i }}.store_stock" type="number" placeholder="0"
                                               class="h-8 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                                        @error('items.'.$i.'.store_stock') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium">Warehouse Stock<span class="text-destructive">*</span></label>
                                        <input wire:model.lazy="items.{{ $i }}.warehouse_stock" type="number"
                                               class="h-8 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                                        @error('items.'.$i.'.warehouse_stock') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <button type="button" wire:click="addItem"
                                class="flex w-full items-center justify-center gap-2 rounded-md border border-dashed px-4 py-2 text-sm text-muted-foreground hover:bg-accent">
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
