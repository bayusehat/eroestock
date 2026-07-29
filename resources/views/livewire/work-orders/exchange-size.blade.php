<div class="space-y-6">
    <x-page-header :title="$isEditing ? 'Edit Tukar Size' : 'Tukar Size'"
                   :description="$isEditing ? 'Ubah tukar size yang ada' : 'Buat tukar size baru'" />

    <form wire:submit="save">
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Left: Order Details --}}
            <div class="space-y-6">
                <div class="rounded-lg border bg-card shadow-sm">
                    <div class="border-b p-4">
                        <h3 class="font-semibold">Order Details</h3>
                    </div>
                    <div class="space-y-4 p-4">
                        {{-- <div class="grid gap-4 sm:grid-cols-2"> --}}
                            {{-- <div class="space-y-1.5">
                                <label class="text-sm font-medium">Customer <span class="text-destructive">*</span></label>
                                <x-select wire:model="client_id" placeholder="Pilih customer..." :searchable="true" :options="$clients->pluck('name', 'id')->toArray()" />
                            </div> --}}

                        {{-- </div> --}}
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium">Order Number</label>
                            <input wire:model="order_sn" type="text" placeholder="e.g. John Doe"
                                    class="h-9 w-full rounded-md border-transparent focus:border-transparent focus:ring-0" readonly/>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium">Nama Customer</label>
                            <input wire:model="buyer_name" type="text" placeholder="e.g. John Doe"
                                    class="h-9 w-full rounded-md border-transparent focus:border-transparent focus:ring-0" readonly/>
                        </div>
                    </div>
                </div>
                <!-- Item from shopee -->
                    <div class="rounded-lg border bg-card shadow-sm">
                        <div class="border-b p-4">
                            <h3 class="font-semibold">Item from Shopee</h3>
                        </div>
                        <div class="space-y-2 p-4">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b text-left text-muted-foreground">
                                        <th class="px-4 py-3 font-medium">Item Name</th>
                                        <th class="px-4 py-3 font-medium">Item SKU</th>
                                        <th class="px-4 py-3 font-medium">Qty</th>
                                        <th class="px-4 py-3 text-right font-medium">Unit Price</th>
                                        <th class="px-4 py-3 text-right font-medium">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($shopeeItems as $item)
                                        <tr class="border-b">
                                            <td class="px-4 py-3">{{ $item->item_name ?? '' }}</td>
                                            <td class="px-4 py-3">{{ $item->model_sku ?? '' }}</td>
                                            <td class="px-4 py-3">{{ $item->model_quantity_purchased ?? '' }}</td>
                                            <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency($item->model_original_price ?? 0) }}</td>
                                            <td class="px-4 py-3 text-right">{{ App\Helpers\Format::currency(($item->model_original_price * $item->model_quantity_purchased ?? 0)) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>

            {{-- Right: Line Items + Summary --}}
            <div class="space-y-6">
                <div class="rounded-lg border bg-card shadow-sm">
                    <div class="border-b p-4">
                        <h3 class="font-semibold">Change item with :</h3>
                    </div>
                    <div class="space-y-4 p-4">
                        @foreach ($items as $i => $item)
                            <div class="rounded-lg border p-4 space-y-3" wire:key="item-{{ $i }}">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-muted-foreground">Item {{ $i + 1 }}</span>
                                    <button type="button" wire:click="removeItem({{ $i }})"
                                            @if(count($items) === 1) disabled @endif
                                            class="flex items-center gap-1 text-xs text-muted-foreground hover:text-destructive disabled:opacity-40">
                                        <x-icon name="trash-2" class="size-3.5" /> Remove
                                    </button>
                                </div>
                                <div class="space-y-1.5">
                                    {{-- <label class="text-xs font-medium">Description</label>
                                    <input wire:model="items.{{ $i }}.description" type="text" placeholder="Item description"
                                           class="h-8 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" /> --}}
                                    <label class="text-xs font-medium">Pilih Item / SKU<span class="text-destructive">*</span></label>
                                    <x-select wire:model.live="items.{{ $i }}.inventory_id" placeholder="Pilih Item..." :searchable="true"
                                        :options="collect($inventoryItem[0])->pluck('name_size','id')->toArray()" option-value="id" option-label="name_size" />
                                    @error('items.'.$i.'.inventory_id') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium">Quantity</label>
                                        <input wire:model.lazy="items.{{ $i }}.quantity" type="number" step="0.01"
                                               class="h-8 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium">Unit</label>
                                        <input wire:model="items.{{ $i }}.unit" type="text" placeholder="pcs"
                                               class="h-8 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium">Unit Price</label>
                                        <input wire:model.lazy="items.{{ $i }}.unit_price" type="number" step="100"
                                               class="h-8 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium">Discount</label>
                                        <input wire:model.lazy="items.{{ $i }}.discount" type="number" step="100"
                                               class="h-8 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium">Tax %</label>
                                        <input wire:model.lazy="items.{{ $i }}.tax_rate" type="number" step="0.01"
                                               class="h-8 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium">Subtotal</label>
                                        <div class="flex h-8 items-center rounded-md border bg-muted/50 px-3 text-sm font-medium">
                                            @php
                                                $lineTotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
                                                $tax = $lineTotal * (($item['tax_rate'] ?? 0) / 100);
                                                $sub = $lineTotal - ($item['discount'] ?? 0) + $tax;
                                            @endphp
                                            {{ App\Helpers\Format::currency($sub) }}
                                        </div>
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

                {{-- Summary --}}
                <div class="rounded-lg border bg-card shadow-sm">
                    <div class="border-b p-4">
                        <h3 class="font-semibold">Summary</h3>
                    </div>
                    <div class="space-y-2 p-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>{{ App\Helpers\Format::currency($this->subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Total Discount</span>
                            <span>{{ App\Helpers\Format::currency($this->totalDiscount) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-muted-foreground">Total Tax</span>
                            <span>{{ App\Helpers\Format::currency($this->totalTax) }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2 font-semibold">
                            <span>Grand Total</span>
                            <span>{{ App\Helpers\Format::currency($this->grandTotal) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="mt-6 flex items-center gap-2 rounded-lg border bg-card p-4">
            <a wire:navigate href="{{ route('work-orders.index') }}"
               class="rounded-md border px-4 py-2 text-sm hover:bg-accent">Batal</a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                <span wire:loading.remove>{{ $isEditing ? 'Simpan Perubahan' : 'Simpan Tukar Size' }}</span>
                <span wire:loading class="flex items-center gap-2">
                    <x-icon name="loader-2" class="size-4 animate-spin" /> Menyimpan...
                </span>
            </button>
        </div>
    </form>
</div>
