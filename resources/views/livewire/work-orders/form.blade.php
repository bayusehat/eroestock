<div class="space-y-6">
    <x-page-header :title="$isEditing ? 'Edit Work Order' : 'Create Work Order'"
                   :description="$isEditing ? 'Ubah work order yang ada' : 'Buat work order baru'" />

    <form wire:submit="save">
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Left: Order Details --}}
            <div class="space-y-6">
                <div class="rounded-lg border bg-card shadow-sm">
                    <div class="border-b p-4">
                        <h3 class="font-semibold">Order Details</h3>
                    </div>
                    <div class="space-y-4 p-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium">Client</label>
                                <x-select wire:model="client_id" placeholder="Pilih client..." :searchable="true"
                                          :options="$clients->pluck('name', 'id')->toArray()" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium">Client WO ID</label>
                                <input wire:model="client_work_order_id" type="text" placeholder="e.g. PO-2026-001"
                                       class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium">Judul <span class="text-destructive">*</span></label>
                            <input wire:model="title" type="text"
                                   class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring @error('title') border-destructive @enderror" />
                            @error('title') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium">Deskripsi</label>
                            <textarea wire:model="description" rows="3"
                                      class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring"></textarea>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium">Kategori</label>
                                <x-select wire:model="category" placeholder="Pilih kategori"
                                          :options="['' => 'Pilih kategori', 'service' => 'Service', 'product' => 'Product', 'consulting' => 'Consulting', 'other' => 'Other']" />
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium">Priority</label>
                                <x-select wire:model="priority"
                                          :options="['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent']" />
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium">Order Date <span class="text-destructive">*</span></label>
                                <input wire:model="order_date" type="date"
                                       class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring @error('order_date') border-destructive @enderror" />
                                @error('order_date') <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-medium">Due Date</label>
                                <input wire:model="due_date" type="date"
                                       class="h-9 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Line Items + Summary --}}
            <div class="space-y-6">
                <div class="rounded-lg border bg-card shadow-sm">
                    <div class="border-b p-4">
                        <h3 class="font-semibold">Line Items</h3>
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
                                    <label class="text-xs font-medium">Description</label>
                                    <input wire:model="items.{{ $i }}.description" type="text" placeholder="Item description"
                                           class="h-8 w-full rounded-md border border-input bg-transparent px-3 text-sm focus:outline-none focus:ring-1 focus:ring-ring" />
                                </div>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-medium">Qty</label>
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
                <span wire:loading.remove>{{ $isEditing ? 'Simpan Perubahan' : 'Buat Work Order' }}</span>
                <span wire:loading class="flex items-center gap-2">
                    <x-icon name="loader-2" class="size-4 animate-spin" /> Menyimpan...
                </span>
            </button>
        </div>
    </form>
</div>
