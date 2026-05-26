<div class="space-y-6">
    <x-page-header title="Purchase Order" description="Kelola daftar Purchase Order">
        <a wire:navigate href="{{ route('purchase-order.create') }}" class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
            <x-icon name="plus" class="size-4" /> Tambah Purchase Order
        </a>
    </x-page-header>
    <input wire:model.live.debounce.300ms="search" type="search" placeholder="Cari purchase order..."
           class="h-9 max-w-xs rounded-md border border-input bg-transparent px-3 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring" />
    <div class="rounded-md border overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50 text-left text-muted-foreground">
                    <th class="px-4 py-3 font-medium">No. Purchase Order</th>
                    <th class="px-4 py-3 font-medium">Supplier</th>
                    <th class="px-4 py-3 font-medium">Keterangan</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchaseOrders as $purchaseOrder)
                     <tr class="border-b hover:bg-muted/30">
                        <td class="px-4 py-3">
                            <a wire:navigate href="{{ route('purchase-order.show', $purchaseOrder) }}" class="font-medium text-primary hover:underline">{{ $item->name }}</a>
                        </td>
                        <td class="px-4 py-3">{{ $purchaseOrder->client?->name }}</td>
                        <td class="px-4 py-3">{{ date('d F Y', strtotime($purchaseOrder->created_at)) }}</td>
                        <td class="px-4 py-3">{{ $purchaseOrder->description }}</td>
                        <td class="px-4 py-3 text-right">
                        <div x-data="{
                                open: false,
                                pos: { top: '0px', left: '0px' },
                                toggle() {
                                    this.open = !this.open;
                                    if (this.open) {
                                        const rect = this.$refs.trigger.getBoundingClientRect();
                                        this.$nextTick(() => {
                                            const menuH = this.$refs.menu.offsetHeight;
                                            const menuW = this.$refs.menu.offsetWidth;
                                            const spaceBelow = window.innerHeight - rect.bottom;
                                            this.pos = {
                                                top: spaceBelow < menuH + 8
                                                    ? (rect.top - menuH - 4) + 'px'
                                                    : (rect.bottom + 4) + 'px',
                                                left: (rect.right - menuW) + 'px',
                                            };
                                        });
                                    }
                                },
                                close() { this.open = false }
                            }">
                                <button x-ref="trigger" @click="toggle()"
                                        class="rounded-md p-1 hover:bg-accent">
                                    <x-icon name="more-horizontal" class="size-4" />
                                </button>
                                <template x-teleport="body">
                                    <div x-show="open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        @click.outside="close()"
                                        x-ref="menu" :style="{ position: 'fixed', top: pos.top, left: pos.left, zIndex: 9999 }"
                                        class="w-40 origin-top-right rounded-lg bg-popover p-1 text-popover-foreground shadow-md ring-1 ring-foreground/10">
                                        <button wire:click="openStatusModal({{ $purchaseOrder->id }})" @click="close()"
                                                class="flex w-full cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                            <x-icon name="refresh-cw" class="size-4" /> Lihat List Item PO
                                        </button>
                                        <a wire:navigate href="{{ route('purchase-order.show', $purchaseOrder) }}"
                                        class="flex cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                            <x-icon name="eye" class="size-4" /> View
                                        </a>
                                        <a wire:navigate href="{{ route('purchase-order.edit', $purchaseOrder) }}"
                                            class="flex cursor-default items-center gap-1.5 rounded-md px-1.5 py-1 text-sm select-none hover:bg-accent hover:text-accent-foreground">
                                            <x-icon name="pencil" class="size-4" /> Edit
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </td>
                     </tr>
                @empty
                     <tr><td colspan="6" class="px-4 py-12 text-center text-muted-foreground">Tidak ada Purchase Order</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>{{ $purchaseOrders->links() }}</div>
</div>
