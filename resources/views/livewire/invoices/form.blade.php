<div class="space-y-6">
    <x-page-header :title="$isEditing ? 'Edit Invoice' : 'Buat Invoice'" />
    <form wire:submit="save">
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-6">
                <div class="rounded-lg border bg-card shadow-sm">
                    <div class="border-b p-4"><h3 class="font-semibold">Detail Invoice</h3></div>
                    <div class="grid gap-4 p-4 sm:grid-cols-2">
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="text-sm font-medium">Client <span class="text-destructive">*</span></label>
                            <x-select wire:model="client_id" placeholder="Pilih client..." :searchable="true"
                                      :options="$clients->pluck('name', 'id')->toArray()" />
                            @error('client_id') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium">Tanggal Invoice <span class="text-destructive">*</span></label>
                            <input wire:model="issue_date" type="date" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium">Jatuh Tempo <span class="text-destructive">*</span></label>
                            <input wire:model="due_date" type="date" class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                        </div>
                        <div class="space-y-1.5 sm:col-span-2">
                            <label class="text-sm font-medium">Catatan</label>
                            <textarea wire:model="notes" rows="2" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring"></textarea>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg border bg-card shadow-sm">
                    <div class="border-b p-4"><h3 class="font-semibold">Summary</h3></div>
                    <div class="space-y-2 p-4">
                        <div class="flex justify-between text-sm"><span class="text-muted-foreground">Subtotal</span><span>{{ App\Helpers\Format::currency($this->subtotal) }}</span></div>
                        <div class="flex justify-between text-sm"><span class="text-muted-foreground">Discount</span><span>{{ App\Helpers\Format::currency($this->discountAmount) }}</span></div>
                        <div class="flex justify-between text-sm"><span class="text-muted-foreground">Tax</span><span>{{ App\Helpers\Format::currency($this->taxAmount) }}</span></div>
                        <div class="flex justify-between border-t pt-2 font-semibold"><span>Grand Total</span><span>{{ App\Helpers\Format::currency($this->grandTotal) }}</span></div>
                    </div>
                </div>
            </div>
            <div class="rounded-lg border bg-card shadow-sm">
                <div class="border-b p-4"><h3 class="font-semibold">Line Items</h3></div>
                <div class="space-y-4 p-4">
                    @foreach ($items as $i => $item)
                        <div class="rounded-lg border p-3 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-muted-foreground">Item {{ $i + 1 }}</span>
                                <button type="button" wire:click="removeItem({{ $i }})" @if(count($items)===1) disabled @endif class="text-xs text-muted-foreground hover:text-destructive disabled:opacity-40">Remove</button>
                            </div>
                            <input wire:model="items.{{ $i }}.description" type="text" placeholder="Deskripsi"
                                   class="h-8 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                            <div class="grid grid-cols-3 gap-2">
                                <div><label class="text-xs">Qty</label><input wire:model.lazy="items.{{ $i }}.quantity" type="number" step="0.01" class="h-7 w-full rounded-md border border-input bg-transparent px-2 text-sm focus:outline-none" /></div>
                                <div><label class="text-xs">Harga</label><input wire:model.lazy="items.{{ $i }}.unit_price" type="number" step="100" class="h-7 w-full rounded-md border border-input bg-transparent px-2 text-sm focus:outline-none" /></div>
                                <div><label class="text-xs">Tax %</label><input wire:model.lazy="items.{{ $i }}.tax_rate" type="number" step="0.01" class="h-7 w-full rounded-md border border-input bg-transparent px-2 text-sm focus:outline-none" /></div>
                            </div>
                        </div>
                    @endforeach
                    <button type="button" wire:click="addItem" class="flex w-full items-center justify-center gap-2 rounded-md border border-dashed px-4 py-2 text-sm text-muted-foreground hover:bg-accent">
                        <x-icon name="plus" class="size-4" /> Tambah Item
                    </button>
                </div>
            </div>
        </div>
        <div class="mt-6 flex gap-2">
            <a wire:navigate href="{{ route('invoices.index') }}" class="rounded-md border px-4 py-2 text-sm hover:bg-accent">Batal</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                <span wire:loading.remove>{{ $isEditing ? 'Simpan' : 'Buat Invoice' }}</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
